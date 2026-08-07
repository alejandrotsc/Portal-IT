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


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe el usuario al que se enviará el correo y el código de
    | verificación generado para completar el proceso de validación.
    |
    */


    public function __construct(
        public Usuario $usuario,
        public string $codigo
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Configurar encabezado del correo
    |--------------------------------------------------------------------------
    |
    | Define el asunto que será utilizado en el correo de verificación
    | enviado al usuario desde el Portal TI.
    |
    */


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de verificación - Portal TI'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Construir contenido del correo
    |--------------------------------------------------------------------------
    |
    | Define la vista utilizada por el correo y proporciona los datos
    | necesarios para mostrar la información del usuario, el código
    | de verificación y su tiempo de expiración.
    |
    */


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