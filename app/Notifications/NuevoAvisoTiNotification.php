<?php

namespace App\Notifications;

use App\Models\Aviso;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NuevoAvisoTiNotification extends Notification
{
    use Queueable;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe el aviso publicado por el equipo de TI y lo conserva para
    | construir la información que será enviada mediante la notificación.
    |
    */

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
    |
    | Define la información que será persistida dentro del historial de
    | notificaciones del usuario.
    |
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
    |
    | Construye el mensaje que será transmitido en tiempo real para que
    | el usuario reciba inmediatamente el nuevo aviso publicado.
    |
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
    |
    | Define el identificador utilizado por el cliente para reconocer
    | las notificaciones correspondientes a nuevos avisos de TI.
    |
    */

    public function broadcastType(): string
    {
        return 'nuevo-aviso-ti';
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibilidad
    |--------------------------------------------------------------------------
    |
    | Proporciona la representación general de la notificación utilizando
    | los mismos datos empleados por los demás canales disponibles.
    |
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
    |
    | Construye el texto mostrado al usuario utilizando el título del
    | aviso cuando este se encuentra disponible.
    |
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