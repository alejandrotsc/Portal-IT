<?php

namespace App\Mail;

use App\Models\Incidencia;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class IncidenciaMail extends Mailable
{

    use Queueable, SerializesModels;



    /**
     * Incidencia registrada
     */
    public $incidencia;





    /**
     * Constructor
     */
    public function __construct(
        Incidencia $incidencia
    )
    {

        /*
        Cargar relaciones necesarias
        para que el correo tenga usuario
        y archivos con OCR
        */

        $this->incidencia = $incidencia->load([
            'usuario',
            'archivos'
        ]);

    }







    /**
     * Construcción del correo
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