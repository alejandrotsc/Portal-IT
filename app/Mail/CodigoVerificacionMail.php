<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CodigoVerificacionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $codigo
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de verificación - Portal TI'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.codigo_verificacion',
            with: [
                'nombre' => $this->usuario->nombre,
                'correo' => $this->usuario->correo,
                'codigo' => $this->codigo,
                'minutosExpiracion' => 5,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}