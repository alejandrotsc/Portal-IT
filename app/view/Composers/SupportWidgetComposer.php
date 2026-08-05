<?php

namespace App\View\Composers;

use App\Models\GuardiaSoporte;
use Carbon\Carbon;
use Illuminate\View\View;

class SupportWidgetComposer
{
    public function compose(
        View $view
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Fecha y hora actual
        |--------------------------------------------------------------------------
        */

        $zonaHoraria = config(
            'app.timezone',
            'America/Tegucigalpa'
        );

        $ahora = Carbon::now(
            $zonaHoraria
        );

        $hoy = $ahora
            ->copy()
            ->startOfDay();

        $fechaHoy = $hoy
            ->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Comprobar si es fin de semana
        |--------------------------------------------------------------------------
        */

        $esFinDeSemana = $hoy
            ->isWeekend();


        /*
        |--------------------------------------------------------------------------
        | Guardia activa asignada para hoy
        |--------------------------------------------------------------------------
        */

        $guardiaHoy = null;

        if ($esFinDeSemana) {
            $guardiaHoy = GuardiaSoporte::query()
                ->with([
                    'agente.rol',
                ])
                ->activas()
                ->whereDate(
                    'fecha',
                    $fechaHoy
                )
                ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Disponibilidad actual
        |--------------------------------------------------------------------------
        */

        $guardiaDisponibleAhora = false;

        if (
            $guardiaHoy
            && filled($guardiaHoy->hora_inicio)
            && filled($guardiaHoy->hora_fin)
        ) {
            $inicioGuardia = Carbon::parse(
                $guardiaHoy
                    ->fecha
                    ->toDateString()
                . ' '
                . $guardiaHoy->hora_inicio,
                $zonaHoraria
            );

            $finGuardia = Carbon::parse(
                $guardiaHoy
                    ->fecha
                    ->toDateString()
                . ' '
                . $guardiaHoy->hora_fin,
                $zonaHoraria
            );

            $guardiaDisponibleAhora = $ahora
                ->betweenIncluded(
                    $inicioGuardia,
                    $finGuardia
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Próximas fechas de guardia
        |--------------------------------------------------------------------------
        |
        | Si hoy es sábado:
        |
        | - Domingo inmediato.
        | - Sábado siguiente.
        |
        | Si hoy es domingo o día laboral:
        |
        | - Próximo sábado.
        | - Domingo siguiente.
        |
        */

        if ($hoy->isSaturday()) {
            $primeraFechaGuardia = $hoy
                ->copy()
                ->addDay();

            $segundaFechaGuardia = $hoy
                ->copy()
                ->next(
                    Carbon::SATURDAY
                );
        } else {
            $primeraFechaGuardia = $hoy
                ->copy()
                ->next(
                    Carbon::SATURDAY
                );

            $segundaFechaGuardia = $primeraFechaGuardia
                ->copy()
                ->addDay();
        }

        $proximasFechasGuardia = collect([
            $primeraFechaGuardia,
            $segundaFechaGuardia,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Guardias próximas activas
        |--------------------------------------------------------------------------
        */

        $fechasConsulta = $proximasFechasGuardia
            ->map(
                fn (Carbon $fecha): string =>
                    $fecha->toDateString()
            )
            ->values()
            ->all();

        $guardiasProximas = GuardiaSoporte::query()
            ->with([
                'agente.rol',
            ])
            ->activas()
            ->whereIn(
                'fecha',
                $fechasConsulta
            )
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->keyBy(
                fn (GuardiaSoporte $guardia): string =>
                    $guardia
                        ->fecha
                        ->toDateString()
            );


        /*
        |--------------------------------------------------------------------------
        | Compartir información con el widget
        |--------------------------------------------------------------------------
        */

        $view->with([
            'widgetAhora' =>
                $ahora,

            'widgetHoy' =>
                $hoy,

            'widgetEsFinDeSemana' =>
                $esFinDeSemana,

            'widgetGuardiaHoy' =>
                $guardiaHoy,

            'widgetGuardiaDisponibleAhora' =>
                $guardiaDisponibleAhora,

            'widgetProximasFechasGuardia' =>
                $proximasFechasGuardia,

            'widgetGuardiasProximas' =>
                $guardiasProximas,
        ]);
    }
}