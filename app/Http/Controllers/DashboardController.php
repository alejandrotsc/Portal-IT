<?php

namespace App\Http\Controllers;

use App\Models\GuardiaSoporte;
use App\Models\Incidencia;
use App\Models\Memorando;
use App\Models\Solicitud;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Mostrar dashboard según el rol
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {
        $usuario = $request->user();

        return match ($usuario->rol?->nombre) {
            'Usuario' =>
                $this->dashboardUsuario(),

            'UsuarioTI',
            'Administrador' =>
                $this->dashboardAdministrativo(),

            default =>
                abort(
                    403,
                    'No tienes permiso para acceder al dashboard.'
                ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard del usuario
    |--------------------------------------------------------------------------
    */

    private function dashboardUsuario(): View {
        /*
        |--------------------------------------------------------------------------
        | Zona horaria
        |--------------------------------------------------------------------------
        */

        $zonaHoraria = config(
            'app.timezone',
            'America/Tegucigalpa'
        );

        /*
        |--------------------------------------------------------------------------
        | Fecha y hora actual
        |--------------------------------------------------------------------------
        */

        $ahora = Carbon::now(
            $zonaHoraria
        );

        /*
        |--------------------------------------------------------------------------
        | Inicio del día evaluado
        |--------------------------------------------------------------------------
        */

        $hoy = $ahora
            ->copy()
            ->startOfDay();

        $fechaHoy = $hoy->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Comprobar si es fin de semana
        |--------------------------------------------------------------------------
        */

        $esFinDeSemana = $hoy->isWeekend();

        /*
        |--------------------------------------------------------------------------
        | Guardia asignada para hoy
        |--------------------------------------------------------------------------
        */

        $guardiaHoy = null;

        if ($esFinDeSemana) {
            $guardiaHoy = GuardiaSoporte::query()
                ->with([
                    'agente.rol',
                ])
                ->activas()
                ->whereDate(
                    'fecha',
                    $fechaHoy
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Disponibilidad actual del agente
        |--------------------------------------------------------------------------
        */

        $guardiaDisponibleAhora = false;

        $inicioGuardia = null;
        $finGuardia = null;

        if ($guardiaHoy) {
            $fechaGuardia = $guardiaHoy
                ->fecha
                ->toDateString();

            $horaInicio = substr(
                (string) $guardiaHoy->hora_inicio,
                0,
                8
            );

            $horaFin = substr(
                (string) $guardiaHoy->hora_fin,
                0,
                8
            );

            $inicioGuardia = Carbon::parse(
                $fechaGuardia
                    . ' '
                    . $horaInicio,
                $zonaHoraria
            );

            $finGuardia = Carbon::parse(
                $fechaGuardia
                    . ' '
                    . $horaFin,
                $zonaHoraria
            );

            $guardiaDisponibleAhora = $ahora
                ->betweenIncluded(
                    $inicioGuardia,
                    $finGuardia
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Próximas dos fechas de guardia
        |--------------------------------------------------------------------------
        |
        | Si hoy es sábado:
        |
        | - Domingo inmediato.
        | - Sábado siguiente.
        |
        | Si hoy es domingo o día laboral:
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

            $segundaFechaGuardia = $primeraFechaGuardia
                ->copy()
                ->addDay();
        }

        $proximasFechasGuardia = collect([
            $primeraFechaGuardia,
            $segundaFechaGuardia,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Guardias asignadas para las próximas fechas
        |--------------------------------------------------------------------------
        */

        $fechasConsulta = $proximasFechasGuardia
            ->map(
                fn (Carbon $fecha): string =>
                    $fecha->toDateString()
            )
            ->values()
            ->all();

        $guardiasProximas = GuardiaSoporte::query()
            ->with([
                'agente.rol',
            ])
            ->activas()
            ->whereIn(
                'fecha',
                $fechasConsulta
            )
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->keyBy(
                fn (GuardiaSoporte $guardia): string =>
                    $guardia
                        ->fecha
                        ->toDateString()
            );

        /*
        |--------------------------------------------------------------------------
        | Mostrar dashboard del usuario
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Mostrar dashboard administrativo
        |--------------------------------------------------------------------------
        */

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