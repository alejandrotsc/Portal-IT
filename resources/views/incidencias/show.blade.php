@extends('layouts.app')

@section('title', 'Detalle de incidencia')

@section('content')

<div
    class="min-h-screen bg-background"
    x-data="{ imagenPreview: null }"
>

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-6">


        {{-- HEADER --}}
        <section
            class="flex flex-col sm:flex-row
                   sm:items-start sm:justify-between gap-4"
        >

            <div>


                <div class="flex flex-wrap items-center gap-2">

                    <span
                        class="inline-flex items-center px-2.5 py-1
                               rounded-full bg-primary/10
                               text-primary text-xs font-semibold"
                    >
                        {{ $incidencia->codigo }}
                    </span>

                </div>


                <h1 class="text-xl font-semibold text-foreground mt-2">
                    {{ $incidencia->titulo }}
                </h1>


                <p class="text-sm text-muted-foreground mt-1">
                    Reportada el
                    {{ $incidencia->created_at->format('d/m/Y') }}
                    a las
                    {{ $incidencia->created_at->format('H:i') }}
                </p>

            </div>


            <a
                href="{{ route('incidencias.create') }}"
                class="inline-flex items-center justify-center gap-2
                       px-4 py-2.5 rounded-xl
                       bg-primary text-white
                       text-sm font-medium
                       hover:opacity-90 transition-opacity"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>

                Nueva incidencia
            </a>

        </section>


        {{-- INFORMACIÓN GENERAL --}}
        <section
            class="bg-card border border-border
                   rounded-2xl overflow-hidden"
        >

            <div
                class="px-5 py-4 border-b border-border
                       flex items-center justify-between gap-4"
            >
                <div>

                    <h2 class="text-sm font-medium text-foreground">
                        Información general
                    </h2>

                    <p class="text-xs text-muted-foreground mt-1">
                        Datos relacionados con el reporte.
                    </p>

                </div>

                <i
                    data-lucide="info"
                    class="w-4 h-4 text-muted-foreground"
                ></i>
            </div>


            <div
                class="grid grid-cols-1 sm:grid-cols-2
                       divide-y sm:divide-y-0
                       sm:divide-x divide-border"
            >

                {{-- USUARIO --}}
                <div class="px-5 py-4">

                    <div class="flex items-start gap-3">

                        <i
                            data-lucide="user"
                            class="w-4 h-4 text-muted-foreground mt-0.5"
                        ></i>

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Reportado por
                            </p>

                            <p class="text-sm font-medium text-foreground mt-1">
                                {{ $incidencia->usuario->nombre ?? 'No disponible' }}
                            </p>

                            <p class="text-xs text-muted-foreground mt-0.5">
                                {{ $incidencia->usuario->correo ?? 'Correo no disponible' }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- FECHA --}}
                <div class="px-5 py-4">

                    <div class="flex items-start gap-3">

                        <i
                            data-lucide="calendar"
                            class="w-4 h-4 text-muted-foreground mt-0.5"
                        ></i>

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Fecha del reporte
                            </p>

                            <p class="text-sm font-medium text-foreground mt-1">
                                {{ $incidencia->created_at->format('d/m/Y H:i') }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <div
                class="grid grid-cols-1 sm:grid-cols-2
                       border-t border-border
                       divide-y sm:divide-y-0
                       sm:divide-x divide-border"
            >

                {{-- EQUIPO --}}
                <div class="px-5 py-4">

                    <div class="flex items-start gap-3">

                        <i
                            data-lucide="monitor"
                            class="w-4 h-4 text-muted-foreground mt-0.5"
                        ></i>

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Equipo afectado
                            </p>

                            <p class="text-sm font-medium text-foreground mt-1">
                                {{ $incidencia->equipo ?: 'No especificado' }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- UBICACIÓN --}}
                <div class="px-5 py-4">

                    <div class="flex items-start gap-3">

                        <i
                            data-lucide="map-pin"
                            class="w-4 h-4 text-muted-foreground mt-0.5"
                        ></i>

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Ubicación
                            </p>

                            <p class="text-sm font-medium text-foreground mt-1">
                                {{ $incidencia->ubicacion ?: 'No especificada' }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- DESCRIPCIÓN Y DETALLES --}}
        <section
            class="bg-card border border-border
                   rounded-2xl overflow-hidden"
        >

            <div class="px-5 py-4 border-b border-border">

                <h2 class="text-sm font-medium text-foreground">
                    Descripción del problema
                </h2>

            </div>


            <div class="px-5 py-5">

                <p
                    class="text-sm leading-relaxed text-foreground
                           whitespace-pre-line"
                >{{ $incidencia->descripcion }}</p>

            </div>


            @php
                $tiemposProblema = [
                    'hoy' => 'Hoy',
                    'ayer' => 'Ayer',
                    'varios_dias' => 'Hace varios días',
                ];

                $afectaciones = [
                    'solo' => 'Solo a mí',
                    'varios' => 'A varias personas',
                    'todos' => 'A toda el área',
                ];
            @endphp


            <div
                class="grid grid-cols-1 sm:grid-cols-2
                       border-t border-border
                       divide-y sm:divide-y-0
                       sm:divide-x divide-border"
            >

                <div class="px-5 py-4">

                    <p class="text-xs text-muted-foreground">
                        ¿Cuándo comenzó?
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{
                            $tiemposProblema[$incidencia->tiempo_problema]
                            ?? 'No indicado'
                        }}
                    </p>

                </div>


                <div class="px-5 py-4">

                    <p class="text-xs text-muted-foreground">
                        ¿A quién afecta?
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{
                            $afectaciones[$incidencia->afectacion]
                            ?? 'No indicada'
                        }}
                    </p>

                </div>

            </div>

        </section>


        {{-- EVIDENCIAS --}}
        @if($incidencia->archivos->count())

            <section
                class="bg-card border border-border
                       rounded-2xl overflow-hidden"
            >

                <div
                    class="px-5 py-4 border-b border-border
                           flex items-center justify-between gap-4"
                >

                    <div>

                        <h2 class="text-sm font-medium text-foreground">
                            Evidencias adjuntas
                        </h2>

                        <p class="text-xs text-muted-foreground mt-1">
                            Selecciona una imagen para verla completa.
                        </p>

                    </div>


                    <span class="text-xs text-muted-foreground">

                        {{ $incidencia->archivos->count() }}

                        {{
                            $incidencia->archivos->count() === 1
                                ? 'archivo'
                                : 'archivos'
                        }}

                    </span>

                </div>


                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                    @foreach($incidencia->archivos as $archivo)

                        <article
                            class="border border-border
                                   rounded-xl overflow-hidden bg-background"
                        >

                            <button
                                type="button"
                                class="block w-full"
                                @click="imagenPreview = @js(asset('storage/'.$archivo->ruta))"
                            >
                                <img
                                    src="{{ asset('storage/'.$archivo->ruta) }}"
                                    alt="{{ $archivo->nombre_original }}"
                                    class="w-full h-40 object-cover
                                           hover:opacity-90 transition-opacity"
                                >
                            </button>


                            <div class="p-3">

                                <p
                                    class="text-xs font-medium
                                           text-foreground truncate"
                                >
                                    {{ $archivo->nombre_original }}
                                </p>


                                @if($archivo->texto_ocr)

                                    <details class="mt-2">

                                        <summary
                                            class="cursor-pointer
                                                   text-xs text-primary"
                                        >
                                            Texto identificado
                                        </summary>

                                        <div
                                            class="mt-2 text-xs leading-relaxed
                                                   bg-muted rounded-lg p-3
                                                   whitespace-pre-line"
                                        >{{ $archivo->texto_ocr }}</div>

                                    </details>

                                @endif

                            </div>

                        </article>

                    @endforeach

                </div>

            </section>

        @endif


        {{-- VOLVER --}}
        <div class="flex justify-end">

            <a
                href="{{ route('mis-incidencias') }}"
                class="inline-flex items-center gap-2
                       px-4 py-2.5 rounded-xl
                       border border-border bg-white
                       text-sm font-medium text-foreground
                       hover:bg-muted transition-colors"
            >
                <i data-lucide="arrow-left" class="w-4 h-4"></i>

                Volver a mis incidencias
            </a>

        </div>

    </main>


    {{-- MODAL DE IMAGEN --}}
    <div
        x-show="imagenPreview"
        x-transition.opacity
        @keydown.escape.window="imagenPreview = null"
        @click.self="imagenPreview = null"
        class="fixed inset-0 z-50 flex items-center
               justify-center bg-black/80 p-6"
        style="display: none;"
    >

        <button
            type="button"
            @click="imagenPreview = null"
            class="absolute top-6 right-8
                   text-white hover:opacity-70 transition-opacity"
            aria-label="Cerrar imagen"
        >
            <i data-lucide="x" class="w-7 h-7"></i>
        </button>


        <img
            :src="imagenPreview"
            alt="Vista ampliada de la evidencia"
            class="max-h-[90vh] max-w-[90vw]
                   rounded-2xl shadow-2xl"
        >

    </div>

</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>

@endsection