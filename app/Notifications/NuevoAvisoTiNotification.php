<?php

namespace App\Notifications;

use App\Models\Aviso;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevoAvisoTiNotification extends Notification
{
    use Queueable;


    public function __construct(
        private readonly Aviso $aviso
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Canales
    |--------------------------------------------------------------------------
    */

    public function via(
        object $notifiable
    ): array {
        return [
            'database',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Datos almacenados
    |--------------------------------------------------------------------------
    */

    public function toDatabase(
        object $notifiable
    ): array {
        return [
            'titulo' =>
                'Nuevo aviso de TI',

            'mensaje' =>
                $this->construirMensaje(),

            'tipo' =>
                'aviso',

            'icono' =>
                'megaphone',

            'estado' =>
                'PUBLICADO',

            'gestion_id' =>
                $this->aviso->id,

            'codigo' =>
                null,

            'aviso_id' =>
                $this->aviso->id,

            'url' =>
                route(
                    'avisos.publicos',
                    [
                        'aviso' =>
                            $this->aviso->id,
                    ]
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Compatibilidad
    |--------------------------------------------------------------------------
    */

    public function toArray(
        object $notifiable
    ): array {
        return $this->toDatabase(
            $notifiable
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mensaje
    |--------------------------------------------------------------------------
    */

    private function construirMensaje(): string
    {
        $titulo = trim(
            (string) $this->aviso->titulo
        );

        if ($titulo === '') {
            return 'El equipo de TI publicó un nuevo aviso.';
        }

        return "El equipo de TI publicó el aviso: {$titulo}.";
    }
}