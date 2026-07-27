<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command(
    'inspire',
    function () {
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
    ->withoutOverlapping()
    ->appendOutputTo(
        storage_path(
            'logs/avisos-programados.log'
        )
    );