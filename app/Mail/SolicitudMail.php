<?php

namespace App\Mail;

use App\Models\Solicitud;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class SolicitudMail extends Mailable
{

    use Queueable, SerializesModels;



    public $solicitud;



    public function __construct(
        Solicitud $solicitud
    )
    {

        /*
        Cargar relaciones necesarias
        para mostrar información del usuario
        */

        $this->solicitud = $solicitud->load([
            'usuario'
        ]);

    }




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