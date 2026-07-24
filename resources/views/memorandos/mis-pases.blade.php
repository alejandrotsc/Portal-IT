@extends('layouts.app')

@section('title', 'Mis pases')

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

    $coleccionMemorandos = method_exists(
        $memorandos,
        'getCollection'
    )
        ? $memorandos->getCollection()
        : $memorandos;

    $totalPases = $totalPases
        ?? (
            method_exists($memorandos, 'total')
                ? $memorandos->total()
                : $memorandos->count()
        );

    $pasesMenores = $pasesMenores
        ?? $coleccionMemorandos
            ->filter(
                fn ($memorando) =>
                    $memorando->tipo?->slug
                    === 'pase_temporal'
            )
            ->count();

    $pasesMayores = $pasesMayores
        ?? $coleccionMemorandos
            ->filter(
                fn ($memorando) =>
                    $memorando->tipo?->slug
                    === 'autorizacion'
            )
            ->count();

@endphp


<div class="min-h-screen bg-background">

    <main class="max-w-7xl mx-auto px-6 py-10">


        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 rounded-full border border-primary/10 bg-primary/5 text-xs font-semibold text-primary">

                        <i
                            data-lucide="clipboard-check"
                            stroke-width="1.8"
                            class="w-3.5 h-3.5 shrink-0">
                        </i>

                        Seguimiento personal

                    </div>

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">
                        Mis pases
                    </h1>

                    <p class="text-sm text-muted-foreground mt-2 max-w-2xl leading-relaxed">
                        Consulta los pases enviados y revisa la información registrada.
                    </p>

                </div>


                <div class="flex flex-wrap items-center gap-2">

                    <a
                        href="{{ route('memorandos.pase_temporal') }}"
                        class="group/menor inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-primary/20 bg-primary/5 text-primary text-sm font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/10 hover:shadow-md active:translate-y-0">

                        <i
                            data-lucide="clock-3"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/menor:scale-110">
                        </i>

                        Pase menor

                    </a>


                    <a
                        href="{{ route('memorandos.autorizacion') }}"
                        class="group/mayor inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                        <i
                            data-lucide="file-plus-2"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/mayor:scale-110">
                        </i>

                        Pase mayor

                    </a>

                </div>

            </div>

        </section>



        {{-- Resumen --}}

        <section class="mb-8">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">


                {{-- Total --}}

                <div class="group relative overflow-hidden rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50 via-white to-indigo-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-blue-400/10 transition-all duration-500 group-hover:scale-150 group-hover:bg-blue-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-blue-500/10 text-blue-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-blue-100">

                            <i
                                data-lucide="files"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">
                                {{ $totalPases ?? 0 }}
                            </p>

                            <p class="text-sm text-muted-foreground">
                                Pases registrados
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Menores --}}

                <div class="group relative overflow-hidden rounded-2xl border border-cyan-200/60 bg-gradient-to-br from-cyan-50 via-white to-blue-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-cyan-300 hover:shadow-lg hover:shadow-cyan-500/10">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-cyan-400/10 transition-all duration-500 group-hover:scale-150 group-hover:bg-cyan-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-cyan-500/10 text-cyan-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-cyan-100">

                            <i
                                data-lucide="clock-3"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">
                                {{ $pasesMenores ?? 0 }}
                            </p>

                            <p class="text-sm text-muted-foreground">
                                Menores a 24 horas
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Mayores --}}

                <div class="group relative overflow-hidden rounded-2xl border border-violet-200/60 bg-gradient-to-br from-violet-50 via-white to-purple-50/50 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-violet-300 hover:shadow-lg hover:shadow-violet-500/10">

                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-violet-400/10 transition-all duration-500 group-hover:scale-150 group-hover:bg-violet-400/20"></div>

                    <div class="relative flex items-center gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-violet-500/10 text-violet-600 transition-all duration-300 group-hover:scale-105 group-hover:bg-violet-100">

                            <i
                                data-lucide="file-check-2"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 group-hover:scale-110">
                            </i>

                        </div>

                        <div>

                            <p class="text-2xl font-semibold text-foreground">
                                {{ $pasesMayores ?? 0 }}
                            </p>

                            <p class="text-sm text-muted-foreground">
                                Mayores a 24 horas
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- Listado --}}

        <section class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-shadow duration-300 hover:shadow-md">


            {{-- Cabecera y filtros --}}

            <div class="px-5 py-5 border-b border-border">

                <div class="mb-5">

                    <h2 class="text-base font-semibold text-foreground">
                        Pases registrados
                    </h2>

                    <p class="text-sm text-muted-foreground mt-1">
                        Selecciona un periodo y el tipo de pase que deseas consultar.
                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('memorandos.mis-pases') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[190px_150px_210px_max-content] gap-3 lg:items-center">


                    {{-- Mes --}}

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-white transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10">

                        <i
                            data-lucide="calendar-days"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <select
                            id="mes"
                            name="mes"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0">

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

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-white transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10">

                        <i
                            data-lucide="calendar-range"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <select
                            id="anio"
                            name="anio"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0">

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


                    {{-- Tipo --}}

                    <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-white transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10">

                        <i
                            data-lucide="list-filter"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                        </i>

                        <select
                            id="tipo"
                            name="tipo"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0">

                            <option
                                value="todos"
                                @selected($tipoSeleccionado === 'todos')>
                                Todos los pases
                            </option>

                            <option
                                value="pase_temporal"
                                @selected($tipoSeleccionado === 'pase_temporal')>
                                Menor a 24 horas
                            </option>

                            <option
                                value="autorizacion"
                                @selected($tipoSeleccionado === 'autorizacion')>
                                Mayor a 24 horas
                            </option>

                        </select>

                        <i
                            data-lucide="chevron-down"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
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
                            || $tipoSeleccionado !== 'todos'
                        )

                            <a
                                href="{{ route('memorandos.mis-pases') }}"
                                title="Limpiar filtros"
                                class="group/clear inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary active:translate-y-0">

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

                <table class="w-full min-w-[1050px]">

                    <thead class="border-b border-border bg-muted/40">

                        <tr class="text-left">

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Pase
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Tipo
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Responsable del equipo
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


                    <tbody class="divide-y divide-border">

                        @forelse($memorandos as $memorando)

                            @php

                                $esTemporal =
                                    $memorando->tipo?->slug
                                    === 'pase_temporal';

                                $identificador = $memorando->codigo
                                    ?: 'PASE-'.str_pad(
                                        (string) $memorando->id,
                                        5,
                                        '0',
                                        STR_PAD_LEFT
                                    );

                                $datos = $memorando->datos_extra
                                    ?? [];

                                $colaborador = $datos['colaborador']
                                    ?? $datos['nombre_colaborador']
                                    ?? null;

                            @endphp


                            <tr class="group transition-colors duration-200 hover:bg-primary/[0.025]">


                                {{-- Pase --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-start gap-3">

                                        <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary transition-all duration-300 group-hover:scale-105 group-hover:bg-primary/15">

                                            <i
                                                data-lucide="{{ $esTemporal
                                                    ? 'clock-3'
                                                    : 'file-check-2'
                                                }}"
                                                stroke-width="1.8"
                                                class="w-[18px] h-[18px] transition-transform duration-300 group-hover:scale-110">
                                            </i>

                                        </div>

                                        <div class="min-w-0">

                                            <a
                                                href="{{ route(
                                                    'memorandos.show-pase',
                                                    $memorando
                                                ) }}"
                                                class="text-sm font-semibold text-foreground transition-colors duration-200 hover:text-primary">

                                                {{ $identificador }}

                                            </a>

                                            <p
                                                title="{{ $memorando->asunto }}"
                                                class="max-w-[300px] mt-1 text-xs text-muted-foreground truncate">

                                                {{ $memorando->asunto }}

                                            </p>

                                        </div>

                                    </div>

                                </td>


                                {{-- Tipo --}}

                                <td class="px-5 py-4">

                                    <span
                                        @class([
                                            'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-medium',

                                            'border-sky-200 bg-sky-50 text-sky-700' =>
                                                $esTemporal,

                                            'border-indigo-200 bg-indigo-50 text-indigo-700' =>
                                                ! $esTemporal,
                                        ])>

                                        <i
                                            data-lucide="{{ $esTemporal
                                                ? 'timer'
                                                : 'calendar-range'
                                            }}"
                                            stroke-width="1.8"
                                            class="w-3.5 h-3.5 shrink-0">
                                        </i>

                                        {{ $esTemporal
                                            ? 'Menor a 24 horas'
                                            : 'Mayor a 24 horas'
                                        }}

                                    </span>

                                </td>


                                {{-- Colaborador --}}

                                <td class="px-5 py-4">

                                    @if($colaborador)

                                        <span class="inline-flex items-center gap-1.5 max-w-[220px] text-sm text-foreground">

                                            <i
                                                data-lucide="user"
                                                stroke-width="1.8"
                                                class="w-3.5 h-3.5 shrink-0 text-primary">
                                            </i>

                                            <span class="truncate">
                                                {{ $colaborador }}
                                            </span>

                                        </span>

                                    @else

                                        <span class="text-xs text-muted-foreground">
                                            No especificado
                                        </span>

                                    @endif

                                </td>


                                {{-- Estado --}}

                                <td class="px-5 py-4">

                                    @switch($memorando->estado)

                                        @case('APROBADO')

                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700">

                                                <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-emerald-500"></span>

                                                Aprobado

                                            </span>

                                            @break


                                        @case('RECHAZADO')

                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 text-xs font-medium text-red-600">

                                                <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-red-500"></span>

                                                Rechazado

                                            </span>

                                            @break


                                        @case('ARCHIVADO')

                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-500/10 text-xs font-medium text-slate-600">

                                                <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-slate-400"></span>

                                                Archivado

                                            </span>

                                            @break


                                        @case('EN_FIRMA')

                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-500/10 text-xs font-medium text-blue-700">

                                                <span class="relative flex w-1.5 h-1.5 shrink-0">

                                                    <span class="absolute inline-flex w-full h-full rounded-full bg-blue-400 opacity-60 animate-ping"></span>

                                                    <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-blue-500"></span>

                                                </span>

                                                En firma

                                            </span>

                                            @break


                                        @case('ENVIADO_EMAIL')

                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-500/10 text-xs font-medium text-cyan-700">

                                                <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-cyan-500"></span>

                                                Enviado

                                            </span>

                                            @break


                                        @default

                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-medium text-amber-700">

                                                <span class="relative flex w-1.5 h-1.5 shrink-0">

                                                    <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                                    <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                                </span>

                                                Por revisar

                                            </span>

                                    @endswitch

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
                                                'memorandos.show-pase',
                                                $memorando
                                            ) }}"
                                            class="group/button inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0">

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
                                        No se encontraron pases
                                    </h3>

                                    <p class="max-w-md mx-auto mt-1 text-sm leading-relaxed text-muted-foreground">
                                        No existen pases que coincidan con el periodo y tipo seleccionados.
                                    </p>

                                    @if(
                                        (int) $mes !== now()->month
                                        || (int) $anio !== now()->year
                                        || $tipoSeleccionado !== 'todos'
                                    )

                                        <a
                                            href="{{ route('memorandos.mis-pases') }}"
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

            @if(
                method_exists(
                    $memorandos,
                    'hasPages'
                )
                && $memorandos->hasPages()
            )

                @php

                    $paginaActual = $memorandos->currentPage();
                    $ultimaPagina = $memorandos->lastPage();

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


                <div class="flex flex-col gap-4 px-5 py-4 border-t border-border bg-blue-50/20 sm:flex-row sm:items-center sm:justify-between">

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

                            <span
                                aria-disabled="true"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed">

                                <i
                                    data-lucide="chevron-left"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </span>

                        @else

                            <a
                                href="{{ $memorandos->previousPageUrl() }}"
                                rel="prev"
                                aria-label="Página anterior"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0">

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
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm">

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
                                    href="{{ $memorandos->url($pagina) }}"
                                    class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0">

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
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0">

                                {{ $ultimaPagina }}

                            </a>

                        @endif


                        @if($memorandos->hasMorePages())

                            <a
                                href="{{ $memorandos->nextPageUrl() }}"
                                rel="next"
                                aria-label="Página siguiente"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0">

                                <i
                                    data-lucide="chevron-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </a>

                        @else

                            <span
                                aria-disabled="true"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed">

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