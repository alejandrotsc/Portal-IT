<?php

namespace App\Notifications;

use App\Models\Memorando;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class EstadoPaseActualizadoNotification extends Notification
{
    use Queueable;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe el memorando correspondiente al pase cuyo estado fue
    | actualizado y lo conserva para construir la notificación.
    |
    */

    public function __construct(
        private readonly Memorando $memorando
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Canales de notificación
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
    | Información almacenada en la base de datos
    |--------------------------------------------------------------------------
    |
    | Define los datos que serán persistidos como parte del registro
    | de la notificación asociada al cambio de estado del pase.
    |
    */

    public function toDatabase(
        object $notifiable
    ): array {
        return $this->datosNotificacion();
    }

    /*
    |--------------------------------------------------------------------------
    | Información enviada en tiempo real
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
    | Tipo de evento transmitido
    |--------------------------------------------------------------------------
    |
    | Define el identificador utilizado por el cliente para reconocer
    | las notificaciones relacionadas con cambios de estado de pases.
    |
    */

    public function broadcastType(): string
    {
        return 'estado-pase-actualizado';
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibilidad con otros canales
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
    | Datos comunes de la notificación
    |--------------------------------------------------------------------------
    |
    | Centraliza la información utilizada por los distintos canales para
    | mantener una estructura consistente en toda la notificación.
    |
    */

    private function datosNotificacion(): array
    {
        $codigo = $this->obtenerCodigo();

        $estadoTexto = $this->obtenerEstadoTexto();

        return [
            'titulo' =>
                $this->obtenerTitulo(),

            'mensaje' =>
                "Tu pase {$codigo} fue {$estadoTexto}.",

            'tipo' =>
                'pase',

            'icono' =>
                $this->obtenerIcono(),

            'estado' =>
                $this->memorando->estado,

            'gestion_id' =>
                $this->memorando->id,

            'codigo' =>
                $codigo,

            'memorando_id' =>
                $this->memorando->id,

            'url' =>
                route(
                    'memorandos.show-pase',
                    [
                        'memorando' =>
                            $this->memorando->id,
                    ]
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Código visible del pase
    |--------------------------------------------------------------------------
    |
    | Obtiene el código asignado al memorando o genera una representación
    | alternativa utilizando su identificador cuando no existe un código.
    |
    */

    private function obtenerCodigo(): string
    {
        return $this->memorando->codigo
            ?: 'PASE-'.str_pad(
                (string) $this->memorando->id,
                5,
                '0',
                STR_PAD_LEFT
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Título
    |--------------------------------------------------------------------------
    |
    | Determina el título mostrado en la notificación según el estado
    | actual del pase.
    |
    */

    private function obtenerTitulo(): string
    {
        return match (
            $this->memorando->estado
        ) {
            Memorando::ESTADO_APROBADO =>
                'Pase aprobado',

            Memorando::ESTADO_RECHAZADO =>
                'Pase rechazado',

            default =>
                'Estado de pase actualizado',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Estado legible
    |--------------------------------------------------------------------------
    |
    | Convierte el estado interno del memorando en un texto comprensible
    | que pueda ser utilizado directamente dentro de la notificación.
    |
    */

    private function obtenerEstadoTexto(): string
    {
        return match (
            $this->memorando->estado
        ) {
            Memorando::ESTADO_APROBADO =>
                'aprobado',

            Memorando::ESTADO_RECHAZADO =>
                'rechazado',

            Memorando::ESTADO_GENERADO =>
                'registrado y está pendiente de revisión',

            default =>
                mb_strtolower(
                    str_replace(
                        '_',
                        ' ',
                        $this->memorando->estado
                    )
                ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Icono Lucide
    |--------------------------------------------------------------------------
    |
    | Determina el icono utilizado visualmente para representar el
    | estado actual del pase dentro de la notificación.
    |
    */

    private function obtenerIcono(): string
    {
        return match (
            $this->memorando->estado
        ) {
            Memorando::ESTADO_APROBADO =>
                'badge-check',

            Memorando::ESTADO_RECHAZADO =>
                'circle-x',

            default =>
                'clock-3',
        };
    }
}