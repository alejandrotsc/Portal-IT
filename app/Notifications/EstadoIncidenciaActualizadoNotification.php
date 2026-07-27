<?php

namespace App\Notifications;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class EstadoIncidenciaActualizadoNotification extends Notification
{
    use Queueable;


    public function __construct(
        private readonly Incidencia $incidencia
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
            'broadcast',
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
        return $this->datosNotificacion();
    }


    /*
    |--------------------------------------------------------------------------
    | Datos enviados en tiempo real
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
    | Tipo del evento broadcast
    |--------------------------------------------------------------------------
    */

    public function broadcastType(): string
    {
        return 'estado-incidencia-actualizado';
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