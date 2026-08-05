@extends('layouts.app')

@section('title', 'Administración de turnos')

@section('content')

@php

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
    | Obtener sábados y domingos del período
    |--------------------------------------------------------------------------
    */

    $inicioMes = \Carbon\Carbon::create(
        $anio,
        $mes,
        1
    )->startOfMonth();

    $finMes = $inicioMes
        ->copy()
        ->endOfMonth();

    $finesDeSemana = collect(
        \Carbon\CarbonPeriod::create(
            $inicioMes,
            $finMes
        )
    )
        ->filter(
            fn ($fecha) =>
                $fecha->isSaturday()
                || $fecha->isSunday()
        )
        ->values();

    $guardiasPorFecha = $guardias
        ->getCollection()
        ->keyBy(
            fn ($guardia) =>
                $guardia->fecha->format('Y-m-d')
        );

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
                                data-lucide="headphones"
                                stroke-width="1.8"
                                class="h-3.5 w-3.5"
                            ></i>

                            Soporte TI
                        </span>

                        <h1
                            class="text-2xl font-semibold tracking-tight
                                   text-foreground"
                        >
                            Administración de turnos
                        </h1>

                        <p
                            class="mt-1.5 max-w-2xl text-sm leading-relaxed
                                   text-muted-foreground"
                        >
                            Asigna los agentes que atenderán durante los
                            fines de semana en TVC o CNT.
                        </p>

                </div>

            </div>

        </section>


        {{-- Mensajes --}}

        @if(session('success'))

            <div
                class="mb-6 flex items-start gap-3 rounded-xl
                       border border-emerald-200/60 bg-emerald-50
                       px-4 py-3 text-sm text-emerald-700
                       dark:border-emerald-900/60
                       dark:bg-emerald-950/30
                       dark:text-emerald-300"
            >
                <i
                    data-lucide="circle-check"
                    stroke-width="1.8"
                    class="mt-0.5 h-4 w-4 shrink-0"
                ></i>

                <span>
                    {{ session('success') }}
                </span>
            </div>

        @endif


        @if($errors->any())

            <div
                class="mb-6 rounded-xl border border-red-200/60
                       bg-red-50 px-4 py-3
                       dark:border-red-900/60 dark:bg-red-950/30"
            >
                <div class="flex items-start gap-3">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="mt-0.5 h-4 w-4 shrink-0
                               text-red-600 dark:text-red-400"
                    ></i>

                    <div>

                        <p
                            class="text-sm font-semibold
                                   text-red-700 dark:text-red-300"
                        >
                            Revisa la información ingresada
                        </p>

                        <ul
                            class="mt-1 list-inside list-disc
                                   text-xs text-red-600
                                   dark:text-red-400"
                        >
                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach
                        </ul>

                    </div>

                </div>
            </div>

        @endif


        {{-- Formulario de asignación --}}

        <section
            class="group/form mb-10 overflow-hidden rounded-2xl
                   border border-cyan-200/60 bg-card shadow-sm
                   transition-all duration-300
                   hover:border-cyan-300 hover:shadow-md
                   dark:border-cyan-900/60
                   dark:hover:border-cyan-800"
        >
            <div
                class="flex items-center gap-3 border-b border-cyan-200/60
                       bg-gradient-to-r from-cyan-500/[0.06]
                       via-transparent to-transparent px-6 py-5
                       dark:border-cyan-900/60"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center
                           justify-center rounded-xl bg-cyan-500/10
                           text-cyan-600 transition-all duration-300
                           motion-safe:group-hover/form:scale-105
                           dark:bg-cyan-950/60 dark:text-cyan-300"
                >
                    <i
                        data-lucide="user-round-plus"
                        stroke-width="1.8"
                        class="h-[18px] w-[18px]"
                    ></i>
                </div>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">
                        Asignar nuevo turno
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Selecciona el agente, la fecha, el horario y
                        la ubicación.
                    </p>

                </div>
            </div>


            <form
                method="POST"
                action="{{ route('admin.guardias.store') }}"
                class="p-6"
            >
                @csrf

                <div
                    class="grid grid-cols-1 gap-5
                           md:grid-cols-2 xl:grid-cols-4"
                >

                    {{-- Agente --}}

                    <div class="md:col-span-2 xl:col-span-1">

                        <label
                            for="usuario_id"
                            class="mb-2 block text-xs font-semibold
                                   uppercase tracking-widest
                                   text-muted-foreground"
                        >
                            Agente de soporte

                            <span class="text-primary">*</span>
                        </label>

                        <select
                            id="usuario_id"
                            name="usuario_id"
                            required
                            @disabled($agentes->isEmpty())
                            class="w-full rounded-lg border border-border
                                   bg-card px-3.5 py-2.5 text-sm
                                   text-foreground shadow-sm
                                   transition-all duration-200
                                   focus:border-primary focus:outline-none
                                   focus:ring-2 focus:ring-primary/10
                                   dark:border-slate-700
                                   dark:focus:border-blue-500
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60"
                        >
                            <option value="" disabled
                                @selected(! old('usuario_id'))
                            >
                                Selecciona un agente
                            </option>

                            @foreach($agentes as $agente)

                                <option
                                    value="{{ $agente->id }}"
                                    @selected(
                                        (string) old('usuario_id')
                                        ===
                                        (string) $agente->id
                                    )
                                >
                                    {{ $agente->nombre }}
                                </option>

                            @endforeach
                        </select>

                    </div>


                    {{-- Fecha --}}

                    <div>

                        <label
                            for="fecha"
                            class="mb-2 block text-xs font-semibold
                                   uppercase tracking-widest
                                   text-muted-foreground"
                        >
                            Fecha

                            <span class="text-primary">*</span>
                        </label>

                        <input
    type="date"
    id="fecha"
    name="fecha"
    value="{{ old('fecha') }}"
    min="{{ today()->format('Y-m-d') }}"
    required
    inputmode="none"
    onkeydown="event.preventDefault()"
    onpaste="event.preventDefault()"
    onclick="this.showPicker && this.showPicker()"
    onfocus="this.showPicker && this.showPicker()"
    class="w-full rounded-lg border border-border
           bg-card px-3.5 py-2.5 text-sm
           text-foreground shadow-sm
           transition-all duration-200
           focus:border-primary focus:outline-none
           focus:ring-2 focus:ring-primary/10
           dark:border-slate-700
           dark:focus:border-blue-500
           cursor-pointer"
>

                        <p class="mt-1.5 text-xs text-muted-foreground">
                            Únicamente sábado o domingo.
                        </p>

                    </div>


                    {{-- Hora inicial --}}

                    <div>

                        <label
                            for="hora_inicio"
                            class="mb-2 block text-xs font-semibold
                                   uppercase tracking-widest
                                   text-muted-foreground"
                        >
                            Hora inicial

                            <span class="text-primary">*</span>
                        </label>

                        <input
                            type="time"
                            id="hora_inicio"
                            name="hora_inicio"
                            value="{{ old('hora_inicio', '09:00') }}"
                            required
                            class="w-full rounded-lg border border-border
                                   bg-card px-3.5 py-2.5 text-sm
                                   text-foreground shadow-sm
                                   transition-all duration-200
                                   focus:border-primary focus:outline-none
                                   focus:ring-2 focus:ring-primary/10
                                   dark:border-slate-700
                                   dark:focus:border-blue-500"
                        >

                    </div>


                    {{-- Hora final --}}

                    <div>

                        <label
                            for="hora_fin"
                            class="mb-2 block text-xs font-semibold
                                   uppercase tracking-widest
                                   text-muted-foreground"
                        >
                            Hora final

                            <span class="text-primary">*</span>
                        </label>

                        <input
                            type="time"
                            id="hora_fin"
                            name="hora_fin"
                            value="{{ old('hora_fin', '18:00') }}"
                            required
                            class="w-full rounded-lg border border-border
                                   bg-card px-3.5 py-2.5 text-sm
                                   text-foreground shadow-sm
                                   transition-all duration-200
                                   focus:border-primary focus:outline-none
                                   focus:ring-2 focus:ring-primary/10
                                   dark:border-slate-700
                                   dark:focus:border-blue-500"
                        >

                    </div>


                    {{-- Ubicación --}}

                    <div>

                        <label
                            for="ubicacion"
                            class="mb-2 block text-xs font-semibold
                                   uppercase tracking-widest
                                   text-muted-foreground"
                        >
                            Ubicación

                            <span class="text-primary">*</span>
                        </label>

                        <select
                            id="ubicacion"
                            name="ubicacion"
                            required
                            class="w-full rounded-lg border border-border
                                   bg-card px-3.5 py-2.5 text-sm
                                   text-foreground shadow-sm
                                   transition-all duration-200
                                   focus:border-primary focus:outline-none
                                   focus:ring-2 focus:ring-primary/10
                                   dark:border-slate-700
                                   dark:focus:border-blue-500"
                        >
                            <option value="" disabled
                                @selected(! old('ubicacion'))
                            >
                                Selecciona la ubicación
                            </option>

                            <option
                                value="TVC"
                                @selected(old('ubicacion') === 'TVC')
                            >
                                TVC
                            </option>

                            <option
                                value="CNT"
                                @selected(old('ubicacion') === 'CNT')
                            >
                                CNT
                            </option>
                        </select>

                    </div>


                    {{-- Observación --}}

                    <div class="md:col-span-2 xl:col-span-3">

                        <label
                            for="observacion"
                            class="mb-2 block text-xs font-semibold
                                   uppercase tracking-widest
                                   text-muted-foreground"
                        >
                            Observación

                            <span
                                class="font-normal normal-case tracking-normal"
                            >
                                (opcional)
                            </span>
                        </label>

                        <input
                            type="text"
                            id="observacion"
                            name="observacion"
                            value="{{ old('observacion') }}"
                            maxlength="500"
                            placeholder="Información adicional del turno"
                            class="w-full rounded-lg border border-border
                                   bg-card px-3.5 py-2.5 text-sm
                                   text-foreground shadow-sm
                                   placeholder:text-muted-foreground
                                   transition-all duration-200
                                   focus:border-primary focus:outline-none
                                   focus:ring-2 focus:ring-primary/10
                                   dark:border-slate-700
                                   dark:focus:border-blue-500"
                        >

                    </div>

                </div>


                @if($agentes->isEmpty())

                    <div
                        class="mt-5 flex items-start gap-2.5 rounded-xl
                               border border-amber-200/60 bg-amber-50
                               px-4 py-3 text-xs text-amber-700
                               dark:border-amber-900/60
                               dark:bg-amber-950/30
                               dark:text-amber-300"
                    >
                        <i
                            data-lucide="triangle-alert"
                            stroke-width="1.8"
                            class="mt-px h-4 w-4 shrink-0"
                        ></i>

                        <span>
                            No existen usuarios activos con el rol
                            UsuarioTI. Asigna ese rol antes de programar
                            una guardia.
                        </span>
                    </div>

                @endif


                <div
                    class="mt-6 flex justify-end border-t
                           border-border pt-5 dark:border-slate-700"
                >
                    <button
                        type="submit"
                        @disabled($agentes->isEmpty())
                        class="group inline-flex items-center
                               justify-center gap-2 rounded-lg
                               bg-primary px-5 py-2.5
                               text-sm font-semibold text-white
                               shadow-sm transition-all duration-200
                               hover:bg-primary/90 hover:shadow-md
                               disabled:cursor-not-allowed
                               disabled:opacity-60
                               motion-safe:hover:-translate-y-0.5"
                    >
                        <i
                            data-lucide="calendar-plus"
                            stroke-width="1.8"
                            class="h-4 w-4 transition-transform
                                   group-hover:scale-110"
                        ></i>

                        Asignar turno
                    </button>
                </div>

            </form>

        </section>


        {{-- Filtros --}}

        <section class="mb-6">

            <form
                method="GET"
                action="{{ route('admin.guardias.index') }}"
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
                                @selected((int) $mes === $numero)
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


        {{-- Calendario de fines de semana --}}

        <section>

            <div class="mb-5">

                <h2 class="text-base font-semibold text-foreground">
                    Turnos de {{ $meses[$mes] }} de {{ $anio }}
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Se muestran todos los sábados y domingos del período,
                    incluyendo los días que todavía no tienen asignación.
                </p>

            </div>


            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                @foreach($finesDeSemana as $fecha)

                    @php

                        $fechaLlave = $fecha->format('Y-m-d');

                        $guardia = $guardiasPorFecha
                            ->get($fechaLlave);

                        $esPasada = $fecha
                            ->copy()
                            ->startOfDay()
                            ->isBefore(today());

                    @endphp


                    @if($guardia)

                        {{-- Guardia asignada --}}

                        <article
                            @class([
                                'group relative overflow-hidden rounded-2xl border bg-card shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-cyan-500/10 motion-safe:hover:-translate-y-0.5',

                                'border-cyan-200/70 hover:border-cyan-300 dark:border-cyan-900/60' =>
                                    $guardia->activo,

                                'border-slate-200 opacity-75 dark:border-slate-700' =>
                                    ! $guardia->activo,
                            ])
                        >
                            <div
                                @class([
                                    'pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full transition-all duration-500 motion-safe:group-hover:scale-150',

                                    'bg-cyan-400/10 group-hover:bg-cyan-400/20' =>
                                        $guardia->activo,

                                    'bg-slate-400/10 group-hover:bg-slate-400/15' =>
                                        ! $guardia->activo,
                                ])
                            ></div>

                            <div class="relative p-5">

                                <div
                                    class="mb-4 flex items-start
                                           justify-between gap-4"
                                >
                                    <div class="flex items-center gap-3">

                                        <div
                                            @class([
                                                'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-all duration-300 motion-safe:group-hover:scale-105',

                                                'bg-cyan-500/10 text-cyan-600 group-hover:bg-cyan-100 dark:text-cyan-400 dark:group-hover:bg-cyan-950/70' =>
                                                    $guardia->activo,

                                                'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' =>
                                                    ! $guardia->activo,
                                            ])
                                        >
                                            <i
                                                data-lucide="calendar-check-2"
                                                stroke-width="1.8"
                                                class="h-5 w-5 transition-transform
                                                       duration-300
                                                       motion-safe:group-hover:scale-110"
                                            ></i>
                                        </div>

                                        <div>

                                            <p
                                                class="text-sm font-semibold
                                                       capitalize
                                                       text-foreground"
                                            >
                                                {{
                                                    $fecha
                                                        ->locale('es')
                                                        ->translatedFormat(
                                                            'l d'
                                                        )
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs
                                                       text-muted-foreground"
                                            >
                                                {{
                                                    $fecha
                                                        ->locale('es')
                                                        ->translatedFormat(
                                                            'F Y'
                                                        )
                                                }}
                                            </p>

                                        </div>

                                    </div>


                                    @if($guardia->activo)

                                        <span
                                            class="inline-flex items-center
                                                   gap-1.5 rounded-full
                                                   bg-emerald-500/10
                                                   px-2.5 py-1 text-xs
                                                   font-medium text-emerald-700
                                                   dark:text-emerald-400"
                                        >
                                            <span
                                                class="h-1.5 w-1.5
                                                       rounded-full
                                                       bg-emerald-500"
                                            ></span>

                                            Asignada
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex items-center
                                                   gap-1.5 rounded-full
                                                   bg-slate-500/10
                                                   px-2.5 py-1 text-xs
                                                   font-medium text-slate-600
                                                   dark:text-slate-400"
                                        >
                                            Cancelada
                                        </span>

                                    @endif

                                </div>


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
                                            {{ $guardia->agente->correo }}
                                        </p>

                                    </div>

                                </div>


                                <div
                                    class="mt-4 grid grid-cols-1 gap-3
                                           sm:grid-cols-2"
                                >
                                    <div
                                        class="flex items-center gap-2
                                               rounded-lg bg-muted/50
                                               px-3 py-2.5"
                                    >
                                        <i
                                            data-lucide="clock"
                                            stroke-width="1.8"
                                            class="h-4 w-4 shrink-0
                                                   text-primary"
                                        ></i>

                                        <span
                                            class="text-xs
                                                   text-muted-foreground"
                                        >
                                            {{ $guardia->horario }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center gap-2
                                               rounded-lg bg-muted/50
                                               px-3 py-2.5"
                                    >
                                        <i
                                            data-lucide="map-pin"
                                            stroke-width="1.8"
                                            class="h-4 w-4 shrink-0
                                                   text-primary"
                                        ></i>

                                        <span
                                            class="text-xs font-medium
                                                   text-foreground"
                                        >
                                            {{ $guardia->ubicacion }}
                                        </span>
                                    </div>
                                </div>


                                @if($guardia->observacion)

                                    <div
                                        class="mt-3 rounded-lg
                                               border border-border
                                               bg-muted/30 px-3 py-2.5
                                               dark:border-slate-700"
                                    >
                                        <p
                                            class="text-xs leading-relaxed
                                                   text-muted-foreground"
                                        >
                                            {{ $guardia->observacion }}
                                        </p>
                                    </div>

                                @endif

                            </div>


                            {{-- Acciones --}}

                            <div
                                class="flex flex-wrap items-center
                                       justify-end gap-2
                                       border-t border-border
                                       bg-muted/20 px-5 py-3
                                       dark:border-slate-700"
                            >
                                @if(! $esPasada)

                                    <details class="relative">

                                        <summary
                                            class="inline-flex cursor-pointer
                                                   list-none items-center gap-1.5
                                                   rounded-lg border
                                                   border-border bg-card
                                                   px-3 py-2 text-xs
                                                   font-medium text-foreground
                                                   transition-colors
                                                   hover:border-primary/30
                                                   hover:text-primary
                                                   dark:border-slate-700"
                                        >
                                            <i
                                                data-lucide="pencil"
                                                stroke-width="1.8"
                                                class="h-3.5 w-3.5"
                                            ></i>

                                            Editar
                                        </summary>

                                        <div
                                            class="mt-3 w-full rounded-xl
                                                   border border-border
                                                   bg-card p-4 shadow-lg
                                                   dark:border-slate-700
                                                   sm:w-[420px]"
                                        >
                                            <form
                                                method="POST"
                                                action="{{
                                                    route(
                                                        'admin.guardias.update',
                                                        $guardia
                                                    )
                                                }}"
                                            >
                                                @csrf
                                                @method('PUT')

                                                <div
                                                    class="grid grid-cols-1
                                                           gap-4 sm:grid-cols-2"
                                                >
                                                    <div
                                                        class="sm:col-span-2"
                                                    >
                                                        <label
                                                            class="mb-1.5 block
                                                                   text-xs
                                                                   font-medium
                                                                   text-muted-foreground"
                                                        >
                                                            Agente
                                                        </label>

                                                        <select
                                                            name="usuario_id"
                                                            required
                                                            class="w-full
                                                                   rounded-lg
                                                                   border
                                                                   border-border
                                                                   bg-card
                                                                   px-3 py-2
                                                                   text-sm
                                                                   text-foreground"
                                                        >
                                                            @foreach(
                                                                $agentes
                                                                as $agente
                                                            )
                                                                <option
                                                                    value="{{
                                                                        $agente
                                                                            ->id
                                                                    }}"
                                                                    @selected(
                                                                        $guardia
                                                                            ->usuario_id
                                                                        ===
                                                                        $agente
                                                                            ->id
                                                                    )
                                                                >
                                                                    {{
                                                                        $agente
                                                                            ->nombre
                                                                    }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div
                                                        class="sm:col-span-2"
                                                    >
                                                        <label
                                                            class="mb-1.5 block
                                                                   text-xs
                                                                   font-medium
                                                                   text-muted-foreground"
                                                        >
                                                            Fecha
                                                        </label>

                                                        <input
    type="date"
    name="fecha"
    value="{{ $guardia->fecha->format('Y-m-d') }}"
    min="{{ today()->format('Y-m-d') }}"
    required
    inputmode="none"
    onkeydown="event.preventDefault()"
    onpaste="event.preventDefault()"
    onclick="this.showPicker && this.showPicker()"
    onfocus="this.showPicker && this.showPicker()"
    class="w-full
           rounded-lg
           border
           border-border
           bg-card
           px-3 py-2
           text-sm
           text-foreground
           cursor-pointer"
>
                                                    </div>

                                                    <div>
                                                        <label
                                                            class="mb-1.5 block
                                                                   text-xs
                                                                   font-medium
                                                                   text-muted-foreground"
                                                        >
                                                            Hora inicial
                                                        </label>

                                                        <input
                                                            type="time"
                                                            name="hora_inicio"
                                                            value="{{
                                                                substr(
                                                                    $guardia
                                                                        ->hora_inicio,
                                                                    0,
                                                                    5
                                                                )
                                                            }}"
                                                            required
                                                            class="w-full
                                                                   rounded-lg
                                                                   border
                                                                   border-border
                                                                   bg-card
                                                                   px-3 py-2
                                                                   text-sm
                                                                   text-foreground"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label
                                                            class="mb-1.5 block
                                                                   text-xs
                                                                   font-medium
                                                                   text-muted-foreground"
                                                        >
                                                            Hora final
                                                        </label>

                                                        <input
                                                            type="time"
                                                            name="hora_fin"
                                                            value="{{
                                                                substr(
                                                                    $guardia
                                                                        ->hora_fin,
                                                                    0,
                                                                    5
                                                                )
                                                            }}"
                                                            required
                                                            class="w-full
                                                                   rounded-lg
                                                                   border
                                                                   border-border
                                                                   bg-card
                                                                   px-3 py-2
                                                                   text-sm
                                                                   text-foreground"
                                                        >
                                                    </div>

                                                    <div>
                                                        <label
                                                            class="mb-1.5 block
                                                                   text-xs
                                                                   font-medium
                                                                   text-muted-foreground"
                                                        >
                                                            Ubicación
                                                        </label>

                                                        <select
                                                            name="ubicacion"
                                                            required
                                                            class="w-full
                                                                   rounded-lg
                                                                   border
                                                                   border-border
                                                                   bg-card
                                                                   px-3 py-2
                                                                   text-sm
                                                                   text-foreground"
                                                        >
                                                            <option
                                                                value="TVC"
                                                                @selected(
                                                                    $guardia
                                                                        ->ubicacion
                                                                    ===
                                                                    'TVC'
                                                                )
                                                            >
                                                                TVC
                                                            </option>

                                                            <option
                                                                value="CNT"
                                                                @selected(
                                                                    $guardia
                                                                        ->ubicacion
                                                                    ===
                                                                    'CNT'
                                                                )
                                                            >
                                                                CNT
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label
                                                            class="mb-1.5 block
                                                                   text-xs
                                                                   font-medium
                                                                   text-muted-foreground"
                                                        >
                                                            Observación
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="observacion"
                                                            value="{{
                                                                $guardia
                                                                    ->observacion
                                                            }}"
                                                            maxlength="500"
                                                            class="w-full
                                                                   rounded-lg
                                                                   border
                                                                   border-border
                                                                   bg-card
                                                                   px-3 py-2
                                                                   text-sm
                                                                   text-foreground"
                                                        >
                                                    </div>
                                                </div>

                                                <div
                                                    class="mt-4 flex
                                                           justify-end"
                                                >
                                                    <button
                                                        type="submit"
                                                        class="inline-flex
                                                               items-center
                                                               gap-2
                                                               rounded-lg
                                                               bg-primary
                                                               px-4 py-2
                                                               text-xs
                                                               font-semibold
                                                               text-white"
                                                    >
                                                        <i
                                                            data-lucide="save"
                                                            stroke-width="1.8"
                                                            class="h-3.5 w-3.5"
                                                        ></i>

                                                        Guardar cambios
                                                    </button>
                                                </div>

                                            </form>
                                        </div>

                                    </details>

                                @endif


                                <form
                                    method="POST"
                                    action="{{
                                        route(
                                            'admin.guardias.change-status',
                                            $guardia
                                        )
                                    }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        @if(
                                            ! $guardia->activo
                                            && $esPasada
                                        )
                                            disabled
                                        @endif
                                        @class([
                                            'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50',

                                            'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-400' =>
                                                $guardia->activo,

                                            'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400' =>
                                                ! $guardia->activo,
                                        ])
                                    >
                                        <i
                                            data-lucide="{{
                                                $guardia->activo
                                                    ? 'calendar-x'
                                                    : 'calendar-check'
                                            }}"
                                            stroke-width="1.8"
                                            class="h-3.5 w-3.5"
                                        ></i>

                                        {{
                                            $guardia->activo
                                                ? 'Cancelar'
                                                : 'Reactivar'
                                        }}
                                    </button>
                                </form>

                            </div>

                        </article>

                    @else

                        {{-- Sin asignación --}}

                        <article
                            class="rounded-2xl border border-dashed
                                   border-slate-300 bg-card/60 p-5
                                   transition-all duration-300
                                   hover:border-cyan-300
                                   dark:border-slate-700
                                   dark:hover:border-cyan-800"
                        >
                            <div
                                class="flex h-full min-h-44 flex-col
                                       items-center justify-center
                                       text-center"
                            >
                                <div
                                    class="flex h-11 w-11 items-center
                                           justify-center rounded-full
                                           bg-slate-100 text-slate-500
                                           dark:bg-slate-800
                                           dark:text-slate-400"
                                >
                                    <i
                                        data-lucide="calendar-x-2"
                                        stroke-width="1.8"
                                        class="h-5 w-5"
                                    ></i>
                                </div>

                                <p
                                    class="mt-3 text-sm font-semibold
                                           capitalize text-foreground"
                                >
                                    {{
                                        $fecha
                                            ->locale('es')
                                            ->translatedFormat(
                                                'l d \d\e F'
                                            )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs
                                           text-muted-foreground"
                                >
                                    Aún no se ha asignado un agente.
                                </p>

                                @if($esPasada)

                                    <span
                                        class="mt-3 inline-flex rounded-full
                                               bg-slate-100 px-2.5 py-1
                                               text-[11px] font-medium
                                               text-slate-500
                                               dark:bg-slate-800
                                               dark:text-slate-400"
                                    >
                                        Fecha finalizada
                                    </span>

                                @else

                                    <button
                                        type="button"
                                        onclick="
                                            document.getElementById('fecha')
                                                .value = '{{ $fechaLlave }}';

                                            document.getElementById('usuario_id')
                                                .focus();

                                            window.scrollTo({
                                                top: 0,
                                                behavior: 'smooth'
                                            });
                                        "
                                        class="mt-3 inline-flex
                                               items-center gap-1.5
                                               rounded-lg bg-cyan-50
                                               px-3 py-2 text-xs
                                               font-medium text-cyan-700
                                               transition-colors
                                               hover:bg-cyan-100
                                               dark:bg-cyan-950/40
                                               dark:text-cyan-300"
                                    >
                                        <i
                                            data-lucide="plus"
                                            stroke-width="1.8"
                                            class="h-3.5 w-3.5"
                                        ></i>

                                        Asignar este día
                                    </button>

                                @endif

                            </div>

                        </article>

                    @endif

                @endforeach

            </div>


            @if($finesDeSemana->isEmpty())

                <div
                    class="rounded-2xl border border-dashed border-border
                           bg-card p-10 text-center
                           dark:border-slate-700"
                >
                    <i
                        data-lucide="calendar-off"
                        stroke-width="1.8"
                        class="mx-auto h-8 w-8 text-muted-foreground"
                    ></i>

                    <p class="mt-3 text-sm text-muted-foreground">
                        No se encontraron fines de semana para este período.
                    </p>
                </div>

            @endif


            @if($guardias->hasPages())

                <div class="mt-6">
                    {{ $guardias->links() }}
                </div>

            @endif

        </section>

    </main>

</div>

@endsection