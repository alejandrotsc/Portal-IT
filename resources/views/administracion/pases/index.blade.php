@extends('layouts.app')

@section('title', 'Administración de pases')

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

@endphp

<div class="min-h-screen bg-background">

    <main class="max-w-7xl mx-auto px-6 py-10">


        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 rounded-full border border-primary/10 bg-primary/5 text-xs font-semibold text-primary dark:border-blue-800/60 dark:bg-blue-950/30">

                <i
                    data-lucide="file-check-2"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5 shrink-0">
                </i>

                Gestión interna

            </div>

            <h1 class="text-2xl font-semibold text-foreground tracking-tight">

                Administración de pases

            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">

                Consulta los pases registrados y actualiza su estado de revisión.

            </p>

        </section>



        {{-- Mensajes --}}

        @if(session('success'))

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">

                    <i
                        data-lucide="circle-check"
                        stroke-width="1.8"
                        class="w-4 h-4">
                    </i>

                </div>

                <p class="pt-1.5 leading-relaxed">

                    {{ session('success') }}

                </p>

            </div>

        @endif


        @if($errors->any())

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800 dark:border-red-900/70 dark:bg-red-950/30 dark:text-red-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="w-4 h-4">
                    </i>

                </div>

                <div class="pt-1.5">

                    @foreach($errors->all() as $error)

                        <p>
                            {{ $error }}
                        </p>

                    @endforeach

                </div>

            </div>

        @endif



        {{-- Resumen --}}

        <section class="mb-10">

            <div class="mb-5">

                <h2 class="text-base font-semibold text-foreground">
                    Resumen de pases
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Estado actual de los pases registrados en el portal.
                </p>

            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                @php

                    $tarjetasResumen = [
                        [
                            'valor' => $resumen['total'] ?? 0,
                            'texto' => 'Pases registrados',
                            'icono' => 'files',
                            'etiqueta' => 'Total',
                            'badgeClases' => 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
                            'puntoClases' => 'bg-blue-500',
                            'clases' => 'border-blue-200/60 from-blue-50 to-indigo-50/60 hover:border-blue-300 hover:shadow-blue-500/10 dark:border-blue-900/60 dark:from-blue-950/30 dark:via-slate-900 dark:to-indigo-950/20 dark:hover:border-blue-800/70',
                            'circulo' => 'bg-blue-400/10 group-hover:bg-blue-400/20',
                            'iconoClases' => 'bg-blue-500/10 text-blue-600 group-hover:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-400 dark:group-hover:bg-blue-900/50',
                        ],
                        [
                            'valor' => $resumen['generados'] ?? 0,
                            'texto' => 'Por revisar',
                            'icono' => 'clock-3',
                            'etiqueta' => 'Pendientes',
                            'badgeClases' => 'bg-amber-500/10 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                            'puntoClases' => 'bg-amber-500',
                            'clases' => 'border-amber-200/60 from-amber-50 to-orange-50/50 hover:border-amber-300 hover:shadow-amber-500/10 dark:border-amber-700/40 dark:from-amber-950/25 dark:via-slate-900 dark:to-orange-950/20 dark:hover:border-amber-700/60',
                            'circulo' => 'bg-amber-400/10 group-hover:bg-amber-400/20',
                            'iconoClases' => 'bg-amber-500/10 text-amber-600 group-hover:bg-amber-100 dark:bg-amber-500/15 dark:text-amber-400 dark:group-hover:bg-amber-900/40',
                        ],
                        [
                            'valor' => $resumen['aprobados'] ?? 0,
                            'texto' => 'Aprobados',
                            'icono' => 'badge-check',
                            'etiqueta' => 'Aprobados',
                            'badgeClases' => 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
                            'puntoClases' => 'bg-emerald-500',
                            'clases' => 'border-emerald-200/60 from-emerald-50 to-teal-50/50 hover:border-emerald-300 hover:shadow-emerald-500/10 dark:border-emerald-900/60 dark:from-emerald-950/25 dark:via-slate-900 dark:to-teal-950/20 dark:hover:border-emerald-800/70',
                            'circulo' => 'bg-emerald-400/10 group-hover:bg-emerald-400/20',
                            'iconoClases' => 'bg-emerald-500/10 text-emerald-600 group-hover:bg-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-400 dark:group-hover:bg-emerald-900/40',
                        ],
                        [
                            'valor' => $resumen['rechazados'] ?? 0,
                            'texto' => 'Rechazados',
                            'icono' => 'circle-x',
                            'etiqueta' => 'Rechazados',
                            'badgeClases' => 'bg-slate-500/10 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                            'puntoClases' => 'bg-slate-400',
                            'clases' => 'border-slate-200 from-slate-50 to-slate-100/60 hover:border-slate-300 hover:shadow-slate-500/10 dark:border-slate-700 dark:from-slate-800/70 dark:via-slate-900 dark:to-slate-800/40 dark:hover:border-slate-600',
                            'circulo' => 'bg-slate-400/10 group-hover:bg-slate-400/20',
                            'iconoClases' => 'bg-slate-500/10 text-slate-600 group-hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600',
                        ],
                    ];

                @endphp


                @foreach($tarjetasResumen as $tarjeta)

                    <div class="group relative overflow-hidden rounded-2xl border bg-gradient-to-br via-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg {{ $tarjeta['clases'] }}">

                        <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full pointer-events-none transition-all duration-500 group-hover:scale-150 {{ $tarjeta['circulo'] }}"></div>

                        <div class="relative flex flex-wrap items-center justify-between gap-4">

                            <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl transition-all duration-300 group-hover:scale-105 {{ $tarjeta['iconoClases'] }}">

                                <i
                                    data-lucide="{{ $tarjeta['icono'] }}"
                                    stroke-width="1.8"
                                    class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                                </i>

                            </div>

                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $tarjeta['badgeClases'] }}">

                                <span class="w-1.5 h-1.5 rounded-full {{ $tarjeta['puntoClases'] }}"></span>

                                {{ $tarjeta['etiqueta'] }}

                            </span>

                            <div class="w-full min-w-0">

                                <p class="text-2xl font-semibold leading-none text-foreground">

                                    {{ $tarjeta['valor'] }}

                                </p>

                                <p class="mt-2 text-sm text-muted-foreground">

                                    {{ $tarjeta['texto'] }}

                                </p>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>



        {{-- Listado --}}

        <section class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden dark:border-slate-700">


            {{-- Cabecera y filtros --}}

            <div class="px-5 py-5 border-b border-border dark:border-slate-700">

                <div class="mb-5">

                    <h2 class="text-base font-semibold text-foreground">

                        Pases registrados

                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">

                        Busca por código, asunto o solicitante y filtra los resultados.

                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('admin.pases') }}"
                    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_170px_160px_130px_190px_auto] gap-3 xl:items-center">


                    {{-- Búsqueda --}}

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15">

                        <i
                            data-lucide="search"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <input
                            type="search"
                            name="buscar"
                            value="{{ $busqueda }}"
                            placeholder="Código, asunto o solicitante..."
                            autocomplete="off"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none">

                    </div>



                    {{-- Estado --}}

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15">

                        <i
                            data-lucide="list-filter"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <select
                            name="estado"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">

                            <option value="">
                                Todos los estados
                            </option>

                            <option
                                value="GENERADO"
                                @selected($estadoSeleccionado === 'GENERADO')>

                                Por revisar

                            </option>

                            <option
                                value="APROBADO"
                                @selected($estadoSeleccionado === 'APROBADO')>

                                Aprobados

                            </option>

                            <option
                                value="RECHAZADO"
                                @selected($estadoSeleccionado === 'RECHAZADO')>

                                Rechazados

                            </option>

                        </select>

                        <i
                            data-lucide="chevron-down"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                    </div>



                    {{-- Tipo --}}

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15">

                        <i
                            data-lucide="files"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <select
                            name="tipo"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">

                            <option value="">
                                Todos los tipos
                            </option>

                            <option
                                value="pase_temporal"
                                @selected($tipoSeleccionado === 'pase_temporal')>

                                Pase menor a 24 horas

                            </option>

                            <option
                                value="autorizacion"
                                @selected($tipoSeleccionado === 'autorizacion')>

                                Pase mayor a 24 horas

                            </option>

                        </select>

                        <i
                            data-lucide="chevron-down"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                    </div>



                    {{-- Mes --}}

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15">

                        <i
                            data-lucide="calendar-days"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <select
                            id="mes"
                            name="mes"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">

                            @foreach($meses as $numero => $nombre)

                                <option
                                    value="{{ $numero }}"
                                    @selected(
                                        (int) $mes
                                        ===
                                        (int) $numero
                                    )>

                                    {{ $nombre }}

                                </option>

                            @endforeach

                        </select>

                        <i
                            data-lucide="chevron-down"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                    </div>


                    {{-- Año --}}

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15">

                        <i
                            data-lucide="calendar-range"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <select
                            id="anio"
                            name="anio"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">

                            @foreach($aniosDisponibles as $anioDisponible)

                                <option
                                    value="{{ $anioDisponible }}"
                                    @selected(
                                        (int) $anio
                                        ===
                                        (int) $anioDisponible
                                    )>

                                    {{ $anioDisponible }}

                                </option>

                            @endforeach

                        </select>

                        <i
                            data-lucide="chevron-down"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                    </div>



                    {{-- Acciones --}}

                    <div class="flex items-center gap-2">

                        <button
                            type="submit"
                            class="inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                            <i
                                data-lucide="filter"
                                stroke-width="1.8"
                                class="w-4 h-4">
                            </i>

                            Filtrar

                        </button>


                        @if(
                            $busqueda !== ''
                            || filled($estadoSeleccionado)
                            || filled($tipoSeleccionado)
                            || (int) $mes !== now()->month
                            || (int) $anio !== now()->year
                        )

                            <a
                                href="{{ route('admin.pases') }}"
                                title="Limpiar filtros"
                                class="group/clear inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary active:translate-y-0 dark:border-slate-700">

                                <i
                                    data-lucide="x"
                                    stroke-width="1.8"
                                    class="w-4 h-4 transition-transform duration-200 group-hover/clear:rotate-90">
                                </i>

                            </a>

                        @endif

                    </div>

                </form>

            </div>



            {{-- Tabla --}}

            <div class="overflow-x-auto dark:bg-slate-900/30">

                <table class="w-full min-w-[1100px]">

                    <thead class="border-b border-border bg-muted/40 dark:border-slate-700 dark:bg-slate-900/80">

                        <tr class="text-left">

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Pase
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Solicitante
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Tipo
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Estado
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Registro
                            </th>

                            <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Acción
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-border dark:divide-slate-800">

                        @forelse($memorandos as $memorando)

                            @php

                                $esPaseTemporal =
                                    $memorando->tipo?->slug
                                    === 'pase_temporal';

                                $codigoMostrado =
                                    $memorando->codigo
                                    ?: 'PASE-'.str_pad(
                                        (string) $memorando->id,
                                        5,
                                        '0',
                                        STR_PAD_LEFT
                                    );

                            @endphp

                            <tr class="group transition-colors duration-200 hover:bg-primary/[0.025] dark:bg-slate-900/20 dark:hover:bg-slate-800/40">


                                {{-- Pase --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-start gap-3">

                                        <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary transition-all duration-300 group-hover:scale-105 group-hover:bg-primary/15">

                                            <i
                                                data-lucide="file-check-2"
                                                stroke-width="1.8"
                                                class="w-[18px] h-[18px] transition-transform duration-300 group-hover:scale-110">
                                            </i>
                                        </div>

                                        <div class="min-w-0">

                                            <a
                                                href="{{ route(
                                                    'admin.pases.show',
                                                    $memorando
                                                ) }}"
                                                class="text-sm font-semibold text-foreground transition-colors duration-200 hover:text-primary">

                                                {{ $codigoMostrado }}

                                            </a>

                                            <p
                                                title="{{ $memorando->asunto }}"
                                                class="max-w-[280px] mt-1 text-xs text-muted-foreground truncate">

                                                {{ $memorando->asunto }}

                                            </p>

                                        </div>

                                    </div>

                                </td>



                                {{-- Solicitante --}}

                                <td class="px-5 py-4">

                                    <p class="max-w-[220px] text-sm font-medium text-foreground truncate">

                                        {{ $memorando->solicitante?->nombre
                                            ?? 'Usuario no disponible'
                                        }}

                                    </p>

                                    <p class="max-w-[220px] mt-0.5 text-xs text-muted-foreground truncate">

                                        {{ $memorando->solicitante?->correo
                                            ?? 'Sin correo registrado'
                                        }}

                                    </p>

                                </td>



                                {{-- Tipo --}}

                                <td class="px-5 py-4">

                                    <span
                                        @class([
                                            'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-medium',

                                            'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-400' =>
                                                $esPaseTemporal,

                                            'border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-400' =>
                                                ! $esPaseTemporal,
                                        ])>

                                        <i
                                            data-lucide="{{ $esPaseTemporal
                                                ? 'timer'
                                                : 'calendar-range'
                                            }}"
                                            stroke-width="1.8"
                                            class="w-3.5 h-3.5">
                                        </i>

                                        {{ $esPaseTemporal
                                            ? 'Menor a 24 horas'
                                            : 'Mayor a 24 horas'
                                        }}

                                    </span>

                                </td>



                                {{-- Estado --}}

                                <td class="px-5 py-4">

                                    @if($memorando->estaAprobado())

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">

                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                            Aprobado

                                        </span>

                                    @elseif($memorando->estaRechazado())

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 text-xs font-medium text-red-600 dark:bg-red-500/15 dark:text-red-400">

                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>

                                            Rechazado

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">

                                            <span class="relative flex w-1.5 h-1.5">

                                                <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                                <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                            </span>

                                            Por revisar

                                        </span>

                                    @endif

                                </td>



                                {{-- Registro --}}

                                <td class="px-5 py-4">

                                    <p class="text-sm text-foreground">

                                        {{ $memorando->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('d/m/Y') }}

                                    </p>

                                    <p class="mt-0.5 text-xs text-muted-foreground">

                                        {{ $memorando->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('h:i A') }}

                                    </p>

                                </td>



                                {{-- Acción --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-center justify-end">

                                        <a
                                            href="{{ route(
                                                'admin.pases.show',
                                                $memorando
                                            ) }}"
                                            class="group/button inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg border border-border bg-card text-xs font-semibold text-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0 dark:border-slate-700">

                                            Ver detalle

                                            <i
                                                data-lucide="arrow-right"
                                                stroke-width="1.8"
                                                class="w-3.5 h-3.5 transition-transform duration-200 group-hover/button:translate-x-0.5">
                                            </i>

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-16 text-center">

                                    <div class="flex items-center justify-center w-14 h-14 mx-auto rounded-2xl bg-primary/5 text-primary">

                                        <i
                                            data-lucide="inbox"
                                            stroke-width="1.7"
                                            class="w-6 h-6">
                                        </i>

                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-foreground">

                                        No se encontraron pases

                                    </h3>

                                    <p class="max-w-md mx-auto mt-1 text-sm leading-relaxed text-muted-foreground">

                                        No existen pases que coincidan con los filtros seleccionados.

                                    </p>


                                    @if(
                                        $busqueda !== ''
                                        || filled($estadoSeleccionado)
                                        || filled($tipoSeleccionado)
                                        || (int) $mes !== now()->month
                                        || (int) $anio !== now()->year
                                    )

                                        <a
                                            href="{{ route('admin.pases') }}"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 mt-5 rounded-lg border border-primary/20 bg-primary/5 text-sm font-semibold text-primary transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/10 hover:shadow-sm active:translate-y-0">

                                            <i
                                                data-lucide="rotate-ccw"
                                                stroke-width="1.8"
                                                class="w-4 h-4">
                                            </i>

                                            Limpiar filtros

                                        </a>

                                    @endif

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>



            {{-- Paginación --}}

            @php
                $memorandos->appends(
                    request()->except('page')
                );
            @endphp

            @if($memorandos->hasPages())

                @php

                    $paginaActual =
                        $memorandos->currentPage();

                    $ultimaPagina =
                        $memorandos->lastPage();

                    $paginaInicial = max(
                        1,
                        $paginaActual - 2
                    );

                    $paginaFinal = min(
                        $ultimaPagina,
                        $paginaActual + 2
                    );

                    if ($paginaActual <= 3) {
                        $paginaFinal = min(
                            5,
                            $ultimaPagina
                        );
                    }

                    if (
                        $paginaActual
                        >= $ultimaPagina - 2
                    ) {
                        $paginaInicial = max(
                            1,
                            $ultimaPagina - 4
                        );
                    }

                @endphp


                <div class="flex flex-col gap-4 px-5 py-4 border-t border-border bg-blue-50/20 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-blue-950/10">

                    <p class="text-xs text-muted-foreground">

                        Mostrando

                        <span class="font-semibold text-foreground">
                            {{ $memorandos->firstItem() }}
                        </span>

                        a

                        <span class="font-semibold text-foreground">
                            {{ $memorandos->lastItem() }}
                        </span>

                        de

                        <span class="font-semibold text-foreground">
                            {{ $memorandos->total() }}
                        </span>

                        pases

                    </p>


                    <nav
                        aria-label="Paginación de pases"
                        class="flex flex-wrap items-center gap-1">

                        @if($memorandos->onFirstPage())

                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-slate-600">

                                <i
                                    data-lucide="chevron-left"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </span>

                        @else

                            <a
                                href="{{ $memorandos->previousPageUrl() }}"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary dark:border-slate-700">

                                <i
                                    data-lucide="chevron-left"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </a>

                        @endif


                        @if($paginaInicial > 1)

                            <a
                                href="{{ $memorandos->url(1) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary dark:border-slate-700">

                                1

                            </a>

                            @if($paginaInicial > 2)

                                <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">
                                    …
                                </span>

                            @endif

                        @endif


                        @for(
                            $pagina = $paginaInicial;
                            $pagina <= $paginaFinal;
                            $pagina++
                        )

                            @if($pagina === $paginaActual)

                                <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-primary bg-primary text-xs font-semibold text-white shadow-sm">

                                    {{ $pagina }}

                                </span>

                            @else

                                <a
                                    href="{{ $memorandos->url($pagina) }}"
                                    class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary dark:border-slate-700">

                                    {{ $pagina }}

                                </a>

                            @endif

                        @endfor


                        @if($paginaFinal < $ultimaPagina)

                            @if($paginaFinal < $ultimaPagina - 1)

                                <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">
                                    …
                                </span>

                            @endif

                            <a
                                href="{{ $memorandos->url($ultimaPagina) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary dark:border-slate-700">

                                {{ $ultimaPagina }}

                            </a>

                        @endif


                        @if($memorandos->hasMorePages())

                            <a
                                href="{{ $memorandos->nextPageUrl() }}"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary dark:border-slate-700">

                                <i
                                    data-lucide="chevron-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </a>

                        @else

                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-slate-600">

                                <i
                                    data-lucide="chevron-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </span>

                        @endif

                    </nav>

                </div>

            @endif

        </section>

    </main>

</div>

@endsection
