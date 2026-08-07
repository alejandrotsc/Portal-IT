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

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe el memorando correspondiente al pase recién registrado y lo
    | conserva para construir la información que será notificada.
    |
    */

    public function __construct(
        private readonly Memorando $memorando
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
    | canales para notificar el registro de un nuevo pase.
    |
    */

    private function datosNotificacion(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar relaciones necesarias
        |--------------------------------------------------------------------------
        |
        | Carga el tipo de memorando y el usuario solicitante para incluir
        | esta información dentro del mensaje de notificación.
        |
        */

        $this->memorando->loadMissing([
            'tipo',
            'solicitante',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Información del solicitante
        |--------------------------------------------------------------------------
        */

        $nombreSolicitante =
            $this->memorando->solicitante?->nombre
            ?? 'Un usuario';

        /*
        |--------------------------------------------------------------------------
        | Tipo de pase
        |--------------------------------------------------------------------------
        |
        | Determina el nombre descriptivo del pase según el tipo de
        | memorando asociado a la gestión.
        |
        */

        $tipoPase =
            $this->memorando->tipo?->slug
            ===
            'pase_temporal'
                ? 'pase menor a 24 horas'
                : 'pase mayor a 24 horas';

        /*
        |--------------------------------------------------------------------------
        | Identificador visible
        |--------------------------------------------------------------------------
        |
        | Utiliza el código del memorando cuando está disponible o genera
        | un identificador alternativo utilizando el ID del registro.
        |
        */

        $identificador =
            $this->memorando->codigo
            ?? 'PASE-'.str_pad(
                (string) $this->memorando->id,
                5,
                '0',
                STR_PAD_LEFT
            );

        /*
        |--------------------------------------------------------------------------
        | Información compartida
        |--------------------------------------------------------------------------
        |
        | Define los datos del pase que serán almacenados y transmitidos
        | mediante los canales configurados.
        |
        */

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
    | informar inmediatamente sobre el nuevo pase registrado.
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
    | las notificaciones correspondientes a nuevos pases.
    |
    */

    public function broadcastType(): string
    {
        return 'pase.nuevo';
    }
}