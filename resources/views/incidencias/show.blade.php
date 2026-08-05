@extends('layouts.app')

@section('title', 'Detalle de incidencia')

@section('content')

<div
    class="min-h-screen bg-background"
    x-data="{ imagenPreview: null }">

    <main class="max-w-5xl mx-auto px-6 py-10">


        {{-- Navegación --}}

        <a
            href="{{ route('mis-incidencias') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/20 bg-primary/5 text-sm font-medium text-primary transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/10 hover:shadow-sm dark:border-slate-700 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:border-blue-500/40 dark:hover:bg-blue-500/15 active:translate-y-0">

            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5">
            </i>

            <span>
                Volver a mis incidencias
            </span>

        </a>



        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                <div class="flex items-start gap-4 min-w-0">

                    <div class="group flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:bg-primary/15 hover:shadow-md dark:border-blue-800/70 dark:bg-blue-950/40 dark:text-blue-400 dark:hover:border-blue-700 dark:hover:bg-blue-900/50">

                        <i
                            data-lucide="ticket-check"
                            stroke-width="1.8"
                            class="w-6 h-6 transition-transform duration-300 group-hover:scale-110">
                        </i>

                    </div>

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <h1 class="text-2xl font-semibold text-foreground tracking-tight">
                                {{ $incidencia->codigo }}
                            </h1>


                            {{-- Estado --}}

                            @if($incidencia->estaResuelta())

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700 dark:bg-emerald-950/45 dark:text-emerald-300">

                                    <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-emerald-500"></span>

                                    Resuelta

                                </span>

                            @elseif($incidencia->estaEnProceso())

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-500/10 text-xs font-medium text-cyan-700 dark:bg-cyan-950/45 dark:text-cyan-300">

                                    <span class="relative flex w-1.5 h-1.5 shrink-0">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-cyan-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-cyan-500"></span>

                                    </span>

                                    En proceso

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-medium text-amber-700 dark:bg-amber-950/45 dark:text-amber-300">

                                    <span class="relative flex w-1.5 h-1.5 shrink-0">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                    </span>

                                    Abierta

                                </span>

                            @endif

                        </div>

                        <h2 class="mt-2 text-lg font-semibold text-foreground leading-relaxed break-words">
                            {{ $incidencia->titulo }}
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground leading-relaxed">
                            Consulta la información y el estado actual de tu reporte.
                        </p>

                    </div>

                </div>


                <a
                    href="{{ route('incidencias.create') }}"
                    class="group/create inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold whitespace-nowrap shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                    <i
                        data-lucide="plus"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/create:rotate-90">
                    </i>

                    Nueva incidencia

                </a>

            </div>

        </section>



        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_300px] gap-6">


            {{-- Contenido principal --}}

            <div class="space-y-6">


                {{-- Información general --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

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
                                Datos principales relacionados con el reporte.
                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2">

                        {{-- Equipo --}}

                        <div class="px-6 py-5 border-b border-border sm:border-r">

                            <p class="text-xs font-medium text-muted-foreground">
                                Equipo afectado
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground break-words">
                                {{ $incidencia->equipo ?: 'No especificado' }}
                            </p>

                        </div>


                        {{-- Ubicación --}}

                        <div class="px-6 py-5 border-b border-border">

                            <p class="text-xs font-medium text-muted-foreground">
                                Ubicación
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground break-words">
                                {{ $incidencia->ubicacion ?: 'No especificada' }}
                            </p>

                        </div>


                        {{-- Inicio del problema --}}

                        <div class="px-6 py-5 border-b border-border sm:border-b-0 sm:border-r">

                            <p class="text-xs font-medium text-muted-foreground">
                                Inicio del problema
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground">

                                {{ [
                                    'hoy' => 'Hoy',
                                    'ayer' => 'Ayer',
                                    'varios_dias' => 'Hace varios días',
                                ][$incidencia->tiempo_problema]
                                    ?? 'No indicado'
                                }}

                            </p>

                        </div>


                        {{-- Afectación --}}

                        <div class="px-6 py-5">

                            <p class="text-xs font-medium text-muted-foreground">
                                Personas afectadas
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground">

                                {{ [
                                    'solo' => 'Solo a mí',
                                    'varios' => 'A varias personas',
                                    'todos' => 'A toda el área',
                                ][$incidencia->afectacion]
                                    ?? 'No indicada'
                                }}

                            </p>

                        </div>

                    </div>

                </section>



                {{-- Descripción --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

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
                                Descripción del problema
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Información proporcionada al crear la incidencia.
                            </p>

                        </div>

                    </div>


                    <div class="p-6">

                        <div class="rounded-xl border border-border bg-muted/20 px-4 py-3.5 transition-colors duration-200 hover:border-primary/15 hover:bg-primary/[0.02] dark:border-slate-700/70 dark:bg-slate-950/25 dark:hover:border-blue-800/70 dark:hover:bg-blue-950/15">

                            <p class="text-sm text-foreground leading-relaxed whitespace-pre-line break-words">{{ $incidencia->descripcion }}</p>

                        </div>

                    </div>

                </section>



                {{-- Evidencias --}}

                @if($incidencia->archivos->isNotEmpty())

                    <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

                        <div class="flex items-center justify-between gap-4 px-6 py-5 border-b border-border">

                            <div class="flex items-center gap-3">

                                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                                    <i
                                        data-lucide="images"
                                        stroke-width="1.8"
                                        class="w-[18px] h-[18px]">
                                    </i>

                                </div>

                                <div>

                                    <h2 class="text-sm font-semibold text-foreground">
                                        Evidencias adjuntas
                                    </h2>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Selecciona una imagen para visualizarla completa.
                                    </p>

                                </div>

                            </div>


                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-primary/20 bg-primary/5 text-xs font-medium text-primary shrink-0 dark:border-blue-800/70 dark:bg-blue-950/35 dark:text-blue-400">

                                <i
                                    data-lucide="paperclip"
                                    stroke-width="1.8"
                                    class="w-3.5 h-3.5">
                                </i>

                                {{ $incidencia->archivos->count() }}

                            </span>

                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">

                            @foreach($incidencia->archivos as $archivo)

                                <article class="group/file overflow-hidden rounded-xl border border-border bg-background transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/15 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-950/35 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

                                    <button
                                        type="button"
                                        class="relative block w-full overflow-hidden bg-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-inset"
                                        @click="imagenPreview = @js(asset('storage/'.$archivo->ruta))">

                                        <img
                                            src="{{ asset('storage/'.$archivo->ruta) }}"
                                            alt="{{ $archivo->nombre_original }}"
                                            class="w-full h-44 object-cover transition-transform duration-500 group-hover/file:scale-105">

                                        <span class="absolute inset-0 flex items-center justify-center bg-slate-950/0 text-white opacity-0 transition-all duration-300 group-hover/file:bg-slate-950/25 group-hover/file:opacity-100">

                                            <span class="flex items-center justify-center w-10 h-10 rounded-full bg-white/90 text-primary shadow-sm backdrop-blur-sm dark:bg-slate-900/90 dark:text-blue-400">

                                                <i
                                                    data-lucide="maximize-2"
                                                    stroke-width="1.8"
                                                    class="w-4 h-4">
                                                </i>

                                            </span>

                                        </span>

                                    </button>


                                    <div class="p-4">

                                        <div class="flex items-center gap-2">

                                            <i
                                                data-lucide="image"
                                                stroke-width="1.8"
                                                class="w-4 h-4 shrink-0 text-primary">
                                            </i>

                                            <p
                                                title="{{ $archivo->nombre_original }}"
                                                class="text-xs font-medium text-foreground truncate">

                                                {{ $archivo->nombre_original }}

                                            </p>

                                        </div>


                                        @if($archivo->texto_ocr)

                                            <details class="group/details mt-3">

                                                <summary class="flex items-center gap-1.5 cursor-pointer list-none text-xs font-medium text-primary">

                                                    <i
                                                        data-lucide="scan-text"
                                                        stroke-width="1.8"
                                                        class="w-3.5 h-3.5">
                                                    </i>

                                                    Texto identificado

                                                    <i
                                                        data-lucide="chevron-down"
                                                        stroke-width="1.8"
                                                        class="w-3.5 h-3.5 ml-auto transition-transform duration-200 group-open/details:rotate-180">
                                                    </i>

                                                </summary>

                                                <div class="mt-3 max-h-48 overflow-y-auto rounded-lg bg-muted/60 p-3 text-xs text-muted-foreground leading-relaxed whitespace-pre-line break-words">{{ $archivo->texto_ocr }}</div>

                                            </details>

                                        @endif

                                    </div>

                                </article>

                            @endforeach

                        </div>

                    </section>

                @endif

                {{-- Notificación por correo --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

                    <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                        <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                            <i
                                data-lucide="mail"
                                stroke-width="1.8"
                                class="w-[18px] h-[18px]">
                            </i>

                        </div>

                        <div>

                            <h2 class="text-sm font-semibold text-foreground">
                                Notificación por correo
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Resultado del aviso enviado al equipo de TI.
                            </p>

                        </div>

                    </div>


                    <div class="p-6">

                        @if($incidencia->correo_enviado)

                            <div class="group/mail flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 transition-all duration-300 hover:border-emerald-300 hover:bg-emerald-50 hover:shadow-sm dark:border-emerald-800/70 dark:bg-emerald-950/30 dark:hover:border-emerald-700 dark:hover:bg-emerald-950/45">

                                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 transition-transform duration-300 group-hover/mail:scale-105 dark:bg-emerald-900/60 dark:text-emerald-300">

                                    <i
                                        data-lucide="mail-check"
                                        stroke-width="1.8"
                                        class="w-[18px] h-[18px]">
                                    </i>

                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                        Notificación enviada
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400">

                                        El equipo de TI recibió una notificación sobre esta incidencia.

                                        @if($incidencia->fecha_envio_correo)

                                            El envío se realizó el

                                            {{ $incidencia->fecha_envio_correo
                                                ->timezone('America/Tegucigalpa')
                                                ->format('d/m/Y h:i A') }}.

                                        @endif

                                    </p>

                                </div>

                            </div>

                        @else

                            <div class="group/mail flex items-start gap-3 rounded-xl border border-red-200 bg-red-50/70 p-4 transition-all duration-300 hover:border-red-300 hover:bg-red-50 hover:shadow-sm dark:border-red-800/70 dark:bg-red-950/30 dark:hover:border-red-700 dark:hover:bg-red-950/45">

                                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-red-100 text-red-600 transition-transform duration-300 group-hover/mail:scale-105 dark:bg-red-900/60 dark:text-red-300">

                                    <i
                                        data-lucide="mail-x"
                                        stroke-width="1.8"
                                        class="w-[18px] h-[18px]">
                                    </i>

                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                                        Notificación no enviada
                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-red-700 dark:text-red-400">
                                        La incidencia quedó registrada, pero no fue posible completar la notificación por correo.
                                    </p>

                                </div>

                            </div>

                        @endif

                    </div>

                </section>

            </div>



            {{-- Panel lateral --}}

            <aside class="space-y-5">


                {{-- Reportado por --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/15 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

                    <div class="px-5 py-4 border-b border-border">

                        <h2 class="text-sm font-semibold text-foreground">
                            Reportado por
                        </h2>

                    </div>


                    <div class="p-5">

                        <div class="flex items-center gap-3">

                            <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-full bg-primary/10 text-sm font-semibold text-primary transition-all duration-300 group-hover:scale-105 group-hover:bg-primary/15 dark:bg-blue-950/45 dark:text-blue-400 dark:group-hover:bg-blue-900/55">

                                {{ $incidencia->usuario?->nombre
                                    ? mb_strtoupper(
                                        mb_substr(
                                            $incidencia->usuario->nombre,
                                            0,
                                            1
                                        )
                                    )
                                    : 'U'
                                }}

                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-foreground truncate">
                                    {{ $incidencia->usuario?->nombre ?? 'Usuario no disponible' }}
                                </p>

                                <p class="mt-0.5 text-xs text-muted-foreground truncate">
                                    {{ $incidencia->usuario?->correo ?? 'Correo no disponible' }}
                                </p>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- Registro --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/15 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

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
                                    Fecha del reporte
                                </p>

                                <p class="mt-1 text-sm font-medium text-foreground">

                                    {{ $incidencia->created_at
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

                                    {{ $incidencia->created_at
                                        ?->timezone('America/Tegucigalpa')
                                        ->format('h:i A')
                                    }}

                                </p>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- Seguimiento --}}

                @if($incidencia->estaResuelta())

                    <section class="group relative overflow-hidden rounded-2xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50 via-white to-teal-50/70 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10 dark:border-emerald-900/70 dark:from-emerald-950/45 dark:via-slate-900 dark:to-teal-950/30 dark:hover:border-emerald-700 dark:hover:shadow-black/20">

                        <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-emerald-400/10 transition-transform duration-500 group-hover:scale-150"></div>

                        <div class="relative">

                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-emerald-500/15 text-emerald-700 ring-4 ring-emerald-500/5 transition-transform duration-300 group-hover:scale-105 dark:bg-emerald-900/50 dark:text-emerald-300">

                                <i
                                    data-lucide="circle-check-big"
                                    stroke-width="1.8"
                                    class="w-5 h-5">
                                </i>

                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">

                                <h2 class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">
                                    Incidencia resuelta
                                </h2>

                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">

                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                    Resuelta

                                </span>

                            </div>

                            <p class="mt-2 text-xs leading-relaxed text-emerald-800/80 dark:text-emerald-300/80">
                                El reporte fue atendido correctamente y su seguimiento se encuentra completado.
                            </p>

                        </div>

                    </section>


                @elseif($incidencia->estaEnProceso())

                    <section class="group relative overflow-hidden rounded-2xl border border-cyan-200/70 bg-gradient-to-br from-cyan-50 via-white to-blue-50/70 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-lg hover:shadow-cyan-500/10 dark:border-cyan-900/70 dark:from-cyan-950/45 dark:via-slate-900 dark:to-blue-950/30 dark:hover:border-cyan-700 dark:hover:shadow-black/20">

                        <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-cyan-400/10 transition-transform duration-500 group-hover:scale-150"></div>

                        <div class="relative">

                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-cyan-500/10 text-cyan-700 ring-4 ring-cyan-500/5 transition-transform duration-300 group-hover:scale-105 dark:bg-cyan-900/50 dark:text-cyan-300">

                                <i
                                    data-lucide="loader-circle"
                                    stroke-width="1.8"
                                    class="w-5 h-5 animate-spin">
                                </i>

                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">

                                <h2 class="text-sm font-semibold text-cyan-900 dark:text-cyan-200">
                                    Incidencia en proceso
                                </h2>

                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-cyan-500/10 text-[10px] font-semibold uppercase tracking-wide text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300">

                                    <span class="relative flex w-1.5 h-1.5">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-cyan-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-cyan-500"></span>

                                    </span>

                                    Atendiendo

                                </span>

                            </div>

                            <p class="mt-2 text-xs leading-relaxed text-cyan-800/80 dark:text-cyan-300/80">
                                El equipo TI se encuentra trabajando actualmente en esta incidencia.
                            </p>

                        </div>

                    </section>


                @else

                    <section class="group relative overflow-hidden rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-orange-50/70 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10 dark:border-amber-900/70 dark:from-amber-950/45 dark:via-slate-900 dark:to-orange-950/30 dark:hover:border-amber-700 dark:hover:shadow-black/20">

                        <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-amber-400/10 transition-transform duration-500 group-hover:scale-150"></div>

                        <div class="relative">

                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-amber-500/10 text-amber-700 ring-4 ring-amber-500/5 transition-transform duration-300 group-hover:scale-105 dark:bg-amber-900/50 dark:text-amber-300">

                                <i
                                    data-lucide="clock-3"
                                    stroke-width="1.8"
                                    class="w-5 h-5">
                                </i>

                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">

                                <h2 class="text-sm font-semibold text-amber-900 dark:text-amber-200">
                                    Incidencia abierta
                                </h2>

                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-500/10 text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">

                                    <span class="relative flex w-1.5 h-1.5">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                    </span>

                                    Por revisar

                                </span>

                            </div>

                            <p class="mt-2 text-xs leading-relaxed text-amber-800/80 dark:text-amber-300/80">
                                El reporte fue recibido y se encuentra pendiente de revisión por el equipo TI.
                            </p>

                        </div>

                    </section>

                @endif

            </aside>

        </div>

    </main>



    {{-- Modal de imagen --}}

    <div
        x-show="imagenPreview"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="imagenPreview = null"
        @click.self="imagenPreview = null"
        role="dialog"
        aria-modal="true"
        aria-label="Vista ampliada de la evidencia"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 p-6 backdrop-blur-sm dark:bg-black/90"
        style="display: none;">

        <button
            type="button"
            @click="imagenPreview = null"
            aria-label="Cerrar imagen"
            class="absolute top-5 right-5 inline-flex items-center justify-center w-10 h-10 rounded-full border border-white/80 bg-white/90 text-primary shadow-lg transition-all duration-200 hover:rotate-90 hover:bg-white dark:border-slate-600 dark:bg-slate-900/95 dark:text-blue-400 dark:hover:border-blue-500 dark:hover:bg-slate-800">

            <i
                data-lucide="x"
                stroke-width="1.8"
                class="w-5 h-5">
            </i>

        </button>


        <img
            :src="imagenPreview"
            alt="Vista ampliada de la evidencia"
            class="max-h-[88vh] max-w-[92vw] rounded-2xl object-contain shadow-2xl">

    </div>

</div>

@endsection