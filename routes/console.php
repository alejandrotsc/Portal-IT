<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


/*
|--------------------------------------------------------------------------
| Zona horaria de las tareas programadas
|--------------------------------------------------------------------------
|
| Permite que las horas configuradas se interpreten según la hora local
| de Honduras, independientemente de la zona horaria del servidor.
|
*/

$zonaHoraria = 'America/Tegucigalpa';


/*
|--------------------------------------------------------------------------
| Comando de ejemplo de Laravel
|--------------------------------------------------------------------------
*/

Artisan::command(
    'inspire',
    function (): void {
        $this->comment(
            Inspiring::quote()
        );
    }
)->purpose(
    'Display an inspiring quote'
);


/*
|--------------------------------------------------------------------------
| Notificar avisos TI programados
|--------------------------------------------------------------------------
|
| Revisa cada minuto los avisos activos cuya fecha y hora de inicio ya
| llegaron y que todavía no tienen notificacion_enviada_at.
|
*/

Schedule::command(
    'avisos:notificar-programados'
)
    ->everyMinute()
    ->timezone($zonaHoraria)
    ->withoutOverlapping(5)
    ->appendOutputTo(
        storage_path(
            'logs/avisos-programados.log'
        )
    );


/*
|--------------------------------------------------------------------------
| Limpiar datos temporales del Portal IT
|--------------------------------------------------------------------------
|
| Elimina registros temporales antiguos según las políticas definidas
| dentro del comando portal:limpiar.
|
| Incluye:
| - Caché y bloqueos vencidos.
| - Sesiones antiguas.
| - Tokens usados o vencidos.
| - Conversaciones antiguas del chatbot.
| - Historial técnico antiguo de correos.
| - Notificaciones leídas antiguas.
| - Desactivación de avisos vencidos.
|
*/

Schedule::command(
    'portal:limpiar --force'
)
    ->dailyAt('02:00')
    ->timezone($zonaHoraria)
    ->withoutOverlapping(60)
    ->appendOutputTo(
        storage_path(
            'logs/limpieza-programada.log'
        )
    );


/*
|--------------------------------------------------------------------------
| Limpiar trabajos fallidos antiguos
|--------------------------------------------------------------------------
|
| Conserva los trabajos fallidos durante 90 días.
| 2160 horas equivalen a 90 días.
|
*/

Schedule::command(
    'queue:prune-failed --hours=2160'
)
    ->dailyAt('02:10')
    ->timezone($zonaHoraria)
    ->withoutOverlapping(30)
    ->appendOutputTo(
        storage_path(
            'logs/limpieza-failed-jobs.log'
        )
    );


/*
|--------------------------------------------------------------------------
| Limpiar lotes de trabajos antiguos
|--------------------------------------------------------------------------
|
| Elimina metadatos de lotes terminados con más de 30 días.
| 720 horas equivalen a 30 días.
|
*/

Schedule::command(
    'queue:prune-batches --hours=720'
)
    ->dailyAt('02:20')
    ->timezone($zonaHoraria)
    ->withoutOverlapping(30)
    ->appendOutputTo(
        storage_path(
            'logs/limpieza-job-batches.log'
        )
    );