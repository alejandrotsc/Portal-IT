<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnlaceMagicoMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $url
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Acceso al Portal TI'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enlace_magico',
            with: [
                'nombre' => $this->usuario->nombre,
                'correo' => $this->usuario->correo,
                'url' => $this->url,
                'minutosExpiracion' => 10,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}