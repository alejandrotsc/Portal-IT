@extends('layouts.app')

@section('title', 'Administración de pases')

@section('content')

@php

    $datos = $memorando->datos_extra
        ?? [];

    $equipos = $datos['equipos']
        ?? [];

    if (!is_array($equipos)) {
        $equipos = [];
    }

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

    $camposOmitidos = [
        '_method',
        'tipo_id',
        'para_nombre',
        'cc_nombre',
        'de_nombre',
        'asunto',
        'observaciones',
        'fecha_documento',
        'equipos',
    ];

    $nombresCampos = [
        'colaborador' =>
            'Colaborador',

        'nombre_colaborador' =>
            'Colaborador',

        'cargo_area' =>
            'Cargo o área',

        'motivo_autorizacion' =>
            'Motivo de autorización',

        'fecha_ingreso' =>
            'Fecha de ingreso',

        'fecha_salida' =>
            'Fecha de salida',

        'hora_ingreso' =>
            'Hora de ingreso',

        'hora_salida' =>
            'Hora de salida',

        'identidad' =>
            'Número de identidad',

        'placa' =>
            'Placa del vehículo',

        'equipo' =>
            'Equipo',

        'descripcion_equipo' =>
            'Descripción del equipo',
    ];

    $datosAdicionales = collect($datos)
        ->reject(
            fn ($valor, $campo) =>
                in_array(
                    $campo,
                    $camposOmitidos,
                    true
                )
                || $valor === null
                || $valor === ''
                || $valor === []
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
            href="{{ route('admin.pases') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/20 bg-primary/5 text-sm font-medium text-primary transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/10 hover:shadow-sm active:translate-y-0">

            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5">
            </i>

            Volver a pases

        </a>



        {{-- Mensajes --}}

        @if(session('success'))

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-emerald-100 text-emerald-600">

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

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-red-100 text-red-600">

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



        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                <div class="flex items-start gap-4">

                    <div class="group flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-primary/15 hover:shadow-md">

                        <i
                            data-lucide="file-check-2"
                            stroke-width="1.8"
                            class="w-6 h-6 transition-transform duration-300 group-hover:scale-110">
                        </i>

                    </div>

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <h1 class="text-2xl font-semibold tracking-tight text-foreground">

                                {{ $codigoMostrado }}

                            </h1>


                            @if($memorando->estaAprobado())

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700">

                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                    Aprobado

                                </span>

                            @elseif($memorando->estaRechazado())

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 text-xs font-medium text-red-600">

                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>

                                    Rechazado

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-medium text-amber-700">

                                    <span class="relative flex w-1.5 h-1.5">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                    </span>

                                    Por revisar

                                </span>

                            @endif

                        </div>

                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">

                            Revisa la información registrada y determina el resultado del pase.

                        </p>

                    </div>

                </div>


                <div class="inline-flex items-center gap-2 text-xs text-muted-foreground sm:pt-2">

                    <i
                        data-lucide="calendar-days"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0 text-primary">
                    </i>

                    Registrado el

                    <span class="font-medium text-foreground">

                        {{ $memorando->created_at
                            ?->timezone('America/Tegucigalpa')
                            ->format('d/m/Y h:i A') }}

                    </span>

                </div>

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

@if($datosAdicionales->isNotEmpty())

    <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

        <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                <i
                    data-lucide="list-tree"
                    stroke-width="1.8"
                    class="w-[18px] h-[18px]">
                </i>

            </div>

            <div>

                <h2 class="text-sm font-semibold text-foreground">
                    Información de autorización
                </h2>

                <p class="mt-1 text-xs text-muted-foreground">
                    Datos correspondientes al tipo de pase.
                </p>

            </div>

        </div>


        <div class="grid grid-cols-1 sm:grid-cols-2">


            {{-- Tipo de documento --}}

            <div class="px-6 py-4 border-b border-border sm:border-r transition-colors duration-200 hover:bg-primary/[0.025]">

                <p class="text-xs font-medium text-muted-foreground">
                    Tipo de documento
                </p>

                <p class="mt-1 text-sm font-medium text-foreground break-words">

                    {{ $memorando->tipo?->slug === 'pase_temporal'
                        ? 'Pase menor a 24 horas'
                        : 'Pase mayor a 24 horas'
                    }}

                </p>

            </div>


            {{-- Responsable del equipo --}}

            <div class="px-6 py-4 border-b border-border transition-colors duration-200 hover:bg-primary/[0.025]">

                <p class="text-xs font-medium text-muted-foreground">
                    Responsable del equipo
                </p>

                <p class="mt-1 text-sm font-medium text-foreground break-words">

                    {{ $datosAdicionales->get('colaborador')
                        ?? $datosAdicionales->get('nombre_colaborador')
                        ?? 'No especificado'
                    }}

                </p>

            </div>


            {{-- Cargo o área --}}

            <div class="px-6 py-4 border-b border-border sm:border-b-0 sm:border-r transition-colors duration-200 hover:bg-primary/[0.025]">

                <p class="text-xs font-medium text-muted-foreground">
                    Cargo o área
                </p>

                <p class="mt-1 text-sm font-medium text-foreground break-words">

                    {{ $datosAdicionales->get('cargo_area')
                        ?? 'No especificado'
                    }}

                </p>

            </div>


            {{-- Motivo de autorización --}}

            <div class="px-6 py-4 transition-colors duration-200 hover:bg-primary/[0.025]">

                <p class="text-xs font-medium text-muted-foreground">
                    Motivo de autorización
                </p>

                <p class="mt-1 text-sm font-medium leading-relaxed text-foreground whitespace-pre-line break-words">{{ $datosAdicionales->get('motivo_autorizacion')
                        ?? 'No especificado'
                    }}</p>

            </div>

        </div>

    </section>

@endif



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
                                    Comentario registrado durante la revisión administrativa.
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



                {{-- Documento PDF --}}

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
                                Documento generado
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Consulta o descarga el PDF asociado al pase.
                            </p>

                        </div>

                    </div>


                    <div class="p-6">

                        @if($memorando->tienePdf())

                            <div class="flex flex-col gap-3 rounded-xl border border-blue-200 bg-blue-50/60 p-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex items-center gap-3 min-w-0">

                                    <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-lg bg-blue-100 text-blue-600">

                                        <i
                                            data-lucide="file-down"
                                            stroke-width="1.8"
                                            class="w-5 h-5">
                                        </i>

                                    </div>

                                    <div class="min-w-0">

                                        <p class="text-sm font-semibold text-blue-800">
                                            PDF disponible
                                        </p>

                                        <p class="mt-1 text-xs text-blue-700">
                                            El documento fue generado correctamente.
                                        </p>

                                    </div>

                                </div>


                                <div class="flex items-center gap-2">

                                    

                                    <a
                                        href="{{ route(
                                            'memorandos.download',
                                            $memorando->id
                                        ) }}"
                                        class="inline-flex flex-1 items-center justify-center gap-2 px-3.5 py-2 rounded-lg bg-primary text-xs font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-sm">

                                        <i
                                            data-lucide="download"
                                            stroke-width="1.8"
                                            class="w-3.5 h-3.5">
                                        </i>

                                        Descargar

                                    </a>

                                </div>

                            </div>

                        @else

                            <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/70 p-4">

                                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-amber-100 text-amber-600">

                                    <i
                                        data-lucide="file-warning"
                                        stroke-width="1.8"
                                        class="w-[18px] h-[18px]">
                                    </i>

                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-amber-800">
                                        PDF no disponible
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-amber-700">
                                        Este pase no cuenta con un documento PDF generado.
                                    </p>

                                </div>

                            </div>

                        @endif

                    </div>

                </section>



                {{-- Historial --}}

                @if($memorando->historial->isNotEmpty())

                    <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

                        <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary">

                                <i
                                    data-lucide="history"
                                    stroke-width="1.8"
                                    class="w-[18px] h-[18px]">
                                </i>

                            </div>

                            <div>

                                <h2 class="text-sm font-semibold text-foreground">
                                    Historial de seguimiento
                                </h2>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Cambios registrados para este pase.
                                </p>

                            </div>

                        </div>


                        <div class="divide-y divide-border">

                            @foreach($memorando->historial as $evento)

                                <div class="flex items-start gap-3 px-6 py-4 transition-colors duration-200 hover:bg-primary/[0.025]">

                                    <div class="mt-1.5 w-2 h-2 shrink-0 rounded-full bg-primary"></div>

                                    <div class="min-w-0 flex-1">

                                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                                            <p class="text-sm font-semibold text-foreground">

                                                {{ str($evento->estado_nuevo)
                                                    ->replace('_', ' ')
                                                    ->title() }}

                                            </p>

                                            <span class="text-xs text-muted-foreground">

                                                {{ $evento->created_at
                                                    ?->timezone('America/Tegucigalpa')
                                                    ->format('d/m/Y h:i A') }}

                                            </span>

                                        </div>

                                        @if(filled($evento->comentario))

                                            <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                                                {{ $evento->comentario }}
                                            </p>

                                        @endif

                                    </div>

                                </div>

                            @endforeach

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
                            Solicitante
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
                                    : '?'
                                }}

                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-foreground truncate">

                                    {{ $memorando->solicitante?->nombre
                                        ?? 'Usuario no disponible'
                                    }}

                                </p>

                                <p
                                    title="{{ $memorando->solicitante?->correo }}"
                                    class="mt-0.5 text-xs text-muted-foreground truncate">

                                    {{ $memorando->solicitante?->correo
                                        ?? 'Sin correo registrado'
                                    }}

                                </p>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- Seguimiento --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/15 hover:shadow-md">

                    <div class="px-5 py-4 border-b border-border">

                        <h2 class="text-sm font-semibold text-foreground">
                            Seguimiento
                        </h2>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Estado administrativo actual.
                        </p>

                    </div>


                    <div class="p-5 space-y-4">

                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Estado
                            </span>

                            @if($memorando->estaAprobado())

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">

                                    <i
                                        data-lucide="badge-check"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    Aprobado

                                </span>

                            @elseif($memorando->estaRechazado())

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600">

                                    <i
                                        data-lucide="circle-x"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    Rechazado

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700">

                                    <i
                                        data-lucide="clock-3"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    Por revisar

                                </span>

                            @endif

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Tipo de documento
                            </span>

                            <span class="max-w-[165px] text-right text-xs font-medium text-foreground">

                                {{ $esPaseTemporal
                                    ? 'Pase menor a 24 horas'
                                    : 'Pase mayor a 24 horas'
                                }}

                            </span>

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Código
                            </span>

                            <span class="text-xs font-semibold text-foreground">
                                {{ $codigoMostrado }}
                            </span>

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Registro
                            </span>

                            <span class="text-right text-xs font-medium text-foreground">

                                {{ $memorando->created_at
                                    ?->timezone('America/Tegucigalpa')
                                    ->format('d/m/Y') }}

                            </span>

                        </div>

                    </div>

                </section>



                {{-- Decisión administrativa --}}

                @if($memorando->estaGenerado())

                    <section class="group relative overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:shadow-md">

                        <div class="absolute -right-10 -top-10 w-24 h-24 rounded-full bg-primary/5 transition-transform duration-500 group-hover:scale-150"></div>

                        <div class="relative">

                            <h2 class="text-sm font-semibold text-foreground">
                                Decisión administrativa
                            </h2>

                            <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                                Aprueba el pase o registra su rechazo.
                            </p>


                            <div class="mt-5 space-y-4">

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.pases.aprobar',
                                        $memorando
                                    ) }}"
                                    onsubmit="return confirm('¿Confirmas que deseas aprobar este pase?')">

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="group/aprobar inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg bg-primary text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                                        <i
                                            data-lucide="badge-check"
                                            stroke-width="1.8"
                                            class="w-4 h-4 transition-transform duration-200 group-hover/aprobar:scale-110">
                                        </i>

                                        Aprobar pase

                                    </button>

                                </form>


                                <div class="border-t border-primary/10 pt-4">

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.pases.rechazar',
                                            $memorando
                                        ) }}"
                                        onsubmit="return confirm('¿Confirmas que deseas rechazar este pase?')">

                                        @csrf
                                        @method('PATCH')

                                        <label
                                            for="comentario"
                                            class="block mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">

                                            Motivo del rechazo

                                            <span class="text-red-600">
                                                *
                                            </span>

                                        </label>

                                        <textarea
                                            id="comentario"
                                            name="comentario"
                                            rows="3"
                                            maxlength="500"
                                            required
                                            placeholder="Explica brevemente el motivo del rechazo..."
                                            class="w-full resize-none rounded-lg border border-border bg-white px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500/10">{{ old('comentario') }}</textarea>

                                        <button
                                            type="submit"
                                            class="group/rechazar mt-3 inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:bg-red-50 hover:text-red-600 hover:shadow-sm active:translate-y-0">

                                            <i
                                                data-lucide="circle-x"
                                                stroke-width="1.8"
                                                class="w-4 h-4 transition-transform duration-200 group-hover/rechazar:scale-110">
                                            </i>

                                            Rechazar pase

                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </section>

                @elseif($memorando->estaAprobado())

                    <section class="group rounded-2xl border border-emerald-200 bg-emerald-50/60 p-5 transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50 hover:shadow-sm">

                        <div class="flex items-start gap-3">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 transition-transform duration-300 group-hover:scale-105">

                                <i
                                    data-lucide="badge-check"
                                    stroke-width="1.8"
                                    class="w-[18px] h-[18px]">
                                </i>

                            </div>

                            <div>

                                <h2 class="text-sm font-semibold text-emerald-800">
                                    Pase aprobado
                                </h2>

                                <p class="mt-1 text-xs leading-relaxed text-emerald-700">
                                    La revisión administrativa fue completada y el pase quedó aprobado.
                                </p>

                            </div>

                        </div>

                    </section>

                @else

                    <section class="group rounded-2xl border border-red-200 bg-red-50/70 p-5 transition-all duration-300 hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-50 hover:shadow-sm">

                        <div class="flex items-start gap-3">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-red-200 text-red-600 transition-transform duration-300 group-hover:scale-105">

                                <i
                                    data-lucide="circle-x"
                                    stroke-width="1.8"
                                    class="w-[18px] h-[18px]">
                                </i>

                            </div>

                            <div>

                                <h2 class="text-sm font-semibold text-red-600">
                                    Pase rechazado
                                </h2>

                                <p class="mt-1 text-xs leading-relaxed text-red-700">
                                    La revisión administrativa fue completada y el pase no fue autorizado. Consulta el motivo registrado en el detalle.
                                </p>

                            </div>

                        </div>

                    </section>

                @endif

            </aside>

        </div>

    </main>

</div>

@endsection