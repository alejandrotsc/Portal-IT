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

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe la solicitud recién registrada y la conserva para construir
    | la información que será enviada mediante la notificación.
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
    | Datos de la notificación
    |--------------------------------------------------------------------------
    |
    | Construye y centraliza la información utilizada por los diferentes
    | canales para notificar el registro de una nueva solicitud.
    |
    */

    private function datosNotificacion(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar usuario relacionado
        |--------------------------------------------------------------------------
        |
        | Carga la información del usuario que registró la solicitud
        | para incluir su nombre dentro del mensaje de notificación.
        |
        */

        $this->solicitud->loadMissing(
            'usuario'
        );

        $nombreUsuario =
            $this->solicitud->usuario?->nombre
            ?? 'Un usuario';

        /*
        |--------------------------------------------------------------------------
        | Información compartida
        |--------------------------------------------------------------------------
        |
        | Define los datos de la solicitud que serán almacenados y
        | transmitidos mediante los canales configurados.
        |
        */

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
    | Enviar mediante Reverb
    |--------------------------------------------------------------------------
    |
    | Construye el mensaje que será transmitido en tiempo real para
    | informar inmediatamente sobre la nueva solicitud registrada.
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
    | las notificaciones correspondientes a nuevas solicitudes.
    |
    */

    public function broadcastType(): string
    {
        return 'solicitud.nueva';
    }
}