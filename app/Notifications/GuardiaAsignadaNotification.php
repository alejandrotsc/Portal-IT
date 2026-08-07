<?php

namespace App\Notifications;

use App\Models\GuardiaSoporte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class GuardiaAsignadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe la guardia de soporte asignada y la conserva para construir
    | la información que será enviada al usuario mediante la notificación.
    |
    */

    public function __construct(
        private readonly GuardiaSoporte $guardia
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
    | Información compartida
    |--------------------------------------------------------------------------
    |
    | Construye y centraliza los datos utilizados por los diferentes
    | canales de notificación relacionados con la guardia asignada.
    |
    */

    private function datosNotificacion(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar relaciones necesarias
        |--------------------------------------------------------------------------
        |
        | Carga la información del agente asignado y del usuario que creó
        | la guardia para incluir estos datos dentro de la notificación.
        |
        */

        $this->guardia->loadMissing([
            'agente',
            'creador',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Formatear fecha de la guardia
        |--------------------------------------------------------------------------
        |
        | Prepara una representación legible de la fecha utilizando la
        | configuración regional en español.
        |
        */

        $fecha = $this->guardia
            ->fecha
            ->copy()
            ->locale('es');

        $fechaFormateada = ucfirst(
            $fecha->isoFormat(
                'dddd D [de] MMMM [de] YYYY'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Datos de la notificación
        |--------------------------------------------------------------------------
        |
        | Define la información de la guardia que será almacenada y
        | transmitida al usuario mediante los canales configurados.
        |
        */

        return [
            'tipo' =>
                'guardia_asignada',

            'titulo' =>
                'Nueva guardia asignada',

            'mensaje' =>
                'Se te asignó una guardia para el '
                .$fechaFormateada
                .', de '
                .$this->guardia->horario
                .', en '
                .$this->guardia->ubicacion
                .'.',

            'icono' =>
                'calendar-check-2',

            'url' =>
                route(
                    'admin.guardias.mis-guardias',
                    [
                        'mes' =>
                            $this->guardia
                                ->fecha
                                ->month,

                        'anio' =>
                            $this->guardia
                                ->fecha
                                ->year,
                    ],
                    false
                ),

            'guardia_id' =>
                $this->guardia->id,

            'usuario_id' =>
                $this->guardia->usuario_id,

            'agente_nombre' =>
                $this->guardia
                    ->agente
                    ?->nombre,

            'fecha' =>
                $this->guardia
                    ->fecha
                    ->format('Y-m-d'),

            'fecha_formateada' =>
                $fechaFormateada,

            'hora_inicio' =>
                substr(
                    $this->guardia->hora_inicio,
                    0,
                    5
                ),

            'hora_fin' =>
                substr(
                    $this->guardia->hora_fin,
                    0,
                    5
                ),

            'horario' =>
                $this->guardia->horario,

            'ubicacion' =>
                $this->guardia->ubicacion,

            'observacion' =>
                $this->guardia->observacion,

            'creado_por' =>
                $this->guardia->creado_por,

            'creado_por_nombre' =>
                $this->guardia
                    ->creador
                    ?->nombre,

            'creada_en' =>
                $this->guardia
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
    | Construye el mensaje que será transmitido en tiempo real para que
    | el usuario reciba inmediatamente la asignación de la guardia.
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
    | las notificaciones correspondientes a nuevas guardias asignadas.
    |
    */

    public function broadcastType(): string
    {
        return 'guardia.asignada';
    }
}