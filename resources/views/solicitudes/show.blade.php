@extends('layouts.app')

@section('title', 'Detalle de solicitud')

@section('content')

@php

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

    $categoriaSolicitud =
        $categorias[$solicitud->categoria]
        ?? str($solicitud->categoria)
            ->replace('_', ' ')
            ->title();

@endphp


<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-10">


        {{-- Navegación --}}

        <a
            href="{{ route('mis-solicitudes') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/20 bg-primary/5 text-sm font-medium text-primary transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/10 hover:shadow-sm active:translate-y-0">

            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5">
            </i>

            <span>
                Volver a mis solicitudes
            </span>

        </a>



        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                <div class="flex items-start gap-4 min-w-0">

                    <div class="group flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:bg-primary/15 hover:shadow-md">

                        <i
                            data-lucide="file-text"
                            stroke-width="1.8"
                            class="w-6 h-6 transition-transform duration-300 group-hover:scale-110">
                        </i>

                    </div>


                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <h1 class="text-2xl font-semibold text-foreground tracking-tight">
                                {{ $solicitud->folio }}
                            </h1>


                            {{-- Estado --}}

                            @if(
                                in_array(
                                    $solicitud->estado,
                                    [
                                        'finalizada',
                                        'completada',
                                    ],
                                    true
                                )
                            )

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700">

                                    <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-emerald-500"></span>

                                    {{ $solicitud->estado === 'completada'
                                        ? 'Completada'
                                        : 'Finalizada'
                                    }}

                                </span>

                            @elseif(
                                in_array(
                                    $solicitud->estado,
                                    [
                                        'rechazada',
                                        'cancelada',
                                    ],
                                    true
                                )
                            )

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 text-xs font-medium text-red-700">

                                    <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-red-500"></span>

                                    {{ $solicitud->estado === 'rechazada'
                                        ? 'Rechazada'
                                        : 'Cancelada'
                                    }}

                                </span>

                            @elseif($solicitud->estado === 'en_proceso')

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-500/10 text-xs font-medium text-blue-700">

                                    <span class="relative flex w-1.5 h-1.5 shrink-0">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-blue-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-blue-500"></span>

                                    </span>

                                    En proceso

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-medium text-amber-700">

                                    <span class="relative flex w-1.5 h-1.5 shrink-0">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                    </span>

                                    Pendiente

                                </span>

                            @endif

                        </div>


                        <h2 class="mt-2 text-lg font-semibold text-foreground leading-relaxed break-words">
                            {{ $solicitud->asunto }}
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground leading-relaxed">
                            Consulta la información y el estado actual de tu solicitud.
                        </p>

                    </div>

                </div>


                <a
                    href="{{ route('solicitudes.create') }}"
                    class="group/create inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold whitespace-nowrap shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                    <i
                        data-lucide="plus"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/create:rotate-90">
                    </i>

                    Nueva solicitud

                </a>

            </div>

        </section>



        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_300px] gap-6">


            {{-- Contenido principal --}}

            <div class="space-y-6">


                {{-- Información general --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

                    <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                        <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                            <i
                                data-lucide="info"
                                stroke-width="1.8"
                                class="w-[18px] h-[18px]">
                            </i>

                        </div>

                        <div>

                            <h2 class="text-sm font-semibold text-foreground">
                                Información general
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Datos principales relacionados con la solicitud.
                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2">

                        {{-- Categoría --}}

                        <div class="px-6 py-5 border-b border-border sm:border-r">

                            <p class="text-xs font-medium text-muted-foreground">
                                Categoría
                            </p>

                            <div class="mt-2">

                                <span class="inline-flex items-center gap-1.5 max-w-full px-2.5 py-1 rounded-full border border-blue-200/70 bg-blue-50 text-xs font-medium text-blue-700 transition-all duration-200 hover:border-blue-300 hover:bg-blue-100">

                                    <i
                                        data-lucide="tag"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5 shrink-0">
                                    </i>

                                    <span class="truncate">
                                        {{ $categoriaSolicitud }}
                                    </span>

                                </span>

                            </div>

                        </div>


                        {{-- Fecha de registro --}}

                        <div class="px-6 py-5 border-b border-border">

                            <p class="text-xs font-medium text-muted-foreground">
                                Fecha de registro
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground">

                                {{ $solicitud->created_at
                                    ?->timezone('America/Tegucigalpa')
                                    ->format('d/m/Y')
                                }}

                            </p>

                        </div>


                        {{-- Hora --}}

                        <div class="px-6 py-5 border-b border-border sm:border-b-0 sm:border-r">

                            <p class="text-xs font-medium text-muted-foreground">
                                Hora
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground">

                                {{ $solicitud->created_at
                                    ?->timezone('America/Tegucigalpa')
                                    ->format('h:i A')
                                }}

                            </p>

                        </div>


                        {{-- Notificación --}}

                        <div class="px-6 py-5">

                            <p class="text-xs font-medium text-muted-foreground">
                                Notificación
                            </p>

                            @if($solicitud->correo_enviado)

                                <p class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700">

                                    <i
                                        data-lucide="mail-check"
                                        stroke-width="1.8"
                                        class="w-4 h-4 shrink-0 text-emerald-600">
                                    </i>

                                    Correo enviado

                                </p>

                            @else

                                <p class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-red-700">

                                    <i
                                        data-lucide="mail-x"
                                        stroke-width="1.8"
                                        class="w-4 h-4 shrink-0 text-red-600">
                                    </i>

                                    Correo no enviado

                                </p>

                            @endif

                        </div>

                    </div>

                </section>



                {{-- Descripción --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

                    <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                        <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                            <i
                                data-lucide="align-left"
                                stroke-width="1.8"
                                class="w-[18px] h-[18px]">
                            </i>

                        </div>

                        <div>

                            <h2 class="text-sm font-semibold text-foreground">
                                Descripción de la solicitud
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Información proporcionada al registrar el servicio.
                            </p>

                        </div>

                    </div>


                    <div class="p-6">

                        <div class="rounded-xl border border-border bg-muted/20 px-4 py-3.5 transition-colors duration-200 hover:border-primary/15 hover:bg-primary/[0.02]">

                            <p class="text-sm text-foreground leading-relaxed whitespace-pre-line break-words">{{ $solicitud->descripcion }}</p>

                        </div>

                    </div>

                </section>



                {{-- Información adicional --}}

                @if(
                    is_array($solicitud->datos_extra)
                    && count($solicitud->datos_extra) > 0
                )

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
                                    Información adicional
                                </h2>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Datos específicos del servicio solicitado.
                                </p>

                            </div>

                        </div>


                        <dl class="grid grid-cols-1 sm:grid-cols-1">

                            @foreach($solicitud->datos_extra as $campo => $valor)

                                @continue(
                                    $valor === null
                                    || $valor === ''
                                    || $valor === []
                                )

                                @php

                                    $nombreCampo = str($campo)
                                        ->replace('_', ' ')
                                        ->title();

                                    if (is_array($valor)) {
                                        $valorMostrado = collect($valor)
                                            ->map(
                                                function ($item) {
                                                    if (is_array($item)) {
                                                        return collect($item)
                                                            ->filter(
                                                                fn ($dato) =>
                                                                    $dato !== null
                                                                    && $dato !== ''
                                                            )
                                                            ->implode(', ');
                                                    }

                                                    return $item;
                                                }
                                            )
                                            ->filter(
                                                fn ($item) =>
                                                    $item !== null
                                                    && $item !== ''
                                            )
                                            ->implode(' · ');
                                    } elseif (is_bool($valor)) {
                                        $valorMostrado = $valor
                                            ? 'Sí'
                                            : 'No';
                                    } else {
                                        $valorMostrado = (string) $valor;
                                    }

                                @endphp


                                <div class="px-6 py-5 border-b border-border transition-colors duration-200 hover:bg-primary/[0.025] odd:sm:border-r sm:[&:nth-last-child(-n+2)]:border-b-0">

                                    <dt class="text-xs font-medium text-muted-foreground">
                                        {{ $nombreCampo }}
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-foreground break-words">
                                        {{ $valorMostrado !== ''
                                            ? $valorMostrado
                                            : 'No especificado'
                                        }}
                                    </dd>

                                </div>

                            @endforeach

                        </dl>

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

                                {{ $solicitud->usuario?->nombre
                                    ? mb_strtoupper(
                                        mb_substr(
                                            $solicitud->usuario->nombre,
                                            0,
                                            1
                                        )
                                    )
                                    : 'U'
                                }}

                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-foreground truncate">
                                    {{ $solicitud->usuario?->nombre ?? 'Usuario no disponible' }}
                                </p>

                                <p class="mt-0.5 text-xs text-muted-foreground truncate">
                                    {{ $solicitud->usuario?->correo ?? 'Correo no disponible' }}
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
                                    Fecha
                                </p>

                                <p class="mt-1 text-sm font-medium text-foreground">

                                    {{ $solicitud->created_at
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

                                    {{ $solicitud->created_at
                                        ?->timezone('America/Tegucigalpa')
                                        ->format('h:i A')
                                    }}

                                </p>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- Seguimiento --}}

                @if(
                    in_array(
                        $solicitud->estado,
                        [
                            'finalizada',
                            'completada',
                        ],
                        true
                    )
                )

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

                            <div class="mt-4 flex items-center gap-2">

                                <h2 class="text-sm font-semibold text-emerald-900">
                                    Solicitud finalizada
                                </h2>

                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">

                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                    Completada

                                </span>

                            </div>

                            <p class="mt-2 text-xs text-emerald-800/80 leading-relaxed">
                                La solicitud fue atendida correctamente y su seguimiento se encuentra completado.
                            </p>

                        </div>

                    </section>

                @elseif(
                    in_array(
                        $solicitud->estado,
                        [
                            'rechazada',
                            'cancelada',
                        ],
                        true
                    )
                )

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

                            <div class="mt-4 flex items-center gap-2">

                                <h2 class="text-sm font-semibold text-red-900">

                                    {{ $solicitud->estado === 'rechazada'
                                        ? 'Solicitud rechazada'
                                        : 'Solicitud cancelada'
                                    }}

                                </h2>

                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-red-500/10 text-[10px] font-semibold uppercase tracking-wide text-red-700">

                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>

                                    Cerrada

                                </span>

                            </div>

                            <p class="mt-2 text-xs text-red-800/80 leading-relaxed">
                                Esta solicitud fue cerrada y ya no tiene acciones de seguimiento pendientes.
                            </p>

                        </div>

                    </section>

                @elseif($solicitud->estado === 'en_proceso')

                    <section class="group relative overflow-hidden rounded-2xl border border-blue-200/70 bg-gradient-to-br from-blue-50 via-white to-cyan-50/70 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10">

                        <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-blue-400/10 transition-transform duration-500 group-hover:scale-150"></div>

                        <div class="relative">

                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-blue-500/10 text-blue-700 ring-4 ring-blue-500/5 transition-transform duration-300 group-hover:scale-105">

                                <i
                                    data-lucide="loader-circle"
                                    stroke-width="1.8"
                                    class="w-5 h-5 animate-spin">
                                </i>

                            </div>

                            <div class="mt-4 flex items-center gap-2">

                                <h2 class="text-sm font-semibold text-blue-900">
                                    Solicitud en proceso
                                </h2>

                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-blue-500/10 text-[10px] font-semibold uppercase tracking-wide text-blue-700">

                                    <span class="relative flex w-1.5 h-1.5">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-blue-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-blue-500"></span>

                                    </span>

                                    Atendiendo

                                </span>

                            </div>

                            <p class="mt-2 text-xs text-blue-800/80 leading-relaxed">
                                El equipo TI se encuentra trabajando actualmente en esta solicitud.
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

                            <div class="mt-4 flex items-center gap-2">

                                <h2 class="text-sm font-semibold text-amber-900">
                                    Solicitud pendiente
                                </h2>

                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-500/10 text-[10px] font-semibold uppercase tracking-wide text-amber-700">

                                    <span class="relative flex w-1.5 h-1.5">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                    </span>

                                    Por revisar

                                </span>

                            </div>

                            <p class="mt-2 text-xs text-amber-800/80 leading-relaxed">
                                La solicitud fue recibida y se encuentra pendiente de revisión por el equipo TI.
                            </p>

                        </div>

                    </section>

                @endif



                {{-- Nueva solicitud --}}

                <a
                    href="{{ route('solicitudes.create') }}"
                    class="group/new flex items-center justify-between gap-3 rounded-2xl border border-border bg-card p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:bg-primary/[0.025] hover:shadow-md">

                    <div>

                        <p class="text-sm font-semibold text-foreground transition-colors duration-200 group-hover/new:text-primary">
                            Crear otra solicitud
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground leading-relaxed">
                            Registra una nueva gestión para el equipo TI.
                        </p>

                    </div>

                    <i
                        data-lucide="arrow-right"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0 text-foreground transition-all duration-200 group-hover/new:translate-x-0.5 group-hover/new:text-primary">
                    </i>

                </a>

            </aside>

        </div>

    </main>

</div>

@endsection