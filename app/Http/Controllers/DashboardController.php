<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{

    public function index()
    {
        $usuario = auth()->user();


        return match($usuario->rol->nombre){

            'Usuario' =>
                view('dashboard.usuario'),


            'UsuarioTI' =>
                view('dashboard.usuarioTI'),


            'Administrador' =>
                view('dashboard.administrador'),


            default =>
                abort(403)

        };
    }

}