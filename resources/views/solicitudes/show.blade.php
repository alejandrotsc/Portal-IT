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


    $estados = [
        'pendiente' => [
            'label' => 'Pendiente',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
        ],

        'en_proceso' => [
            'label' => 'En proceso',
            'class' => 'bg-blue-50 text-blue-700 border-blue-200',
        ],

        'completada' => [
            'label' => 'Completada',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],

        'rechazada' => [
            'label' => 'Rechazada',
            'class' => 'bg-red-50 text-red-700 border-red-200',
        ],

        'cancelada' => [
            'label' => 'Cancelada',
            'class' => 'bg-slate-50 text-slate-600 border-slate-200',
        ],
    ];


    $estado = $estados[$solicitud->estado] ?? [
        'label' => ucfirst(
            str_replace('_', ' ', $solicitud->estado)
        ),

        'class' => 'bg-slate-50 text-slate-600 border-slate-200',
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
                               rounded-full bg-primary/10
                               text-primary text-xs font-semibold"
                    >
                        {{ $solicitud->folio }}
                    </span>


                    <span
                        class="inline-flex items-center px-2.5 py-1
                               rounded-full border text-[11px] font-medium
                               {{ $estado['class'] }}"
                    >
                        {{ $estado['label'] }}
                    </span>

                </div>


                <h1 class="text-xl font-semibold text-foreground mt-2">
                    {{ $solicitud->asunto }}
                </h1>


                <p class="text-sm text-muted-foreground mt-1">
                    Registrada el
                    {{ $solicitud->created_at->format('d/m/Y') }}
                    a las
                    {{ $solicitud->created_at->format('H:i') }}
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
                <i data-lucide="plus" class="w-4 h-4"></i>

                Nueva solicitud
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
                        Datos relacionados con la solicitud.
                    </p>

                </div>

                <i
                    data-lucide="info"
                    class="w-4 h-4 text-muted-foreground"
                ></i>

            </div>


            <div
                class="grid grid-cols-1 sm:grid-cols-3
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

        <div class="min-w-0">

            <p class="text-xs text-muted-foreground">
                Solicitado por
            </p>

            <p class="text-sm font-medium text-foreground mt-1">
                {{ $solicitud->usuario->nombre ?? 'No disponible' }}
            </p>

            <p class="text-xs text-muted-foreground mt-0.5 truncate">
                {{ $solicitud->usuario->correo ?? 'Correo no disponible' }}
            </p>

        </div>

    </div>

</div>


                {{-- CATEGORÍA --}}
                <div class="px-5 py-4">

                    <div class="flex items-start gap-3">

                        <i
                            data-lucide="folder"
                            class="w-4 h-4 text-muted-foreground mt-0.5"
                        ></i>

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Categoría
                            </p>

                            <p class="text-sm font-medium text-foreground mt-1">
                                {{
                                    $categorias[$solicitud->categoria]
                                    ?? ucfirst($solicitud->categoria)
                                }}
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
                                Fecha
                            </p>

                            <p class="text-sm font-medium text-foreground mt-1">
                                {{ $solicitud->created_at->format('d/m/Y H:i') }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- DESCRIPCIÓN --}}
        <section
            class="bg-card border border-border
                   rounded-2xl overflow-hidden"
        >

            <div class="px-5 py-4 border-b border-border">

                <h2 class="text-sm font-medium text-foreground">
                    Descripción de la solicitud
                </h2>

            </div>


            <div class="px-5 py-5">

                <p
                    class="text-sm leading-relaxed
                           text-foreground whitespace-pre-line"
                >{{ $solicitud->descripcion }}</p>

            </div>

        </section>


        {{-- INFORMACIÓN ADICIONAL --}}
        @if(!empty($solicitud->datos_extra))

            <section
                class="bg-card border border-border
                       rounded-2xl overflow-hidden"
            >

                <div class="px-5 py-4 border-b border-border">

                    <h2 class="text-sm font-medium text-foreground">
                        Información adicional
                    </h2>

                    <p class="text-xs text-muted-foreground mt-1">
                        Datos específicos del servicio solicitado.
                    </p>

                </div>


                <dl class="grid grid-cols-1 sm:grid-cols-2">

                    @foreach($solicitud->datos_extra as $campo => $valor)

                        @continue(
                            $valor === null ||
                            $valor === '' ||
                            $valor === []
                        )


                        <div
                            class="px-5 py-4 border-b border-border
                                   odd:sm:border-r
                                   last:border-b-0"
                        >

                            <dt class="text-xs text-muted-foreground">

                                {{
                                    ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $campo
                                        )
                                    )
                                }}

                            </dt>


                            <dd class="text-sm font-medium text-foreground mt-1">

                                @if(is_array($valor))

                                    {{ implode(', ', $valor) }}

                                @elseif(is_bool($valor))

                                    {{ $valor ? 'Sí' : 'No' }}

                                @else

                                    {{ $valor }}

                                @endif

                            </dd>

                        </div>

                    @endforeach

                </dl>

            </section>

        @endif


        {{-- SEGUIMIENTO --}}
        <section
            class="bg-card border border-border
                   rounded-2xl overflow-hidden"
        >

            <div class="px-5 py-4 flex items-start gap-3">

                <div
                    class="w-8 h-8 rounded-lg bg-primary/10
                           flex items-center justify-center shrink-0"
                >
                    <i
                        data-lucide="info"
                        class="w-4 h-4 text-primary"
                    ></i>
                </div>


                <div>

                    <p class="text-sm font-medium text-foreground">
                        Seguimiento de la solicitud
                    </p>

                    <p class="text-xs leading-relaxed text-muted-foreground mt-1">
                        Cuando el equipo TI actualice esta solicitud,
                        el nuevo estado se mostrará en este apartado.
                    </p>

                </div>

            </div>

        </section>


        {{-- VOLVER --}}
        <div class="flex justify-end">

            <a
                href="{{ route('mis-solicitudes') }}"
                class="inline-flex items-center gap-2
                       px-4 py-2.5 rounded-xl
                       border border-border bg-white
                       text-sm font-medium text-foreground
                       hover:bg-muted transition-colors"
            >
                <i data-lucide="arrow-left" class="w-4 h-4"></i>

                Volver a mis solicitudes
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