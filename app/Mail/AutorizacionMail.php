<?php

namespace App\Mail;

use App\Models\Memorando;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AutorizacionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public Memorando $memorando;

    public function __construct(
        Memorando $memorando
    ) {
        $this->memorando = $memorando;

        $this->memorando->loadMissing([
            'tipo',
            'solicitante',
        ]);
    }

    public function envelope(): Envelope
    {
        $replyTo = [];

        $correoSolicitante =
            $this->memorando
                ->solicitante
                ?->correo;

        if (
            is_string($correoSolicitante)
            && filter_var(
                $correoSolicitante,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $replyTo[] = new Address(
                $correoSolicitante,
                $this->memorando
                    ->solicitante
                    ?->nombre
                    ?? 'Solicitante'
            );
        }

        return new Envelope(
            replyTo:
                $replyTo,

            subject:
                'Solicitud de pase mayor a 24 horas'
        );
    }

    public function content(): Content
    {
        $datos = array_merge(
            $this->memorando->datos_extra
                ?? [],

            [
                'para_nombre' =>
                    $this->memorando->para_nombre,

                'cc_nombre' =>
                    $this->memorando->cc_nombre,

                'de_nombre' =>
                    $this->memorando->de_nombre,

                'asunto' =>
                    $this->memorando->asunto,

                'fecha_documento' =>
                    $this->memorando
                        ->fecha_documento
                        ?->format('Y-m-d'),

                'observaciones' =>
                    $this->memorando->observaciones,
            ]
        );

        return new Content(
            view:
                'emails.autorizacion',

            with: [
                'memorando' =>
                    $this->memorando,

                'datos' =>
                    $datos,

                'remitenteName' =>
                    $this->memorando
                        ->solicitante
                        ?->nombre
                    ?? 'N/A',

                'remitenteEmail' =>
                    $this->memorando
                        ->solicitante
                        ?->correo
                    ?? 'N/A',
            ]
        );
    }

    public function attachments(): array
    {
        $rutaPdf =
            $this->memorando->archivo_pdf;

        if (
            ! $rutaPdf
            || ! Storage::exists($rutaPdf)
        ) {
            return [];
        }

        $nombrePdf =
            (
                $this->memorando->codigo
                ?? 'MEM-'.$this->memorando->id
            )
            .'.pdf';

        return [
            Attachment::fromStorage(
                $rutaPdf
            )
                ->as($nombrePdf)
                ->withMime('application/pdf'),
        ];
    }
}