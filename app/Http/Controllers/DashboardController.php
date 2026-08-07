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

/*
|--------------------------------------------------------------------------
| Controlador de dashboards
|--------------------------------------------------------------------------
|
| Selecciona el panel correspondiente según el rol autenticado y prepara la
| información necesaria para el dashboard del usuario y el dashboard
| administrativo del Portal TI.
|
*/

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Mostrar dashboard según el rol
    |--------------------------------------------------------------------------
    |
    | Determina qué dashboard debe visualizar el usuario autenticado según el nombre de su rol y bloquea cualquier rol no autorizado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Resolver dashboard del usuario
    |--------------------------------------------------------------------------
    |
    | Evalúa el rol autenticado y delega la construcción de la vista al método
    | correspondiente.
    |
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
    |
    | Prepara la información de soporte de turno, disponibilidad actual y próximas guardias que se mostrará al usuario final.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Construir dashboard del usuario
    |--------------------------------------------------------------------------
    |
    | Calcula la información de guardias, disponibilidad y próximas fechas de
    | soporte antes de renderizar la vista del usuario.
    |
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
    | Prepara los indicadores principales utilizados por el panel compartido
    | entre UsuarioTI y Administrador. Las acciones disponibles continúan
    | restringiéndose según el rol autenticado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Construir dashboard administrativo
    |--------------------------------------------------------------------------
    |
    | Calcula usuarios conectados y métricas operativas principales antes de
    | renderizar el panel administrativo.
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