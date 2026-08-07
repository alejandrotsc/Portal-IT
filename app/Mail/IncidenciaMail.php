<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IncidenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    /*
    |--------------------------------------------------------------------------
    | Incidencia registrada
    |--------------------------------------------------------------------------
    |
    | Contiene la incidencia que será utilizada para construir el correo,
    | incluyendo la información del usuario y las evidencias asociadas.
    |
    */

    public $incidencia;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe la incidencia registrada y carga las relaciones necesarias
    | para disponer de la información del usuario y de los archivos
    | adjuntos asociados a la gestión.
    |
    */

    public function __construct(
        Incidencia $incidencia
    )
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar relaciones necesarias
        |--------------------------------------------------------------------------
        |
        | Se cargan el usuario relacionado y los archivos de evidencia
        | para que estén disponibles durante la construcción del correo.
        |
        */

        $this->incidencia = $incidencia->load([
            'usuario',
            'archivos'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Construir correo de incidencia
    |--------------------------------------------------------------------------
    |
    | Define el asunto y la vista del correo correspondiente a la
    | incidencia registrada y posteriormente adjunta las evidencias
    | disponibles en almacenamiento.
    |
    */

    public function build()
    {
        $mail = $this
            ->subject(
                'Nueva incidencia '.$this->incidencia->codigo
            )
            ->view(
                'emails.incidencia'
            );

        /*
        |--------------------------------------------------------------------------
        | Adjuntar evidencias
        |--------------------------------------------------------------------------
        |
        | Recorre los archivos relacionados con la incidencia y adjunta
        | aquellos que se encuentren disponibles físicamente dentro
        | del almacenamiento público de la aplicación.
        |
        */

        foreach(
            $this->incidencia->archivos as $archivo
        ){
            $ruta = storage_path(
                'app/public/'.$archivo->ruta
            );

            if(
                file_exists($ruta)
            ){
                $mail->attach(
                    $ruta,
                    [
                        'as' =>
                            $archivo->nombre_original
                    ]
                );
            }
        }

        return $mail;
    }
}