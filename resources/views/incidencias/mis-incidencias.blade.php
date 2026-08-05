@extends('layouts.app')

@section('title', 'Mis incidencias')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-7xl mx-auto px-6 py-10">


        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 rounded-full border border-primary/10 bg-primary/5 text-xs font-semibold text-primary dark:border-blue-800/70 dark:bg-blue-950/35 dark:text-blue-400">

                        <i
                            data-lucide="ticket-check"
                            stroke-width="1.8"
                            class="w-3.5 h-3.5 shrink-0">
                        </i>

                        Seguimiento personal

                    </div>

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">
                        Mis incidencias
                    </h1>

                    <p class="text-sm text-muted-foreground mt-2 max-w-2xl leading-relaxed">
                        Consulta los reportes enviados al equipo TI y revisa su estado actual.
                    </p>

                </div>


                <a
                    href="{{ route('incidencias.create') }}"
                    class="group/create inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                    <i
                        data-lucide="plus"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/create:rotate-90">
                    </i>

                    Reportar incidencia

                </a>

            </div>

        </section>



        {{-- Resumen --}}

        <section class="mb-8">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">


                {{-- Reportes --}}

                <div class="group relative overflow-hidden rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50 via-white to-indigo-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10 dark:border-blue-900/70 dark:from-blue-950/45 dark:via-slate-900 dark:to-indigo-950/30 dark:hover:border-blue-700 dark:hover:shadow-black/20">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-blue-400/10 transition-all duration-500 group-hover:scale-150 group-hover:bg-blue-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-blue-500/10 text-blue-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-400 dark:group-hover:bg-blue-900/70">

                            <i
                                data-lucide="tickets"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">
                                {{ $totalIncidencias ?? 0 }}
                            </p>

                            <p class="text-sm text-muted-foreground">
                                Reportes
                            </p>

                        </div>

                    </div>

                </div>



                {{-- Evidencias --}}

                <div class="group relative overflow-hidden rounded-2xl border border-violet-200/60 bg-gradient-to-br from-violet-50 via-white to-purple-50/50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-violet-300 hover:shadow-lg hover:shadow-violet-500/10 dark:border-violet-900/70 dark:from-violet-950/40 dark:via-slate-900 dark:to-purple-950/30 dark:hover:border-violet-700 dark:hover:shadow-black/20">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-violet-400/10 transition-all duration-500 group-hover:scale-150 group-hover:bg-violet-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-violet-500/10 text-violet-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-violet-100 dark:bg-violet-950/60 dark:text-violet-400 dark:group-hover:bg-violet-900/70">

                            <i
                                data-lucide="paperclip"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">
                                {{ $totalEvidencias ?? 0 }}
                            </p>

                            <p class="text-sm text-muted-foreground">
                                Evidencias
                            </p>

                        </div>

                    </div>

                </div>



                {{-- Último reporte --}}

                <div class="group relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50 via-white to-teal-50/50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10 dark:border-emerald-900/70 dark:from-emerald-950/40 dark:via-slate-900 dark:to-teal-950/30 dark:hover:border-emerald-700 dark:hover:shadow-black/20">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-emerald-400/10 transition-all duration-500 group-hover:scale-150 group-hover:bg-emerald-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-emerald-500/10 text-emerald-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-400 dark:group-hover:bg-emerald-900/70">

                            <i
                                data-lucide="calendar-check-2"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-xl font-semibold text-foreground">

                                {{ $ultimaIncidencia?->created_at
                                    ?->timezone('America/Tegucigalpa')
                                    ->format('d/m/Y')
                                    ?? '—'
                                }}

                            </p>

                            <p class="text-sm text-muted-foreground">
                                Último
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- Listado --}}

        <section class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-shadow duration-300 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:shadow-black/20">


            {{-- Cabecera y filtros --}}

            <div class="px-5 py-5 border-b border-border dark:border-slate-700/70">

                <div class="mb-5">

                    <h2 class="text-base font-semibold text-foreground">
                        Incidencias registradas
                    </h2>

                    <p class="text-sm text-muted-foreground mt-1">
                        Selecciona un periodo para consultar los reportes enviados.
                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('mis-incidencias') }}"
                    class="grid grid-cols-1 sm:grid-cols-[190px_150px_max-content] gap-3 sm:items-center">


                    {{-- Mes --}}

                    <div class="group/field flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-white transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700/70 dark:bg-slate-900 dark:focus-within:border-blue-500">

                        <i
                            data-lucide="calendar-days"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-colors duration-200 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400">
                        </i>

                        <select
                            id="mes"
                            name="mes"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 dark:text-slate-200 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-200">

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
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-all duration-200 group-focus-within/field:rotate-180 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400">
                        </i>

                    </div>



                    {{-- Año --}}

                    <div class="group/field flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-white transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700/70 dark:bg-slate-900 dark:focus-within:border-blue-500">

                        <i
                            data-lucide="calendar-range"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-colors duration-200 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400">
                        </i>

                        <select
                            id="anio"
                            name="anio"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 dark:text-slate-200 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-200">

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
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-all duration-200 group-focus-within/field:rotate-180 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400">
                        </i>

                    </div>



                    {{-- Acciones --}}

                    <div class="flex items-center gap-2 justify-self-start">

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold whitespace-nowrap shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                            <i
                                data-lucide="filter"
                                stroke-width="1.8"
                                class="w-4 h-4 shrink-0">
                            </i>

                            Filtrar

                        </button>

                        @if(
                            (int) $mes !== now()->month
                            || (int) $anio !== now()->year
                        )

                            <a
                                href="{{ route('mis-incidencias') }}"
                                title="Volver al mes actual"
                                class="group/clear inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-blue-700 dark:hover:bg-blue-950/35 dark:hover:text-blue-400 active:translate-y-0">

                                <i
                                    data-lucide="rotate-ccw"
                                    stroke-width="1.8"
                                    class="w-4 h-4 transition-transform duration-200 group-hover/clear:-rotate-90">
                                </i>

                            </a>

                        @endif

                    </div>

                </form>

            </div>



            {{-- Tabla --}}

            <div class="overflow-x-auto">

                <table class="w-full min-w-[980px]">

                    <thead class="border-b border-border bg-muted/40 dark:border-slate-700/70 dark:bg-slate-800/50">

                        <tr class="text-left">

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Incidencia
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Estado
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Equipo
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Evidencias
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Registro
                            </th>

                            <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Acción
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-border dark:divide-slate-700/70">

                        @forelse($incidencias as $incidencia)

                            <tr class="group transition-colors duration-200 hover:bg-primary/[0.025] dark:hover:bg-blue-950/20">


                                {{-- Incidencia --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-start gap-3">

                                        <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary transition-all duration-300 group-hover:scale-105 group-hover:bg-primary/15 dark:bg-blue-950/45 dark:text-blue-400 dark:group-hover:bg-blue-900/55">

                                            <i
                                                data-lucide="ticket-check"
                                                stroke-width="1.8"
                                                class="w-[18px] h-[18px] transition-transform duration-300 group-hover:scale-110">
                                            </i>

                                        </div>

                                        <div class="min-w-0">

                                            <a
                                                href="{{ route(
                                                    'incidencias.show',
                                                    $incidencia
                                                ) }}"
                                                class="text-sm font-semibold text-foreground transition-colors duration-200 hover:text-primary">

                                                {{ $incidencia->codigo }}

                                            </a>

                                            <p
                                                title="{{ $incidencia->titulo }}"
                                                class="max-w-[310px] mt-1 text-xs text-muted-foreground truncate">

                                                {{ $incidencia->titulo }}

                                            </p>

                                        </div>

                                    </div>

                                </td>



                                {{-- Estado --}}

                                <td class="px-5 py-4">

                                    @if($incidencia->estado === 'Resuelta')

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700 dark:bg-emerald-950/45 dark:text-emerald-300">

                                            <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-emerald-500"></span>

                                            Resuelta

                                        </span>

                                    @elseif($incidencia->estado === 'En_proceso')

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-500/10 text-xs font-medium text-cyan-700 dark:bg-cyan-950/45 dark:text-cyan-300">

                                            <span class="relative flex w-1.5 h-1.5 shrink-0">

                                                <span class="absolute inline-flex w-full h-full rounded-full bg-cyan-400 opacity-60 animate-ping"></span>

                                                <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-cyan-500"></span>

                                            </span>

                                            En proceso

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-medium text-amber-700 dark:bg-amber-950/45 dark:text-amber-300">

                                            <span class="relative flex w-1.5 h-1.5 shrink-0">

                                                <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                                <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                            </span>

                                            Abierta

                                        </span>

                                    @endif

                                </td>



                                {{-- Equipo --}}

                                <td class="px-5 py-4">

                                    @if($incidencia->equipo)

                                        <span class="inline-flex items-center gap-1.5 max-w-[210px] text-sm text-foreground">

                                            <i
                                                data-lucide="laptop"
                                                stroke-width="1.8"
                                                class="w-3.5 h-3.5 shrink-0 text-muted-foreground">
                                            </i>

                                            <span class="truncate">
                                                {{ $incidencia->equipo }}
                                            </span>

                                        </span>

                                    @else

                                        <span class="text-xs text-muted-foreground">
                                            No especificado
                                        </span>

                                    @endif

                                </td>



                                {{-- Evidencias --}}

                                <td class="px-5 py-4">

                                    @if($incidencia->archivos->count())

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-violet-200/70 bg-violet-50 text-xs font-medium text-violet-700 dark:border-violet-800/70 dark:bg-violet-950/40 dark:text-violet-300">

                                            <i
                                                data-lucide="paperclip"
                                                stroke-width="1.8"
                                                class="w-3.5 h-3.5 shrink-0">
                                            </i>

                                            {{ $incidencia->archivos->count() }}

                                            {{
                                                $incidencia->archivos->count() === 1
                                                    ? 'archivo'
                                                    : 'archivos'
                                            }}

                                        </span>

                                    @else

                                        <span class="text-xs text-muted-foreground">
                                            Sin archivos
                                        </span>

                                    @endif

                                </td>



                                {{-- Registro --}}

                                <td class="px-5 py-4">

                                    <p class="text-sm text-foreground">

                                        {{ $incidencia->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('d/m/Y') }}

                                    </p>

                                    <p class="mt-0.5 text-xs text-muted-foreground">

                                        {{ $incidencia->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('h:i A') }}

                                    </p>

                                </td>



                                {{-- Acción --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-center justify-end">

                                        <a
                                            href="{{ route(
                                                'incidencias.show',
                                                $incidencia
                                            ) }}"
                                            class="group/button inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-700 dark:hover:bg-blue-950/35 dark:hover:text-blue-400 active:translate-y-0">

                                            Ver detalle

                                            <i
                                                data-lucide="arrow-right"
                                                stroke-width="1.8"
                                                class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 group-hover/button:translate-x-0.5">
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

                                    <div class="flex items-center justify-center w-14 h-14 mx-auto rounded-2xl bg-primary/5 text-primary dark:bg-blue-950/35 dark:text-blue-400">

                                        <i
                                            data-lucide="inbox"
                                            stroke-width="1.7"
                                            class="w-6 h-6">
                                        </i>

                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-foreground">
                                        No se encontraron incidencias
                                    </h3>

                                    <p class="max-w-md mx-auto mt-1 text-sm leading-relaxed text-muted-foreground">
                                        No existen incidencias registradas durante {{ $meses[$mes] }} de {{ $anio }}.
                                    </p>

                                    @if(
                                        (int) $mes !== now()->month
                                        || (int) $anio !== now()->year
                                    )

                                        <a
                                            href="{{ route('mis-incidencias') }}"
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 mt-5 rounded-lg border border-primary/20 bg-primary/5 text-sm font-semibold text-primary transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/10 hover:shadow-sm active:translate-y-0">

                                            <i
                                                data-lucide="rotate-ccw"
                                                stroke-width="1.8"
                                                class="w-4 h-4">
                                            </i>

                                            Ver mes actual

                                        </a>

                                    @endif

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>



            {{-- Paginación personalizada --}}

            @if($incidencias->hasPages())

                @php
                    $paginaActual = $incidencias->currentPage();
                    $ultimaPagina = $incidencias->lastPage();

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
                        >=
                        $ultimaPagina - 2
                    ) {
                        $paginaInicial = max(
                            1,
                            $ultimaPagina - 4
                        );
                    }
                @endphp


                <div class="flex flex-col gap-4 px-5 py-4 border-t border-border bg-blue-50/20 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700/70 dark:bg-slate-950/25">

                    <p class="text-xs text-muted-foreground">
                        Mostrando

                        <span class="font-semibold text-foreground">
                            {{ $incidencias->firstItem() }}
                        </span>

                        a

                        <span class="font-semibold text-foreground">
                            {{ $incidencias->lastItem() }}
                        </span>

                        de

                        <span class="font-semibold text-foreground">
                            {{ $incidencias->total() }}
                        </span>

                        incidencias
                    </p>


                    <nav
                        aria-label="Paginación de incidencias"
                        class="flex flex-wrap items-center gap-1">

                        @if($incidencias->onFirstPage())

                            <span
                                aria-disabled="true"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-600">

                                <i data-lucide="chevron-left" stroke-width="1.8" class="w-4 h-4"></i>

                            </span>

                        @else

                            <a
                                href="{{ $incidencias->previousPageUrl() }}"
                                rel="prev"
                                aria-label="Página anterior"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-blue-700 dark:hover:bg-blue-950/35 dark:hover:text-blue-400 active:translate-y-0">

                                <i data-lucide="chevron-left" stroke-width="1.8" class="w-4 h-4"></i>

                            </a>

                        @endif


                        @if($paginaInicial > 1)

                            <a
                                href="{{ $incidencias->url(1) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-blue-700 dark:hover:bg-blue-950/35 dark:hover:text-blue-400">
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

                                <span
                                    aria-current="page"
                                    class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-primary bg-primary text-xs font-semibold text-white shadow-sm">
                                    {{ $pagina }}
                                </span>

                            @else

                                <a
                                    href="{{ $incidencias->url($pagina) }}"
                                    class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-blue-700 dark:hover:bg-blue-950/35 dark:hover:text-blue-400 active:translate-y-0">
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
                                href="{{ $incidencias->url($ultimaPagina) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-blue-700 dark:hover:bg-blue-950/35 dark:hover:text-blue-400 active:translate-y-0">
                                {{ $ultimaPagina }}
                            </a>

                        @endif


                        @if($incidencias->hasMorePages())

                            <a
                                href="{{ $incidencias->nextPageUrl() }}"
                                rel="next"
                                aria-label="Página siguiente"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-blue-700 dark:hover:bg-blue-950/35 dark:hover:text-blue-400 active:translate-y-0">

                                <i data-lucide="chevron-right" stroke-width="1.8" class="w-4 h-4"></i>

                            </a>

                        @else

                            <span
                                aria-disabled="true"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-600">

                                <i data-lucide="chevron-right" stroke-width="1.8" class="w-4 h-4"></i>

                            </span>

                        @endif

                    </nav>

                </div>

            @endif

        </section>

        @include('partials.support-widget')

    </main>

</div>

@endsection