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

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe la incidencia recién registrada y la conserva para construir
    | la información que será enviada mediante la notificación.
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
    | la base de datos y transmitirla en tiempo real.
    |
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
    |
    | Construye y centraliza los datos utilizados por los diferentes
    | canales para notificar el registro de una nueva incidencia.
    |
    */

    private function datosNotificacion(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar usuario relacionado
        |--------------------------------------------------------------------------
        |
        | Carga la información del usuario que registró la incidencia
        | para incluir su nombre dentro del mensaje de notificación.
        |
        */

        $this->incidencia->loadMissing('usuario');

        $nombreUsuario =
            $this->incidencia->usuario?->nombre
            ?? 'Un usuario';

        /*
        |--------------------------------------------------------------------------
        | Datos de la notificación
        |--------------------------------------------------------------------------
        |
        | Define la información de la incidencia que será almacenada y
        | transmitida mediante los canales configurados.
        |
        */

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
    |
    | Define la información que será almacenada de forma persistente
    | dentro de la tabla de notificaciones del sistema.
    |
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
    |
    | Construye el mensaje que será transmitido en tiempo real para
    | informar inmediatamente sobre la nueva incidencia registrada.
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
    | Tipo enviado mediante Broadcast
    |--------------------------------------------------------------------------
    |
    | Define el identificador utilizado por el cliente para reconocer
    | las notificaciones correspondientes a nuevas incidencias.
    |
    */

    public function broadcastType(): string
    {
        return 'incidencia.nueva';
    }
}