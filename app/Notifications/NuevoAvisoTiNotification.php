<?php

namespace App\Notifications;

use App\Models\Aviso;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
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
    |
    | database:
    | Guarda la notificación para mostrarla en el historial y la campana.
    |
    | broadcast:
    | Envía la notificación en tiempo real mediante Laravel Reverb.
    |
    */

    public function via(
        object $notifiable
    ): array {
        return [
            'database',
            'broadcast',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Datos almacenados en la base de datos
    |--------------------------------------------------------------------------
    */

    public function toDatabase(
        object $notifiable
    ): array {
        return $this->datosNotificacion();
    }


    /*
    |--------------------------------------------------------------------------
    | Datos transmitidos mediante Reverb
    |--------------------------------------------------------------------------
    */

    public function toBroadcast(
        object $notifiable
    ): BroadcastMessage {
        return new BroadcastMessage(
            $this->datosNotificacion()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Tipo de notificación transmitida
    |--------------------------------------------------------------------------
    */

    public function broadcastType(): string
    {
        return 'nuevo-aviso-ti';
    }


    /*
    |--------------------------------------------------------------------------
    | Compatibilidad
    |--------------------------------------------------------------------------
    */

    public function toArray(
        object $notifiable
    ): array {
        return $this->datosNotificacion();
    }


    /*
    |--------------------------------------------------------------------------
    | Datos comunes
    |--------------------------------------------------------------------------
    |
    | Se utiliza el mismo formato para database y broadcast para evitar que
    | la campana reciba información diferente a la almacenada.
    |
    */

    private function datosNotificacion(): array
    {
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