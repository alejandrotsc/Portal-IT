@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

    <main class="mx-auto max-w-6xl px-6 py-10">

        {{-- Encabezado --}}

        <section class="mb-10">

            <div
                class="flex flex-col gap-5
                       sm:flex-row sm:items-end sm:justify-between"
            >
                <div>

                    <span
                        class="mb-4 inline-flex items-center gap-2
                               rounded-full border border-primary/10
                               bg-primary/[0.06] px-3 py-1.5
                               text-xs font-semibold text-primary
                               dark:border-blue-800/70"
                    >
                        <i
                            data-lucide="shield-check"
                            stroke-width="1.8"
                            class="h-3.5 w-3.5 shrink-0"
                        ></i>

                        Panel administrativo
                    </span>

                    <h1
                        class="text-2xl font-semibold tracking-tight
                               text-foreground sm:text-3xl"
                    >
                        Administración del Portal TI
                    </h1>

                    <p
                        class="mt-2 max-w-2xl text-sm leading-relaxed
                               text-muted-foreground"
                    >
                        Administra usuarios, guardias, solicitudes,
                        incidencias, pases y avisos informativos del portal.
                    </p>

                </div>

                <div
                    class="inline-flex items-center gap-2
                           text-xs font-medium text-muted-foreground"
                >
                    <span class="relative flex h-2.5 w-2.5 shrink-0">

                        <span
                            class="absolute inline-flex h-full w-full
                                   animate-ping rounded-full bg-emerald-400
                                   opacity-60"
                        ></span>

                        <span
                            class="relative inline-flex h-2.5 w-2.5
                                   rounded-full bg-emerald-500"
                        ></span>

                    </span>

                    <span>Portal disponible</span>
                </div>

            </div>

        </section>

        {{-- Resumen general --}}

        <section class="mb-10">

            <div class="mb-5">

                <h2 class="text-base font-semibold text-foreground">
                    Resumen general
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Actividad actual de usuarios y gestiones pendientes
                    de revisión.
                </p>

            </div>

            <div
                class="grid grid-cols-1 gap-4
                       sm:grid-cols-2 xl:grid-cols-4"
            >

                {{-- Usuarios conectados --}}

                <div
                    class="group relative overflow-hidden rounded-2xl
                           border border-emerald-200/60
                           bg-gradient-to-br from-emerald-50 via-white
                           to-teal-50/50 p-5 shadow-sm
                           transition-all duration-300
                           hover:border-emerald-300
                           hover:shadow-lg hover:shadow-emerald-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-emerald-900/60
                           dark:from-emerald-950/30 dark:via-slate-900
                           dark:to-teal-950/20"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8
                               h-24 w-24 rounded-full bg-emerald-400/10
                               transition-all duration-500
                               group-hover:bg-emerald-400/20
                               motion-safe:group-hover:scale-150"
                    ></div>

                    <div
                        class="relative mb-5 flex items-center
                               justify-between"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl
                                   bg-emerald-500/10 text-emerald-600
                                   transition-all duration-300
                                   group-hover:bg-emerald-100
                                   motion-safe:group-hover:scale-105
                                   dark:text-emerald-400
                                   dark:group-hover:bg-emerald-950/70"
                        >
                            <i
                                data-lucide="user-check"
                                stroke-width="1.8"
                                class="h-5 w-5 shrink-0
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110"
                            ></i>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-emerald-500/10
                                   px-2.5 py-1 text-xs font-medium
                                   text-emerald-700
                                   dark:text-emerald-400"
                        >
                            <span class="relative flex h-1.5 w-1.5">

                                <span
                                    class="absolute inline-flex h-full
                                           w-full animate-ping rounded-full
                                           bg-emerald-400 opacity-50"
                                ></span>

                                <span
                                    class="relative inline-flex h-1.5 w-1.5
                                           rounded-full bg-emerald-500"
                                ></span>

                            </span>

                            En línea
                        </span>
                    </div>

                    <p
                        class="relative text-2xl font-semibold leading-none
                               text-foreground"
                    >
                        {{ $usuariosConectados ?? 0 }}
                    </p>

                    <p class="relative mt-2 text-sm text-muted-foreground">
                        Usuarios activos en el portal
                    </p>
                </div>

                {{-- Pases por revisar --}}

                <div
                    class="group relative overflow-hidden rounded-2xl
                           border border-amber-200/60
                           bg-gradient-to-br from-amber-50 via-white
                           to-orange-50/50 p-5 shadow-sm
                           transition-all duration-300
                           hover:border-amber-300
                           hover:shadow-lg hover:shadow-amber-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-amber-900/60
                           dark:from-amber-950/30 dark:via-slate-900
                           dark:to-orange-950/20"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8
                               h-24 w-24 rounded-full bg-amber-400/10
                               transition-all duration-500
                               group-hover:bg-amber-400/20
                               motion-safe:group-hover:scale-150"
                    ></div>

                    <div
                        class="relative mb-5 flex items-center
                               justify-between"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl bg-amber-500/10
                                   text-amber-600 transition-all duration-300
                                   group-hover:bg-amber-100
                                   motion-safe:group-hover:scale-105
                                   dark:text-amber-400
                                   dark:group-hover:bg-amber-950/70"
                        >
                            <i
                                data-lucide="file-check-2"
                                stroke-width="1.8"
                                class="h-5 w-5 shrink-0
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110"
                            ></i>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-amber-500/10 px-2.5 py-1
                                   text-xs font-medium text-amber-700
                                   dark:text-amber-400"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full
                                       bg-amber-500"
                            ></span>

                            Pendientes
                        </span>
                    </div>

                    <p
                        class="relative text-2xl font-semibold leading-none
                               text-foreground"
                    >
                        {{ $pasesPorRevisar ?? 0 }}
                    </p>

                    <p class="relative mt-2 text-sm text-muted-foreground">
                        Pases por revisar
                    </p>
                </div>

                {{-- Solicitudes pendientes --}}

                <div
                    class="group relative overflow-hidden rounded-2xl
                           border border-cyan-200/60
                           bg-gradient-to-br from-cyan-50 via-white
                           to-blue-50/60 p-5 shadow-sm
                           transition-all duration-300
                           hover:border-cyan-300
                           hover:shadow-lg hover:shadow-cyan-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-cyan-900/60
                           dark:from-cyan-950/30 dark:via-slate-900
                           dark:to-blue-950/20"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8
                               h-24 w-24 rounded-full bg-cyan-400/10
                               transition-all duration-500
                               group-hover:bg-cyan-400/20
                               motion-safe:group-hover:scale-150"
                    ></div>

                    <div
                        class="relative mb-5 flex items-center
                               justify-between"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl bg-cyan-500/10
                                   text-cyan-600 transition-all duration-300
                                   group-hover:bg-cyan-100
                                   motion-safe:group-hover:scale-105
                                   dark:text-cyan-400
                                   dark:group-hover:bg-cyan-950/70"
                        >
                            <i
                                data-lucide="clipboard-clock"
                                stroke-width="1.8"
                                class="h-5 w-5 shrink-0
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110"
                            ></i>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-cyan-500/10 px-2.5 py-1
                                   text-xs font-medium text-cyan-700
                                   dark:text-cyan-400"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full
                                       bg-cyan-500"
                            ></span>

                            Pendientes
                        </span>
                    </div>

                    <p
                        class="relative text-2xl font-semibold leading-none
                               text-foreground"
                    >
                        {{ $solicitudesPendientes ?? 0 }}
                    </p>

                    <p class="relative mt-2 text-sm text-muted-foreground">
                        Solicitudes por revisar
                    </p>
                </div>

                {{-- Incidencias abiertas --}}

                <div
                    class="group relative overflow-hidden rounded-2xl
                           border border-violet-200/60
                           bg-gradient-to-br from-violet-50 via-white
                           to-indigo-50/50 p-5 shadow-sm
                           transition-all duration-300
                           hover:border-violet-300
                           hover:shadow-lg hover:shadow-violet-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-violet-900/60
                           dark:from-violet-950/30 dark:via-slate-900
                           dark:to-indigo-950/20"
                >
                    <div
                        class="pointer-events-none absolute -right-8 -top-8
                               h-24 w-24 rounded-full bg-violet-400/10
                               transition-all duration-500
                               group-hover:bg-violet-400/20
                               motion-safe:group-hover:scale-150"
                    ></div>

                    <div
                        class="relative mb-5 flex items-center
                               justify-between"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl
                                   bg-violet-500/10 text-violet-600
                                   transition-all duration-300
                                   group-hover:bg-violet-100
                                   motion-safe:group-hover:scale-105
                                   dark:text-violet-400
                                   dark:group-hover:bg-violet-950/70"
                        >
                            <i
                                data-lucide="triangle-alert"
                                stroke-width="1.8"
                                class="h-5 w-5 shrink-0
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110"
                            ></i>
                        </div>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-violet-500/10 px-2.5 py-1
                                   text-xs font-medium text-violet-700
                                   dark:text-violet-400"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full
                                       bg-violet-500"
                            ></span>

                            Abiertas
                        </span>
                    </div>

                    <p
                        class="relative text-2xl font-semibold leading-none
                               text-foreground"
                    >
                        {{ $incidenciasAbiertas ?? 0 }}
                    </p>

                    <p class="relative mt-2 text-sm text-muted-foreground">
                        Incidencias por atender
                    </p>
                </div>

            </div>

        </section>

        {{-- Administración --}}

<section>

    <div class="mb-5">

        <h2 class="text-base font-semibold text-foreground">
            Administración
        </h2>

        <p class="mt-1 text-sm text-muted-foreground">
            Selecciona el apartado que deseas gestionar.
        </p>

    </div>

    <div
        class="grid grid-cols-1 gap-5
               md:grid-cols-2 xl:grid-cols-3"
    >

        {{-- ========================================================= --}}
        {{-- TARJETAS EXCLUSIVAS DEL ADMINISTRADOR                     --}}
        {{-- ========================================================= --}}

        @if (auth()->user()->rol?->nombre === 'Administrador')

            {{-- Administración de usuarios --}}

            <a
                href="{{ route('usuarios.index') }}"
                class="group block h-full"
            >
                <div
                    class="relative h-full overflow-hidden rounded-2xl
                           border border-border bg-card p-5 shadow-sm
                           transition-all duration-300
                           hover:border-blue-200
                           hover:shadow-lg hover:shadow-blue-500/10
                           motion-safe:hover:-translate-y-1
                           dark:hover:border-blue-800"
                >
                    <div
                        class="pointer-events-none absolute inset-0
                               bg-gradient-to-br from-blue-50/90
                               via-transparent to-indigo-50/80
                               opacity-40 transition-opacity duration-300
                               group-hover:opacity-100
                               dark:from-blue-950/30
                               dark:to-indigo-950/20"
                    ></div>

                    <div class="relative z-10 flex h-full flex-col">

                        <div class="mb-5 flex items-start justify-between">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center
                                       justify-center rounded-xl bg-blue-100
                                       transition-all duration-300
                                       group-hover:bg-blue-200
                                       motion-safe:group-hover:scale-105
                                       dark:bg-blue-950/60
                                       dark:group-hover:bg-blue-900/70"
                            >
                                <i
                                    data-lucide="users"
                                    stroke-width="1.8"
                                    class="h-5 w-5 text-blue-600
                                           transition-transform duration-300
                                           motion-safe:group-hover:scale-110
                                           dark:text-blue-400"
                                ></i>
                            </div>

                            <i
                                data-lucide="arrow-right"
                                stroke-width="1.8"
                                class="h-4 w-4 -translate-x-1
                                       text-blue-500 opacity-0
                                       transition-all duration-300
                                       group-hover:opacity-100
                                       motion-safe:group-hover:translate-x-1"
                            ></i>

                        </div>

                        <h3
                            class="mb-2 text-sm font-semibold
                                   text-foreground"
                        >
                            Administración de usuarios
                        </h3>

                        <p
                            class="text-xs leading-relaxed
                                   text-muted-foreground"
                        >
                            Consulta usuarios, asigna roles y controla
                            el acceso al portal.
                        </p>

                        <div class="mt-auto flex flex-wrap gap-2 pt-5">

                            <span
                                class="inline-flex items-center gap-1.5
                                       rounded-full bg-blue-100
                                       px-2.5 py-1 text-[11px]
                                       font-medium text-blue-700
                                       dark:bg-blue-950/60
                                       dark:text-blue-300"
                            >
                                <i
                                    data-lucide="list"
                                    stroke-width="1.8"
                                    class="h-3 w-3"
                                ></i>

                                Ver usuarios
                            </span>

                            <span
                                class="inline-flex items-center gap-1.5
                                       rounded-full bg-indigo-100
                                       px-2.5 py-1 text-[11px]
                                       font-medium text-indigo-700
                                       dark:bg-indigo-950/60
                                       dark:text-indigo-300"
                            >
                                <i
                                    data-lucide="shield"
                                    stroke-width="1.8"
                                    class="h-3 w-3"
                                ></i>

                                Roles y accesos
                            </span>

                        </div>

                    </div>

                </div>
            </a>

            {{-- Administración de guardias --}}

<a
    href="{{ route('admin.guardias.index') }}"
    class="group block h-full"
>
    <div
        class="relative h-full overflow-hidden rounded-2xl
               border border-border bg-card p-5 shadow-sm
               transition-all duration-300
               hover:border-cyan-200
               hover:shadow-lg hover:shadow-cyan-500/10
               motion-safe:hover:-translate-y-1
               dark:hover:border-cyan-800"
    >
        <div
            class="pointer-events-none absolute inset-0
                   bg-gradient-to-br from-cyan-50/90
                   via-transparent to-blue-50/80
                   opacity-40 transition-opacity duration-300
                   group-hover:opacity-100
                   dark:from-cyan-950/30
                   dark:to-blue-950/20"
        ></div>

        <div class="relative z-10 flex h-full flex-col">

            <div class="mb-5 flex items-start justify-between">

                <div
                    class="flex h-11 w-11 shrink-0 items-center
                           justify-center rounded-xl bg-cyan-100
                           transition-all duration-300
                           group-hover:bg-cyan-200
                           motion-safe:group-hover:scale-105
                           dark:bg-cyan-950/60
                           dark:group-hover:bg-cyan-900/70"
                >
                    <i
                        data-lucide="calendar-days"
                        stroke-width="1.8"
                        class="h-5 w-5 text-cyan-600
                               transition-transform duration-300
                               motion-safe:group-hover:scale-110
                               dark:text-cyan-400"
                    ></i>
                </div>

                <i
                    data-lucide="arrow-right"
                    stroke-width="1.8"
                    class="h-4 w-4 -translate-x-1
                           text-cyan-500 opacity-0
                           transition-all duration-300
                           group-hover:opacity-100
                           motion-safe:group-hover:translate-x-1"
                ></i>

            </div>

            <h3
                class="mb-2 text-sm font-semibold
                       text-foreground"
            >
                Administración de turnos
            </h3>

            <p
                class="text-xs leading-relaxed
                       text-muted-foreground"
            >
                Programa el agente de soporte asignado para
                los turnos de sábado y domingo.
            </p>

            <div class="mt-auto flex flex-wrap gap-2 pt-5">

                <span
                    class="inline-flex items-center gap-1.5
                           rounded-full bg-cyan-100
                           px-2.5 py-1 text-[11px]
                           font-medium text-cyan-700
                           dark:bg-cyan-950/60
                           dark:text-cyan-300"
                >
                    <i
                        data-lucide="user-round-plus"
                        stroke-width="1.8"
                        class="h-3 w-3"
                    ></i>

                    Asignar agente
                </span>

                <span
                    class="inline-flex items-center gap-1.5
                           rounded-full bg-blue-100
                           px-2.5 py-1 text-[11px]
                           font-medium text-blue-700
                           dark:bg-blue-950/60
                           dark:text-blue-300"
                >
                    <i
                        data-lucide="calendar-clock"
                        stroke-width="1.8"
                        class="h-3 w-3"
                    ></i>

                    Sábado y domingo
                </span>

            </div>

        </div>

    </div>
</a>

            {{-- Avisos TI --}}

            <a
                href="{{ route('avisos.index') }}"
                class="group block h-full"
            >
                <div
                    class="relative h-full overflow-hidden rounded-2xl
                           border border-border bg-card p-5 shadow-sm
                           transition-all duration-300
                           hover:border-amber-200
                           hover:shadow-lg hover:shadow-amber-500/10
                           motion-safe:hover:-translate-y-1
                           dark:hover:border-amber-800"
                >
                    <div
                        class="pointer-events-none absolute inset-0
                               bg-gradient-to-br from-amber-50/90
                               via-transparent to-orange-50/80
                               opacity-40 transition-opacity duration-300
                               group-hover:opacity-100
                               dark:from-amber-950/30
                               dark:to-orange-950/20"
                    ></div>

                    <div class="relative z-10 flex h-full flex-col">

                        <div class="mb-5 flex items-start justify-between">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center
                                       justify-center rounded-xl
                                       bg-amber-100
                                       transition-all duration-300
                                       group-hover:bg-amber-200
                                       motion-safe:group-hover:scale-105
                                       dark:bg-amber-950/60
                                       dark:group-hover:bg-amber-900/70"
                            >
                                <i
                                    data-lucide="megaphone"
                                    stroke-width="1.8"
                                    class="h-5 w-5 text-amber-600
                                           transition-transform duration-300
                                           motion-safe:group-hover:scale-110
                                           dark:text-amber-400"
                                ></i>
                            </div>

                            <i
                                data-lucide="arrow-right"
                                stroke-width="1.8"
                                class="h-4 w-4 -translate-x-1
                                       text-amber-500 opacity-0
                                       transition-all duration-300
                                       group-hover:opacity-100
                                       motion-safe:group-hover:translate-x-1"
                            ></i>

                        </div>

                        <h3
                            class="mb-2 text-sm font-semibold

                                   text-foreground"
                        >
                            Avisos de TI
                        </h3>

                        <p
                            class="text-xs leading-relaxed
                                   text-muted-foreground"
                        >
                            Publica y administra los avisos mostrados
                            en la cinta informativa.
                        </p>

                        <div class="mt-auto flex flex-wrap gap-2 pt-5">

                            <span
                                class="inline-flex items-center gap-1.5
                                       rounded-full bg-amber-100
                                       px-2.5 py-1 text-[11px]
                                       font-medium text-amber-700
                                       dark:bg-amber-950/60
                                       dark:text-amber-300"
                            >
                                <i
                                    data-lucide="plus-circle"
                                    stroke-width="1.8"
                                    class="h-3 w-3"
                                ></i>

                                Crear aviso
                            </span>

                            <span
                                class="inline-flex items-center gap-1.5
                                       rounded-full bg-orange-100
                                       px-2.5 py-1 text-[11px]
                                       font-medium text-orange-700
                                       dark:bg-orange-950/60
                                       dark:text-orange-300"
                            >
                                <i
                                    data-lucide="calendar-clock"
                                    stroke-width="1.8"
                                    class="h-3 w-3"
                                ></i>

                                Controlar vigencia
                            </span>

                        </div>

                    </div>

                </div>
            </a>

        @elseif (auth()->user()->rol?->nombre === 'UsuarioTI')

            {{-- Mis guardias --}}

            <a
            href="{{ route('admin.guardias.mis-guardias') }}"
            class="group block h-full md:col-span-2 xl:col-span-3"
            >
                <div
                    class="relative h-full overflow-hidden rounded-2xl
                           border border-cyan-200/60 bg-card p-5
                           shadow-sm transition-all duration-300
                           hover:border-cyan-300
                           hover:shadow-lg hover:shadow-cyan-500/10
                           motion-safe:hover:-translate-y-1
                           dark:border-cyan-900/60
                           dark:hover:border-cyan-800"
                >
                    <div
                        class="pointer-events-none absolute inset-0
                               bg-gradient-to-br from-cyan-50/90
                               via-transparent to-blue-50/80
                               opacity-40 transition-opacity duration-300
                               group-hover:opacity-100
                               dark:from-cyan-950/30
                               dark:to-blue-950/20"
                    ></div>

                    <div
                        class="relative z-10 flex h-full flex-col gap-5
                               sm:flex-row sm:items-center
                               sm:justify-between"
                    >
                        <div class="flex min-w-0 items-start gap-4">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center
                                       justify-center rounded-xl bg-cyan-100
                                       transition-all duration-300
                                       group-hover:bg-cyan-200
                                       motion-safe:group-hover:scale-105
                                       dark:bg-cyan-950/60
                                       dark:group-hover:bg-cyan-900/70"
                            >
                                <i
                                    data-lucide="calendar-days"
                                    stroke-width="1.8"
                                    class="h-5 w-5 text-cyan-600
                                           transition-transform duration-300
                                           motion-safe:group-hover:scale-110
                                           dark:text-cyan-400"
                                ></i>
                            </div>

                            <div class="min-w-0">

                                <div
                                    class="mb-2 flex flex-wrap
                                           items-center gap-2"
                                >
                                    <h3
                                        class="text-sm font-semibold
                                               text-foreground"
                                    >
                                        Mis guardias
                                    </h3>

                                    <span
                                        class="inline-flex items-center
                                               gap-1.5 rounded-full
                                               bg-cyan-100 px-2.5 py-1
                                               text-[11px] font-medium
                                               text-cyan-700
                                               dark:bg-cyan-950/60
                                               dark:text-cyan-300"
                                    >
                                        <i
                                            data-lucide="eye"
                                            stroke-width="1.8"
                                            class="h-3 w-3"
                                        ></i>

                                        Solo consulta
                                    </span>
                                </div>

                                <p
                                    class="max-w-xl text-xs leading-relaxed
                                           text-muted-foreground"
                                >
                                    Consulta las guardias de sábado y
                                    domingo que te hayan sido asignadas.
                                </p>

                            </div>

                        </div>

                        <div
                            class="flex shrink-0 flex-wrap gap-2
                                   sm:justify-end"
                        >
                            <span
                                class="inline-flex items-center gap-1.5
                                       rounded-full bg-cyan-100
                                       px-2.5 py-1 text-[11px]
                                       font-medium text-cyan-700
                                       dark:bg-cyan-950/60
                                       dark:text-cyan-300"
                            >
                                <i
                                    data-lucide="calendar-range"
                                    stroke-width="1.8"
                                    class="h-3 w-3"
                                ></i>

                                Ver calendario
                            </span>

                            <span
                                class="inline-flex items-center gap-1.5
                                       rounded-full bg-blue-100
                                       px-2.5 py-1 text-[11px]
                                       font-medium text-blue-700
                                       dark:bg-blue-950/60
                                       dark:text-blue-300"
                            >
                                <i
                                    data-lucide="clock"
                                    stroke-width="1.8"
                                    class="h-3 w-3"
                                ></i>

                                Próximas guardias
                            </span>
                        </div>

                    </div>

                </div>

            </a>

        @endif

        {{-- ========================================================= --}}
        {{-- GESTIONES DISPONIBLES PARA ADMINISTRADOR Y USUARIOTI      --}}
        {{-- ========================================================= --}}

        {{-- Administración de pases --}}

        <a
            href="{{ route('admin.pases') }}"
            class="group block h-full"
        >
            <div
                class="relative h-full overflow-hidden rounded-2xl
                       border border-border bg-card p-5 shadow-sm
                       transition-all duration-300
                       hover:border-sky-200
                       hover:shadow-lg hover:shadow-sky-500/10
                       motion-safe:hover:-translate-y-1
                       dark:hover:border-sky-800"
            >
                <div
                    class="pointer-events-none absolute inset-0
                           bg-gradient-to-br from-sky-50/90
                           via-transparent to-blue-50/80
                           opacity-40 transition-opacity duration-300
                           group-hover:opacity-100
                           dark:from-sky-950/30
                           dark:to-blue-950/20"
                ></div>

                <div class="relative z-10 flex h-full flex-col">

                    <div class="mb-5 flex items-start justify-between">

                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl bg-sky-100
                                   transition-all duration-300
                                   group-hover:bg-sky-200
                                   motion-safe:group-hover:scale-105
                                   dark:bg-sky-950/60
                                   dark:group-hover:bg-sky-900/70"
                        >
                            <i
                                data-lucide="file-check-2"
                                stroke-width="1.8"
                                class="h-5 w-5 text-sky-600
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110
                                       dark:text-sky-400"
                            ></i>
                        </div>

                        <i
                            data-lucide="arrow-right"
                            stroke-width="1.8"
                            class="h-4 w-4 -translate-x-1 text-sky-500
                                   opacity-0 transition-all duration-300
                                   group-hover:opacity-100
                                   motion-safe:group-hover:translate-x-1"
                        ></i>

                    </div>

                    <h3
                        class="mb-2 text-sm font-semibold text-foreground"
                    >
                        Administración de pases
                    </h3>

                    <p
                        class="text-xs leading-relaxed
                               text-muted-foreground"
                    >
                        Revisa los pases registrados y determina si deben
                        aprobarse o rechazarse.
                    </p>

                    <div class="mt-auto flex flex-wrap gap-2 pt-5">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-sky-100 px-2.5 py-1
                                   text-[11px] font-medium text-sky-700
                                   dark:bg-sky-950/60 dark:text-sky-300"
                        >
                            <i
                                data-lucide="files"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Ver pases
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-blue-100 px-2.5 py-1
                                   text-[11px] font-medium text-blue-700
                                   dark:bg-blue-950/60 dark:text-blue-300"
                        >
                            <i
                                data-lucide="badge-check"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Aprobar o rechazar
                        </span>

                    </div>

                </div>

            </div>
        </a>

        {{-- Administración de solicitudes --}}

        <a
            href="{{ route('admin.solicitudes') }}"
            class="group block h-full"
        >
            <div
                class="relative h-full overflow-hidden rounded-2xl
                       border border-border bg-card p-5 shadow-sm
                       transition-all duration-300
                       hover:border-emerald-200
                       hover:shadow-lg hover:shadow-emerald-500/10
                       motion-safe:hover:-translate-y-1
                       dark:hover:border-emerald-800"
            >
                <div
                    class="pointer-events-none absolute inset-0
                           bg-gradient-to-br from-emerald-50/90
                           via-transparent to-teal-50/80
                           opacity-40 transition-opacity duration-300
                           group-hover:opacity-100
                           dark:from-emerald-950/30
                           dark:to-teal-950/20"
                ></div>

                <div class="relative z-10 flex h-full flex-col">

                    <div class="mb-5 flex items-start justify-between">

                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl bg-emerald-100
                                   transition-all duration-300
                                   group-hover:bg-emerald-200
                                   motion-safe:group-hover:scale-105
                                   dark:bg-emerald-950/60
                                   dark:group-hover:bg-emerald-900/70"
                        >
                            <i
                                data-lucide="clipboard-list"
                                stroke-width="1.8"
                                class="h-5 w-5 text-emerald-600
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110
                                       dark:text-emerald-400"
                            ></i>
                        </div>

                        <i
                            data-lucide="arrow-right"
                            stroke-width="1.8"
                            class="h-4 w-4 -translate-x-1
                                   text-emerald-500 opacity-0
                                   transition-all duration-300
                                   group-hover:opacity-100
                                   motion-safe:group-hover:translate-x-1"
                        ></i>

                    </div>

                    <h3
                        class="mb-2 text-sm font-semibold text-foreground"
                    >
                        Administración de solicitudes
                    </h3>

                    <p
                        class="text-xs leading-relaxed
                               text-muted-foreground"
                    >
                        Consulta los requerimientos registrados y
                        actualiza su estado de seguimiento.
                    </p>

                    <div class="mt-auto flex flex-wrap gap-2 pt-5">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-emerald-100 px-2.5 py-1
                                   text-[11px] font-medium text-emerald-700
                                   dark:bg-emerald-950/60
                                   dark:text-emerald-300"
                        >
                            <i
                                data-lucide="inbox"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Ver solicitudes
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-teal-100 px-2.5 py-1
                                   text-[11px] font-medium text-teal-700
                                   dark:bg-teal-950/60
                                   dark:text-teal-300"
                        >
                            <i
                                data-lucide="circle-check"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Actualizar estados
                        </span>

                    </div>

                </div>

            </div>
        </a>

        {{-- Administración de incidencias --}}

        <a
            href="{{ route('admin.incidencias') }}"
            class="group block h-full"
        >
            <div
                class="relative h-full overflow-hidden rounded-2xl
                       border border-border bg-card p-5 shadow-sm
                       transition-all duration-300
                       hover:border-violet-200
                       hover:shadow-lg hover:shadow-violet-500/10
                       motion-safe:hover:-translate-y-1
                       dark:hover:border-violet-800"
            >
                <div
                    class="pointer-events-none absolute inset-0
                           bg-gradient-to-br from-violet-50/90
                           via-transparent to-indigo-50/80
                           opacity-40 transition-opacity duration-300
                           group-hover:opacity-100
                           dark:from-violet-950/30
                           dark:to-indigo-950/20"
                ></div>

                <div class="relative z-10 flex h-full flex-col">

                    <div class="mb-5 flex items-start justify-between">

                        <div
                            class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-xl bg-violet-100
                                   transition-all duration-300
                                   group-hover:bg-violet-200
                                   motion-safe:group-hover:scale-105
                                   dark:bg-violet-950/60
                                   dark:group-hover:bg-violet-900/70"
                        >
                            <i
                                data-lucide="triangle-alert"
                                stroke-width="1.8"
                                class="h-5 w-5 text-violet-600
                                       transition-transform duration-300
                                       motion-safe:group-hover:scale-110
                                       dark:text-violet-400"
                            ></i>
                        </div>

                        <i
                            data-lucide="arrow-right"
                            stroke-width="1.8"
                            class="h-4 w-4 -translate-x-1
                                   text-violet-500 opacity-0
                                   transition-all duration-300
                                   group-hover:opacity-100
                                   motion-safe:group-hover:translate-x-1"
                        ></i>

                    </div>

                    <h3
                        class="mb-2 text-sm font-semibold text-foreground"
                    >
                        Administración de incidencias
                    </h3>

                    <p
                        class="text-xs leading-relaxed
                               text-muted-foreground"
                    >
                        Consulta los reportes, establece su prioridad
                        y actualiza el estado de atención.
                    </p>

                    <div class="mt-auto flex flex-wrap gap-2 pt-5">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-violet-100 px-2.5 py-1
                                   text-[11px] font-medium text-violet-700
                                   dark:bg-violet-950/60
                                   dark:text-violet-300"
                        >
                            <i
                                data-lucide="inbox"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Ver incidencias
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full bg-indigo-100 px-2.5 py-1
                                   text-[11px] font-medium text-indigo-700
                                   dark:bg-indigo-950/60
                                   dark:text-indigo-300"
                        >
                            <i
                                data-lucide="activity"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Gestionar atención
                        </span>

                    </div>

                </div>

            </div>
        </a>

    </div>

</section>

        {{-- Información --}}

        <section class="mt-10">

            <div
                class="group/info relative overflow-hidden rounded-2xl
                       border border-primary/10
                       bg-gradient-to-br from-primary/[0.05] via-white
                       to-blue-50/50 p-5 shadow-sm
                       transition-all duration-300
                       hover:border-primary/20 hover:shadow-md
                       motion-safe:hover:-translate-y-0.5
                       dark:border-blue-900/70
                       dark:via-slate-900 dark:to-blue-950/20
                       dark:hover:border-blue-800/80"
            >
                <span
                    class="pointer-events-none absolute -right-10 -top-12
                           h-32 w-32 rounded-full bg-primary/10 blur-3xl
                           transition-all duration-500
                           group-hover/info:bg-primary/20
                           motion-safe:group-hover/info:scale-125"
                ></span>

                <div class="relative flex items-start gap-3.5">

                    <div
                        class="flex h-9 w-9 shrink-0 items-center
                               justify-center rounded-lg bg-primary/10
                               text-primary transition-all duration-300
                               group-hover/info:bg-primary/15
                               motion-safe:group-hover/info:scale-105"
                    >
                        <i
                            data-lucide="info"
                            stroke-width="1.8"
                            class="h-4 w-4 shrink-0
                                   transition-transform duration-300
                                   motion-safe:group-hover/info:scale-110"
                        ></i>
                    </div>

                    <div class="min-w-0 pt-0.5">

                        <h3 class="text-sm font-semibold text-foreground">
                            Acceso administrativo
                        </h3>

                        <p
                            class="mt-1 text-sm leading-relaxed
                                   text-muted-foreground"
                        >
                            Los cambios realizados en usuarios, turnos,
                            solicitudes, incidencias, pases y avisos pueden
                            afectar directamente el acceso, seguimiento y la
                            información mostrada dentro del portal.
                        </p>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection
