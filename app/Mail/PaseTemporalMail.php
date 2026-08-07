<?php

namespace App\Mail;

use App\Models\Memorando;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaseTemporalMail extends Mailable
{
    use Queueable, SerializesModels;

    /*
    |--------------------------------------------------------------------------
    | Memorando asociado
    |--------------------------------------------------------------------------
    |
    | Contiene el memorando correspondiente al pase temporal que será
    | utilizado para construir el contenido y configurar el correo.
    |
    */

    public Memorando $memorando;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe el memorando relacionado con la solicitud de pase temporal
    | y lo almacena para utilizarlo durante la construcción del correo.
    |
    */

    public function __construct(
        Memorando $memorando
    )
    {
        $this->memorando = $memorando;
    }

    /*
    |--------------------------------------------------------------------------
    | Configurar encabezado del correo
    |--------------------------------------------------------------------------
    |
    | Define el asunto utilizado para identificar las solicitudes de
    | pase temporal con duración menor a 24 horas.
    |
    */

    public function envelope(): Envelope
    {
        return new Envelope(
            subject:
                'Solicitud de pase menor a 24 horas'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Construir contenido del correo
    |--------------------------------------------------------------------------
    |
    | Define la vista del correo y proporciona los datos adicionales
    | del memorando junto con la información del solicitante.
    |
    */

    public function content(): Content
    {
        return new Content(
            view:
                'emails.pase_temporal',

            with: [
                'datos' =>
                    $this->memorando->datos_extra ?? [],

                'remitenteName' =>
                    $this->memorando->solicitante->nombre ?? 'N/A',

                'remitenteEmail' =>
                    $this->memorando->solicitante->correo ?? 'N/A',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Configurar dirección de respuesta
    |--------------------------------------------------------------------------
    |
    | Establece el correo y nombre del solicitante como dirección de
    | respuesta para facilitar la comunicación sobre la gestión.
    |
    */

    public function build()
    {
        return $this->replyTo(
            $this->memorando->solicitante->correo,
            $this->memorando->solicitante->nombre
        );
    }
}