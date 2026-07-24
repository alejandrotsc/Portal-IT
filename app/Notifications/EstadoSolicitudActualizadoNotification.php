<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EstadoSolicitudActualizadoNotification extends Notification
{
    use Queueable;


    public function __construct(
        private readonly Solicitud $solicitud
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

            'codigo' =>
                $this->solicitud->folio,

            'url' =>
                url(
                    '/solicitudes/'
                    .$this->solicitud->id
                ),
        ];
    }


    public function toArray(
        object $notifiable
    ): array {
        return $this->toDatabase(
            $notifiable
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Título
    |--------------------------------------------------------------------------
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