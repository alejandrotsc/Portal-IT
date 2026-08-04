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

    public function __construct(
        private readonly GuardiaSoporte $guardia
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
    | Información compartida
    |--------------------------------------------------------------------------
    */

    private function datosNotificacion(): array
    {
        $this->guardia->loadMissing([
            'agente',
            'creador',
        ]);

        $fecha = $this->guardia
            ->fecha
            ->copy()
            ->locale('es');

        $fechaFormateada = ucfirst(
            $fecha->isoFormat(
                'dddd D [de] MMMM [de] YYYY'
            )
        );

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
        return 'guardia.asignada';
    }
}