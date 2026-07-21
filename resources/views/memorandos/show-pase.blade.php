@extends('layouts.app')

@section('title', 'Detalle del pase')

@section('content')

@php
    $datos = $memorando->datos_extra ?? [];
    $equipos = $datos['equipos'] ?? [];

    $esTemporal =
        $memorando->tipo?->slug === 'pase_temporal';

    $identificador = $memorando->codigo
        ?: 'PASE-'.str_pad(
            (string) $memorando->id,
            5,
            '0',
            STR_PAD_LEFT
        );

    $estados = [
        'GENERADO' => [
            'Generado',
            'bg-blue-50 text-blue-700 border-blue-200',
        ],

        'EN_FIRMA' => [
            'En firma',
            'bg-amber-50 text-amber-700 border-amber-200',
        ],

        'APROBADO' => [
            'Aprobado',
            'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],

        'RECHAZADO' => [
            'Rechazado',
            'bg-red-50 text-red-700 border-red-200',
        ],

        'ARCHIVADO' => [
            'Archivado',
            'bg-slate-50 text-slate-600 border-slate-200',
        ],
    ];

    $estado = $estados[$memorando->estado]
        ?? [
            ucfirst(strtolower($memorando->estado)),
            'bg-slate-50 text-slate-600 border-slate-200',
        ];
@endphp


<div class="min-h-screen bg-background">

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
                               rounded-full bg-primary/10 text-primary
                               text-xs font-semibold"
                    >
                        {{ $identificador }}
                    </span>


                    <span
                        class="inline-flex items-center px-2.5 py-1
                               rounded-full border text-[11px] font-medium
                               {{ $estado[1] }}"
                    >
                        {{ $estado[0] }}
                    </span>

                </div>


                <h1 class="text-xl font-semibold text-foreground mt-2">
                    {{ $memorando->asunto }}
                </h1>


                <p class="text-sm text-muted-foreground mt-1">
                    {{ $memorando->tipo?->nombre_visual }}
                    ·
                    {{ $memorando->created_at->format('d/m/Y H:i') }}
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                @if($memorando->archivo_pdf)

                    <a
                        href="{{ route(
                            'memorandos.download',
                            $memorando->id
                        ) }}"
                        class="inline-flex items-center justify-center gap-2
                               px-4 py-2.5 rounded-xl border border-border
                               bg-white text-sm font-medium text-foreground
                               hover:bg-muted transition-colors"
                    >
                        <i data-lucide="download" class="w-4 h-4"></i>

                        Descargar PDF
                    </a>

                @endif


                <a
                    href="{{
                        $esTemporal
                            ? route('memorandos.pase_temporal')
                            : route('memorandos.autorizacion')
                    }}"
                    class="inline-flex items-center justify-center gap-2
                           px-4 py-2.5 rounded-xl bg-primary text-white
                           text-sm font-medium hover:opacity-90
                           transition-opacity"
                >
                    <i data-lucide="plus" class="w-4 h-4"></i>

                    Nuevo pase
                </a>

            </div>

        </section>


        {{-- INFORMACIÓN GENERAL --}}
        <section class="bg-card border border-border rounded-2xl overflow-hidden">

            <div class="px-5 py-4 border-b border-border">

                <h2 class="text-sm font-medium text-foreground">
                    Información general
                </h2>

                <p class="text-xs text-muted-foreground mt-1">
                    Datos principales del pase.
                </p>

            </div>


            <div
                class="grid grid-cols-1 sm:grid-cols-3
                       divide-y sm:divide-y-0
                       sm:divide-x divide-border"
            >

                <div class="px-5 py-4">

                    <p class="text-xs text-muted-foreground">
                        Solicitado por
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{ $memorando->solicitante->nombre ?? 'No disponible' }}
                    </p>

                    <p class="text-xs text-muted-foreground mt-0.5">
                        {{ $memorando->solicitante->correo ?? 'Correo no disponible' }}
                    </p>

                </div>


                <div class="px-5 py-4">

                    <p class="text-xs text-muted-foreground">
                        Tipo de pase
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{ $memorando->tipo?->nombre_visual }}
                    </p>

                </div>


                <div class="px-5 py-4">

                    <p class="text-xs text-muted-foreground">
                        Fecha del documento
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{
                            $memorando->fecha_documento
                                ?->format('d/m/Y')
                            ?? 'No indicada'
                        }}
                    </p>

                </div>

            </div>

        </section>


        {{-- INFORMACIÓN DEL DOCUMENTO --}}
        <section class="bg-card border border-border rounded-2xl overflow-hidden">

            <div class="px-5 py-4 border-b border-border">

                <h2 class="text-sm font-medium text-foreground">
                    Información del documento
                </h2>

            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2">

                <div class="px-5 py-4 border-b border-border sm:border-r">

                    <p class="text-xs text-muted-foreground">
                        Para
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{ $memorando->para_nombre ?: 'No indicado' }}
                    </p>

                </div>


                <div class="px-5 py-4 border-b border-border">

                    <p class="text-xs text-muted-foreground">
                        CC
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{ $memorando->cc_nombre ?: 'No indicado' }}
                    </p>

                </div>


                <div class="px-5 py-4 border-b sm:border-b-0 border-border sm:border-r">

                    <p class="text-xs text-muted-foreground">
                        De
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{ $memorando->de_nombre ?: 'No indicado' }}
                    </p>

                </div>


                <div class="px-5 py-4">

                    <p class="text-xs text-muted-foreground">
                        Asunto
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{ $memorando->asunto }}
                    </p>

                </div>

            </div>

        </section>


        {{-- INFORMACIÓN DE AUTORIZACIÓN --}}
        <section class="bg-card border border-border rounded-2xl overflow-hidden">

            <div class="px-5 py-4 border-b border-border">

                <h2 class="text-sm font-medium text-foreground">
                    Información de autorización
                </h2>

            </div>


            <div
                class="grid grid-cols-1 sm:grid-cols-2
                       divide-y sm:divide-y-0
                       sm:divide-x divide-border"
            >

                <div class="px-5 py-4">

                    <p class="text-xs text-muted-foreground">
                        Colaborador
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{ $datos['colaborador'] ?? 'No indicado' }}
                    </p>

                </div>


                <div class="px-5 py-4">

                    <p class="text-xs text-muted-foreground">
                        Cargo / Área
                    </p>

                    <p class="text-sm font-medium text-foreground mt-1">
                        {{ $datos['cargo_area'] ?? 'No indicado' }}
                    </p>

                </div>

            </div>


            <div class="px-5 py-4 border-t border-border">

                <p class="text-xs text-muted-foreground">
                    Motivo de autorización
                </p>

                <p
                    class="text-sm leading-relaxed text-foreground
                           mt-1 whitespace-pre-line"
                >{{ $datos['motivo_autorizacion'] ?? 'No indicado' }}</p>

            </div>

        </section>


        {{-- EQUIPOS --}}
        @if(!empty($equipos))

            <section class="bg-card border border-border rounded-2xl overflow-hidden">

                <div
                    class="px-5 py-4 border-b border-border
                           flex items-center justify-between gap-4"
                >

                    <div>

                        <h2 class="text-sm font-medium text-foreground">
                            Equipos autorizados
                        </h2>

                        <p class="text-xs text-muted-foreground mt-1">
                            Equipos relacionados con este pase.
                        </p>

                    </div>


                    <span class="text-xs text-muted-foreground">

                        {{ count($equipos) }}

                        {{ count($equipos) === 1 ? 'equipo' : 'equipos' }}

                    </span>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr class="bg-muted/50 border-b border-border">

                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">
                                    Equipo
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">
                                    Marca
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">
                                    Modelo
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">
                                    Serie
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">
                                    Color
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-border">

                            @foreach($equipos as $equipo)

                                <tr>

                                    <td class="px-4 py-3 text-foreground">
                                        {{ $equipo['descripcion'] ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-foreground">
                                        {{ $equipo['marca'] ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-foreground">
                                        {{ $equipo['modelo'] ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-foreground">
                                        {{ $equipo['codigo'] ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-foreground">
                                        {{ $equipo['color'] ?? '—' }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </section>

        @endif


        {{-- OBSERVACIONES --}}
        @if($memorando->observaciones)

            <section class="bg-card border border-border rounded-2xl overflow-hidden">

                <div class="px-5 py-4 border-b border-border">

                    <h2 class="text-sm font-medium text-foreground">
                        Observaciones
                    </h2>

                </div>

                <div class="px-5 py-5">

                    <p
                        class="text-sm leading-relaxed text-foreground
                               whitespace-pre-line"
                    >{{ $memorando->observaciones }}</p>

                </div>

            </section>

        @endif


        <div class="flex justify-end">

            <a
                href="{{ route('memorandos.mis-pases') }}"
                class="inline-flex items-center gap-2
                       px-4 py-2.5 rounded-xl border border-border
                       bg-white text-sm font-medium text-foreground
                       hover:bg-muted transition-colors"
            >
                <i data-lucide="arrow-left" class="w-4 h-4"></i>

                Volver a mis pases
            </a>

        </div>

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