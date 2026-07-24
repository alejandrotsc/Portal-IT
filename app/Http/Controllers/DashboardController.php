<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Models\Solicitud;
use App\Models\Usuario;
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


        /*
        |--------------------------------------------------------------------------
        | Total de usuarios registrados
        |--------------------------------------------------------------------------
        */

        $totalUsuarios = Usuario::query()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Usuarios conectados
        |--------------------------------------------------------------------------
        |
        | Se considera conectado a un usuario que haya tenido actividad
        | durante los últimos cinco minutos.
        |
        | Se cuentan usuarios únicos y no la cantidad de sesiones.
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
        | Avisos visibles actualmente
        |--------------------------------------------------------------------------
        |
        | El aviso debe:
        |
        | - Estar activo.
        | - Haber alcanzado su fecha de inicio.
        | - No haber superado su fecha de finalización.
        |--------------------------------------------------------------------------
        */

        $avisosActivos = Aviso::query()
            ->where(
                'activo',
                true
            )
            ->where(
                function ($query) use ($ahora) {
                    $query
                        ->whereNull(
                            'fecha_inicio'
                        )
                        ->orWhere(
                            'fecha_inicio',
                            '<=',
                            $ahora
                        );
                }
            )
            ->where(
                function ($query) use ($ahora) {
                    $query
                        ->whereNull(
                            'fecha_fin'
                        )
                        ->orWhere(
                            'fecha_fin',
                            '>=',
                            $ahora
                        );
                }
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Mostrar dashboard
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard.administrador',
            compact(
                'totalUsuarios',
                'usuariosConectados',
                'solicitudesPendientes',
                'avisosActivos'
            )
        );
    }
}