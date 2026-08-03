@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')

@php

    $usuario =
        auth()->user();

    $totalNoLeidas =
        $usuario
            ?->unreadNotifications()
            ->count()
        ?? 0;

@endphp


<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-10">



        {{-- Mensajes --}}

        @if(session('success'))

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800 shadow-sm dark:!border-emerald-800/70 dark:!bg-emerald-950/35 dark:!text-emerald-200 dark:shadow-black/20">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/60 dark:text-emerald-300">

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



        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                <div class="flex items-start gap-4">

                    <div class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm dark:!border-blue-800/70 dark:!bg-blue-950/45 dark:!text-blue-400 dark:shadow-black/20">

                        <i
                            data-lucide="bell"
                            stroke-width="1.8"
                            class="w-6 h-6">
                        </i>

                    </div>


                    <div>

                        <div class="flex flex-wrap items-center gap-2">

                            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                                Notificaciones
                            </h1>


                            @if($totalNoLeidas > 0)

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-primary/10 bg-primary/10 text-xs font-semibold text-primary dark:!border-blue-800/60 dark:!bg-blue-950/45 dark:!text-blue-300">

                                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>

                                    {{ $totalNoLeidas }}

                                    {{ $totalNoLeidas === 1
                                        ? 'sin leer'
                                        : 'sin leer'
                                    }}

                                </span>

                            @endif

                        </div>


                        <p class="mt-2 max-w-xl text-sm leading-relaxed text-muted-foreground">

                            Consulta las actualizaciones relacionadas con tus pases, incidencias, solicitudes y avisos de TI.

                        </p>

                    </div>

                </div>


                @if($totalNoLeidas > 0)

                    <form
                        method="POST"
                        action="{{ route('notificaciones.marcar-todas') }}">

                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-primary/20 bg-primary/5 text-sm font-semibold text-primary shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/10 hover:shadow-md active:translate-y-0 dark:!border-blue-800/70 dark:!bg-blue-950/30 dark:!text-blue-300 dark:hover:!border-blue-600/70 dark:hover:!bg-blue-950/55 dark:hover:shadow-black/20">

                            <i
                                data-lucide="check-check"
                                stroke-width="1.8"
                                class="w-4 h-4">
                            </i>

                            Marcar todas como leídas

                        </button>

                    </form>

                @endif

            </div>

        </section>



        {{-- Listado --}}

        <section class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:!border-slate-700/70 dark:!bg-slate-900/70 dark:shadow-black/25">


            <div class="flex items-center justify-between gap-4 border-b border-border bg-white/60 px-6 py-5 dark:!border-slate-700/70 dark:!bg-slate-900/90">

                <div>

                    <h2 class="text-sm font-semibold text-foreground">
                        Historial de notificaciones
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Las más recientes aparecen primero.
                    </p>

                </div>


                <div class="flex items-center gap-2 text-xs text-muted-foreground">

                    <i
                        data-lucide="inbox"
                        stroke-width="1.8"
                        class="w-4 h-4">
                    </i>

                    {{ $notificaciones->total() }}

                    {{ $notificaciones->total() === 1
                        ? 'notificación'
                        : 'notificaciones'
                    }}

                </div>

            </div>



            <div class="divide-y divide-border dark:!divide-slate-700/70">

                @forelse($notificaciones as $notificacion)

                    @php

                        $datos =
                            $notificacion->data;

                        $estaSinLeer =
                            $notificacion->unread();

                        $tipo =
                            $datos['tipo']
                            ?? 'general';

                        $clasesIcono = match($tipo) {
                            'pase' =>
                                'bg-blue-500/10 text-blue-600 dark:bg-blue-950/55 dark:text-blue-300',

                            'incidencia' =>
                                'bg-amber-500/10 text-amber-600 dark:bg-amber-950/50 dark:text-amber-300',

                            'solicitud' =>
                                'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-300',

                            'aviso' =>
                                'bg-violet-500/10 text-violet-600 dark:bg-violet-950/50 dark:text-violet-300',

                            default =>
                                'bg-primary/10 text-primary dark:bg-blue-950/50 dark:text-blue-300',
                        };

                    @endphp


                    <a
                        href="{{ route(
                            'notificaciones.abrir',
                            $notificacion->id
                        ) }}"
                        @class([
                            'group flex items-start gap-4 px-6 py-5 transition-colors duration-200 hover:bg-primary/[0.025] dark:hover:!bg-slate-800/55',
                            'bg-primary/[0.035] dark:!bg-blue-950/20' =>
                                $estaSinLeer,
                        ])>


                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl {{ $clasesIcono }}">

                            <i
                                data-lucide="{{ $datos['icono'] ?? 'bell' }}"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-200 group-hover:scale-105">
                            </i>

                        </div>


                        <div class="min-w-0 flex-1">

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">

                                <div class="min-w-0">

                                    <div class="flex items-center gap-2">

                                        <h3 class="text-sm font-semibold text-foreground break-words">

                                            {{ $datos['titulo']
                                                ?? 'Nueva notificación'
                                            }}

                                        </h3>


                                        @if($estaSinLeer)

                                            <span
                                                title="Sin leer"
                                                class="w-2 h-2 shrink-0 rounded-full bg-primary">
                                            </span>

                                        @endif

                                    </div>


                                    <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground break-words">

                                        {{ $datos['mensaje']
                                            ?? 'Tienes una nueva actualización.'
                                        }}

                                    </p>

                                </div>


                                <span class="text-xs text-muted-foreground whitespace-nowrap">

                                    {{ $notificacion->created_at
                                        ?->timezone('America/Tegucigalpa')
                                        ->format('d/m/Y h:i A')
                                    }}

                                </span>

                            </div>


                            <div class="mt-3 flex flex-wrap items-center gap-3">

                                <span
                                    @class([
                                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium',
                                        'bg-blue-500/10 text-blue-700 dark:bg-blue-950/55 dark:text-blue-300' =>
                                            $tipo === 'pase',

                                        'bg-amber-500/10 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300' =>
                                            $tipo === 'incidencia',

                                        'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' =>
                                            $tipo === 'solicitud',

                                        'bg-violet-500/10 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300' =>
                                            $tipo === 'aviso',

                                        'bg-primary/10 text-primary dark:bg-blue-950/50 dark:text-blue-300' =>
                                            ! in_array(
                                                $tipo,
                                                [
                                                    'pase',
                                                    'incidencia',
                                                    'solicitud',
                                                    'aviso',
                                                ],
                                                true
                                            ),
                                    ])>

                                    {{ match($tipo) {
                                        'pase' =>
                                            'Pase',

                                        'incidencia' =>
                                            'Incidencia',

                                        'solicitud' =>
                                            'Solicitud',

                                        'aviso' =>
                                            'Aviso TI',

                                        default =>
                                            'General',
                                    } }}

                                </span>


                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-primary">

                                    Abrir detalle

                                    <i
                                        data-lucide="arrow-right"
                                        stroke-width="1.8"
                                        class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5">
                                    </i>

                                </span>

                            </div>

                        </div>

                    </a>

                @empty

                    <div class="bg-white/30 px-6 py-16 text-center dark:!bg-slate-900/40">

                        <div class="flex items-center justify-center w-14 h-14 mx-auto rounded-2xl border border-primary/10 bg-primary/5 text-primary dark:!border-blue-800/60 dark:!bg-blue-950/35 dark:!text-blue-400">

                            <i
                                data-lucide="bell-off"
                                stroke-width="1.8"
                                class="w-6 h-6">
                            </i>

                        </div>

                        <h3 class="mt-4 text-sm font-semibold text-foreground">
                            No tienes notificaciones
                        </h3>

                        <p class="max-w-md mx-auto mt-1 text-sm leading-relaxed text-muted-foreground">

                            Aquí aparecerán los cambios de estado de tus gestiones y los nuevos avisos de TI.

                        </p>

                    </div>

                @endforelse

            </div>



            {{-- Paginación --}}

            @if($notificaciones->hasPages())

                <div class="border-t border-border bg-muted/20 px-6 py-4 dark:!border-slate-700/70 dark:!bg-slate-950/30 [&_a]:dark:!border-slate-700 [&_a]:dark:!bg-slate-900 [&_a]:dark:!text-slate-300 [&_a:hover]:dark:!bg-slate-800 [&_span]:dark:!border-slate-700 [&_span]:dark:!text-slate-400 [&_span[aria-current='page']_span]:dark:!border-blue-600 [&_span[aria-current='page']_span]:dark:!bg-blue-600 [&_span[aria-current='page']_span]:dark:!text-white">

                    {{ $notificaciones->links() }}

                </div>

            @endif

        </section>

    </main>

</div>

@endsection