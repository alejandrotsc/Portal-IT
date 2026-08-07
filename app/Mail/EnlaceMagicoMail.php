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


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe el usuario al que se enviará el correo y la URL temporal
    | utilizada para permitir el acceso al Portal TI mediante un
    | enlace mágico.
    |
    */


    public function __construct(
        public Usuario $usuario,
        public string $url
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Configurar encabezado del correo
    |--------------------------------------------------------------------------
    |
    | Define el asunto utilizado en el correo que contiene el enlace
    | temporal de acceso al Portal TI.
    |
    */


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Acceso al Portal TI'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Construir contenido del correo
    |--------------------------------------------------------------------------
    |
    | Define la vista utilizada por el correo y proporciona los datos
    | necesarios para mostrar la información del usuario, el enlace
    | de acceso y su tiempo de expiración.
    |
    */


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


    /*
    |--------------------------------------------------------------------------
    | Archivos adjuntos
    |--------------------------------------------------------------------------
    |
    | Este correo no requiere archivos adjuntos, por lo que se retorna
    | un arreglo vacío.
    |
    */


    public function attachments(): array
    {
        return [];
    }
}