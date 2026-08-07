<?php

namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudMail extends Mailable
{
    use Queueable, SerializesModels;

    /*
    |--------------------------------------------------------------------------
    | Solicitud registrada
    |--------------------------------------------------------------------------
    |
    | Contiene la solicitud que será utilizada para construir el correo
    | junto con la información del usuario relacionado.
    |
    */

    public $solicitud;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe la solicitud registrada y carga las relaciones necesarias
    | para disponer de la información del usuario durante la generación
    | del correo.
    |
    */

    public function __construct(
        Solicitud $solicitud
    )
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar relaciones necesarias
        |--------------------------------------------------------------------------
        |
        | Se carga el usuario relacionado con la solicitud para que su
        | información esté disponible dentro de la plantilla del correo.
        |
        */

        $this->solicitud = $solicitud->load([
            'usuario'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Construir correo de solicitud
    |--------------------------------------------------------------------------
    |
    | Define el asunto utilizando el folio de la solicitud y establece
    | la vista que será utilizada para generar el contenido del correo.
    |
    */

    public function build()
    {
        return $this
            ->subject(
                'Nueva solicitud '.$this->solicitud->folio
            )
            ->view(
                'emails.solicitud'
            );
    }
}