<?php

namespace App\View\Composers;

use App\Models\GuardiaSoporte;
use Carbon\Carbon;
use Illuminate\View\View;

class SupportWidgetComposer
{
    /*
    |--------------------------------------------------------------------------
    | Componer widget de soporte
    |--------------------------------------------------------------------------
    |
    | Prepara toda la información necesaria para mostrar el estado actual
    | del soporte TI, la guardia activa del día y las próximas guardias
    | programadas dentro del widget compartido por las vistas.
    |
    */

    public function compose(
        View $view
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Fecha y hora actual
        |--------------------------------------------------------------------------
        |
        | Obtiene la fecha y hora utilizando la zona horaria configurada
        | para la aplicación y prepara una referencia del día actual.
        |
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
        |
        | Determina si la fecha actual corresponde a sábado o domingo,
        | periodo en el cual puede existir una guardia individual asignada.
        |
        */

        $esFinDeSemana = $hoy
            ->isWeekend();

        /*
        |--------------------------------------------------------------------------
        | Guardia activa asignada para hoy
        |--------------------------------------------------------------------------
        |
        | Durante fines de semana consulta la guardia activa correspondiente
        | a la fecha actual junto con la información del agente asignado.
        |
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
        |
        | Comprueba si existe una guardia asignada y determina si la hora
        | actual se encuentra dentro del horario configurado para el agente.
        |
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
        | Calcula las dos próximas fechas relevantes de guardia tomando
        | como referencia el día actual.
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
        |
        | Recupera las guardias activas correspondientes a las próximas
        | fechas calculadas y las organiza por fecha para facilitar su
        | acceso desde la vista del widget.
        |
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
        |
        | Expone en la vista la fecha actual, disponibilidad, guardia del
        | día y próximas asignaciones necesarias para representar el estado
        | dinámico del soporte TI.
        |
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