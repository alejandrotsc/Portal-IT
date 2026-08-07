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


    /*
    |--------------------------------------------------------------------------
    | Memorando asociado
    |--------------------------------------------------------------------------
    |
    | Contiene el memorando utilizado para construir el correo de
    | autorización y obtener la información que será mostrada
    | dentro de la plantilla y el documento adjunto.
    |
    */


    public Memorando $memorando;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe el memorando correspondiente a la autorización y carga las
    | relaciones necesarias para construir correctamente el correo,
    | evitando consultas adicionales cuando se utilicen posteriormente.
    |
    */


    public function __construct(
        Memorando $memorando
    ) {
        $this->memorando = $memorando;


        $this->memorando->loadMissing([
            'tipo',
            'solicitante',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Configurar encabezado del correo
    |--------------------------------------------------------------------------
    |
    | Define el asunto del correo y, cuando el solicitante posee una
    | dirección válida, la establece como dirección de respuesta
    | para facilitar la comunicación relacionada con la gestión.
    |
    */


    public function envelope(): Envelope
    {
        $replyTo = [];


        /*
        |--------------------------------------------------------------------------
        | Obtener correo del solicitante
        |--------------------------------------------------------------------------
        */


        $correoSolicitante =
            $this->memorando
                ->solicitante
                ?->correo;


        /*
        |--------------------------------------------------------------------------
        | Configurar dirección de respuesta
        |--------------------------------------------------------------------------
        */


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


        /*
        |--------------------------------------------------------------------------
        | Retornar configuración del correo
        |--------------------------------------------------------------------------
        */


        return new Envelope(
            replyTo:
                $replyTo,


            subject:
                'Solicitud de pase mayor a 24 horas'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Construir contenido del correo
    |--------------------------------------------------------------------------
    |
    | Prepara los datos del memorando que serán enviados a la vista del
    | correo, combinando la información adicional almacenada con los
    | datos principales de la autorización.
    |
    */


    public function content(): Content
    {
        /*
        |--------------------------------------------------------------------------
        | Preparar datos de la autorización
        |--------------------------------------------------------------------------
        */


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


        /*
        |--------------------------------------------------------------------------
        | Retornar vista y datos del correo
        |--------------------------------------------------------------------------
        */


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


    /*
    |--------------------------------------------------------------------------
    | Adjuntar documento PDF
    |--------------------------------------------------------------------------
    |
    | Verifica si el memorando posee un archivo PDF generado y disponible
    | en almacenamiento. Si existe, lo adjunta al correo utilizando el
    | código del memorando como nombre del archivo.
    |
    */


    public function attachments(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Obtener ruta del PDF
        |--------------------------------------------------------------------------
        */


        $rutaPdf =
            $this->memorando->archivo_pdf;


        /*
        |--------------------------------------------------------------------------
        | Verificar existencia del documento
        |--------------------------------------------------------------------------
        */


        if (
            ! $rutaPdf
            || ! Storage::exists($rutaPdf)
        ) {
            return [];
        }


        /*
        |--------------------------------------------------------------------------
        | Definir nombre del archivo adjunto
        |--------------------------------------------------------------------------
        */


        $nombrePdf =
            (
                $this->memorando->codigo
                ?? 'MEM-'.$this->memorando->id
            )
            .'.pdf';


        /*
        |--------------------------------------------------------------------------
        | Retornar archivo adjunto
        |--------------------------------------------------------------------------
        */


        return [
            Attachment::fromStorage(
                $rutaPdf
            )
                ->as($nombrePdf)
                ->withMime('application/pdf'),
        ];
    }
}