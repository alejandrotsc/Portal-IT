@extends('layouts.app')

@section('title', 'Detalle del pase')

@section('content')

@php

    $datos = $memorando->datos_extra
        ?? [];

    $equipos = $datos['equipos']
        ?? [];

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


    $ultimoRechazo = $memorando->historial
        ->where(
            'estado_nuevo',
            \App\Models\Memorando::ESTADO_RECHAZADO
        )
        ->sortByDesc('created_at')
        ->first();

    $motivoRechazo =
        $ultimoRechazo?->comentario;

@endphp


<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-10">


        {{-- Navegación --}}

        <a
            href="{{ route('memorandos.mis-pases') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/20 bg-primary/5 text-sm font-medium text-primary transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/10 hover:shadow-sm active:translate-y-0">

            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5">
            </i>

            <span>
                Volver a mis pases
            </span>

        </a>



        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                <div class="flex items-start gap-4 min-w-0">

                    <div class="group flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:bg-primary/15 hover:shadow-md">

                        <i
                            data-lucide="{{ $esTemporal
                                ? 'clock-3'
                                : 'file-check-2'
                            }}"
                            stroke-width="1.8"
                            class="w-6 h-6 transition-transform duration-300 group-hover:scale-110">
                        </i>

                    </div>


                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <h1 class="text-2xl font-semibold text-foreground tracking-tight">
                                {{ $identificador }}
                            </h1>


                            {{-- Estado --}}

                            @switch($memorando->estado)

                                @case('APROBADO')

                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700">

                                        <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-emerald-500"></span>

                                        Aprobado

                                    </span>

                                    @break


                                @case('RECHAZADO')

                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 text-xs font-medium text-red-700">

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

                        </div>


                        <h2 class="mt-2 text-lg font-semibold text-foreground leading-relaxed break-words">
                            {{ $memorando->asunto }}
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground leading-relaxed">
                            Consulta el estado, la información registrada y el documento asociado a tu pase.
                        </p>

                    </div>

                </div>


                @if($memorando->archivo_pdf)

                    <div class="flex flex-wrap items-center gap-2">


                        <a
                            href="{{ route(
                                'memorandos.download',
                                $memorando
                            ) }}"
                            class="group/download inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold whitespace-nowrap shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                            <i
                                data-lucide="download"
                                stroke-width="1.8"
                                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/download:translate-y-0.5">
                            </i>

                            Descargar PDF

                        </a>

                    </div>

                @endif

            </div>

        </section>



        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_300px] gap-6">


            {{-- Contenido principal --}}

            <div class="space-y-6">


                {{-- Información del pase --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

                    <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                        <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                            <i
                                data-lucide="file-text"
                                stroke-width="1.8"
                                class="w-[18px] h-[18px]">
                            </i>

                        </div>

                        <div>

                            <h2 class="text-sm font-semibold text-foreground">
                                Información del pase
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Datos principales incluidos en el documento.
                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2">

                        {{-- Asunto --}}

                        <div class="px-6 py-5 border-b border-border sm:col-span-2">

                            <p class="text-xs font-medium text-muted-foreground">
                                Asunto
                            </p>

                            <p class="mt-1 text-sm font-medium leading-relaxed text-foreground break-words">
                                {{ $memorando->asunto ?: 'No indicado' }}
                            </p>

                        </div>


                        {{-- Para --}}

                        <div class="px-6 py-5 border-b border-border sm:border-r">

                            <p class="text-xs font-medium text-muted-foreground">
                                Para
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground break-words">
                                {{ $memorando->para_nombre ?: 'No indicado' }}
                            </p>

                        </div>


                        {{-- CC --}}

                        <div class="px-6 py-5 border-b border-border">

                            <p class="text-xs font-medium text-muted-foreground">
                                CC
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground break-words">
                                {{ $memorando->cc_nombre ?: 'No indicado' }}
                            </p>

                        </div>


                        {{-- De --}}

                        <div class="px-6 py-5 border-b border-border sm:border-b-0 sm:border-r">

                            <p class="text-xs font-medium text-muted-foreground">
                                De
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground break-words">
                                {{ $memorando->de_nombre ?: 'No indicado' }}
                            </p>

                        </div>


                        {{-- Fecha del documento --}}

                        <div class="px-6 py-5">

                            <p class="text-xs font-medium text-muted-foreground">
                                Fecha del documento
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground">

                                {{ $memorando->fecha_documento
                                    ?->format('d/m/Y')
                                    ?? 'No indicada'
                                }}

                            </p>

                        </div>

                    </div>

                </section>



                {{-- Información de autorización --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

                    <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                        <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                            <i
                                data-lucide="shield-check"
                                stroke-width="1.8"
                                class="w-[18px] h-[18px]">
                            </i>

                        </div>

                        <div>

                            <h2 class="text-sm font-semibold text-foreground">
                                Información de autorización
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Datos principales registrados para este pase.
                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2">

                        <div class="px-6 py-4 border-b border-border sm:border-r transition-colors duration-200 hover:bg-primary/[0.025]">

                            <p class="text-xs font-medium text-muted-foreground">
                                Tipo de documento
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground break-words">

                                {{ $esTemporal
                                    ? 'Pase menor a 24 horas'
                                    : 'Pase mayor a 24 horas'
                                }}

                            </p>

                        </div>


                        <div class="px-6 py-4 border-b border-border transition-colors duration-200 hover:bg-primary/[0.025]">

                            <p class="text-xs font-medium text-muted-foreground">
                                Responsable del equipo
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground break-words">

                                {{ $datos['colaborador']
                                    ?? $datos['nombre_colaborador']
                                    ?? 'No indicado'
                                }}

                            </p>

                        </div>


                        <div class="px-6 py-4 border-b border-border sm:border-b-0 sm:border-r transition-colors duration-200 hover:bg-primary/[0.025]">

                            <p class="text-xs font-medium text-muted-foreground">
                                Cargo o área
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground break-words">
                                {{ $datos['cargo_area'] ?? 'No indicado' }}
                            </p>

                        </div>


                        <div class="px-6 py-4 transition-colors duration-200 hover:bg-primary/[0.025]">

                            <p class="text-xs font-medium text-muted-foreground">
                                Motivo de autorización
                            </p>

                            <p class="mt-1 text-sm font-medium leading-relaxed text-foreground whitespace-pre-line break-words">{{ $datos['motivo_autorizacion'] ?? 'No indicado' }}</p>

                        </div>

                    </div>

                </section>



                {{-- Equipos autorizados --}}

                @if(!empty($equipos))

                    <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

                        <div class="flex items-center justify-between gap-4 px-6 py-5 border-b border-border">

                            <div class="flex items-center gap-3">

                                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                                    <i
                                        data-lucide="laptop"
                                        stroke-width="1.8"
                                        class="w-[18px] h-[18px]">
                                    </i>

                                </div>

                                <div>

                                    <h2 class="text-sm font-semibold text-foreground">
                                        Equipos autorizados
                                    </h2>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Equipos relacionados con este pase.
                                    </p>

                                </div>

                            </div>


                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-primary/20 bg-primary/5 text-xs font-medium text-primary shrink-0">

                                <i
                                    data-lucide="package-check"
                                    stroke-width="1.8"
                                    class="w-3.5 h-3.5">
                                </i>

                                {{ count($equipos) }}

                                {{ count($equipos) === 1
                                    ? 'equipo'
                                    : 'equipos'
                                }}

                            </span>

                        </div>


                        <div class="overflow-x-auto">

                            <table class="w-full min-w-[700px]">

                                <thead class="border-b border-border bg-muted/40">

                                    <tr class="text-left">

                                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Equipo
                                        </th>

                                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Marca
                                        </th>

                                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Modelo
                                        </th>

                                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Serie
                                        </th>

                                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            Color
                                        </th>

                                    </tr>

                                </thead>


                                <tbody class="divide-y divide-border">

                                    @foreach($equipos as $equipo)

                                        <tr class="transition-colors duration-200 hover:bg-primary/[0.025]">

                                            <td class="px-5 py-4 text-sm font-medium text-foreground">
                                                {{ $equipo['descripcion'] ?? '—' }}
                                            </td>

                                            <td class="px-5 py-4 text-sm text-foreground">
                                                {{ $equipo['marca'] ?? '—' }}
                                            </td>

                                            <td class="px-5 py-4 text-sm text-foreground">
                                                {{ $equipo['modelo'] ?? '—' }}
                                            </td>

                                            <td class="px-5 py-4">

                                                <span class="inline-flex items-center gap-1.5 font-mono text-xs text-foreground">

                                                    <i
                                                        data-lucide="barcode"
                                                        stroke-width="1.8"
                                                        class="w-3.5 h-3.5 shrink-0 text-primary">
                                                    </i>

                                                    {{ $equipo['codigo'] ?? '—' }}

                                                </span>

                                            </td>

                                            <td class="px-5 py-4 text-sm text-foreground">
                                                {{ $equipo['color'] ?? '—' }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </section>

                @endif



                {{-- Motivo del rechazo --}}

                @if(
                    $memorando->estaRechazado()
                    && filled($motivoRechazo)
                )

                    <section class="group rounded-2xl border border-red-200 bg-red-50/50 shadow-sm overflow-hidden transition-all duration-300 hover:border-red-300 hover:shadow-md">

                        <div class="flex items-center gap-3 px-6 py-5 border-b border-red-200">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-red-100 text-red-600 transition-transform duration-300 group-hover:scale-105">

                                <i
                                    data-lucide="message-square-warning"
                                    stroke-width="1.8"
                                    class="w-[18px] h-[18px]">
                                </i>

                            </div>

                            <div>

                                <h2 class="text-sm font-semibold text-red-900">
                                    Motivo del rechazo
                                </h2>

                                <p class="mt-1 text-xs text-red-700/80">
                                    Explicación registrada durante la revisión administrativa.
                                </p>

                            </div>

                        </div>


                        <div class="p-6">

                            <p class="text-sm leading-relaxed text-red-800 whitespace-pre-line break-words">{{ $motivoRechazo }}</p>

                            @if($ultimoRechazo?->created_at)

                                <p class="mt-3 text-xs text-red-700/70">

                                    Registrado el

                                    {{ $ultimoRechazo->created_at
                                        ->timezone('America/Tegucigalpa')
                                        ->format('d/m/Y h:i A')
                                    }}

                                </p>

                            @endif

                        </div>

                    </section>

                @endif



                {{-- Observaciones --}}

                @if($memorando->observaciones)

                    <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

                        <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                                <i
                                    data-lucide="message-square-text"
                                    stroke-width="1.8"
                                    class="w-[18px] h-[18px]">
                                </i>

                            </div>

                            <div>

                                <h2 class="text-sm font-semibold text-foreground">
                                    Observaciones
                                </h2>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Información adicional registrada en el documento.
                                </p>

                            </div>

                        </div>


                        <div class="p-6">

                            <div class="rounded-xl border border-border bg-muted/20 px-4 py-3.5 transition-colors duration-200 hover:border-primary/15 hover:bg-primary/[0.02]">

                                <p class="text-sm text-foreground leading-relaxed whitespace-pre-line break-words">{{ $memorando->observaciones }}</p>

                            </div>

                        </div>

                    </section>

                @endif

            </div>



            {{-- Panel lateral --}}

            <aside class="space-y-5">


                {{-- Solicitante --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/15 hover:shadow-md">

                    <div class="px-5 py-4 border-b border-border">

                        <h2 class="text-sm font-semibold text-foreground">
                            Solicitado por
                        </h2>

                    </div>


                    <div class="p-5">

                        <div class="flex items-center gap-3">

                            <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-full bg-primary/10 text-sm font-semibold text-primary transition-all duration-300 group-hover:scale-105 group-hover:bg-primary/15">

                                {{ $memorando->solicitante?->nombre
                                    ? mb_strtoupper(
                                        mb_substr(
                                            $memorando->solicitante->nombre,
                                            0,
                                            1
                                        )
                                    )
                                    : 'U'
                                }}

                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-foreground truncate">
                                    {{ $memorando->solicitante?->nombre ?? 'Usuario no disponible' }}
                                </p>

                                <p class="mt-0.5 text-xs text-muted-foreground truncate">
                                    {{ $memorando->solicitante?->correo ?? 'Correo no disponible' }}
                                </p>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- Registro --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/15 hover:shadow-md">

                    <div class="px-5 py-4 border-b border-border">

                        <h2 class="text-sm font-semibold text-foreground">
                            Registro
                        </h2>

                    </div>


                    <div class="p-5 space-y-4">

                        <div class="flex items-start gap-3">

                            <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-primary/10 text-primary">

                                <i
                                    data-lucide="calendar-days"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </div>

                            <div>

                                <p class="text-xs text-muted-foreground">
                                    Fecha de creación
                                </p>

                                <p class="mt-1 text-sm font-medium text-foreground">

                                    {{ $memorando->created_at
                                        ?->timezone('America/Tegucigalpa')
                                        ->format('d/m/Y')
                                    }}

                                </p>

                            </div>

                        </div>


                        <div class="flex items-start gap-3">

                            <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-primary/10 text-primary">

                                <i
                                    data-lucide="clock"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </div>

                            <div>

                                <p class="text-xs text-muted-foreground">
                                    Hora
                                </p>

                                <p class="mt-1 text-sm font-medium text-foreground">

                                    {{ $memorando->created_at
                                        ?->timezone('America/Tegucigalpa')
                                        ->format('h:i A')
                                    }}

                                </p>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- Tipo de pase --}}

                <section class="group relative overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:shadow-md">

                    <div class="absolute -right-10 -top-10 w-24 h-24 rounded-full bg-primary/5 transition-transform duration-500 group-hover:scale-150"></div>

                    <div class="relative">

                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 text-primary">

                            <i
                                data-lucide="{{ $esTemporal
                                    ? 'clock-3'
                                    : 'calendar-range'
                                }}"
                                stroke-width="1.8"
                                class="w-[18px] h-[18px]">
                            </i>

                        </div>

                        <h2 class="mt-4 text-sm font-semibold text-foreground">
                            {{ $esTemporal
                                ? 'Pase menor a 24 horas'
                                : 'Pase mayor a 24 horas'
                            }}
                        </h2>

                        <p class="mt-1 text-xs text-muted-foreground leading-relaxed">

                            {{ $esTemporal
                                ? 'Autorización temporal registrada para un periodo menor a un día.'
                                : 'Documento de autorización registrado para un periodo mayor a un día.'
                            }}

                        </p>

                    </div>

                </section>



                {{-- Estado del memorando --}}

@if($memorando->estaAprobado())

    <section class="group relative overflow-hidden rounded-2xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50 via-white to-teal-50/70 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10">

        <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-emerald-400/10 transition-transform duration-500 group-hover:scale-150"></div>

        <div class="relative">

            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-emerald-500/15 text-emerald-700 ring-4 ring-emerald-500/5 transition-transform duration-300 group-hover:scale-105">

                <i
                    data-lucide="circle-check-big"
                    stroke-width="1.8"
                    class="w-5 h-5">
                </i>

            </div>


            <div class="mt-4 flex flex-wrap items-center gap-2">

                <h2 class="text-sm font-semibold text-emerald-900">
                    Pase aprobado
                </h2>

                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">

                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                    Aprobado

                </span>

            </div>


            <p class="mt-2 text-xs leading-relaxed text-emerald-800/80">
                El pase fue revisado correctamente y su autorización fue aprobada.
            </p>

        </div>

    </section>


@elseif($memorando->estaRechazado())

    <section class="group relative overflow-hidden rounded-2xl border border-red-200/70 bg-gradient-to-br from-red-50 via-white to-rose-50/70 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-red-300 hover:shadow-lg hover:shadow-red-500/10">

        <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-red-400/10 transition-transform duration-500 group-hover:scale-150"></div>

        <div class="relative">

            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-red-500/10 text-red-700 ring-4 ring-red-500/5 transition-transform duration-300 group-hover:scale-105">

                <i
                    data-lucide="circle-x"
                    stroke-width="1.8"
                    class="w-5 h-5">
                </i>

            </div>


            <div class="mt-4 flex flex-wrap items-center gap-2">

                <h2 class="text-sm font-semibold text-red-900">
                    Pase rechazado
                </h2>

                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-red-500/10 text-[10px] font-semibold uppercase tracking-wide text-red-700">

                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>

                    Rechazado

                </span>

            </div>


            <p class="mt-2 text-xs leading-relaxed text-red-800/80">
                El pase fue revisado, pero la autorización solicitada fue rechazada. Consulta el motivo registrado en el detalle.
            </p>

        </div>

    </section>


@else

    <section class="group relative overflow-hidden rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-orange-50/70 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10">

        <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-amber-400/10 transition-transform duration-500 group-hover:scale-150"></div>

        <div class="relative">

            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-amber-500/10 text-amber-700 ring-4 ring-amber-500/5 transition-transform duration-300 group-hover:scale-105">

                <i
                    data-lucide="clock-3"
                    stroke-width="1.8"
                    class="w-5 h-5">
                </i>

            </div>


            <div class="mt-4 flex flex-wrap items-center gap-2">

                <h2 class="text-sm font-semibold text-amber-900">
                    Pase pendiente
                </h2>

                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-500/10 text-[10px] font-semibold uppercase tracking-wide text-amber-700">

                    <span class="relative flex w-1.5 h-1.5">

                        <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                    </span>

                    Por revisar

                </span>

            </div>


            <p class="mt-2 text-xs leading-relaxed text-amber-800/80">
                El pase fue recibido y se encuentra pendiente de revisión administrativa.
            </p>

        </div>

    </section>

@endif



                {{-- Crear otro pase --}}

                <section class="rounded-2xl border border-border bg-card p-5 shadow-sm">

                    <h2 class="text-sm font-semibold text-foreground">
                        Crear otro pase
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground leading-relaxed">
                        Selecciona el tipo de pase que deseas registrar.
                    </p>


                    <div class="mt-4 space-y-2">

                        <a
                            href="{{ route('memorandos.pase_temporal') }}"
                            class="group/temporal inline-flex items-center justify-between gap-2 w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-xs font-semibold text-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0">

                            <span class="inline-flex items-center gap-2">

                                <i
                                    data-lucide="clock-3"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                                Pase menor

                            </span>

                            <i
                                data-lucide="arrow-right"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 transition-transform duration-200 group-hover/temporal:translate-x-0.5">
                            </i>

                        </a>


                        <a
                            href="{{ route('memorandos.autorizacion') }}"
                            class="group/autorizacion inline-flex items-center justify-between gap-2 w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-xs font-semibold text-foreground transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm active:translate-y-0">

                            <span class="inline-flex items-center gap-2">

                                <i
                                    data-lucide="file-plus-2"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                                Pase mayor

                            </span>

                            <i
                                data-lucide="arrow-right"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 transition-transform duration-200 group-hover/autorizacion:translate-x-0.5">
                            </i>

                        </a>

                    </div>

                </section>

            </aside>

        </div>

    </main>

</div>

@endsection