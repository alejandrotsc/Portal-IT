<?php

namespace App\Notifications;

use App\Models\Memorando;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NuevoPaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Memorando $memorando
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
    | Datos de la notificación
    |--------------------------------------------------------------------------
    */

    private function datosNotificacion(): array
    {
        $this->memorando->loadMissing([
            'tipo',
            'solicitante',
        ]);

        $nombreSolicitante =
            $this->memorando->solicitante?->nombre
            ?? 'Un usuario';

        $tipoPase =
            $this->memorando->tipo?->slug
            ===
            'pase_temporal'
                ? 'pase menor a 24 horas'
                : 'pase mayor a 24 horas';

        $identificador =
            $this->memorando->codigo
            ?? 'PASE-'.str_pad(
                (string) $this->memorando->id,
                5,
                '0',
                STR_PAD_LEFT
            );

        return [
            'tipo' =>
                'pase_nuevo',

            'titulo' =>
                'Nuevo pase registrado',

            'mensaje' =>
                $nombreSolicitante
                .' registró un '
                .$tipoPase
                .' ('
                .$identificador
                .').',

            'icono' =>
                'badge-check',

            'url' =>
    route(
        'admin.pases.show',
        $this->memorando,
        false
    ),

            'memorando_id' =>
                $this->memorando->id,

            'codigo' =>
                $this->memorando->codigo,

            'identificador' =>
                $identificador,

            'tipo_pase' =>
                $this->memorando->tipo?->slug,

            'tipo_nombre' =>
                $this->memorando->tipo?->nombre,

            'asunto' =>
                $this->memorando->asunto,

            'estado' =>
                $this->memorando->estado,

            'solicitante_id' =>
                $this->memorando->solicitante_id,

            'solicitante_nombre' =>
                $nombreSolicitante,

            'creado_en' =>
                $this->memorando
                    ->created_at
                    ?->timezone(
                        config(
                            'app.timezone',
                            'America/Tegucigalpa'
                        )
                    )
                    ->format(
                        'd/m/Y H:i'
                    ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar en base de datos
    |--------------------------------------------------------------------------
    */

    public function toDatabase(
        object $notifiable
    ): array {
        return $this->datosNotificacion();
    }

    /*
    |--------------------------------------------------------------------------
    | Enviar mediante Reverb
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
    | Tipo de evento broadcast
    |--------------------------------------------------------------------------
    */

    public function broadcastType(): string
    {
        return 'pase.nuevo';
    }
}