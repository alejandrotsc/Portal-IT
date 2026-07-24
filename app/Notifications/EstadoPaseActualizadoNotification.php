<?php

namespace App\Notifications;

use App\Models\Memorando;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EstadoPaseActualizadoNotification extends Notification
{
    use Queueable;


    public function __construct(
        private readonly Memorando $memorando
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Canales de notificación
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
    | Información almacenada en la base de datos
    |--------------------------------------------------------------------------
    */

    public function toDatabase(
        object $notifiable
    ): array {
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

            'url' =>
                url(
                    '/mis-pases/'
                    .$this->memorando->id
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Compatibilidad con otros canales
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
    | Código visible del pase
    |--------------------------------------------------------------------------
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