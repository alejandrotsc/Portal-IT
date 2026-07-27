<?php

namespace App\Notifications;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NuevaIncidenciaNotification extends Notification implements ShouldQueue
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

    public function via(object $notifiable): array
    {
        return [
            'database',
            'broadcast',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Información compartida
    |--------------------------------------------------------------------------
    */

    private function datosNotificacion(): array
    {
        $this->incidencia->loadMissing('usuario');

        $nombreUsuario =
            $this->incidencia->usuario?->nombre
            ?? 'Un usuario';

        return [
            'tipo' =>
                'incidencia_nueva',

            'titulo' =>
                'Nueva incidencia registrada',

            'mensaje' =>
                $nombreUsuario
                .' registró la incidencia '
                .$this->incidencia->codigo
                .': '
                .$this->incidencia->titulo,

            'icono' =>
                'triangle-alert',

            'url' =>
    route(
        'admin.incidencias.show',
        $this->incidencia,
        false
    ),

            'incidencia_id' =>
                $this->incidencia->id,

            'codigo' =>
                $this->incidencia->codigo,

            'estado' =>
                $this->incidencia->estado,

            'prioridad' =>
                $this->incidencia->prioridad,

            'usuario_id' =>
                $this->incidencia->usuario_id,

            'usuario_nombre' =>
                $nombreUsuario,

            'creada_en' =>
                $this->incidencia
                    ->created_at
                    ?->timezone(
                        config(
                            'app.timezone',
                            'America/Tegucigalpa'
                        )
                    )
                    ->format('d/m/Y H:i'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Base de datos
    |--------------------------------------------------------------------------
    */

    public function toDatabase(
        object $notifiable
    ): array {
        return $this->datosNotificacion();
    }

    /*
    |--------------------------------------------------------------------------
    | Broadcast
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
    | Tipo enviado mediante Broadcast
    |--------------------------------------------------------------------------
    */

    public function broadcastType(): string
    {
        return 'incidencia.nueva';
    }
}