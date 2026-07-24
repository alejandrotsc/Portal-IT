<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use App\Models\Memorando;
use App\Models\Solicitud;
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

        return match(
            $usuario->rol?->nombre
        ) {
            'Usuario' =>
                view(
                    'dashboard.usuario'
                ),

            'UsuarioTI' =>
                view(
                    'dashboard.usuarioTI'
                ),

            'Administrador' =>
                $this->dashboardAdministrador(),

            default =>
                abort(403),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard administrativo
    |--------------------------------------------------------------------------
    */

    private function dashboardAdministrador(): View
    {
        $ahora = now();

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
        |
        | Solo se cuentan las incidencias que todavía no han comenzado
        | su proceso de atención.
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