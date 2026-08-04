@extends('layouts.app')

@section('title', 'Administración de solicitudes')

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

            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 rounded-full border border-primary/10 bg-primary/5 text-xs font-semibold text-primary dark:border-blue-800/60 dark:bg-blue-950/30">

                        <i
                            data-lucide="clipboard-list"
                            stroke-width="1.8"
                            class="w-3.5 h-3.5 shrink-0">
                        </i>

                        Gestión interna

                    </div>

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">

                        Administración de solicitudes

                    </h1>

                    <p class="text-sm text-muted-foreground mt-2 max-w-2xl leading-relaxed">

                        Consulta las solicitudes registradas y actualiza su estado de seguimiento.

                    </p>

                </div>

            </div>

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


        @if(session('warning'))

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-800 dark:border-amber-700/40 dark:bg-amber-950/25 dark:text-amber-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">

                    <i
                        data-lucide="triangle-alert"
                        stroke-width="1.8"
                        class="w-4 h-4">
                    </i>

                </div>

                <p class="pt-1.5 leading-relaxed">

                    {{ session('warning') }}

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
                    Resumen de solicitudes
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Estado actual de las solicitudes registradas en el portal.
                </p>

            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">


                {{-- Total --}}

                <div class="group relative overflow-hidden rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50 via-white to-indigo-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10 dark:border-blue-900/60 dark:from-blue-950/30 dark:via-slate-900 dark:to-indigo-950/20 dark:hover:border-blue-800/70">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-blue-400/10 pointer-events-none transition-all duration-500 group-hover:scale-150 group-hover:bg-blue-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-blue-500/10 text-blue-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-400 dark:group-hover:bg-blue-900/50">

                            <i
                                data-lucide="files"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">

                                {{ $resumen['total'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground">

                                Solicitudes registradas

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Pendientes --}}

                <div class="group relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50 via-white to-orange-50/50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10 dark:border-amber-700/40 dark:from-amber-950/25 dark:via-slate-900 dark:to-orange-950/20 dark:hover:border-amber-700/60">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-amber-400/10 pointer-events-none transition-all duration-500 group-hover:scale-150 group-hover:bg-amber-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-amber-500/10 text-amber-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-amber-100 dark:bg-amber-500/15 dark:text-amber-400 dark:group-hover:bg-amber-900/40">

                            <i
                                data-lucide="clock-3"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">

                                {{ $resumen['pendientes'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground">

                                Pendientes

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Finalizadas --}}

                <div class="group relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50 via-white to-teal-50/50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10 dark:border-emerald-900/60 dark:from-emerald-950/25 dark:via-slate-900 dark:to-teal-950/20 dark:hover:border-emerald-800/70">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-emerald-400/10 pointer-events-none transition-all duration-500 group-hover:scale-150 group-hover:bg-emerald-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-emerald-500/10 text-emerald-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-400 dark:group-hover:bg-emerald-900/40">

                            <i
                                data-lucide="circle-check-big"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">

                                {{ $resumen['finalizadas'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground">

                                Finalizadas

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Canceladas --}}

                <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-lg hover:shadow-slate-500/10 dark:border-slate-700 dark:from-slate-800/70 dark:via-slate-900 dark:to-slate-800/40 dark:hover:border-slate-600">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-slate-400/10 pointer-events-none transition-all duration-500 group-hover:scale-150 group-hover:bg-slate-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-slate-500/10 text-slate-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600">

                            <i
                                data-lucide="circle-x"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">

                                {{ $resumen['canceladas'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground">

                                Canceladas

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- Listado --}}

        <section class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden dark:border-slate-700">


            {{-- Cabecera y filtros --}}

            <div class="px-5 py-5 border-b border-border dark:border-slate-700">

                <div class="mb-5">

                    <h2 class="text-base font-semibold text-foreground">

                        Solicitudes recibidas

                    </h2>

                    <p class="text-sm text-muted-foreground mt-1">

                        Busca por folio, asunto o solicitante y filtra los resultados.

                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('admin.solicitudes') }}"
                    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_170px_190px_160px_130px_auto] gap-3 xl:items-center">


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
                            placeholder="Folio, asunto o solicitante..."
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
                                value="pendiente"
                                @selected(
                                    $estadoSeleccionado === 'pendiente'
                                )>

                                Pendientes

                            </option>

                            <option
                                value="finalizada"
                                @selected(
                                    $estadoSeleccionado === 'finalizada'
                                )>

                                Finalizadas

                            </option>

                            <option
                                value="cancelada"
                                @selected(
                                    $estadoSeleccionado === 'cancelada'
                                )>

                                Canceladas

                            </option>

                        </select>

                        <i
                            data-lucide="chevron-down"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                    </div>



                    {{-- Categoría --}}

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15">

                        <i
                            data-lucide="tags"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <select
                            name="categoria"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">

                            <option value="">
                                Todas las categorías
                            </option>

                            @foreach($categorias as $categoria)

                                <option
                                    value="{{ $categoria }}"
                                    @selected(
                                        $categoriaSeleccionada === $categoria
                                    )>

                                    {{ str($categoria)
                                        ->replace('_', ' ')
                                        ->title() }}

                                </option>

                            @endforeach

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


                    {{-- Acciones de filtros --}}

                    <div class="flex items-center gap-2">

                        <button
                            type="submit"
                            class="inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                            <i
                                data-lucide="filter"
                                stroke-width="1.8"
                                class="w-4 h-4 shrink-0">
                            </i>

                            Filtrar

                        </button>


                        @if(
                            $busqueda !== ''
                            || filled($estadoSeleccionado)
                            || filled($categoriaSeleccionada)
                            || (int) $mes !== now()->month
                            || (int) $anio !== now()->year
                        )

                            <a
                                href="{{ route('admin.solicitudes') }}"
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

                <table class="w-full min-w-[1050px]">

                    <thead class="border-b border-border bg-muted/40 dark:border-slate-700 dark:bg-slate-900/80">

                        <tr class="text-left">

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Solicitud
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Solicitante
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Categoría
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

                        @forelse($solicitudes as $solicitud)

                            <tr class="group transition-colors duration-200 hover:bg-primary/[0.025] dark:bg-slate-900/20 dark:hover:bg-slate-800/40">


                                {{-- Solicitud --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-start gap-3">

                                        <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary transition-all duration-300 group-hover:scale-105 group-hover:bg-primary/15">

                                            <i
                                                data-lucide="file-text"
                                                stroke-width="1.8"
                                                class="w-[18px] h-[18px] transition-transform duration-300 group-hover:scale-110">
                                            </i>

                                        </div>

                                        <div class="min-w-0">

                                            <a
                                                href="{{ route(
                                                    'admin.solicitudes.show',
                                                    $solicitud
                                                ) }}"
                                                class="text-sm font-semibold text-foreground transition-colors duration-200 hover:text-primary">

                                                {{ $solicitud->folio }}

                                            </a>

                                            <p
                                                title="{{ $solicitud->asunto }}"
                                                class="max-w-[280px] mt-1 text-xs text-muted-foreground truncate">

                                                {{ $solicitud->asunto }}

                                            </p>

                                        </div>

                                    </div>

                                </td>



                                {{-- Solicitante --}}

                                <td class="px-5 py-4">

                                    <div class="min-w-0">

                                        <p class="max-w-[220px] text-sm font-medium text-foreground truncate">

                                            {{ $solicitud->usuario?->nombre
                                                ?? 'Usuario no disponible'
                                            }}

                                        </p>

                                        <p class="max-w-[220px] mt-0.5 text-xs text-muted-foreground truncate">

                                            {{ $solicitud->usuario?->correo
                                                ?? 'Sin correo registrado'
                                            }}

                                        </p>

                                    </div>

                                </td>



                                {{-- Categoría --}}

                                <td class="px-5 py-4">

                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-blue-200/70 bg-blue-50 text-xs font-medium text-blue-700 transition-all duration-200 group-hover:border-blue-300 group-hover:bg-blue-100/70 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-400 dark:group-hover:border-blue-800 dark:group-hover:bg-blue-900/30">

                                        <i
                                            data-lucide="tag"
                                            stroke-width="1.8"
                                            class="w-3.5 h-3.5 shrink-0">
                                        </i>

                                        {{ str($solicitud->categoria)
                                            ->replace('_', ' ')
                                            ->title() }}

                                    </span>

                                </td>



                                {{-- Estado --}}

                                <td class="px-5 py-4">

                                    @if($solicitud->estado === 'finalizada')

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">

                                            <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-emerald-500"></span>

                                            Finalizada

                                        </span>

                                    @elseif($solicitud->estado === 'cancelada')

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 text-xs font-medium text-red-600 dark:bg-red-500/15 dark:text-red-400">

                                            <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-red-500"></span>

                                            Cancelada

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">

                                            <span class="relative flex w-1.5 h-1.5 shrink-0">

                                                <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                                <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                            </span>

                                            Pendiente

                                        </span>

                                    @endif

                                </td>



                                {{-- Registro --}}

                                <td class="px-5 py-4">

                                    <p class="text-sm text-foreground">

                                        {{ $solicitud->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('d/m/Y') }}

                                    </p>

                                    <p class="mt-0.5 text-xs text-muted-foreground">

                                        {{ $solicitud->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('h:i A') }}

                                    </p>

                                </td>



                                {{-- Acción --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-center justify-end">

                                        <a
                                            href="{{ route(
                                                'admin.solicitudes.show',
                                                $solicitud
                                            ) }}"
                                            class="group/button inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg border border-border bg-card text-xs font-semibold text-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0 dark:border-slate-700">

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

                                    <div class="flex items-center justify-center w-14 h-14 mx-auto rounded-2xl bg-primary/5 text-primary">

                                        <i
                                            data-lucide="inbox"
                                            stroke-width="1.7"
                                            class="w-6 h-6">
                                        </i>

                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-foreground">

                                        No se encontraron solicitudes

                                    </h3>

                                    <p class="max-w-md mx-auto mt-1 text-sm leading-relaxed text-muted-foreground">

                                        No existen solicitudes que coincidan con los filtros seleccionados.

                                    </p>


                                    @if(
                                        $busqueda !== ''
                                        || filled($estadoSeleccionado)
                                        || filled($categoriaSeleccionada)
                                        || (int) $mes !== now()->month
                                        || (int) $anio !== now()->year
                                    )

                                        <a
                                            href="{{ route('admin.solicitudes') }}"
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



            {{-- Paginación personalizada --}}

            @php
                $solicitudes->appends(
                    request()->except('page')
                );
            @endphp

            @if($solicitudes->hasPages())

                @php

                    $paginaActual = $solicitudes->currentPage();
                    $ultimaPagina = $solicitudes->lastPage();

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


                <div class="flex flex-col gap-4 px-5 py-4 border-t border-border bg-blue-50/20 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-blue-950/10">


                    {{-- Información --}}

                    <p class="text-xs text-muted-foreground">

                        Mostrando

                        <span class="font-semibold text-foreground">

                            {{ $solicitudes->firstItem() }}

                        </span>

                        a

                        <span class="font-semibold text-foreground">

                            {{ $solicitudes->lastItem() }}

                        </span>

                        de

                        <span class="font-semibold text-foreground">

                            {{ $solicitudes->total() }}

                        </span>

                        solicitudes

                    </p>



                    {{-- Controles --}}

                    <nav
                        aria-label="Paginación de solicitudes"
                        class="flex flex-wrap items-center gap-1">


                        {{-- Anterior --}}

                        @if($solicitudes->onFirstPage())

                            <span
                                aria-disabled="true"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-slate-600">

                                <i
                                    data-lucide="chevron-left"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </span>

                        @else

                            <a
                                href="{{ $solicitudes->previousPageUrl() }}"
                                rel="prev"
                                aria-label="Página anterior"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0 dark:border-slate-700">

                                <i
                                    data-lucide="chevron-left"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </a>

                        @endif



                        {{-- Primera página y separación --}}

                        @if($paginaInicial > 1)

                            <a
                                href="{{ $solicitudes->url(1) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm dark:border-slate-700">

                                1

                            </a>


                            @if($paginaInicial > 2)

                                <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">

                                    …

                                </span>

                            @endif

                        @endif



                        {{-- Números de página --}}

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
                                    href="{{ $solicitudes->url($pagina) }}"
                                    class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0 dark:border-slate-700">

                                    {{ $pagina }}

                                </a>

                            @endif

                        @endfor



                        {{-- Última página y separación --}}

                        @if($paginaFinal < $ultimaPagina)

                            @if(
                                $paginaFinal
                                <
                                $ultimaPagina - 1
                            )

                                <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">

                                    …

                                </span>

                            @endif


                            <a
                                href="{{ $solicitudes->url($ultimaPagina) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0 dark:border-slate-700">

                                {{ $ultimaPagina }}

                            </a>

                        @endif



                        {{-- Siguiente --}}

                        @if($solicitudes->hasMorePages())

                            <a
                                href="{{ $solicitudes->nextPageUrl() }}"
                                rel="next"
                                aria-label="Página siguiente"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0 dark:border-slate-700">

                                <i
                                    data-lucide="chevron-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </a>

                        @else

                            <span
                                aria-disabled="true"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-slate-600">

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
