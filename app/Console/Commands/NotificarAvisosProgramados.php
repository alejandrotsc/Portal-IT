<?php

namespace App\Console\Commands;

use App\Models\Aviso;
use App\Models\Usuario;
use App\Notifications\NuevoAvisoTiNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class NotificarAvisosProgramados extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Nombre del comando
    |--------------------------------------------------------------------------
    */

    protected $signature =
        'avisos:notificar-programados';


    /*
    |--------------------------------------------------------------------------
    | Descripción
    |--------------------------------------------------------------------------
    */

    protected $description =
        'Envía las notificaciones de los avisos TI cuya fecha de inicio ya llegó.';


    /*
    |--------------------------------------------------------------------------
    | Ejecutar comando
    |--------------------------------------------------------------------------
    */

    public function handle(): int
    {
        $ahora = now();

        $avisosProcesados = 0;

        $notificacionesEnviadas = 0;


        /*
        |--------------------------------------------------------------------------
        | Buscar avisos pendientes
        |--------------------------------------------------------------------------
        */

        Aviso::query()
            ->where(
                'activo',
                true
            )
            ->whereNull(
                'notificacion_enviada_at'
            )
            ->whereNotNull(
                'fecha_inicio'
            )
            ->where(
                'fecha_inicio',
                '<=',
                $ahora
            )
            ->where(
                function ($query) use ($ahora) {
                    $query
                        ->whereNull(
                            'fecha_fin'
                        )
                        ->orWhere(
                            'fecha_fin',
                            '>=',
                            $ahora
                        );
                }
            )
            ->orderBy(
                'id'
            )
            ->chunkById(
                50,
                function ($avisos) use (
                    &$avisosProcesados,
                    &$notificacionesEnviadas
                ) {
                    foreach ($avisos as $aviso) {
                        try {
                            /*
                            |--------------------------------------------------------------------------
                            | Confirmar que todavía sigue pendiente
                            |--------------------------------------------------------------------------
                            |
                            | Se vuelve a consultar para reducir el riesgo de enviar dos veces
                            | la misma notificación.
                            |
                            */

                            $avisoActual = Aviso::query()
                                ->whereKey(
                                    $aviso->id
                                )
                                ->where(
                                    'activo',
                                    true
                                )
                                ->whereNull(
                                    'notificacion_enviada_at'
                                )
                                ->first();

                            if (! $avisoActual) {
                                continue;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Verificar vigencia
                            |--------------------------------------------------------------------------
                            */

                            if (! $avisoActual->estaVisible()) {
                                continue;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Obtener usuarios activos
                            |--------------------------------------------------------------------------
                            */

                            $usuarios = Usuario::query()
                                ->where(
                                    'activo',
                                    true
                                )
                                ->when(
                                    $avisoActual->creado_por,
                                    function ($query) use ($avisoActual) {
                                        $query->where(
                                            'id',
                                            '<>',
                                            $avisoActual->creado_por
                                        );
                                    }
                                )
                                ->get();


                            /*
                            |--------------------------------------------------------------------------
                            | Enviar notificación
                            |--------------------------------------------------------------------------
                            */

                            if ($usuarios->isNotEmpty()) {
                                Notification::send(
                                    $usuarios,
                                    new NuevoAvisoTiNotification(
                                        $avisoActual
                                    )
                                );

                                $notificacionesEnviadas +=
                                    $usuarios->count();
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Marcar aviso como notificado
                            |--------------------------------------------------------------------------
                            */

                            $avisoActual->marcarComoNotificado();

                            $avisosProcesados++;


                            Log::info(
                                'Aviso programado notificado correctamente.',
                                [
                                    'aviso_id' =>
                                        $avisoActual->id,

                                    'titulo' =>
                                        $avisoActual->titulo,

                                    'usuarios_notificados' =>
                                        $usuarios->count(),

                                    'notificacion_enviada_at' =>
                                        $avisoActual
                                            ->notificacion_enviada_at
                                            ?->toDateTimeString(),
                                ]
                            );
                        } catch (Throwable $exception) {
                            /*
                            |--------------------------------------------------------------------------
                            | Registrar error sin detener los demás avisos
                            |--------------------------------------------------------------------------
                            */

                            Log::error(
                                'Error notificando aviso TI programado.',
                                [
                                    'aviso_id' =>
                                        $aviso->id,

                                    'titulo' =>
                                        $aviso->titulo,

                                    'error' =>
                                        $exception->getMessage(),
                                ]
                            );

                            $this->error(
                                "No se pudo notificar el aviso {$aviso->id}: "
                                .$exception->getMessage()
                            );
                        }
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

        if ($avisosProcesados === 0) {
            $this->info(
                'No hay avisos programados pendientes de notificar.'
            );

            return self::SUCCESS;
        }

        $this->info(
            "Avisos procesados: {$avisosProcesados}."
        );

        $this->info(
            "Notificaciones enviadas: {$notificacionesEnviadas}."
        );

        return self::SUCCESS;
    }
}