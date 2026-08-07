<?php

namespace App\Notifications;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class EstadoIncidenciaActualizadoNotification extends Notification
{
    use Queueable;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe la incidencia cuyo estado fue actualizado y la conserva
    | para construir los datos que serán enviados en la notificación.
    |
    */

    public function __construct(
        private readonly Incidencia $incidencia
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
    | Tipo del evento broadcast
    |--------------------------------------------------------------------------
    |
    | Define el identificador utilizado por el cliente para reconocer
    | las notificaciones correspondientes a cambios de incidencia.
    |
    */

    public function broadcastType(): string
    {
        return 'estado-incidencia-actualizado';
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibilidad
    |--------------------------------------------------------------------------
    |
    | Proporciona la representación general de la notificación utilizando
    | los mismos datos empleados por los demás canales.
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
                'incidencia',

            'icono' =>
                $this->obtenerIcono(),

            'estado' =>
                $this->incidencia->estado,

            'gestion_id' =>
                $this->incidencia->id,

            'incidencia_id' =>
                $this->incidencia->id,

            'codigo' =>
                $this->incidencia->codigo,

            'url' =>
                route(
                    'incidencias.show',
                    [
                        'incidencia' =>
                            $this->incidencia->id,
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
    | el estado actual de la incidencia.
    |
    */

    private function obtenerTitulo(): string
    {
        return match (
            $this->incidencia->estado
        ) {
            Incidencia::ESTADO_EN_PROCESO =>
                'Incidencia en proceso',

            Incidencia::ESTADO_RESUELTA =>
                'Incidencia resuelta',

            Incidencia::ESTADO_ABIERTA =>
                'Incidencia reabierta',

            default =>
                'Estado de incidencia actualizado',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Mensaje
    |--------------------------------------------------------------------------
    |
    | Construye el mensaje mostrado al usuario utilizando el código y
    | el estado actual de la incidencia.
    |
    */

    private function obtenerMensaje(): string
    {
        $codigo =
            $this->incidencia->codigo
            ?? 'INC-'.$this->incidencia->id;

        return match (
            $this->incidencia->estado
        ) {
            Incidencia::ESTADO_EN_PROCESO =>
                "Tu incidencia {$codigo} está siendo atendida por el equipo de TI.",

            Incidencia::ESTADO_RESUELTA =>
                "Tu incidencia {$codigo} fue marcada como resuelta.",

            Incidencia::ESTADO_ABIERTA =>
                "Tu incidencia {$codigo} fue reabierta y está pendiente de atención.",

            default =>
                "El estado de tu incidencia {$codigo} fue actualizado.",
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Icono Lucide
    |--------------------------------------------------------------------------
    |
    | Determina el icono que será utilizado visualmente para representar
    | el estado actual de la incidencia dentro de la notificación.
    |
    */

    private function obtenerIcono(): string
    {
        return match (
            $this->incidencia->estado
        ) {
            Incidencia::ESTADO_EN_PROCESO =>
                'loader-circle',

            Incidencia::ESTADO_RESUELTA =>
                'circle-check',

            Incidencia::ESTADO_ABIERTA =>
                'rotate-ccw',

            default =>
                'circle-dot',
        };
    }
}