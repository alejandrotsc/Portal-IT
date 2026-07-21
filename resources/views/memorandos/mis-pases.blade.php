@extends('layouts.app')

@section('title', 'Mis pases')

@section('content')

@php
    $meses = [
        1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
        5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto',
        9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre',
        12=>'Diciembre',
    ];

    $pasesMenores = $memorandos
        ->filter(fn ($memorando) =>
            $memorando->tipo?->slug === 'pase_temporal'
        )
        ->count();

    $pasesMayores = $memorandos
        ->filter(fn ($memorando) =>
            $memorando->tipo?->slug === 'autorizacion'
        )
        ->count();
@endphp


<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-6">

        {{-- HEADER --}}
        <section class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

            <div>

                <h1 class="text-xl font-semibold text-foreground">
                    Mis pases
                </h1>

                <p class="text-sm text-muted-foreground mt-1">
                    Consulta los pases enviados al equipo TI.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <a
                    href="{{ route('memorandos.pase_temporal') }}"
                    class="inline-flex items-center justify-center gap-2
                           px-4 py-2.5 rounded-xl border border-border
                           bg-white text-sm font-medium text-foreground
                           hover:bg-muted transition-colors"
                >
                    <i data-lucide="clock" class="w-4 h-4"></i>

                    Pase menor
                </a>


                <a
                    href="{{ route('memorandos.autorizacion') }}"
                    class="inline-flex items-center justify-center gap-2
                           px-4 py-2.5 rounded-xl bg-primary text-white
                           text-sm font-medium hover:opacity-90
                           transition-opacity"
                >
                    <i data-lucide="file-plus-2" class="w-4 h-4"></i>

                    Pase mayor
                </a>

            </div>

        </section>


        {{-- FILTRO Y RESUMEN --}}
        <section class="bg-card border border-border rounded-2xl overflow-hidden">

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
                    action="{{ route('memorandos.mis-pases') }}"
                    class="flex flex-col sm:flex-row sm:items-end gap-2"
                >

                    <div>

                        <label for="mes" class="sr-only">
                            Mes
                        </label>

                        <select
                            id="mes"
                            name="mes"
                            class="w-full sm:w-40 px-3 py-2 rounded-lg
                                   border border-border bg-white
                                   text-sm text-foreground
                                   focus:outline-none focus:border-primary
                                   focus:ring-2 focus:ring-primary/10"
                        >
                            @foreach($meses as $numero => $nombre)

                                <option
                                    value="{{ $numero }}"
                                    @selected((int) $mes === (int) $numero)
                                >
                                    {{ $nombre }}
                                </option>

                            @endforeach
                        </select>

                    </div>


                    <div>

                        <label for="anio" class="sr-only">
                            Año
                        </label>

                        <select
                            id="anio"
                            name="anio"
                            class="w-full sm:w-28 px-3 py-2 rounded-lg
                                   border border-border bg-white
                                   text-sm text-foreground
                                   focus:outline-none focus:border-primary
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


                    <div>

                        <label for="tipo" class="sr-only">
                            Tipo de pase
                        </label>

                        <select
                            id="tipo"
                            name="tipo"
                            class="w-full sm:w-44 px-3 py-2 rounded-lg
                                   border border-border bg-white
                                   text-sm text-foreground
                                   focus:outline-none focus:border-primary
                                   focus:ring-2 focus:ring-primary/10"
                        >
                            <option
                                value="todos"
                                @selected($tipoSeleccionado === 'todos')
                            >
                                Todos los pases
                            </option>

                            <option
                                value="pase_temporal"
                                @selected(
                                    $tipoSeleccionado === 'pase_temporal'
                                )
                            >
                                Menor a 24 horas
                            </option>

                            <option
                                value="autorizacion"
                                @selected(
                                    $tipoSeleccionado === 'autorizacion'
                                )
                            >
                                Mayor a 24 horas
                            </option>
                        </select>

                    </div>


                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2
                               px-3.5 py-2 rounded-lg border border-border
                               bg-white text-sm font-medium text-foreground
                               hover:bg-muted transition-colors"
                    >
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>

                        Filtrar
                    </button>


                    <a
                        href="{{ route('memorandos.mis-pases') }}"
                        class="inline-flex items-center justify-center
                               px-3 py-2 text-xs font-medium
                               text-muted-foreground hover:text-primary
                               transition-colors"
                    >
                        Mes actual
                    </a>

                </form>

            </div>


            {{-- RESUMEN --}}
            <div
                class="grid grid-cols-1 sm:grid-cols-3
                       divide-y sm:divide-y-0
                       sm:divide-x divide-border"
            >

                <div class="px-5 py-3.5">

                    <div class="flex items-center gap-2.5">

                        <i
                            data-lucide="clipboard-list"
                            class="w-4 h-4 text-muted-foreground"
                        ></i>

                        <p class="text-xs text-muted-foreground">
                            Total
                        </p>

                        <span class="ml-auto text-sm font-semibold text-foreground">
                            {{ $memorandos->count() }}
                        </span>

                    </div>

                </div>


                <div class="px-5 py-3.5">

                    <div class="flex items-center gap-2.5">

                        <i
                            data-lucide="clock"
                            class="w-4 h-4 text-muted-foreground"
                        ></i>

                        <p class="text-xs text-muted-foreground">
                            Menores a 24 h
                        </p>

                        <span class="ml-auto text-sm font-semibold text-foreground">
                            {{ $pasesMenores }}
                        </span>

                    </div>

                </div>


                <div class="px-5 py-3.5">

                    <div class="flex items-center gap-2.5">

                        <i
                            data-lucide="file-check"
                            class="w-4 h-4 text-muted-foreground"
                        ></i>

                        <p class="text-xs text-muted-foreground">
                            Mayores a 24 h
                        </p>

                        <span class="ml-auto text-sm font-semibold text-foreground">
                            {{ $pasesMayores }}
                        </span>

                    </div>

                </div>

            </div>

        </section>


        {{-- LISTADO --}}
        <section>

            <div class="flex items-center justify-between gap-4 mb-3">

                <h2 class="text-sm font-medium text-foreground">
                    Pases registrados
                </h2>

                <span class="text-xs text-muted-foreground">

                    {{ $memorandos->count() }}

                    {{
                        $memorandos->count() === 1
                            ? 'resultado'
                            : 'resultados'
                    }}

                </span>

            </div>


            <div class="bg-card border border-border rounded-2xl overflow-hidden">

                @forelse($memorandos as $memorando)

                    @php
                        $esTemporal =
                            $memorando->tipo?->slug === 'pase_temporal';

                        $identificador = $memorando->codigo
                            ?: 'PASE-'.str_pad(
                                (string) $memorando->id,
                                5,
                                '0',
                                STR_PAD_LEFT
                            );

                        $datos = $memorando->datos_extra ?? [];
                    @endphp


                    <a
                        href="{{ route(
                            'memorandos.show-pase',
                            $memorando
                        ) }}"
                        class="group block px-5 py-4
                               border-b border-border last:border-b-0
                               hover:bg-muted/40 transition-colors"
                    >

                        <div class="flex items-start justify-between gap-5">

                            <div class="min-w-0 flex-1">

                                <div
                                    class="flex flex-wrap items-center
                                           gap-x-2 gap-y-1"
                                >

                                    <span class="text-xs font-semibold text-primary">
                                        {{ $identificador }}
                                    </span>

                                    <span class="w-1 h-1 rounded-full bg-border"></span>

                                    <span class="text-xs text-muted-foreground">
                                        {{ $memorando->created_at->format('d/m/Y') }}
                                    </span>

                                    <span class="text-xs text-muted-foreground">
                                        {{ $memorando->created_at->format('H:i') }}
                                    </span>

                                </div>


                                <h3
                                    class="text-sm font-medium
                                           text-foreground mt-1.5 truncate"
                                >
                                    {{ $memorando->asunto }}
                                </h3>


                                <p
                                    class="text-xs text-muted-foreground
                                           mt-1 leading-relaxed line-clamp-1"
                                >
                                    {{
                                        $datos['motivo_autorizacion']
                                        ?? $memorando->observaciones
                                        ?? 'Sin descripción adicional.'
                                    }}
                                </p>


                                <div
                                    class="flex flex-wrap items-center gap-3
                                           mt-2.5 text-[11px]
                                           text-muted-foreground"
                                >

                                    <span class="inline-flex items-center gap-1">

                                        <i
                                            data-lucide="{{
                                                $esTemporal
                                                    ? 'clock'
                                                    : 'file-check'
                                            }}"
                                            class="w-3 h-3"
                                        ></i>

                                        {{ $memorando->tipo?->nombre_visual }}

                                    </span>


                                    @if(!empty($datos['colaborador']))

                                        <span class="inline-flex items-center gap-1">

                                            <i
                                                data-lucide="user"
                                                class="w-3 h-3"
                                            ></i>

                                            {{
                                                \Illuminate\Support\Str::limit(
                                                    $datos['colaborador'],
                                                    35
                                                )
                                            }}

                                        </span>

                                    @endif

                                </div>

                            </div>


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

                    <div class="px-6 py-12 text-center">

                        <div
                            class="w-11 h-11 rounded-full bg-muted
                                   flex items-center justify-center mx-auto"
                        >
                            <i
                                data-lucide="calendar-x"
                                class="w-5 h-5 text-muted-foreground"
                            ></i>
                        </div>

                        <h3 class="text-sm font-medium text-foreground mt-4">
                            Sin pases en este periodo
                        </h3>

                        <p class="text-xs text-muted-foreground mt-1.5">
                            No hay pases registrados durante
                            {{ $meses[$mes] }} de {{ $anio }}.
                        </p>

                        <a
                            href="{{ route('memorandos.mis-pases') }}"
                            class="inline-flex mt-5 text-xs font-medium
                                   text-muted-foreground hover:text-primary"
                        >
                            Ver mes actual
                        </a>

                    </div>

                @endforelse

            </div>

        </section>

    </main>

</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>

@endsection