@extends('layouts.app')

@section('title', 'Mis solicitudes')

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


    $categorias = [
        'computadora' => 'Computadora o accesorios',
        'programa' => 'Instalar un programa',
        'acceso' => 'Solicitar un acceso',
        'vpn' => 'VPN / Acceso remoto',
        'impresora' => 'Impresoras',
        'cuenta' => 'Cuenta o contraseña',
        'cambio' => 'Cambio o configuración de equipo',
        'otra' => 'Otra solicitud',
    ];


    $estados = [
        'pendiente' => [
            'label' => 'Pendiente',
            'class' => 'text-amber-600',
        ],

        'en_proceso' => [
            'label' => 'En proceso',
            'class' => 'text-blue-600',
        ],

        'completada' => [
            'label' => 'Completada',
            'class' => 'text-emerald-600',
        ],

        'rechazada' => [
            'label' => 'Rechazada',
            'class' => 'text-red-600',
        ],

        'cancelada' => [
            'label' => 'Cancelada',
            'class' => 'text-slate-500',
        ],
    ];


    $pendientes = $solicitudes
        ->whereIn('estado', ['pendiente', 'en_proceso'])
        ->count();

@endphp


<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-6">


        {{-- =====================================================
            HEADER
        ====================================================== --}}
        <section
            class="flex flex-col sm:flex-row
                   sm:items-start sm:justify-between gap-4"
        >

            <div>

                <h1 class="text-xl font-semibold text-foreground">
                    Mis solicitudes
                </h1>

                <p class="text-sm text-muted-foreground mt-1">
                    Consulta las solicitudes enviadas al equipo TI.
                </p>

            </div>


            <a
                href="{{ route('solicitudes.create') }}"
                class="inline-flex items-center justify-center gap-2
                       px-4 py-2.5 rounded-xl
                       bg-primary text-white
                       text-sm font-medium
                       hover:opacity-90 transition-opacity"
            >
                <i
                    data-lucide="arrow-left"
                    class="w-4 h-4"
                ></i>

                Nueva solicitud
            </a>

        </section>


        {{-- =====================================================
            FILTRO Y RESUMEN
        ====================================================== --}}
        <section
            class="bg-card border border-border
                   rounded-2xl overflow-hidden"
        >

            {{-- FILTRO --}}
            <div
                class="px-5 py-4 border-b border-border
                       flex flex-col lg:flex-row
                       lg:items-end lg:justify-between gap-4"
            >

                <div>

                    <p class="text-sm font-medium text-foreground">
                        Historial
                    </p>

                    <p class="text-xs text-muted-foreground mt-1">
                        {{ $meses[$mes] }} de {{ $anio }}
                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('mis-solicitudes') }}"
                    class="flex flex-col sm:flex-row
                           sm:items-end gap-2"
                >

                    {{-- MES --}}
                    <div>

                        <label
                            for="mes"
                            class="sr-only"
                        >
                            Mes
                        </label>

                        <select
                            id="mes"
                            name="mes"
                            class="w-full sm:w-40
                                   px-3 py-2 rounded-lg
                                   border border-border
                                   bg-white text-sm text-foreground
                                   focus:outline-none
                                   focus:border-primary
                                   focus:ring-2 focus:ring-primary/10"
                        >

                            @foreach($meses as $numero => $nombre)

                                <option
                                    value="{{ $numero }}"
                                    @selected(
                                        (int) $mes ===
                                        (int) $numero
                                    )
                                >
                                    {{ $nombre }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- AÑO --}}
                    <div>

                        <label
                            for="anio"
                            class="sr-only"
                        >
                            Año
                        </label>

                        <select
                            id="anio"
                            name="anio"
                            class="w-full sm:w-28
                                   px-3 py-2 rounded-lg
                                   border border-border
                                   bg-white text-sm text-foreground
                                   focus:outline-none
                                   focus:border-primary
                                   focus:ring-2 focus:ring-primary/10"
                        >

                            @foreach($aniosDisponibles as $anioDisponible)

                                <option
                                    value="{{ $anioDisponible }}"
                                    @selected(
                                        (int) $anio ===
                                        (int) $anioDisponible
                                    )
                                >
                                    {{ $anioDisponible }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- APLICAR --}}
                    <button
                        type="submit"
                        class="inline-flex items-center
                               justify-center gap-2
                               px-3.5 py-2 rounded-lg
                               border border-border
                               bg-white text-sm font-medium
                               text-foreground
                               hover:bg-muted
                               transition-colors"
                    >
                        <i
                            data-lucide="filter"
                            class="w-3.5 h-3.5"
                        ></i>

                        Filtrar
                    </button>


                    {{-- MES ACTUAL --}}
                    <a
                        href="{{ route('mis-solicitudes') }}"
                        class="inline-flex items-center
                               justify-center
                               px-3 py-2
                               text-xs font-medium
                               text-muted-foreground
                               hover:text-primary
                               transition-colors"
                    >
                        Mes actual
                    </a>

                </form>

            </div>


            {{-- RESUMEN COMPACTO --}}
            <div
                class="grid grid-cols-1 sm:grid-cols-3
                       divide-y sm:divide-y-0
                       sm:divide-x divide-border"
            >

                {{-- SOLICITUDES --}}
                <div class="px-5 py-3.5">

                    <div class="flex items-center gap-2.5">

                        <i
                            data-lucide="clipboard-list"
                            class="w-4 h-4 text-muted-foreground"
                        ></i>

                        <p class="text-xs text-muted-foreground">
                            Solicitudes
                        </p>

                        <span
                            class="ml-auto text-sm font-semibold
                                   text-foreground"
                        >
                            {{ $solicitudes->count() }}
                        </span>

                    </div>

                </div>


                {{-- PENDIENTES --}}
                <div class="px-5 py-3.5">

                    <div class="flex items-center gap-2.5">

                        <i
                            data-lucide="clock"
                            class="w-4 h-4 text-muted-foreground"
                        ></i>

                        <p class="text-xs text-muted-foreground">
                            Pendientes
                        </p>

                        <span
                            class="ml-auto text-sm font-semibold
                                   text-foreground"
                        >
                            {{ $pendientes }}
                        </span>

                    </div>

                </div>


                {{-- ÚLTIMA SOLICITUD --}}
                <div class="px-5 py-3.5">

                    <div class="flex items-center gap-2.5">

                        <i
                            data-lucide="clock-3"
                            class="w-4 h-4 text-muted-foreground"
                        ></i>

                        <p class="text-xs text-muted-foreground">
                            Última
                        </p>

                        <span
                            class="ml-auto text-sm font-medium
                                   text-foreground"
                        >

                            @if($solicitudes->first())

                                {{
                                    $solicitudes
                                        ->first()
                                        ->created_at
                                        ->format('d/m/Y')
                                }}

                            @else

                                —

                            @endif

                        </span>

                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
            LISTADO
        ====================================================== --}}
        <section>

            <div
                class="flex items-center justify-between
                       gap-4 mb-3"
            >

                <h2 class="text-sm font-medium text-foreground">
                    Solicitudes registradas
                </h2>

                <span class="text-xs text-muted-foreground">

                    {{ $solicitudes->count() }}

                    {{
                        $solicitudes->count() === 1
                            ? 'resultado'
                            : 'resultados'
                    }}

                </span>

            </div>


            <div
                class="bg-card border border-border
                       rounded-2xl overflow-hidden"
            >

                @forelse($solicitudes as $solicitud)

                    @php

                        $estadoSolicitud =
                            $estados[$solicitud->estado]
                            ?? [
                                'label' => ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $solicitud->estado
                                    )
                                ),

                                'class' => 'text-muted-foreground',
                            ];

                    @endphp


                    <a
                        href="{{
                            route(
                                'solicitudes.show',
                                $solicitud
                            )
                        }}"
                        class="group block px-5 py-4
                               border-b border-border
                               last:border-b-0
                               hover:bg-muted/40
                               transition-colors"
                    >

                        <div
                            class="flex items-start
                                   justify-between gap-5"
                        >

                            {{-- INFORMACIÓN --}}
                            <div class="min-w-0 flex-1">

                                <div
                                    class="flex flex-wrap
                                           items-center gap-x-2 gap-y-1"
                                >

                                    {{-- FOLIO --}}
                                    <span
                                        class="text-xs font-semibold
                                               text-primary"
                                    >
                                        {{ $solicitud->folio }}
                                    </span>


                                    <span
                                        class="w-1 h-1 rounded-full
                                               bg-border"
                                    ></span>


                                    {{-- FECHA --}}
                                    <span
                                        class="text-xs
                                               text-muted-foreground"
                                    >
                                        {{
                                            $solicitud
                                                ->created_at
                                                ->format('d/m/Y')
                                        }}
                                    </span>


                                    {{-- HORA --}}
                                    <span
                                        class="text-xs
                                               text-muted-foreground"
                                    >
                                        {{
                                            $solicitud
                                                ->created_at
                                                ->format('H:i')
                                        }}
                                    </span>

                                </div>


                                {{-- ASUNTO --}}
                                <h3
                                    class="text-sm font-medium
                                           text-foreground mt-1.5
                                           truncate"
                                >
                                    {{ $solicitud->asunto }}
                                </h3>


                                {{-- DESCRIPCIÓN --}}
                                <p
                                    class="text-xs text-muted-foreground
                                           mt-1 leading-relaxed
                                           line-clamp-1"
                                >
                                    {{
                                        \Illuminate\Support\Str::limit(
                                            $solicitud->descripcion,
                                            130
                                        )
                                    }}
                                </p>


                                {{-- INFORMACIÓN SECUNDARIA --}}
                                <div
                                    class="flex flex-wrap items-center
                                           gap-3 mt-2.5
                                           text-[11px]
                                           text-muted-foreground"
                                >

                                    {{-- CATEGORÍA --}}
                                    <span
                                        class="inline-flex
                                               items-center gap-1"
                                    >
                                        <i
                                            data-lucide="folder"
                                            class="w-3 h-3"
                                        ></i>

                                        {{
                                            $categorias[
                                                $solicitud->categoria
                                            ]
                                            ?? ucfirst(
                                                $solicitud->categoria
                                            )
                                        }}
                                    </span>


                                    {{-- ESTADO --}}
                                    <span
                                        class="inline-flex
                                               items-center gap-1
                                               {{ $estadoSolicitud['class'] }}"
                                    >
                                        <i
                                            data-lucide="circle"
                                            class="w-2.5 h-2.5"
                                        ></i>

                                        {{ $estadoSolicitud['label'] }}
                                    </span>

                                </div>

                            </div>


                            {{-- FLECHA --}}
                            <i
                                data-lucide="chevron-right"
                                class="w-4 h-4 mt-2 shrink-0
                                       text-muted-foreground
                                       group-hover:text-primary
                                       group-hover:translate-x-0.5
                                       transition-all"
                            ></i>

                        </div>

                    </a>


                @empty

                    {{-- SIN RESULTADOS --}}
                    <div class="px-6 py-12 text-center">

                        <div
                            class="w-11 h-11 rounded-full
                                   bg-muted
                                   flex items-center justify-center
                                   mx-auto"
                        >
                            <i
                                data-lucide="calendar-x"
                                class="w-5 h-5
                                       text-muted-foreground"
                            ></i>
                        </div>


                        <h3
                            class="text-sm font-medium
                                   text-foreground mt-4"
                        >
                            Sin solicitudes en este periodo
                        </h3>


                        <p
                            class="text-xs text-muted-foreground
                                   mt-1.5"
                        >
                            No hay solicitudes registradas durante
                            {{ $meses[$mes] }} de {{ $anio }}.
                        </p>


                        <div
                            class="flex flex-wrap items-center
                                   justify-center gap-3 mt-5"
                        >

                            <a
                                href="{{ route('mis-solicitudes') }}"
                                class="text-xs font-medium
                                       text-muted-foreground
                                       hover:text-primary
                                       transition-colors"
                            >
                                Ver mes actual
                            </a>


                            <a
                                href="{{ route('solicitudes.create') }}"
                                class="inline-flex items-center gap-1.5
                                       px-3.5 py-2 rounded-lg
                                       bg-primary text-white
                                       text-xs font-medium
                                       hover:opacity-90
                                       transition-opacity"
                            >
                                <i
                                    data-lucide="plus"
                                    class="w-3.5 h-3.5"
                                ></i>

                                Nueva solicitud
                            </a>

                        </div>

                    </div>

                @endforelse

            </div>

        </section>

    </main>

</div>


<script>

    document.addEventListener(
        'DOMContentLoaded',
        () => {

            if (window.lucide) {

                window.lucide.createIcons();

            }

        }
    );

</script>

@endsection