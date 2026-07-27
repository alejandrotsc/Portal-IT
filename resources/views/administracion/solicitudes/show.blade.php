@extends('layouts.app')

@section('title', 'Administración de solicitudes')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-10">


        {{-- Navegación --}}

        <a
            href="{{ route('admin.solicitudes') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/20 bg-primary/5 text-sm font-medium text-primary transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/10 hover:shadow-sm active:translate-y-0">

            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5">
            </i>

            <span>
                Volver a solicitudes
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

                                {{ $solicitud->folio }}

                            </h1>


                            {{-- Estado --}}

                            @if($solicitud->estado === 'finalizada')

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-medium text-emerald-700">

                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                    Finalizada

                                </span>

                            @elseif($solicitud->estado === 'cancelada')

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 text-xs font-medium text-red-600">

                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>

                                    Cancelada

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

                        <p class="mt-2 text-sm text-muted-foreground leading-relaxed">

                            Consulta la información registrada y actualiza el estado de seguimiento.

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

                        {{ $solicitud->created_at
                            ?->timezone('America/Tegucigalpa')
                            ->format('d/m/Y h:i A') }}

                    </span>

                </div>

            </div>

        </section>



        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_300px] gap-6">


            {{-- Contenido principal --}}

            <div class="space-y-6">


                {{-- Información de la solicitud --}}

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

                                Información de la solicitud

                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">

                                Datos principales enviados por el usuario.

                            </p>

                        </div>

                    </div>


                    <div class="p-6 space-y-6">


                        {{-- Categoría --}}

                        <div>

                            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">

                                Categoría

                            </p>

                            <div class="mt-2">

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-blue-200/70 bg-blue-50 text-xs font-medium text-blue-700 transition-all duration-200 hover:border-blue-300 hover:bg-blue-100">

                                    <i
                                        data-lucide="tag"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    {{ str($solicitud->categoria)
                                        ->replace('_', ' ')
                                        ->title() }}

                                </span>

                            </div>

                        </div>



                        {{-- Asunto --}}

                        <div>

                            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">

                                Asunto

                            </p>

                            <p class="mt-2 text-sm font-semibold text-foreground leading-relaxed">

                                {{ $solicitud->asunto }}

                            </p>

                        </div>



                        {{-- Descripción --}}

                        <div>

                            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">

                                Descripción

                            </p>

                            <div class="mt-2 rounded-xl border border-border bg-muted/20 px-4 py-3.5 transition-colors duration-200 hover:border-primary/15 hover:bg-primary/[0.02]">

                                <p class="text-sm text-foreground leading-relaxed whitespace-pre-line break-words">{{ $solicitud->descripcion }}</p>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- Información adicional --}}

                @if(
                    is_array($solicitud->datos_extra)
                    && count($solicitud->datos_extra) > 0
                )

                    <section class="group rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-blue-200 hover:shadow-md">

                        <div class="flex items-center gap-3 px-6 py-5 border-b border-border">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-blue-500/10 text-blue-600 transition-transform duration-300 group-hover:scale-105">

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

                                    Datos específicos correspondientes a la categoría.

                                </p>

                            </div>

                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8">

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
                                            ->filter(
                                                fn ($item) =>
                                                    $item !== null
                                                    && $item !== ''
                                            )
                                            ->implode(', ');
                                    } elseif (is_bool($valor)) {
                                        $valorMostrado = $valor
                                            ? 'Sí'
                                            : 'No';
                                    } else {
                                        $valorMostrado = (string) $valor;
                                    }

                                @endphp

                                <div class="px-6 py-4 border-b border-border transition-colors duration-200 hover:bg-primary/[0.025] last:border-b-0 sm:[&:nth-last-child(-n+2)]:border-b-0">

                                    <p class="text-xs font-medium text-muted-foreground">

                                        {{ $nombreCampo }}

                                    </p>

                                    <p class="mt-1 text-sm font-medium text-foreground break-words">

                                        {{ $valorMostrado !== ''
                                            ? $valorMostrado
                                            : 'No especificado'
                                        }}

                                    </p>

                                </div>

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

                                Resultado del envío realizado al registrar la solicitud.

                            </p>

                        </div>

                    </div>


                    <div class="p-6">

                        @if($solicitud->correo_enviado)

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

                                        El equipo responsable recibió una notificación sobre esta solicitud.

                                        @if($solicitud->correo_enviado_at)

                                            El envío se realizó el

                                            {{ $solicitud->correo_enviado_at
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

                                        La solicitud quedó registrada, pero no fue posible completar la notificación por correo.

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

                                {{ $solicitud->usuario?->nombre
                                    ? mb_strtoupper(
                                        mb_substr(
                                            $solicitud->usuario->nombre,
                                            0,
                                            1
                                        )
                                    )
                                    : '?'
                                }}

                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-foreground truncate">

                                    {{ $solicitud->usuario?->nombre
                                        ?? 'Usuario no disponible'
                                    }}

                                </p>

                                <p
                                    title="{{ $solicitud->usuario?->correo }}"
                                    class="mt-0.5 text-xs text-muted-foreground truncate">

                                    {{ $solicitud->usuario?->correo
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

                            @if($solicitud->estado === 'finalizada')

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">

                                    <i
                                        data-lucide="circle-check"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    Finalizada

                                </span>

                            @elseif($solicitud->estado === 'cancelada')

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600">

                                    <i
                                        data-lucide="circle-x"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    Cancelada

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700">

                                    <i
                                        data-lucide="clock-3"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5">
                                    </i>

                                    Pendiente

                                </span>

                            @endif

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Folio
                            </span>

                            <span class="text-xs font-semibold text-foreground">
                                {{ $solicitud->folio }}
                            </span>

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Categoría
                            </span>

                            <span class="max-w-[150px] text-right text-xs font-medium text-foreground">

                                {{ str($solicitud->categoria)
                                    ->replace('_', ' ')
                                    ->title() }}

                            </span>

                        </div>


                        <div class="flex items-start justify-between gap-4">

                            <span class="text-xs text-muted-foreground">
                                Registro
                            </span>

                            <span class="text-right text-xs font-medium text-foreground">

                                {{ $solicitud->created_at
                                    ?->timezone('America/Tegucigalpa')
                                    ->format('d/m/Y') }}

                            </span>

                        </div>

                    </div>

                </section>



                {{-- Acciones --}}

                @if($solicitud->estado === 'pendiente')

                    <section class="group relative overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/60 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/20 hover:shadow-md">

                        <div class="absolute -right-10 -top-10 w-24 h-24 rounded-full bg-primary/5 transition-transform duration-500 group-hover:scale-150"></div>

                        <div class="relative">

                            <h2 class="text-sm font-semibold text-foreground">

                                Actualizar estado

                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground leading-relaxed">

                                Indica si la solicitud fue atendida o si debe cancelarse.

                            </p>


                            <div class="mt-5 space-y-3">


                                {{-- Finalizar --}}

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.solicitudes.finalizar',
                                        $solicitud
                                    ) }}"
                                    onsubmit="return confirm('¿Confirmas que esta solicitud fue atendida y puede marcarse como finalizada?')">

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="group/finalizar inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg bg-primary text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0">

                                        <i
                                            data-lucide="circle-check-big"
                                            stroke-width="1.8"
                                            class="w-4 h-4 transition-transform duration-200 group-hover/finalizar:scale-110">
                                        </i>

                                        Marcar como finalizada

                                    </button>

                                </form>



                                {{-- Cancelar --}}

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.solicitudes.cancelar',
                                        $solicitud
                                    ) }}"
                                    onsubmit="return confirm('¿Confirmas que deseas cancelar esta solicitud? Esta acción cambiará su estado administrativo.')">

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="group/cancelar inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:bg-red-50 hover:text-red-600 hover:shadow-sm active:translate-y-0">

                                        <i
                                            data-lucide="circle-x"
                                            stroke-width="1.8"
                                            class="w-4 h-4 transition-transform duration-200 group-hover/cancelar:scale-110">
                                        </i>

                                        Cancelar solicitud

                                    </button>

                                </form>

                            </div>

                        </div>

                    </section>

                @else

                    <section class="group rounded-2xl border border-border bg-muted/20 p-5 transition-all duration-300 hover:border-primary/15 hover:bg-primary/[0.025]">

                        <div class="flex items-start gap-3">

                            <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-muted text-muted-foreground transition-transform duration-300 group-hover:scale-105">

                                <i
                                    data-lucide="lock-keyhole"
                                    stroke-width="1.8"
                                    class="w-[18px] h-[18px]">
                                </i>

                            </div>

                            <div>

                                <h2 class="text-sm font-semibold text-foreground">

                                    Seguimiento completado

                                </h2>

                                <p class="mt-1 text-xs text-muted-foreground leading-relaxed">

                                    Esta solicitud ya no tiene acciones administrativas pendientes.

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