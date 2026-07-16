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


    public Memorando $memorando;



    public function __construct(
        Memorando $memorando
    )
    {
        $this->memorando = $memorando;
    }






    public function envelope(): Envelope
    {

        return new Envelope(

            subject:
                'Solicitud de pase menor a 24 horas'

        );

    }






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






    public function build()
    {

        return $this->replyTo(

            $this->memorando->solicitante->correo,

            $this->memorando->solicitante->nombre

        );

    }


}