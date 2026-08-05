{{-- Widget flotante: Soporte de turno --}}

@php

    /*
    |--------------------------------------------------------------------------
    | Variables exclusivas del widget
    |--------------------------------------------------------------------------
    |
    | Se usan nombres propios para evitar colisiones con otros partials.
    |
    */

    $soporteAhora =
        $widgetAhora;

    $soporteHoy =
        $widgetHoy;

    $soporteEsFinDeSemana =
        $widgetEsFinDeSemana;

    $soporteGuardiaHoy =
        $widgetGuardiaHoy;

    $soporteGuardiaDisponibleAhora =
        $widgetGuardiaDisponibleAhora;

    $soporteProximasFechasGuardia =
        $widgetProximasFechasGuardia;

    $soporteGuardiasProximas =
        $widgetGuardiasProximas;

@endphp

@php

    /*
    |--------------------------------------------------------------------------
    | Obtener iniciales
    |--------------------------------------------------------------------------
    */

    $obtenerIniciales = function (?string $nombre): string {
        if (! filled($nombre)) {
            return 'TI';
        }

        return collect(
            preg_split(
                '/\s+/',
                trim($nombre)
            )
        )
            ->filter()
            ->take(2)
            ->map(
                fn ($parte) =>
                    mb_strtoupper(
                        mb_substr(
                            $parte,
                            0,
                            1
                        )
                    )
            )
            ->implode('');
    };


    /*
    |--------------------------------------------------------------------------
    | Agente actual
    |--------------------------------------------------------------------------
    */

    $agenteActual =
        $soporteEsFinDeSemana
            ? $soporteGuardiaHoy?->agente
            : null;

    $nombrePrincipal =
        $agenteActual?->nombre
        ?? 'Equipo de Soporte TI';

    $inicialesPrincipal =
        $agenteActual
            ? $obtenerIniciales(
                $agenteActual->nombre
            )
            : 'TI';


    /*
    |--------------------------------------------------------------------------
    | Estado actual
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Disponibilidad general entre semana
    |--------------------------------------------------------------------------
    |
    | El equipo general se considera disponible de lunes a viernes,
    | entre las 09:00 y las 18:00.
    |
    */

    $inicioSoporteGeneral = $soporteHoy
        ->copy()
        ->setTime(9, 0);

    $finSoporteGeneral = $soporteHoy
        ->copy()
        ->setTime(18, 0);

    $soporteGeneralDisponibleAhora =
        ! $soporteEsFinDeSemana
        && $soporteAhora->between(
            $inicioSoporteGeneral,
            $finSoporteGeneral,
            true
        );

    $estaDisponible =
        $soporteEsFinDeSemana
            ? (
                $soporteGuardiaHoy
                && $soporteGuardiaDisponibleAhora
            )
            : $soporteGeneralDisponibleAhora;

    $textoEstado = match (true) {
        $estaDisponible =>
            'Disponible ahora',

        $soporteEsFinDeSemana && ! $soporteGuardiaHoy =>
            'Sin agente asignado',

        default =>
            'Fuera de horario',
    };

    $descripcionPrincipal =
        $soporteEsFinDeSemana
            ? (
                $soporteGuardiaHoy
                    ? 'Soporte TI · Turno de fin de semana'
                    : 'Guardia pendiente de asignación'
            )
            : 'Atención general · Lunes a viernes';


    /*
    |--------------------------------------------------------------------------
    | Horario actual
    |--------------------------------------------------------------------------
    */

    $horarioPrincipal =
        $soporteEsFinDeSemana
            ? (
                $soporteGuardiaHoy?->horario
                ?? (
                    filled($soporteGuardiaHoy?->hora_inicio)
                    && filled($soporteGuardiaHoy?->hora_fin)
                        ? $soporteGuardiaHoy->hora_inicio
                            .' – '
                            .$soporteGuardiaHoy->hora_fin
                        : null
                )
            )
            : '09:00 – 18:00';

    $ubicacionPrincipal =
        $soporteGuardiaHoy?->ubicacion;


    /*
    |--------------------------------------------------------------------------
    | Destinatario dinámico
    |--------------------------------------------------------------------------
    */

    $correoGeneralSoporte =
        'helpdesk@televicentro.hn';

    $correoSoporte =
        $agenteActual?->correo
        ?? $correoGeneralSoporte;

    $nombreContacto =
        $agenteActual?->nombre
        ?? 'equipo de soporte TI';

    $asuntoSoporte =
        $agenteActual
            ? 'Consulta al agente de guardia - Portal TI'
            : 'Consulta desde el Portal TI';

    $cuerpoSoporte = implode(
        "\n",
        [
            'Hola, '.$nombreContacto.':',
            '',
            'Deseo realizar la siguiente consulta:',
            '',
            'Usuario: '
                .(auth()->user()->nombre ?? 'N/A'),

            'Correo: '
                .(auth()->user()->correo ?? 'N/A'),

            '',
            'Detalle de la consulta:',
            '',
        ]
    );

    $outlookUrl =
        'https://outlook.office.com/mail/deeplink/compose'
        .'?to='.rawurlencode($correoSoporte)
        .'&subject='.rawurlencode($asuntoSoporte)
        .'&body='.rawurlencode($cuerpoSoporte);

@endphp


<div
    x-data="{
        abierto: false,

        alternar() {
            this.abierto = ! this.abierto;

            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        cerrar() {
            this.abierto = false;
        }
    }"
    x-on:keydown.escape.window="cerrar()"
    class="fixed bottom-4 right-4 z-[70]
           flex flex-col items-end
           sm:bottom-5 sm:right-5"
>

    {{-- Tarjeta expandida --}}

    <div
        x-show="abierto"
        x-cloak
        x-transition:enter="
            transition-[opacity,transform] ease-out duration-300
            origin-bottom-right
        "
        x-transition:enter-start="
            opacity-0 translate-y-2 translate-x-2 scale-[0.22]
        "
        x-transition:enter-end="
            opacity-100 translate-y-0 translate-x-0 scale-100
        "
        x-transition:leave="
            transition-[opacity,transform] ease-in duration-220
            origin-bottom-right
        "
        x-transition:leave-start="
            opacity-100 translate-y-0 translate-x-0 scale-100
        "
        x-transition:leave-end="
            opacity-0 translate-y-2 translate-x-2 scale-[0.22]
        "
        x-on:click.outside="cerrar()"
        class="w-[calc(100vw-2rem)] max-w-[23rem]
               origin-bottom-right overflow-hidden rounded-2xl
               border border-slate-200/90 bg-white
               shadow-[0_24px_60px_-18px_rgba(30,64,175,0.45)]
               dark:border-slate-700
               dark:bg-slate-900
               dark:shadow-[0_24px_60px_-18px_rgba(0,0,0,0.78)]"
    >

        {{-- Encabezado azul --}}

        <div
            class="relative overflow-hidden
                   bg-primary
                   px-4 pb-4 pt-3.5"
        >

            {{-- Círculos decorativos --}}

            <span
                class="pointer-events-none absolute
                       -right-5 -top-5
                       h-28 w-28 rounded-full
                       bg-white/[0.02]"
            ></span>

            <span
                class="pointer-events-none absolute
                       -bottom-7 -left-4
                       h-32 w-32 rounded-full
                       bg-white/[0.02]"
            ></span>


            {{-- Encabezado superior --}}

            <div
                class="relative mb-3
                       flex items-center justify-between"
            >

                <span
                    class="text-[10px] font-bold uppercase
                           tracking-[0.13em] text-white/55"
                >
                    Soporte de turno
                </span>


                <button
                    type="button"
                    x-on:click="cerrar()"
                    aria-label="Cerrar soporte"
                    class="flex h-6 w-6 items-center
                           justify-center rounded-lg
                           text-white/40
                           transition-colors
                           hover:bg-white/10
                           hover:text-white"
                >
                    <i
                        data-lucide="x"
                        stroke-width="1.8"
                        class="h-3.5 w-3.5"
                    ></i>
                </button>

            </div>


            {{-- Agente actual --}}

            <div
                class="relative flex items-center gap-3.5"
            >

                <div class="relative shrink-0">

                    @if($estaDisponible)

                        <span
                            class="absolute inset-0
                                   rounded-full bg-emerald-400/35
                                   motion-safe:animate-ping"
                            style="animation-duration: 2.4s;"
                        ></span>

                    @endif


                    <div
                        class="relative flex h-11 w-11
                               items-center justify-center
                               rounded-full
                               border-2 border-white/30
                               bg-white/15
                               text-sm font-bold text-white
                               shadow-md"
                    >
                        {{ $inicialesPrincipal }}
                    </div>

                </div>


                <div class="min-w-0 flex-1">

                    <div class="flex min-w-0 items-center gap-2">

                        <p
                            class="min-w-0 truncate text-sm font-bold
                                   leading-tight text-white"
                        >
                            {{ $nombrePrincipal }}
                        </p>

                        <span
                            @class([
                                'inline-flex shrink-0 items-center gap-1
                                 rounded-full border px-2 py-1
                                 text-[9px] font-semibold leading-none',

                                'border-emerald-300/30 bg-emerald-300/15
                                 text-emerald-200' =>
                                    $estaDisponible,

                                'border-amber-300/30 bg-amber-300/15
                                 text-amber-200' =>
                                    ! $estaDisponible,
                            ])
                        >
                            @if($estaDisponible)

                                <span
                                    class="h-1.5 w-1.5 rounded-full
                                           bg-emerald-300
                                           motion-safe:animate-pulse"
                                ></span>

                            @else

                                <i
                                    data-lucide="{{
                                        $soporteGuardiaHoy
                                            ? 'clock'
                                            : 'user-round-x'
                                    }}"
                                    stroke-width="1.8"
                                    class="h-2.5 w-2.5"
                                ></i>

                            @endif

                            {{ $textoEstado }}

                        </span>

                    </div>

                    <p
                        class="mt-0.5 truncate
                               text-xs text-blue-200"
                    >
                        {{ $descripcionPrincipal }}
                    </p>


                    @if(
                        filled($horarioPrincipal)
                        || filled(
                            $agenteActual
                                ?->extension_telefonica
                        )
                    )

                        <div
                            class="mt-1 flex flex-wrap items-center
                                   gap-x-1.5 gap-y-1
                                   text-[11px] text-white/50"
                        >

                            @if(filled($horarioPrincipal))

                                <span
                                    class="inline-flex
                                           items-center gap-1"
                                >
                                    <i
                                        data-lucide="clock"
                                        stroke-width="1.8"
                                        class="h-2.5 w-2.5 shrink-0"
                                    ></i>

                                    {{ $horarioPrincipal }}
                                </span>

                            @endif


                            @if(
                                filled(
                                    $agenteActual
                                        ?->extension_telefonica
                                )
                            )

                                <span class="text-white/30">
                                    ·
                                </span>

                                <span>
                                    Ext.
                                    {{
                                        $agenteActual
                                            ->extension_telefonica
                                    }}
                                </span>

                            @endif

                        </div>

                    @endif

                </div>

            </div>


        </div>


        {{-- Información actual --}}

        <div
            class="border-b border-slate-200
                   bg-white px-4 py-3
                   dark:border-slate-700
                   dark:bg-slate-900"
        >

            <div class="space-y-2">

                {{-- Correo --}}

                <a
                    href="mailto:{{ $correoSoporte }}"
                    class="group/mail flex items-center
                           gap-2 text-xs font-medium
                           text-slate-600 transition-colors
                           hover:text-blue-600
                           dark:text-slate-300
                           dark:hover:text-blue-400"
                >
                    <i
                        data-lucide="mail"
                        stroke-width="1.8"
                        class="h-3.5 w-3.5 shrink-0
                               text-slate-400
                               transition-colors
                               group-hover/mail:text-blue-600
                               dark:text-slate-500
                               dark:group-hover/mail:text-blue-400"
                    ></i>

                    <span class="truncate">
                        {{ $correoSoporte }}
                    </span>
                </a>


                {{-- Extensión --}}

                @if(
                    filled(
                        $agenteActual
                            ?->extension_telefonica
                    )
                )

                    <div
                        class="flex items-center gap-2
                               text-xs font-medium
                               text-blue-600
                               dark:text-blue-400"
                    >
                        <i
                            data-lucide="phone"
                            stroke-width="1.8"
                            class="h-3.5 w-3.5 shrink-0"
                        ></i>

                        <span>
                            Ext. telefónica
                            {{
                                $agenteActual
                                    ->extension_telefonica
                            }}
                        </span>
                    </div>

                @endif


                {{-- Horario y ubicación --}}

                @if(
                    filled($horarioPrincipal)
                    || filled($ubicacionPrincipal)
                )

                    <div
                        class="flex flex-wrap items-center
                               gap-x-3 gap-y-1"
                    >

                        @if(filled($horarioPrincipal))

                            <span
                                class="inline-flex items-center
                                       gap-1 text-xs
                                       text-slate-500
                                       dark:text-slate-400"
                            >
                                <i
                                    data-lucide="clock"
                                    stroke-width="1.8"
                                    class="h-3 w-3 shrink-0"
                                ></i>

                                {{ $horarioPrincipal }}
                            </span>

                        @endif


                        @if(filled($ubicacionPrincipal))

                            <span
                                class="inline-flex items-center
                                       gap-1 text-xs
                                       text-slate-500
                                       dark:text-slate-400"
                            >
                                <i
                                    data-lucide="map-pin"
                                    stroke-width="1.8"
                                    class="h-3 w-3 shrink-0"
                                ></i>

                                {{ $ubicacionPrincipal }}
                            </span>

                        @endif

                    </div>

                @endif

            </div>

        </div>


        {{-- Acciones --}}

        <div
            class="flex gap-2 bg-white px-4 py-4
                   dark:bg-slate-900"
        >

            <a
                href="{{ $outlookUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="flex flex-1 items-center
                       justify-center gap-1.5
                       rounded-xl bg-primary py-2.5
                       text-xs font-semibold text-white
                       shadow-sm shadow-primary/20
                       transition-all
                       hover:bg-primary/90
                       active:scale-[0.97]"
            >
                <i
                    data-lucide="message-circle"
                    stroke-width="1.8"
                    class="h-3.5 w-3.5"
                ></i>

                Contactar
            </a>


            <a
                href="{{ route('incidencias.create') }}"
                class="flex flex-1 items-center
                       justify-center gap-1.5
                       rounded-xl border
                       border-slate-200 bg-white py-2.5
                       text-xs font-semibold
                       text-slate-800
                       transition-all
                       hover:bg-slate-50
                       active:scale-[0.97]
                       dark:border-slate-700
                       dark:bg-slate-900
                       dark:text-slate-100
                       dark:hover:bg-slate-800"
            >
                <i
                    data-lucide="ticket"
                    stroke-width="1.8"
                    class="h-3.5 w-3.5"
                ></i>

                Incidencia
            </a>

        </div>


        {{-- Próximas guardias --}}

        <div
            class="border-t border-slate-200
                   bg-slate-50/95 px-4 py-3
                   dark:border-slate-700
                   dark:bg-slate-950/45"
        >

            <div
                class="mb-2.5 flex items-center
                       justify-between gap-2"
            >

                <p
                    class="text-[10px] font-semibold uppercase
                           tracking-wider text-slate-500
                           dark:text-slate-400"
                >
                    Próximos turnos
                </p>

                <i
                    data-lucide="calendar-range"
                    stroke-width="1.8"
                    class="h-3.5 w-3.5
                           text-slate-400
                           dark:text-slate-500"
                ></i>

            </div>


            <div
                class="max-h-64 space-y-3
                       overflow-y-auto pr-1"
            >

                @forelse(
                    $soporteProximasFechasGuardia
                    as $fechaProxima
                )

                    @php

                        $guardiaProxima =
                            $soporteGuardiasProximas->get(
                                $fechaProxima->format(
                                    'Y-m-d'
                                )
                            );

                        $agenteProximo =
                            $guardiaProxima?->agente;

                    @endphp


                    <div
                        class="rounded-xl
                               border border-slate-200/90
                               bg-white px-3 py-3
                               dark:border-slate-700
                               dark:bg-slate-900"
                    >

                        <div
                            class="flex items-start gap-2.5"
                        >

                            {{-- Fecha --}}

                            <div
                                class="flex h-10 w-10 shrink-0
                                       flex-col items-center
                                       justify-center rounded-xl
                                       bg-blue-50 text-blue-700
                                       dark:bg-blue-950/60
                                       dark:text-blue-300"
                            >
                                <span
                                    class="text-[8px] font-bold
                                           uppercase leading-none
                                           tracking-wide"
                                >
                                    {{
                                        $fechaProxima
                                            ->locale('es')
                                            ->isoFormat('ddd')
                                    }}
                                </span>

                                <span
                                    class="mt-1 text-sm font-bold
                                           leading-none"
                                >
                                    {{
                                        $fechaProxima
                                            ->format('d')
                                    }}
                                </span>
                            </div>


                            <div class="min-w-0 flex-1">

                                <p
                                    class="truncate text-xs
                                           font-semibold capitalize
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{
                                        $fechaProxima
                                            ->locale('es')
                                            ->isoFormat(
                                                'dddd D [de] MMMM'
                                            )
                                    }}
                                </p>


                                @if($guardiaProxima)

                                    <p
                                        class="mt-1 truncate
                                               text-xs font-semibold
                                               text-slate-800
                                               dark:text-slate-200"
                                    >
                                        {{
                                            $agenteProximo?->nombre
                                            ?? 'Agente de soporte'
                                        }}
                                    </p>


                                    <div class="mt-1.5 space-y-1">

                                        {{-- Correo próximo --}}

                                        @if(
                                            filled(
                                                $agenteProximo
                                                    ?->correo
                                            )
                                        )

                                            <a
                                                href="mailto:{{
                                                    $agenteProximo
                                                        ->correo
                                                }}"
                                                class="flex max-w-full
                                                       items-center gap-1.5
                                                       text-[11px]
                                                       font-medium
                                                       text-blue-600
                                                       transition-colors
                                                       hover:text-blue-700
                                                       hover:underline
                                                       dark:text-blue-400
                                                       dark:hover:text-blue-300"
                                            >
                                                <i
                                                    data-lucide="mail"
                                                    stroke-width="1.8"
                                                    class="h-3 w-3
                                                           shrink-0"
                                                ></i>

                                                <span class="truncate">
                                                    {{
                                                        $agenteProximo
                                                            ->correo
                                                    }}
                                                </span>
                                            </a>

                                        @endif


                                        {{-- Extensión próxima --}}

                                        @if(
                                            filled(
                                                $agenteProximo
                                                    ?->extension_telefonica
                                            )
                                        )

                                            <div
                                                class="flex items-center
                                                       gap-1.5
                                                       text-[11px]
                                                       font-medium
                                                       text-blue-600
                                                       dark:text-blue-400"
                                            >
                                                <i
                                                    data-lucide="phone"
                                                    stroke-width="1.8"
                                                    class="h-3 w-3
                                                           shrink-0"
                                                ></i>

                                                <span>
                                                    Ext. telefónica
                                                    {{
                                                        $agenteProximo
                                                            ->extension_telefonica
                                                    }}
                                                </span>
                                            </div>

                                        @endif


                                        {{-- Horario y ubicación próximos --}}

                                        @if(
                                            filled(
                                                $guardiaProxima
                                                    ->horario
                                            )
                                            || filled(
                                                $guardiaProxima
                                                    ->ubicacion
                                            )
                                        )

                                            <div
                                                class="flex flex-wrap
                                                       items-center
                                                       gap-x-3 gap-y-1"
                                            >

                                                @if(
                                                    filled(
                                                        $guardiaProxima
                                                            ->horario
                                                    )
                                                )

                                                    <span
                                                        class="inline-flex
                                                               items-center
                                                               gap-1
                                                               text-[11px]
                                                               text-slate-500
                                                               dark:text-slate-400"
                                                    >
                                                        <i
                                                            data-lucide="clock"
                                                            stroke-width="1.8"
                                                            class="h-3 w-3
                                                                   shrink-0"
                                                        ></i>

                                                        {{
                                                            $guardiaProxima
                                                                ->horario
                                                        }}
                                                    </span>

                                                @endif


                                                @if(
                                                    filled(
                                                        $guardiaProxima
                                                            ->ubicacion
                                                    )
                                                )

                                                    <span
                                                        class="inline-flex
                                                               items-center
                                                               gap-1
                                                               text-[11px]
                                                               text-slate-500
                                                               dark:text-slate-400"
                                                    >
                                                        <i
                                                            data-lucide="map-pin"
                                                            stroke-width="1.8"
                                                            class="h-3 w-3
                                                                   shrink-0"
                                                        ></i>

                                                        {{
                                                            $guardiaProxima
                                                                ->ubicacion
                                                        }}
                                                    </span>

                                                @endif

                                            </div>

                                        @endif

                                    </div>

                                @else

                                    <div
                                        class="mt-1.5 flex items-center
                                               gap-1.5
                                               text-amber-600
                                               dark:text-amber-400"
                                    >
                                        <i
                                            data-lucide="user-round-x"
                                            stroke-width="1.8"
                                            class="h-3.5 w-3.5
                                                   shrink-0"
                                        ></i>

                                        <p
                                            class="text-[11px]
                                                   font-medium"
                                        >
                                            Aún no se ha asignado
                                            un agente
                                        </p>
                                    </div>

                                @endif

                            </div>


                            @if($guardiaProxima)

                                <div
                                    class="flex h-7 w-7 shrink-0
                                           items-center justify-center
                                           rounded-full
                                           border border-slate-200
                                           bg-slate-100
                                           text-[9px] font-semibold
                                           text-slate-500
                                           dark:border-slate-700
                                           dark:bg-slate-800
                                           dark:text-slate-300"
                                >
                                    {{
                                        $obtenerIniciales(
                                            $agenteProximo?->nombre
                                        )
                                    }}
                                </div>

                            @else

                                <i
                                    data-lucide="wifi-off"
                                    stroke-width="1.8"
                                    class="h-3.5 w-3.5 shrink-0
                                           text-slate-300
                                           dark:text-slate-600"
                                ></i>

                            @endif

                        </div>

                    </div>

                @empty

                    <div
                        class="flex items-center gap-2
                               rounded-xl border
                               border-dashed border-slate-300
                               px-3 py-3
                               text-xs text-slate-500
                               dark:border-slate-700
                               dark:text-slate-400"
                    >
                        <i
                            data-lucide="calendar-x"
                            stroke-width="1.8"
                            class="h-4 w-4 shrink-0"
                        ></i>

                        No hay próximas guardias.
                    </div>

                @endforelse

            </div>

        </div>

    </div>


    {{-- Botón flotante --}}

    <button
        x-show="! abierto"
        x-transition:enter="
            transition-[opacity,transform] ease-out duration-220
            origin-bottom-right
        "
        x-transition:enter-start="
            opacity-0 scale-75
        "
        x-transition:enter-end="
            opacity-100 scale-100
        "
        x-transition:leave="
            transition-[opacity,transform] ease-in duration-160
            origin-bottom-right
        "
        x-transition:leave-start="
            opacity-100 scale-100
        "
        x-transition:leave-end="
            opacity-0 scale-75
        "
        type="button"
        x-on:click="alternar()"
        x-bind:aria-expanded="abierto"
        aria-label="Abrir soporte de turno"
        title="Soporte de turno"
        class="relative flex origin-bottom-right items-center
               rounded-full
               bg-primary
               shadow-[0_14px_35px_-8px_rgba(37,99,235,0.65)]
               transition-all duration-300
               hover:-translate-y-0.5
               hover:shadow-[0_18px_40px_-8px_rgba(37,99,235,0.72)]
               active:translate-y-0
               active:scale-95"
    >

        {{-- Pulso --}}

        <span
            class="pointer-events-none absolute inset-0
                   rounded-full bg-blue-500/20
                   motion-safe:animate-ping"
            style="animation-duration: 2s;"
        ></span>


        {{-- Estado cerrado --}}

        <div
    class="relative flex items-center
           gap-3.5 py-2.5 pl-2.5 pr-5"
>

            <div
    class="flex h-12 w-12 shrink-0
           items-center justify-center
           rounded-full
           border-2 border-white/30
           bg-white/20
           text-base font-bold text-white"
>
                {{ $inicialesPrincipal }}
            </div>


            <div class="text-left">

                <p
                    class="text-xs font-bold
                           leading-tight text-white"
                >
                    Soporte TI
                </p>

                <p
                    class="mt-0.5 flex items-center
                           gap-1 text-[10px]
                           font-medium text-blue-200"
                >

                    <span
                        @class([
                            'inline-block h-1.5 w-1.5
                             rounded-full',

                            'bg-emerald-400
                             motion-safe:animate-pulse' =>
                                $estaDisponible,

                            'bg-amber-300' =>
                                ! $estaDisponible,
                        ])
                    ></span>

                    {{ $textoEstado }}
                </p>

            </div>

        </div>


    </button>

</div>