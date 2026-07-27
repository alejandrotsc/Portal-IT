<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NuevaSolicitudNotification extends Notification implements ShouldQueue
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
        $this->solicitud->loadMissing(
            'usuario'
        );

        $nombreUsuario =
            $this->solicitud->usuario?->nombre
            ?? 'Un usuario';

        return [
            'tipo' =>
                'solicitud_nueva',

            'titulo' =>
                'Nueva solicitud registrada',

            'mensaje' =>
                $nombreUsuario
                .' registró la solicitud '
                .$this->solicitud->folio
                .': '
                .$this->solicitud->asunto,

            'icono' =>
                'clipboard-list',

            'url' =>
    route(
        'admin.solicitudes.show',
        $this->solicitud,
        false
    ),

            'solicitud_id' =>
                $this->solicitud->id,

            'folio' =>
                $this->solicitud->folio,

            'categoria' =>
                $this->solicitud->categoria,

            'asunto' =>
                $this->solicitud->asunto,

            'estado' =>
                $this->solicitud->estado,

            'usuario_id' =>
                $this->solicitud->usuario_id,

            'usuario_nombre' =>
                $nombreUsuario,

            'creada_en' =>
                $this->solicitud
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
        return 'solicitud.nueva';
    }
}