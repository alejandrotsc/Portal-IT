@extends('layouts.app')

@section('title', 'Mis guardias')

@section('content')

@php

    $usuarioActual = auth()->user();

    $esAdministrador =
        $usuarioActual->esAdministrador();

    $meses = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    /*
    |--------------------------------------------------------------------------
    | Resumen del período
    |--------------------------------------------------------------------------
    */

    $guardiasProximas = $guardias
    ->filter(
        fn ($guardia) =>
            ! $guardia->fecha
                ->copy()
                ->startOfDay()
                ->isBefore(
                    today()
                )
    );

    $guardiasTVC = $guardias
        ->where(
            'ubicacion',
            'TVC'
        )
        ->count();

    $guardiasCNT = $guardias
        ->where(
            'ubicacion',
            'CNT'
        )
        ->count();

@endphp


<div class="min-h-screen bg-background">

    <main class="mx-auto max-w-6xl px-6 py-10">


        {{-- Encabezado --}}

        <section class="mb-10">

            <div
                class="flex flex-col gap-5
                       sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="min-w-0">

                        <span
                            class="mb-4 inline-flex items-center gap-2
                                   rounded-full border border-cyan-200/60
                                   bg-cyan-500/[0.08] px-3 py-1.5
                                   text-xs font-semibold text-cyan-700
                                   dark:border-cyan-900/70
                                   dark:bg-cyan-950/60
                                   dark:text-cyan-300"
                        >
                            <i
                                data-lucide="{{
                                    $esAdministrador
                                        ? 'shield-check'
                                        : 'headphones'
                                }}"
                                stroke-width="1.8"
                                class="h-3.5 w-3.5"
                            ></i>

                            {{
                                $esAdministrador
                                    ? 'Vista administrativa'
                                    : 'Soporte TI'
                            }}
                        </span>

                        <h1
                            class="text-2xl font-semibold tracking-tight
                                   text-foreground"
                        >
                            {{
                                $esAdministrador
                                    ? 'Calendario general de guardias'
                                    : 'Mis guardias'
                            }}
                        </h1>

                        <p
                            class="mt-1.5 max-w-2xl text-sm leading-relaxed
                                   text-muted-foreground"
                        >
                            @if($esAdministrador)

                                Consulta las guardias activas programadas
                                para el equipo de soporte.

                            @else

                                Consulta las guardias de fin de semana que
                                te han sido asignadas, junto con su horario
                                y ubicación.

                            @endif
                        </p>

                </div>


                @if($esAdministrador)

                    <a
                        href="{{ route('admin.guardias.index') }}"
                        class="inline-flex items-center justify-center
                               gap-2 rounded-lg bg-primary px-4 py-2.5
                               text-sm font-semibold text-white shadow-sm
                               transition-all duration-200
                               hover:bg-primary/90 hover:shadow-md
                               motion-safe:hover:-translate-y-0.5"
                    >
                        <i
                            data-lucide="settings-2"
                            stroke-width="1.8"
                            class="h-4 w-4"
                        ></i>

                        Administrar guardias
                    </a>

                @endif

            </div>

        </section>


        {{-- Resumen --}}

        <section class="mb-10">

            <div class="mb-5">

                <h2 class="text-base font-semibold text-foreground">
                    Resumen del período
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Distribución de guardias para
                    {{ $meses[$mes] }} de {{ $anio }}.
                </p>

            </div>

            <div
                class="grid grid-cols-1 gap-4
                       sm:grid-cols-2 xl:grid-cols-4"
            >

                {{-- Total del período --}}

                <div
                    class="group relative overflow-hidden rounded-2xl
                           border border-blue-200/60
                           bg-gradient-to-br from-blue-50 via-white
                           to-indigo-50/50 p-5 shadow-sm
                           transition-all duration-300
                           hover:border-blue-300
                           hover:shadow-lg hover:shadow-blue-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-blue-900/60
                           dark:from-blue-950/30 dark:via-slate-900
                           dark:to-indigo-950/20"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8
                               h-24 w-24 rounded-full bg-blue-400/10
                               transition-all duration-500
                               group-hover:bg-blue-400/20
                               motion-safe:group-hover:scale-150"
                    ></div>

                    <div
                        class="relative mb-5 flex items-center
                               justify-between"
                    >

                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl bg-blue-500/10
                                   text-blue-600 transition-all duration-300
                                   group-hover:bg-blue-100
                                   motion-safe:group-hover:scale-105
                                   dark:text-blue-400
                                   dark:group-hover:bg-blue-950/70"
                        >
                            <i
                                data-lucide="calendar-days"
                                stroke-width="1.8"
                                class="h-5 w-5 shrink-0
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110"
                            ></i>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-blue-500/10 px-2.5 py-1
                                   text-xs font-medium
                                   text-blue-700 dark:text-blue-400"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-blue-500"
                            ></span>

                            {{ $meses[$mes] }}
                        </span>

                    </div>

                    <p
                        class="relative text-2xl font-semibold leading-none
                               text-foreground"
                    >
                        {{ $guardias->count() }}
                    </p>

                    <p class="relative mt-2 text-sm text-muted-foreground">
                        Guardias del período
                    </p>
                </div>


                {{-- Próximas --}}

                <div
                    class="group relative overflow-hidden rounded-2xl
                           border border-emerald-200/60
                           bg-gradient-to-br from-emerald-50 via-white
                           to-teal-50/50 p-5 shadow-sm
                           transition-all duration-300
                           hover:border-emerald-300
                           hover:shadow-lg hover:shadow-emerald-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-emerald-900/60
                           dark:from-emerald-950/30 dark:via-slate-900
                           dark:to-teal-950/20"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8
                               h-24 w-24 rounded-full bg-emerald-400/10
                               transition-all duration-500
                               group-hover:bg-emerald-400/20
                               motion-safe:group-hover:scale-150"
                    ></div>

                    <div
                        class="relative mb-5 flex items-center
                               justify-between"
                    >

                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl
                                   bg-emerald-500/10 text-emerald-600
                                   transition-all duration-300
                                   group-hover:bg-emerald-100
                                   motion-safe:group-hover:scale-105
                                   dark:text-emerald-400
                                   dark:group-hover:bg-emerald-950/70"
                        >
                            <i
                                data-lucide="calendar-clock"
                                stroke-width="1.8"
                                class="h-5 w-5 shrink-0
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110"
                            ></i>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-emerald-500/10
                                   px-2.5 py-1 text-xs font-medium
                                   text-emerald-700
                                   dark:text-emerald-400"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full
                                       bg-emerald-500"
                            ></span>

                            Vigentes
                        </span>

                    </div>

                    <p
                        class="relative text-2xl font-semibold leading-none
                               text-foreground"
                    >
                        {{ $guardiasProximas->count() }}
                    </p>

                    <p class="relative mt-2 text-sm text-muted-foreground">
                        Guardias próximas
                    </p>
                </div>


                {{-- TVC --}}

                <div
                    class="group relative overflow-hidden rounded-2xl
                           border border-cyan-200/60
                           bg-gradient-to-br from-cyan-50 via-white
                           to-blue-50/50 p-5 shadow-sm
                           transition-all duration-300
                           hover:border-cyan-300
                           hover:shadow-lg hover:shadow-cyan-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-cyan-900/60
                           dark:from-cyan-950/30 dark:via-slate-900
                           dark:to-blue-950/20"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8
                               h-24 w-24 rounded-full bg-cyan-400/10
                               transition-all duration-500
                               group-hover:bg-cyan-400/20
                               motion-safe:group-hover:scale-150"
                    ></div>

                    <div
                        class="relative mb-5 flex items-center
                               justify-between"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl bg-cyan-500/10
                                   text-cyan-600 transition-all duration-300
                                   group-hover:bg-cyan-100
                                   motion-safe:group-hover:scale-105
                                   dark:text-cyan-400
                                   dark:group-hover:bg-cyan-950/70"
                        >
                            <i
                                data-lucide="building-2"
                                stroke-width="1.8"
                                class="h-5 w-5 shrink-0
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110"
                            ></i>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-cyan-500/10 px-2.5 py-1
                                   text-xs font-medium text-cyan-700
                                   dark:text-cyan-400"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                            TVC
                        </span>
                    </div>

                    <p
                        class="relative text-2xl font-semibold leading-none
                               text-foreground"
                    >
                        {{ $guardiasTVC }}
                    </p>

                    <p class="relative mt-2 text-sm text-muted-foreground">
                        Guardias en TVC
                    </p>
                </div>


                {{-- CNT --}}

                <div
                    class="group relative overflow-hidden rounded-2xl
                           border border-violet-200/60
                           bg-gradient-to-br from-violet-50 via-white
                           to-indigo-50/50 p-5 shadow-sm
                           transition-all duration-300
                           hover:border-violet-300
                           hover:shadow-lg hover:shadow-violet-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-violet-900/60
                           dark:from-violet-950/30 dark:via-slate-900
                           dark:to-indigo-950/20"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8
                               h-24 w-24 rounded-full bg-violet-400/10
                               transition-all duration-500
                               group-hover:bg-violet-400/20
                               motion-safe:group-hover:scale-150"
                    ></div>

                    <div
                        class="relative mb-5 flex items-center
                               justify-between"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl bg-violet-500/10
                                   text-violet-600 transition-all duration-300
                                   group-hover:bg-violet-100
                                   motion-safe:group-hover:scale-105
                                   dark:text-violet-400
                                   dark:group-hover:bg-violet-950/70"
                        >
                            <i
                                data-lucide="map-pinned"
                                stroke-width="1.8"
                                class="h-5 w-5 shrink-0
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110"
                            ></i>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-violet-500/10 px-2.5 py-1
                                   text-xs font-medium text-violet-700
                                   dark:text-violet-400"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                            CNT
                        </span>
                    </div>

                    <p
                        class="relative text-2xl font-semibold leading-none
                               text-foreground"
                    >
                        {{ $guardiasCNT }}
                    </p>

                    <p class="relative mt-2 text-sm text-muted-foreground">
                        Guardias en CNT
                    </p>
                </div>

            </div>

        </section>


        {{-- Filtros --}}

        <section class="mb-8">

            <form
                method="GET"
                action="{{
                    route(
                        'admin.guardias.mis-guardias'
                    )
                }}"
                class="flex flex-col gap-4 rounded-2xl
                       border border-border bg-card p-5 shadow-sm
                       transition-shadow duration-300 hover:shadow-md
                       dark:border-slate-700
                       sm:flex-row sm:items-end"
            >
                <div class="flex-1">

                    <label
                        for="mes"
                        class="mb-2 block text-xs font-semibold
                               uppercase tracking-widest
                               text-muted-foreground"
                    >
                        Mes
                    </label>

                    <select
                        id="mes"
                        name="mes"
                        class="w-full rounded-lg border border-border
                               bg-card px-3.5 py-2.5 text-sm
                               text-foreground shadow-sm
                               focus:border-primary focus:outline-none
                               focus:ring-2 focus:ring-primary/10
                               dark:border-slate-700
                               dark:focus:border-blue-500"
                    >
                        @foreach($meses as $numero => $nombre)

                            <option
                                value="{{ $numero }}"
                                @selected(
                                    (int) $mes
                                    ===
                                    $numero
                                )
                            >
                                {{ $nombre }}
                            </option>

                        @endforeach
                    </select>

                </div>


                <div class="flex-1">

                    <label
                        for="anio"
                        class="mb-2 block text-xs font-semibold
                               uppercase tracking-widest
                               text-muted-foreground"
                    >
                        Año
                    </label>

                    <select
                        id="anio"
                        name="anio"
                        class="w-full rounded-lg border border-border
                               bg-card px-3.5 py-2.5 text-sm
                               text-foreground shadow-sm
                               focus:border-primary focus:outline-none
                               focus:ring-2 focus:ring-primary/10
                               dark:border-slate-700
                               dark:focus:border-blue-500"
                    >
                        @foreach($aniosDisponibles as $anioDisponible)

                            <option
                                value="{{ $anioDisponible }}"
                                @selected(
                                    (int) $anio
                                    ===
                                    (int) $anioDisponible
                                )
                            >
                                {{ $anioDisponible }}
                            </option>

                        @endforeach
                    </select>

                </div>


                <button
                    type="submit"
                    class="inline-flex items-center justify-center
                           gap-2 rounded-lg bg-primary px-5 py-2.5
                           text-sm font-semibold text-white shadow-sm
                           transition-all duration-200
                           hover:bg-primary/90 hover:shadow-md"
                >
                    <i
                        data-lucide="filter"
                        stroke-width="1.8"
                        class="h-4 w-4"
                    ></i>

                    Aplicar
                </button>

            </form>

        </section>


        {{-- Listado de guardias --}}

        <section>

            <div class="mb-5">

                <h2 class="text-base font-semibold text-foreground">
                    {{
                        $esAdministrador
                            ? 'Guardias programadas'
                            : 'Asignaciones del período'
                    }}
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    {{ $meses[$mes] }} de {{ $anio }}
                </p>

            </div>


            @if($guardias->isNotEmpty())

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                    @foreach($guardias as $guardia)

                        @php

                            $esHoy = $guardia
                                ->fecha
                                ->isToday();

                            $esPasada = $guardia
                                ->fecha
                                ->copy()
                                ->startOfDay()
                                ->isBefore(
                                    today()
                                );

                            $esProxima = ! $esHoy
                                && ! $esPasada;

                        @endphp


                        <article
                            @class([
                                'group relative overflow-hidden rounded-2xl border bg-card shadow-sm transition-all duration-300 hover:shadow-lg motion-safe:hover:-translate-y-0.5',

                                'border-emerald-300 hover:border-emerald-400 dark:border-emerald-800' =>
                                    $esHoy,

                                'border-cyan-200/70 hover:border-cyan-300 dark:border-cyan-900/60 dark:hover:border-cyan-800' =>
                                    $esProxima,

                                'border-border opacity-75 dark:border-slate-700' =>
                                    $esPasada,
                            ])
                        >
                            {{-- Decoración --}}

                            <div
                                @class([
                                    'pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full blur-2xl transition-transform duration-500 group-hover:scale-150',

                                    'bg-emerald-400/15' =>
                                        $esHoy,

                                    'bg-cyan-400/10' =>
                                        $esProxima,

                                    'bg-slate-400/10' =>
                                        $esPasada,
                                ])
                            ></div>


                            <div class="relative p-5">

                                {{-- Fecha y estado --}}

                                <div
                                    class="mb-5 flex items-start
                                           justify-between gap-4"
                                >
                                    <div class="flex items-center gap-3">

                                        <div
                                            @class([
                                                'flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl',

                                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' =>
                                                    $esHoy,

                                                'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300' =>
                                                    $esProxima,

                                                'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' =>
                                                    $esPasada,
                                            ])
                                        >
                                            <span
                                                class="text-[10px]
                                                       font-semibold uppercase"
                                            >
                                                {{
                                                    $guardia
                                                        ->fecha
                                                        ->locale('es')
                                                        ->translatedFormat(
                                                            'D'
                                                        )
                                                }}
                                            </span>

                                            <span
                                                class="text-lg font-semibold
                                                       leading-none"
                                            >
                                                {{
                                                    $guardia
                                                        ->fecha
                                                        ->format('d')
                                                }}
                                            </span>
                                        </div>

                                        <div>

                                            <p
                                                class="text-sm font-semibold
                                                       capitalize
                                                       text-foreground"
                                            >
                                                {{
                                                    $guardia
                                                        ->fecha
                                                        ->locale('es')
                                                        ->translatedFormat(
                                                            'l d \d\e F'
                                                        )
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs
                                                       text-muted-foreground"
                                            >
                                                {{
                                                    $guardia
                                                        ->fecha
                                                        ->format('Y')
                                                }}
                                            </p>

                                        </div>

                                    </div>


                                    @if($esHoy)

                                        <span
                                            class="inline-flex items-center
                                                   gap-1.5 rounded-full
                                                   bg-emerald-500/10
                                                   px-2.5 py-1 text-xs
                                                   font-medium text-emerald-700
                                                   dark:text-emerald-400"
                                        >
                                            <span class="relative flex h-2 w-2">

                                                <span
                                                    class="absolute inline-flex
                                                           h-full w-full
                                                           animate-ping
                                                           rounded-full
                                                           bg-emerald-400
                                                           opacity-60"
                                                ></span>

                                                <span
                                                    class="relative inline-flex
                                                           h-2 w-2
                                                           rounded-full
                                                           bg-emerald-500"
                                                ></span>

                                            </span>

                                            Guardia de hoy
                                        </span>

                                    @elseif($esPasada)

                                        <span
                                            class="inline-flex items-center
                                                   rounded-full bg-slate-500/10
                                                   px-2.5 py-1 text-xs
                                                   font-medium text-slate-500
                                                   dark:text-slate-400"
                                        >
                                            Finalizada
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex items-center
                                                   gap-1.5 rounded-full
                                                   bg-cyan-500/10
                                                   px-2.5 py-1 text-xs
                                                   font-medium text-cyan-700
                                                   dark:text-cyan-400"
                                        >
                                            <span
                                                class="h-1.5 w-1.5
                                                       rounded-full
                                                       bg-cyan-500"
                                            ></span>

                                            Próxima
                                        </span>

                                    @endif

                                </div>


                                {{-- Agente --}}

                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex h-10 w-10 shrink-0
                                               items-center justify-center
                                               rounded-full bg-primary/10
                                               text-sm font-semibold
                                               text-primary"
                                    >
                                        {{
                                            mb_strtoupper(
                                                mb_substr(
                                                    $guardia
                                                        ->agente
                                                        ->nombre,
                                                    0,
                                                    1
                                                )
                                            )
                                        }}
                                    </div>

                                    <div class="min-w-0">

                                        <p
                                            class="truncate text-sm
                                                   font-semibold
                                                   text-foreground"
                                        >
                                            {{ $guardia->agente->nombre }}
                                        </p>

                                        <p
                                            class="mt-0.5 truncate text-xs
                                                   text-muted-foreground"
                                        >
                                            {{
                                                $esAdministrador
                                                    ? $guardia
                                                        ->agente
                                                        ->correo
                                                    : 'Agente de soporte asignado'
                                            }}
                                        </p>

                                    </div>

                                </div>


                                {{-- Horario y ubicación --}}

                                <div
                                    class="mt-5 grid grid-cols-1 gap-3
                                           sm:grid-cols-2"
                                >
                                    <div
                                        class="flex items-center gap-3
                                               rounded-xl border border-border
                                               bg-muted/30 px-3.5 py-3
                                               dark:border-slate-700"
                                    >
                                        <div
                                            class="flex h-8 w-8 shrink-0
                                                   items-center justify-center
                                                   rounded-lg bg-blue-100
                                                   text-blue-700
                                                   dark:bg-blue-950/60
                                                   dark:text-blue-300"
                                        >
                                            <i
                                                data-lucide="clock-3"
                                                stroke-width="1.8"
                                                class="h-4 w-4"
                                            ></i>
                                        </div>

                                        <div>

                                            <p
                                                class="text-[10px]
                                                       font-semibold uppercase
                                                       tracking-wide
                                                       text-muted-foreground"
                                            >
                                                Horario
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs
                                                       font-medium
                                                       text-foreground"
                                            >
                                                {{ $guardia->horario }}
                                            </p>

                                        </div>
                                    </div>


                                    <div
                                        class="flex items-center gap-3
                                               rounded-xl border border-border
                                               bg-muted/30 px-3.5 py-3
                                               dark:border-slate-700"
                                    >
                                        <div
                                            @class([
                                                'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',

                                                'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300' =>
                                                    $guardia->ubicacion
                                                    === 'TVC',

                                                'bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300' =>
                                                    $guardia->ubicacion
                                                    === 'CNT',
                                            ])
                                        >
                                            <i
                                                data-lucide="map-pin"
                                                stroke-width="1.8"
                                                class="h-4 w-4"
                                            ></i>
                                        </div>

                                        <div>

                                            <p
                                                class="text-[10px]
                                                       font-semibold uppercase
                                                       tracking-wide
                                                       text-muted-foreground"
                                            >
                                                Ubicación
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs
                                                       font-semibold
                                                       text-foreground"
                                            >
                                                {{ $guardia->ubicacion }}
                                            </p>

                                        </div>
                                    </div>

                                </div>


                                {{-- Observación --}}

                                @if($guardia->observacion)

                                    <div
                                        class="mt-4 flex items-start gap-2.5
                                               rounded-xl border border-border
                                               bg-muted/20 px-3.5 py-3
                                               dark:border-slate-700"
                                    >
                                        <i
                                            data-lucide="message-square-text"
                                            stroke-width="1.8"
                                            class="mt-0.5 h-4 w-4 shrink-0
                                                   text-muted-foreground"
                                        ></i>

                                        <p
                                            class="text-xs leading-relaxed
                                                   text-muted-foreground"
                                        >
                                            {{ $guardia->observacion }}
                                        </p>
                                    </div>

                                @endif

                            </div>

                        </article>

                    @endforeach

                </div>

            @else

                {{-- Sin guardias --}}

                <div
                    class="relative overflow-hidden rounded-2xl
                           border border-dashed border-border
                           bg-card p-10 text-center shadow-sm
                           dark:border-slate-700"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center
                               justify-center rounded-full bg-muted
                               text-muted-foreground"
                    >
                        <i
                            data-lucide="calendar-off"
                            stroke-width="1.8"
                            class="h-7 w-7"
                        ></i>
                    </div>

                    <h3
                        class="mt-4 text-sm font-semibold
                               text-foreground"
                    >
                        {{
                            $esAdministrador
                                ? 'No hay guardias programadas'
                                : 'No tienes guardias asignadas'
                        }}
                    </h3>

                    <p
                        class="mx-auto mt-1.5 max-w-md text-sm
                               leading-relaxed text-muted-foreground"
                    >
                        @if($esAdministrador)

                            No existen guardias activas para
                            {{ $meses[$mes] }} de {{ $anio }}.

                        @else

                            No tienes asignaciones de guardia para
                            {{ $meses[$mes] }} de {{ $anio }}.

                        @endif
                    </p>


                    @if($esAdministrador)

                        <a
                            href="{{ route('admin.guardias.index') }}"
                            class="mt-5 inline-flex items-center
                                   justify-center gap-2 rounded-lg
                                   bg-primary px-4 py-2.5 text-sm
                                   font-semibold text-white shadow-sm
                                   transition-colors hover:bg-primary/90"
                        >
                            <i
                                data-lucide="calendar-plus"
                                stroke-width="1.8"
                                class="h-4 w-4"
                            ></i>

                            Programar guardia
                        </a>

                    @endif

                </div>

            @endif

        </section>


        {{-- Información --}}

        <section class="mt-8">

            <div
                class="group/info relative overflow-hidden rounded-2xl
                       border border-primary/10
                       bg-gradient-to-br from-primary/[0.05]
                       via-white to-blue-50/50 p-5 shadow-sm
                       transition-all duration-300
                       hover:border-primary/20 hover:shadow-md
                       motion-safe:hover:-translate-y-0.5
                       dark:border-blue-900/70
                       dark:via-slate-900 dark:to-blue-950/20
                       dark:hover:border-blue-800/80"
            >
                <span
                    class="pointer-events-none absolute -right-10 -top-12
                           h-32 w-32 rounded-full bg-primary/10 blur-3xl
                           transition-all duration-500
                           group-hover/info:bg-primary/20
                           motion-safe:group-hover/info:scale-125"
                ></span>
                <div class="relative flex items-start gap-3.5">

                    <div
                        class="flex h-9 w-9 shrink-0 items-center
                               justify-center rounded-lg bg-primary/10
                               text-primary transition-all duration-300
                               group-hover/info:bg-primary/15
                               motion-safe:group-hover/info:scale-105"
                    >
                        <i
                            data-lucide="info"
                            stroke-width="1.8"
                            class="h-[18px] w-[18px]"
                        ></i>
                    </div>

                    <div>

                        <h3
                            class="text-sm font-semibold
                                   text-foreground"
                        >
                            Información de las guardias
                        </h3>

                        <p
                            class="mt-1 text-sm leading-relaxed
                                   text-muted-foreground"
                        >
                            Las guardias corresponden únicamente a sábados
                            y domingos. Verifica la fecha, el horario y la
                            ubicación antes de cada asignación.
                        </p>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection
