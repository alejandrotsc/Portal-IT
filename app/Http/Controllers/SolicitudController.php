<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    /**
     * Mostrar formulario para crear una solicitud.
     */
    public function create()
    {
        return view('solicitudes.create');
    }


    /**
     * Guardar la solicitud.
     */
    public function store(Request $request)
    {
        //
    }
}