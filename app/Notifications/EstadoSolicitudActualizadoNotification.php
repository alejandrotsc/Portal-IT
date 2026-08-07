<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class EstadoSolicitudActualizadoNotification extends Notification
{
    use Queueable;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe la solicitud cuyo estado fue actualizado y la conserva para
    | construir la información que será enviada en la notificación.
    |
    */

    public function __construct(
        private readonly Solicitud $solicitud
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Canales
    |--------------------------------------------------------------------------
    |
    | Define los canales utilizados para almacenar la notificación en
    | la base de datos y transmitirla en tiempo real al usuario.
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
    | Datos almacenados
    |--------------------------------------------------------------------------
    |
    | Define la información que será persistida en la base de datos
    | como parte del registro de la notificación.
    |
    */

    public function toDatabase(
        object $notifiable
    ): array {
        return $this->datosNotificacion();
    }

    /*
    |--------------------------------------------------------------------------
    | Datos enviados en tiempo real
    |--------------------------------------------------------------------------
    |
    | Construye el mensaje que será transmitido mediante broadcast para
    | actualizar las notificaciones del usuario en tiempo real.
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
    | Tipo de evento broadcast
    |--------------------------------------------------------------------------
    |
    | Define el identificador utilizado por el cliente para reconocer
    | las notificaciones relacionadas con cambios de estado de solicitudes.
    |
    */

    public function broadcastType(): string
    {
        return 'estado-solicitud-actualizado';
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
    | Centraliza la información utilizada por los distintos canales de
    | notificación para mantener una estructura consistente.
    |
    */

    private function datosNotificacion(): array
    {
        return [
            'titulo' =>
                $this->obtenerTitulo(),

            'mensaje' =>
                $this->obtenerMensaje(),

            'tipo' =>
                'solicitud',

            'icono' =>
                $this->obtenerIcono(),

            'estado' =>
                $this->solicitud->estado,

            'gestion_id' =>
                $this->solicitud->id,

            'solicitud_id' =>
                $this->solicitud->id,

            'codigo' =>
                $this->solicitud->folio,

            'url' =>
                route(
                    'solicitudes.show',
                    [
                        'solicitud' =>
                            $this->solicitud->id,
                    ]
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Título
    |--------------------------------------------------------------------------
    |
    | Determina el título que será mostrado en la notificación según
    | el estado actual de la solicitud.
    |
    */

    private function obtenerTitulo(): string
    {
        return match (
            $this->solicitud->estado
        ) {
            Solicitud::ESTADO_FINALIZADA =>
                'Solicitud finalizada',

            Solicitud::ESTADO_CANCELADA =>
                'Solicitud cancelada',

            Solicitud::ESTADO_PENDIENTE =>
                'Solicitud pendiente',

            default =>
                'Estado de solicitud actualizado',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Mensaje
    |--------------------------------------------------------------------------
    |
    | Construye el mensaje mostrado al usuario utilizando el folio y
    | el estado actual de la solicitud.
    |
    */

    private function obtenerMensaje(): string
    {
        $folio =
            $this->solicitud->folio
            ?? 'SOL-'.$this->solicitud->id;

        return match (
            $this->solicitud->estado
        ) {
            Solicitud::ESTADO_FINALIZADA =>
                "Tu solicitud {$folio} fue marcada como finalizada.",

            Solicitud::ESTADO_CANCELADA =>
                "Tu solicitud {$folio} fue cancelada.",

            Solicitud::ESTADO_PENDIENTE =>
                "Tu solicitud {$folio} se encuentra pendiente de atención.",

            default =>
                "El estado de tu solicitud {$folio} fue actualizado.",
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Icono Lucide
    |--------------------------------------------------------------------------
    |
    | Determina el icono utilizado visualmente para representar el
    | estado actual de la solicitud dentro de la notificación.
    |
    */

    private function obtenerIcono(): string
    {
        return match (
            $this->solicitud->estado
        ) {
            Solicitud::ESTADO_FINALIZADA =>
                'circle-check',

            Solicitud::ESTADO_CANCELADA =>
                'circle-x',

            Solicitud::ESTADO_PENDIENTE =>
                'clock-3',

            default =>
                'clipboard-list',
        };
    }
}