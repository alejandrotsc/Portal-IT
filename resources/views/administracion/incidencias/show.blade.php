@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-10">


        {{-- Navegación --}}

        <a
            href="{{ route('admin.incidencias') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/20 bg-primary/5 text-sm font-medium text-primary transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/10 hover:shadow-sm active:translate-y-0">

            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5">
            </i>

            <span>
                Volver a incidencias
            </span>

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

                                {{ $incidencia->codigo }}

                            </h1>


                            {{-- Estado --}}

                            @if($incidencia->estaResuelta())

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700">

                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                    Resuelta

                                </span>

                            @elseif($incidencia->estaEnProceso())

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-500/10 text-xs font-medium text-cyan-700">

                                    <span class="relative flex w-1.5 h-1.5 shrink-0">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-cyan-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-cyan-500"></span>

                                    </span>

                                    En proceso

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-medium text-amber-700">

                                    <span class="relative flex w-1.5 h-1.5 shrink-0">

                                        <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                        <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                    </span>

                                    Abierta

                                </span>

                            @endif

                        </div>

                        <p class="mt-2 text-sm text-muted-foreground leading-relaxed">

                            Consulta el reporte, revisa las evidencias y actualiza su seguimiento.

                        </p>

                    </div>

                </div>


                {{-- Fecha --}}

                <div class="inline-flex items-center gap-2 text-xs text-muted-foreground sm:pt-2">

                    <i
                        data-lucide="calendar-days"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0 text-primary">
                    </i>

                    Registrada el

                    <span class="font-medium text-foreground">

                        {{ $incidencia->created_at
                            ?->timezone('America/Tegucigalpa')
                            ->format('d/m/Y h:i A') }}

                    </span>

                </div>

            </div>

        </section>



        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_300px] gap-6">


            {{-- Contenido principal --}}

            <div class="space-y-6">


                {{-- Información de la incidencia --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

                    <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                        <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-300 group-hover:scale-105">

                            <i
                                data-lucide="clipboard-list"
                                stroke-width="1.8"
                                class="w-[18px] h-[18px]">
                            </i>

                        </div>

                        <div>

                            <h2 class="text-sm font-semibold text-foreground">

                                Información de la incidencia

                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">

                                Datos principales enviados por el usuario.

                            </p>

                        </div>

                    </div>


                    <div class="p-6 space-y-6">


                        {{-- Título --}}

                        <div>

                            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">

                                Título del reporte

                            </p>

                            <p class="mt-2 text-sm font-semibold text-foreground leading-relaxed">

                                {{ $incidencia->titulo }}

                            </p>

                        </div>



                        {{-- Contexto --}}

                        @php

                            $tiemposProblema = [
                                'hoy' =>
                                    'Hoy',

                                'ayer' =>
                                    'Ayer',

                                'varios_dias' =>
                                    'Hace varios días',
                            ];

                            $afectaciones = [
                                'solo' =>
                                    'Solo a mí',

                                'varios' =>
                                    'A varias personas',

                                'todos' =>
                                    'A toda el área',
                            ];

                            $tiempoMostrado =
                                $tiemposProblema[
                                    $incidencia->tiempo_problema
                                ]
                                ?? (
                                    filled($incidencia->tiempo_problema)
                                        ? str($incidencia->tiempo_problema)
                                            ->replace('_', ' ')
                                            ->title()
                                        : 'No especificado'
                                );

                            $afectacionMostrada =
                                $afectaciones[
                                    $incidencia->afectacion
                                ]
                                ?? (
                                    filled($incidencia->afectacion)
                                        ? str($incidencia->afectacion)
                                            ->replace('_', ' ')
                                            ->title()
                                        : 'No especificado'
                                );

                        @endphp


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            @foreach([
                                '¿Cuándo empezó?' => $tiempoMostrado,
                                '¿A quién afecta?' => $afectacionMostrada,
                                'Equipo' => $incidencia->equipo,
                                'Ubicación' => $incidencia->ubicacion,
                            ] as $etiqueta => $valor)

                                <div class="rounded-xl border border-border bg-muted/20 px-4 py-3.5 transition-colors duration-200 hover:border-primary/15 hover:bg-primary/[0.02]">

                                    <p class="text-xs font-medium text-muted-foreground">

                                        {{ $etiqueta }}

                                    </p>

                                    <p class="mt-1.5 text-sm font-medium text-foreground break-words">

                                        {{ filled($valor)
                                            ? $valor
                                            : 'No especificado'
                                        }}

                                    </p>

                                </div>

                            @endforeach

                        </div>



                        {{-- Descripción --}}

                        <div>

                            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">

                                Descripción

                            </p>

                            <div class="mt-2 rounded-xl border border-border bg-muted/20 px-4 py-3.5 transition-colors duration-200 hover:border-primary/15 hover:bg-primary/[0.02]">

                                <p class="text-sm text-foreground leading-relaxed whitespace-pre-line break-words">{{ $incidencia->descripcion }}</p>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- Evidencias --}}

                @if($incidencia->archivos->isNotEmpty())

                    <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-blue-200 hover:shadow-md">

                        <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-blue-500/10 text-blue-600 transition-transform duration-300 group-hover:scale-105">

                                <i
                                    data-lucide="paperclip"
                                    stroke-width="1.8"
                                    class="w-[18px] h-[18px]">
                                </i>

                            </div>

                            <div>

                                <h2 class="text-sm font-semibold text-foreground">

                                    Evidencias adjuntas

                                </h2>

                                <p class="mt-1 text-xs text-muted-foreground">

                                    Archivos enviados por el usuario junto al reporte.

                                </p>

                            </div>

                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">

                            @foreach($incidencia->archivos as $archivo)

                                <a
                                    href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($archivo->ruta) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group/file flex items-center gap-3 rounded-xl border border-border bg-muted/20 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/20 hover:bg-primary/[0.03] hover:shadow-sm">

                                    <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-lg bg-primary/10 text-primary transition-transform duration-200 group-hover/file:scale-105">

                                        <i
                                            data-lucide="image"
                                            stroke-width="1.8"
                                            class="w-[18px] h-[18px]">
                                        </i>

                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p
                                            title="{{ $archivo->nombre_original }}"
                                            class="text-sm font-semibold text-foreground truncate">

                                            {{ $archivo->nombre_original }}

                                        </p>

                                        <p class="mt-1 text-xs text-muted-foreground">

                                            {{ mb_strtoupper($archivo->extension) }}

                                            ·

                                            {{ number_format(
                                                $archivo->tamano / 1024,
                                                1
                                            ) }} KB

                                        </p>

                                    </div>

                                    <i
                                        data-lucide="external-link"
                                        stroke-width="1.8"
                                        class="w-4 h-4 shrink-0 text-muted-foreground transition-colors duration-200 group-hover/file:text-primary">
                                    </i>

                                </a>

                            @endforeach

                        </div>

                    </section>

                @endif



                {{-- Notificación por correo --}}

                <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

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

                            <div class="group/mail flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 transition-all duration-300 hover:border-emerald-300 hover:bg-emerald-50 hover:shadow-sm">

                                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 transition-transform duration-300 group-hover/mail:scale-105">

                                    <i
                                        data-lucide="mail-check"
                                        stroke-width="1.8"
                                        class="w-[18px] h-[18px]">
                                    </i>

                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-emerald-800">

                                        Notificación enviada

                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-emerald-700">

                                        El equipo responsable recibió una notificación sobre esta incidencia.

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

                            <div class="group/mail flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/70 p-4 transition-all duration-300 hover:border-amber-300 hover:bg-amber-50 hover:shadow-sm">

                                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-amber-100 text-amber-600 transition-transform duration-300 group-hover/mail:scale-105">

                                    <i
                                        data-lucide="mail-warning"
                                        stroke-width="1.8"
                                        class="w-[18px] h-[18px]">
                                    </i>

                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-amber-800">

                                        Notificación no enviada

                                    </p>

                                    <p class="mt-1 text-xs leading-relaxed text-amber-700">

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

                                {{ $incidencia->usuario?->nombre
                                    ? mb_strtoupper(
                                        mb_substr(
                                            $incidencia->usuario->nombre,
                                            0,
                                            1
                                        )
                                    )
                                    : '?'
                                }}

                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-foreground truncate">

                                    {{ $incidencia->usuario?->nombre
                                        ?? 'Usuario no disponible'
                                    }}

                                </p>

                                <p
                                    title="{{ $incidencia->usuario?->correo }}"
                                    class="mt-0.5 text-xs text-muted-foreground truncate">

                                    {{ $incidencia->usuario?->correo
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

                            @if($incidencia->estaResuelta())

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">

                                    <i
                                        data-lucide="circle-check"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    Resuelta

                                </span>

                            @elseif($incidencia->estaEnProceso())

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-cyan-700">

                                    <i
                                        data-lucide="loader-circle"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    En proceso

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700">

                                    <i
                                        data-lucide="clock-3"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    Abierta

                                </span>

                            @endif

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Código
                            </span>

                            <span class="text-xs font-semibold text-foreground">
                                {{ $incidencia->codigo }}
                            </span>

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Evidencias
                            </span>

                            <span class="max-w-[150px] text-right text-xs font-medium text-foreground">

                                {{ $incidencia->archivos->count() }}

                            </span>

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Registro
                            </span>

                            <span class="text-right text-xs font-medium text-foreground">

                                {{ $incidencia->created_at
                                    ?->timezone('America/Tegucigalpa')
                                    ->format('d/m/Y') }}

                            </span>

                        </div>

                    </div>

                </section>



                {{-- Prioridad --}}

                <section class="group relative overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:shadow-md">

                    <div class="absolute -right-10 -top-10 w-24 h-24 rounded-full bg-primary/5 transition-transform duration-500 group-hover:scale-150"></div>

                    <div class="relative">

                        <h2 class="text-sm font-semibold text-foreground">

                            Prioridad

                        </h2>

                        <p class="mt-1 text-xs text-muted-foreground leading-relaxed">

                            Clasificación interna para organizar la atención.

                        </p>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.incidencias.prioridad',
                                $incidencia
                            ) }}"
                            class="mt-4 space-y-3">

                            @csrf
                            @method('PATCH')

                            <div class="flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-white transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10">

                                <i
                                    data-lucide="flag"
                                    stroke-width="1.8"
                                    class="w-4 h-4 shrink-0 text-primary">
                                </i>

                                <select
                                    name="prioridad"
                                    required
                                    class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0">

                                    @foreach(\App\Models\Incidencia::PRIORIDADES as $prioridad)

                                        <option
                                            value="{{ $prioridad }}"
                                            @selected($incidencia->prioridad === $prioridad)>

                                            {{ $prioridad }}

                                        </option>

                                    @endforeach

                                </select>

                                <i
                                    data-lucide="chevron-down"
                                    stroke-width="1.8"
                                    class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none">
                                </i>

                            </div>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg border border-primary/20 bg-primary/5 text-sm font-semibold text-primary transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/10 hover:shadow-sm active:translate-y-0">

                                <i
                                    data-lucide="save"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                                Guardar prioridad

                            </button>

                        </form>

                    </div>

                </section>



                {{-- Acciones de estado --}}

                @if(! $incidencia->estaResuelta())

                    <section class="group relative overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:shadow-md">

                        <div class="absolute -right-10 -top-10 w-24 h-24 rounded-full bg-primary/5 transition-transform duration-500 group-hover:scale-150"></div>

                        <div class="relative">

                            <h2 class="text-sm font-semibold text-foreground">

                                Actualizar estado

                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground leading-relaxed">

                                Actualiza el avance conforme se atienda el reporte.

                            </p>


                            <div class="mt-5 space-y-3">


                                {{-- Iniciar --}}

                                @if($incidencia->estaAbierta())

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.incidencias.iniciar',
                                            $incidencia
                                        ) }}"
                                        onsubmit="return confirm('¿Confirmas que deseas iniciar la atención de esta incidencia?')">

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="group/iniciar inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg bg-primary text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                                            <i
                                                data-lucide="play"
                                                stroke-width="1.8"
                                                class="w-4 h-4 transition-transform duration-200 group-hover/iniciar:scale-110">
                                            </i>

                                            Iniciar atención

                                        </button>

                                    </form>

                                @endif



                                {{-- Resolver --}}

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.incidencias.resolver',
                                        $incidencia
                                    ) }}"
                                    onsubmit="return confirm('¿Confirmas que esta incidencia fue atendida y puede marcarse como resuelta?')">

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="group/resolver inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg border border-emerald-200 bg-emerald-50 text-sm font-semibold text-emerald-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-100/70 hover:shadow-sm active:translate-y-0">

                                        <i
                                            data-lucide="circle-check-big"
                                            stroke-width="1.8"
                                            class="w-4 h-4 transition-transform duration-200 group-hover/resolver:scale-110">
                                        </i>

                                        Marcar como resuelta

                                    </button>

                                </form>



                            </div>

                        </div>

                    </section>

                @else

                    <section class="group rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 transition-all duration-300 hover:border-emerald-300 hover:bg-emerald-50 hover:shadow-sm">

                        <div class="flex items-start gap-3">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 transition-transform duration-300 group-hover:scale-105">

                                <i
                                    data-lucide="circle-check-big"
                                    stroke-width="1.8"
                                    class="w-[18px] h-[18px]">
                                </i>

                            </div>

                            <div>

                                <h2 class="text-sm font-semibold text-foreground">

                                    Incidencia resuelta

                                </h2>

                                <p class="mt-1 text-xs text-muted-foreground leading-relaxed">

                                    La atención fue completada. Puedes reabrirla si el problema requiere seguimiento adicional.

                                </p>

                            </div>

                        </div>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.incidencias.reabrir',
                                $incidencia
                            ) }}"
                            class="mt-4"
                            onsubmit="return confirm('¿Confirmas que deseas reabrir esta incidencia?')">

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="group/reabrir inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg border border-emerald-200 bg-white text-sm font-semibold text-emerald-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-100/50 hover:shadow-sm active:translate-y-0">

                                <i
                                    data-lucide="rotate-ccw"
                                    stroke-width="1.8"
                                    class="w-4 h-4 transition-transform duration-300 group-hover/reabrir:-rotate-45">
                                </i>

                                Reabrir incidencia

                            </button>

                        </form>

                    </section>

                @endif

            </aside>

        </div>

    </main>

</div>

@endsection