<?php

namespace App\Http\Controllers;

use App\Models\GuardiaSoporte;
use App\Models\Incidencia;
use App\Models\Memorando;
use App\Models\Solicitud;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Mostrar dashboard según el rol
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $usuario = auth()->user();

        return match ($usuario->rol?->nombre) {
            'Usuario' =>
                $this->dashboardUsuario(),

            'UsuarioTI',
            'Administrador' =>
                $this->dashboardAdministrativo(),

            default =>
                abort(403),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Dashboard del usuario
    |--------------------------------------------------------------------------
    */

    private function dashboardUsuario(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Fecha y hora actual
        |--------------------------------------------------------------------------
        */

        $ahora = now();

        $hoy = $ahora
            ->copy()
            ->startOfDay();


        /*
        |--------------------------------------------------------------------------
        | Comprobar si es fin de semana
        |--------------------------------------------------------------------------
        */

        $esFinDeSemana =
            $hoy->isSaturday()
            || $hoy->isSunday();


        /*
        |--------------------------------------------------------------------------
        | Guardia asignada para hoy
        |--------------------------------------------------------------------------
        |
        | Solo se consulta cuando hoy es sábado o domingo.
        |
        */

        $guardiaHoy = null;

        if ($esFinDeSemana) {
            $guardiaHoy = GuardiaSoporte::query()
                ->with([
                    'agente.rol',
                ])
                ->where(
                    'activo',
                    true
                )
                ->whereDate(
                    'fecha',
                    $hoy
                )
                ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Disponibilidad actual del agente
        |--------------------------------------------------------------------------
        |
        | Comprueba si la hora actual está dentro del horario programado.
        |
        */

        $guardiaDisponibleAhora = false;

        if ($guardiaHoy) {
            $inicioGuardia = Carbon::parse(
                $guardiaHoy
                    ->fecha
                    ->format('Y-m-d')
                .' '
                .$guardiaHoy->hora_inicio,
                config('app.timezone')
            );

            $finGuardia = Carbon::parse(
                $guardiaHoy
                    ->fecha
                    ->format('Y-m-d')
                .' '
                .$guardiaHoy->hora_fin,
                config('app.timezone')
            );

            $guardiaDisponibleAhora =
                $ahora->between(
                    $inicioGuardia,
                    $finGuardia,
                    true
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Próximas dos fechas de guardia
        |--------------------------------------------------------------------------
        |
        | De lunes a viernes:
        |
        | - Próximo sábado.
        | - Domingo siguiente.
        |
        | Si hoy es sábado:
        |
        | - Domingo siguiente.
        | - Sábado de la próxima semana.
        |
        | Si hoy es domingo:
        |
        | - Próximo sábado.
        | - Domingo siguiente.
        |
        */

        if ($hoy->isSaturday()) {
            $primeraFechaGuardia = $hoy
                ->copy()
                ->addDay();

            $segundaFechaGuardia = $hoy
                ->copy()
                ->next(
                    Carbon::SATURDAY
                );
        } else {
            $primeraFechaGuardia = $hoy
                ->copy()
                ->next(
                    Carbon::SATURDAY
                );

            $segundaFechaGuardia =
                $primeraFechaGuardia
                    ->copy()
                    ->addDay();
        }


        $proximasFechasGuardia = collect([
            $primeraFechaGuardia,
            $segundaFechaGuardia,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Guardias de las próximas fechas
        |--------------------------------------------------------------------------
        */

        $guardiasProximas = GuardiaSoporte::query()
            ->with([
                'agente.rol',
            ])
            ->where(
                'activo',
                true
            )
            ->whereIn(
                'fecha',
                $proximasFechasGuardia
                    ->map(
                        fn (Carbon $fecha) =>
                            $fecha->format('Y-m-d')
                    )
                    ->all()
            )
            ->get()
            ->keyBy(
                fn (GuardiaSoporte $guardia) =>
                    $guardia
                        ->fecha
                        ->format('Y-m-d')
            );


        return view(
            'dashboard.usuario',
            compact(
                'ahora',
                'hoy',
                'esFinDeSemana',
                'guardiaHoy',
                'guardiaDisponibleAhora',
                'proximasFechasGuardia',
                'guardiasProximas'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Dashboard administrativo
    |--------------------------------------------------------------------------
    |
    | Es compartido por UsuarioTI y Administrador.
    | Las acciones disponibles se restringen según el rol.
    |
    */

    private function dashboardAdministrativo(): View
    {
        $ahora = now();


        /*
        |--------------------------------------------------------------------------
        | Usuarios conectados
        |--------------------------------------------------------------------------
        */

        $usuariosConectados = DB::table(
            'sessions'
        )
            ->join(
                'usuarios',
                'usuarios.id',
                '=',
                'sessions.user_id'
            )
            ->where(
                'usuarios.activo',
                true
            )
            ->whereNotNull(
                'sessions.user_id'
            )
            ->where(
                'sessions.last_activity',
                '>=',
                $ahora
                    ->copy()
                    ->subMinutes(5)
                    ->timestamp
            )
            ->distinct()
            ->count(
                'sessions.user_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Solicitudes pendientes
        |--------------------------------------------------------------------------
        */

        $solicitudesPendientes = Solicitud::query()
            ->where(
                'estado',
                Solicitud::ESTADO_PENDIENTE
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Incidencias abiertas
        |--------------------------------------------------------------------------
        */

        $incidenciasAbiertas = Incidencia::query()
            ->where(
                'estado',
                Incidencia::ESTADO_ABIERTA
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Pases por revisar
        |--------------------------------------------------------------------------
        */

        $pasesPorRevisar = Memorando::query()
            ->where(
                'estado',
                Memorando::ESTADO_GENERADO
            )
            ->whereHas(
                'tipo',
                function ($query) {
                    $query->whereIn(
                        'slug',
                        [
                            'pase_temporal',
                            'autorizacion',
                        ]
                    );
                }
            )
            ->count();


        return view(
            'dashboard.administrador',
            compact(
                'usuariosConectados',
                'pasesPorRevisar',
                'solicitudesPendientes',
                'incidenciasAbiertas'
            )
        );
    }
}