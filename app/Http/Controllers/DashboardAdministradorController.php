<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardAdministradorController extends Controller
{
    public function index(): View
    {
        $limiteActividad = now()
            ->subMinutes(5)
            ->timestamp;

        $usuariosConectados = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $limiteActividad)
            ->distinct('user_id')
            ->count('user_id');

        return view('dashboard.administrador', [
            'totalUsuarios' => Usuario::count(),

            'usuariosConectados' => $usuariosConectados,

            'avisosActivos' => Aviso::where(
                'activo',
                true
            )->count(),
        ]);
    }
}