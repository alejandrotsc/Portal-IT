@extends('layouts.app')

@section('title', 'Avisos TI')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-7xl mx-auto px-6 py-10">


        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <span class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 rounded-full border border-primary/10 bg-primary/[0.06] text-primary text-xs font-semibold dark:border-blue-700/40">

                        <i
                            data-lucide="megaphone"
                            stroke-width="1.8"
                            class="w-3.5 h-3.5 shrink-0">
                        </i>

                        Comunicaciones TI

                    </span>

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">

                        Avisos de TI

                    </h1>

                    <p class="text-sm text-muted-foreground mt-2 max-w-2xl leading-relaxed">

                        Publica y administra los mensajes mostrados en la cinta informativa del portal.

                    </p>

                </div>


                <a
                    href="{{ route('avisos.create') }}"
                    class="group inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">

                    <i
                        data-lucide="plus"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:rotate-90">
                    </i>

                    Nuevo aviso

                </a>

            </div>

        </section>



        {{-- Mensajes --}}

        @if(session('success'))

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-300">

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

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-300">

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

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-300">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="w-4 h-4">
                    </i>

                </div>

                <div class="pt-1.5 space-y-1">

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
                <h2 class="text-base font-semibold text-foreground">Resumen de avisos</h2>
                <p class="mt-1 text-sm text-muted-foreground">Estado actual de las comunicaciones publicadas en el portal.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">


                {{-- Total --}}

                <div class="group relative overflow-hidden rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50 via-white to-indigo-50/60 p-5 shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10 motion-safe:hover:-translate-y-1 dark:border-blue-900/60 dark:from-blue-950/30 dark:via-slate-900 dark:to-indigo-950/20">

                    <div class="pointer-events-none absolute -right-8 -top-8 w-24 h-24 rounded-full bg-blue-400/10 transition-all duration-500 group-hover:bg-blue-400/20 motion-safe:group-hover:scale-150"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-blue-500/10 text-blue-600 transition-all duration-300 group-hover:bg-blue-100 motion-safe:group-hover:scale-105 dark:text-blue-400 dark:group-hover:bg-blue-950/70">

                            <i
                                data-lucide="files"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-700 dark:text-blue-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            Total
                        </span>

                        <div class="mt-1 w-full min-w-0">

                            <p class="text-2xl font-semibold text-foreground leading-none">

                                {{ $resumen['total'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground mt-2">

                                Avisos registrados

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Visibles --}}

                <div class="group relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50 via-white to-teal-50/50 p-5 shadow-sm transition-all duration-300 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10 motion-safe:hover:-translate-y-1 dark:border-emerald-900/60 dark:from-emerald-950/30 dark:via-slate-900 dark:to-teal-950/20">

                    <div class="pointer-events-none absolute -right-8 -top-8 w-24 h-24 rounded-full bg-emerald-400/10 transition-all duration-500 group-hover:bg-emerald-400/20 motion-safe:group-hover:scale-150"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-emerald-500/10 text-emerald-600 transition-all duration-300 group-hover:bg-emerald-100 motion-safe:group-hover:scale-105 dark:text-emerald-400 dark:group-hover:bg-emerald-950/70">

                            <i
                                data-lucide="eye"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Visibles
                        </span>

                        <div class="mt-1 w-full min-w-0">

                            <p class="text-2xl font-semibold text-foreground leading-none">

                                {{ $resumen['visibles'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground mt-2">

                                Visibles actualmente

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Programados --}}

                <div class="group relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50 via-white to-orange-50/50 p-5 shadow-sm transition-all duration-300 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10 motion-safe:hover:-translate-y-1 dark:border-amber-900/60 dark:from-amber-950/30 dark:via-slate-900 dark:to-orange-950/20">

                    <div class="pointer-events-none absolute -right-8 -top-8 w-24 h-24 rounded-full bg-amber-400/10 transition-all duration-500 group-hover:bg-amber-400/20 motion-safe:group-hover:scale-150"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-amber-500/10 text-amber-600 transition-all duration-300 group-hover:bg-amber-100 motion-safe:group-hover:scale-105 dark:text-amber-400 dark:group-hover:bg-amber-950/70">

                            <i
                                data-lucide="calendar-clock"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Programados
                        </span>

                        <div class="mt-1 w-full min-w-0">

                            <p class="text-2xl font-semibold text-foreground leading-none">

                                {{ $resumen['programados'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground mt-2">

                                Avisos programados

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Inactivos --}}

                <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100/60 p-5 shadow-sm transition-all duration-300 hover:border-slate-300 hover:shadow-lg hover:shadow-slate-500/10 motion-safe:hover:-translate-y-1 dark:border-slate-700 dark:from-slate-800/70 dark:via-slate-900 dark:to-slate-800/40">

                    <div class="pointer-events-none absolute -right-8 -top-8 w-24 h-24 rounded-full bg-slate-400/10 transition-all duration-500 group-hover:bg-slate-400/20 motion-safe:group-hover:scale-150"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-slate-500/10 text-slate-600 transition-all duration-300 group-hover:bg-slate-200 motion-safe:group-hover:scale-105 dark:text-slate-400 dark:group-hover:bg-slate-800">

                            <i
                                data-lucide="eye-off"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-500/10 px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                            Inactivos
                        </span>

                        <div class="mt-1 w-full min-w-0">

                            <p class="text-2xl font-semibold text-foreground leading-none">

                                {{ $resumen['inactivos'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground mt-2">

                                Avisos inactivos

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- Listado --}}

        <section class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-slate-700">


            {{-- Cabecera y filtros --}}

            <div class="px-5 py-5 border-b border-border dark:border-slate-700">

                <div class="mb-5">

                    <h2 class="text-base font-semibold text-foreground">

                        Avisos registrados

                    </h2>

                    <p class="text-sm text-muted-foreground mt-1">

                        Consulta y administra la vigencia de los avisos.

                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('avisos.index') }}"
                    class="grid grid-cols-1 md:grid-cols-[minmax(260px,1fr)_220px_auto] gap-3">


                    {{-- Buscar --}}

                    <div class="group flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card shadow-sm transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 focus-within:shadow-md dark:border-slate-700 dark:focus-within:border-blue-500">

                        <i
                            data-lucide="search"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-all duration-200 group-focus-within:text-primary motion-safe:group-focus-within:scale-110">
                        </i>

                        <input
                            type="search"
                            name="buscar"
                            value="{{ $busqueda ?? '' }}"
                            placeholder="Buscar por título o mensaje..."
                            autocomplete="off"
                            class="w-full py-2.5 bg-transparent border-0 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none">

                    </div>



                    {{-- Estado --}}

                    <div class="group flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card shadow-sm transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 focus-within:shadow-md dark:border-slate-700 dark:focus-within:border-blue-500">

                        <i
                            data-lucide="activity"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-all duration-200 group-focus-within:text-primary motion-safe:group-focus-within:scale-110">
                        </i>

                        <select
                            name="estado"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">

                            <option value="">
                                Todos los estados
                            </option>

                            <option
                                value="visible"
                                @selected(
                                    ($estadoSeleccionado ?? '')
                                    === 'visible'
                                )>

                                Visibles

                            </option>

                            <option
                                value="programado"
                                @selected(
                                    ($estadoSeleccionado ?? '')
                                    === 'programado'
                                )>

                                Programados

                            </option>

                            <option
                                value="finalizado"
                                @selected(
                                    ($estadoSeleccionado ?? '')
                                    === 'finalizado'
                                )>

                                Finalizados

                            </option>

                            <option
                                value="inactivo"
                                @selected(
                                    ($estadoSeleccionado ?? '')
                                    === 'inactivo'
                                )>

                                Inactivos

                            </option>

                        </select>

                        <i
                            data-lucide="chevron-down"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-all duration-200 group-focus-within:text-primary motion-safe:group-focus-within:translate-y-0.5">
                        </i>

                    </div>



                    {{-- Acciones del filtro --}}

                    <div class="flex items-stretch gap-2">

                        <button
                            type="submit"
                            class="group/filter inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">

                            <i
                                data-lucide="filter"
                                stroke-width="1.8"
                                class="w-4 h-4 shrink-0 transition-transform duration-200 motion-safe:group-hover/filter:scale-110">
                            </i>

                            Filtrar

                        </button>


                        @if(
                            filled($busqueda ?? '')
                            || filled($estadoSeleccionado ?? '')
                        )

                            <a
                                href="{{ route('avisos.index') }}"
                                title="Limpiar filtros"
                                class="group/clear inline-flex items-center justify-center w-10 shrink-0 rounded-lg border border-border bg-card text-muted-foreground shadow-sm transition-all duration-200 hover:text-red-600 hover:border-red-500/30 hover:bg-red-500/5 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] dark:border-slate-700 dark:hover:border-red-900/70 dark:hover:bg-red-950/30 dark:hover:text-red-400">

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

                    <thead class="bg-muted/40 border-b border-border dark:border-slate-700 dark:bg-slate-900/80">

                        <tr class="text-left">

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Aviso
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Inicio
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Finalización
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Estado
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Creado por
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider text-right">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-border dark:divide-slate-800">

                        @forelse($avisos as $aviso)

                            @php

                                $programado =
                                    $aviso->activo
                                    && $aviso->fecha_inicio
                                    && $aviso->fecha_inicio->isFuture();

                                $finalizado =
                                    $aviso->fecha_fin
                                    && $aviso->fecha_fin->isPast();

                                $visible =
                                    $aviso->activo
                                    && ! $programado
                                    && ! $finalizado;

                            @endphp


                            <tr class="group/row transition-colors duration-200 hover:bg-primary/[0.025] dark:bg-slate-900/20 dark:hover:bg-slate-800/40">


                                {{-- Aviso --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-start gap-3">

                                        <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary transition-all duration-300 group-hover/row:bg-primary/15 motion-safe:group-hover/row:scale-105">

                                            <i
                                                data-lucide="megaphone"
                                                stroke-width="1.8"
                                                class="w-4 h-4 transition-transform duration-300 motion-safe:group-hover/row:scale-110">
                                            </i>

                                        </div>

                                        <div class="min-w-0 max-w-md">

                                            <p class="text-sm font-semibold text-foreground truncate">

                                                {{ $aviso->titulo }}

                                            </p>

                                            <p class="text-xs text-muted-foreground mt-1 line-clamp-2 leading-relaxed">

                                                {{ $aviso->mensaje }}

                                            </p>

                                        </div>

                                    </div>

                                </td>



                                {{-- Inicio --}}

                                <td class="px-5 py-4">

                                    <p class="text-sm text-foreground">

                                        {{ $aviso->fecha_inicio
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('d/m/Y')
                                            ?? 'Inmediato' }}

                                    </p>

                                    @if($aviso->fecha_inicio)

                                        <p class="text-xs text-muted-foreground mt-0.5">

                                            {{ $aviso->fecha_inicio
                                                ->timezone('America/Tegucigalpa')
                                                ->format('h:i A') }}

                                        </p>

                                    @endif

                                </td>



                                {{-- Finalización --}}

                                <td class="px-5 py-4">

                                    <p class="text-sm text-foreground">

                                        {{ $aviso->fecha_fin
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('d/m/Y')
                                            ?? 'Sin límite' }}

                                    </p>

                                    @if($aviso->fecha_fin)

                                        <p class="text-xs text-muted-foreground mt-0.5">

                                            {{ $aviso->fecha_fin
                                                ->timezone('America/Tegucigalpa')
                                                ->format('h:i A') }}

                                        </p>

                                    @endif

                                </td>



                                {{-- Estado --}}

                                <td class="px-5 py-4">

                                    @if(! $aviso->activo)

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-500/10 text-slate-600 text-xs font-medium dark:text-slate-400">

                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>

                                            Inactivo

                                        </span>

                                    @elseif($programado)

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-700 text-xs font-medium dark:text-amber-400">

                                            <i
                                                data-lucide="clock-3"
                                                stroke-width="1.8"
                                                class="w-3.5 h-3.5">
                                            </i>

                                            Programado

                                        </span>

                                    @elseif($finalizado)

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-500/10 text-slate-600 text-xs font-medium dark:text-slate-400">

                                            <i
                                                data-lucide="calendar-x"
                                                stroke-width="1.8"
                                                class="w-3.5 h-3.5">
                                            </i>

                                            Finalizado

                                        </span>

                                    @elseif($visible)

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 text-xs font-medium dark:text-emerald-400">

                                            <span class="relative flex w-1.5 h-1.5 shrink-0">

                                                <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>

                                                <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                            </span>

                                            Visible

                                        </span>

                                    @endif

                                </td>



                                {{-- Creador --}}

                                <td class="px-5 py-4">

                                    <p class="text-sm text-foreground">

                                        {{ $aviso->creador?->nombre ?? 'N/A' }}

                                    </p>

                                    <p class="text-xs text-muted-foreground mt-0.5">

                                        {{ $aviso->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('d/m/Y') }}

                                    </p>

                                </td>



                                {{-- Acciones --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-center justify-end gap-2">

                                        <a
                                            href="{{ route(
                                                'avisos.edit',
                                                $aviso
                                            ) }}"
                                            title="Editar aviso"
                                            class="group/edit inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border border-border bg-card text-muted-foreground shadow-sm transition-all duration-200 hover:text-primary hover:border-primary/30 hover:bg-primary/5 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.95] dark:border-slate-700">

                                            <i
                                                data-lucide="pencil"
                                                stroke-width="1.8"
                                                class="w-4 h-4 transition-transform duration-200 motion-safe:group-hover/edit:scale-110">
                                            </i>

                                        </a>


                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'avisos.change-status',
                                                $aviso
                                            ) }}"
                                            class="contents"
                                            onsubmit="return confirm('{{ $aviso->activo
                                                ? '¿Deseas desactivar este aviso?'
                                                : '¿Deseas activar este aviso?'
                                            }}')">

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                title="{{ $aviso->activo
                                                    ? 'Desactivar aviso'
                                                    : 'Activar aviso'
                                                }}"
                                                @class([
                                                    'group/status inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border bg-card text-muted-foreground shadow-sm transition-all duration-200 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.95] dark:border-slate-700',

                                                    'border-border hover:text-red-600 hover:border-red-500/30 hover:bg-red-500/5' =>
                                                        $aviso->activo,

                                                    'border-border hover:text-primary hover:border-primary/30 hover:bg-primary/5' =>
                                                        ! $aviso->activo,
                                                ])>

                                                <i
                                                    data-lucide="{{ $aviso->activo
                                                        ? 'eye-off'
                                                        : 'eye'
                                                    }}"
                                                    stroke-width="1.8"
                                                    class="w-4 h-4 transition-transform duration-200 motion-safe:group-hover/status:scale-110">
                                                </i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-16 text-center">

                                    <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-primary/10 text-primary">

                                        <i
                                            data-lucide="megaphone-off"
                                            stroke-width="1.8"
                                            class="w-5 h-5">
                                        </i>

                                    </div>

                                    <h3 class="text-sm font-semibold text-foreground mt-4">

                                        No se encontraron avisos

                                    </h3>

                                    <p class="text-sm text-muted-foreground mt-1">

                                        Modifica los filtros o registra un nuevo aviso.

                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>



            {{-- Paginación personalizada --}}

            @if($avisos->hasPages())

                @php

                    $paginaActual = $avisos->currentPage();
                    $ultimaPagina = $avisos->lastPage();

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

                    <p class="text-xs text-muted-foreground">

                        Mostrando

                        <span class="font-semibold text-foreground">
                            {{ $avisos->firstItem() }}
                        </span>

                        a

                        <span class="font-semibold text-foreground">
                            {{ $avisos->lastItem() }}
                        </span>

                        de

                        <span class="font-semibold text-foreground">
                            {{ $avisos->total() }}
                        </span>

                        avisos

                    </p>


                    <nav
                        aria-label="Paginación de avisos"
                        class="flex flex-wrap items-center gap-1">


                        {{-- Anterior --}}

                        @if($avisos->onFirstPage())

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
                                href="{{ $avisos->previousPageUrl() }}"
                                rel="prev"
                                aria-label="Página anterior"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 dark:border-slate-700">

                                <i
                                    data-lucide="chevron-left"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </a>

                        @endif



                        {{-- Primera página --}}

                        @if($paginaInicial > 1)

                            <a
                                href="{{ $avisos->url(1) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 dark:border-slate-700">

                                1

                            </a>

                            @if($paginaInicial > 2)

                                <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">
                                    …
                                </span>

                            @endif

                        @endif



                        {{-- Páginas --}}

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
                                    href="{{ $avisos->url($pagina) }}"
                                    class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 dark:border-slate-700">

                                    {{ $pagina }}

                                </a>

                            @endif

                        @endfor



                        {{-- Última página --}}

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
                                href="{{ $avisos->url($ultimaPagina) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 dark:border-slate-700">

                                {{ $ultimaPagina }}

                            </a>

                        @endif



                        {{-- Siguiente --}}

                        @if($avisos->hasMorePages())

                            <a
                                href="{{ $avisos->nextPageUrl() }}"
                                rel="next"
                                aria-label="Página siguiente"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 dark:border-slate-700">

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
