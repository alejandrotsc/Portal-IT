@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-6xl mx-auto px-6 py-10">


        {{-- Encabezado --}}

        <section class="mb-10">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <span class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 rounded-full border border-primary/10 bg-primary/[0.06] text-primary text-xs font-semibold">

                        <i
                            data-lucide="shield-check"
                            stroke-width="1.8"
                            class="w-3.5 h-3.5 shrink-0">
                        </i>

                        Panel administrativo

                    </span>

                    <h1 class="text-2xl sm:text-3xl font-semibold text-foreground tracking-tight">

                        Administración del Portal TI

                    </h1>

                    <p class="text-sm text-muted-foreground mt-2 max-w-2xl leading-relaxed">

                        Administra usuarios, avisos informativos y solicitudes registradas en el portal.

                    </p>

                </div>


                <div class="inline-flex items-center gap-2 text-xs font-medium text-muted-foreground">

                    <span class="relative flex w-2.5 h-2.5 shrink-0">

                        <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>

                        <span class="relative inline-flex w-2.5 h-2.5 rounded-full bg-emerald-500"></span>

                    </span>

                    <span>
                        Portal disponible
                    </span>

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
            Información actual de usuarios, solicitudes y avisos.
        </p>

    </div>


    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">


        {{-- Usuarios registrados --}}

        <div class="group relative overflow-hidden rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50 via-white to-indigo-50/60 p-5 shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10 motion-safe:hover:-translate-y-1">

            {{-- Decoración --}}

            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-400/10 transition-all duration-500 group-hover:bg-blue-400/20 motion-safe:group-hover:scale-150">
            </div>


            <div class="relative mb-5 flex items-center justify-between">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 transition-all duration-300 group-hover:bg-blue-100 motion-safe:group-hover:scale-105">

                    <i
                        data-lucide="users"
                        stroke-width="1.8"
                        class="h-5 w-5 shrink-0 transition-transform duration-300 motion-safe:group-hover:scale-110">
                    </i>

                </div>


                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-700">

                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-500">
                    </span>

                    Total

                </span>

            </div>


            <p class="relative text-2xl font-semibold leading-none text-foreground">

                {{ $totalUsuarios ?? 0 }}

            </p>

            <p class="relative mt-2 text-sm text-muted-foreground">

                Usuarios registrados

            </p>

        </div>



        {{-- Usuarios conectados --}}

        <div class="group relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50 via-white to-teal-50/50 p-5 shadow-sm transition-all duration-300 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10 motion-safe:hover:-translate-y-1">

            {{-- Decoración --}}

            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-400/10 transition-all duration-500 group-hover:bg-emerald-400/20 motion-safe:group-hover:scale-150">
            </div>


            <div class="relative mb-5 flex items-center justify-between">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 transition-all duration-300 group-hover:bg-emerald-100 motion-safe:group-hover:scale-105">

                    <i
                        data-lucide="user-check"
                        stroke-width="1.8"
                        class="h-5 w-5 shrink-0 transition-transform duration-300 motion-safe:group-hover:scale-110">
                    </i>

                </div>


                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-700">

                    <span class="relative flex h-1.5 w-1.5 shrink-0">

                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-50">
                        </span>

                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500">
                        </span>

                    </span>

                    En línea

                </span>

            </div>


            <p class="relative text-2xl font-semibold leading-none text-foreground">

                {{ $usuariosConectados ?? 0 }}

            </p>

            <p class="relative mt-2 text-sm text-muted-foreground">

                Usuarios activos en el portal

            </p>

        </div>



        {{-- Solicitudes pendientes --}}

        <div class="group relative overflow-hidden rounded-2xl border border-cyan-200/60 bg-gradient-to-br from-cyan-50 via-white to-blue-50/60 p-5 shadow-sm transition-all duration-300 hover:border-cyan-300 hover:shadow-lg hover:shadow-cyan-500/10 motion-safe:hover:-translate-y-1">

            {{-- Decoración --}}

            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-cyan-400/10 transition-all duration-500 group-hover:bg-cyan-400/20 motion-safe:group-hover:scale-150">
            </div>


            <div class="relative mb-5 flex items-center justify-between">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-600 transition-all duration-300 group-hover:bg-cyan-100 motion-safe:group-hover:scale-105">

                    <i
                        data-lucide="clipboard-clock"
                        stroke-width="1.8"
                        class="h-5 w-5 shrink-0 transition-transform duration-300 motion-safe:group-hover:scale-110">
                    </i>

                </div>


                <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-700">

                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-cyan-500">
                    </span>

                    Pendientes

                </span>

            </div>


            <p class="relative text-2xl font-semibold leading-none text-foreground">

                {{ $solicitudesPendientes ?? 0 }}

            </p>

            <p class="relative mt-2 text-sm text-muted-foreground">

                Solicitudes por revisar

            </p>

        </div>



        {{-- Avisos activos --}}

        <div class="group relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50 via-white to-orange-50/50 p-5 shadow-sm transition-all duration-300 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10 motion-safe:hover:-translate-y-1">

            {{-- Decoración --}}

            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-amber-400/10 transition-all duration-500 group-hover:bg-amber-400/20 motion-safe:group-hover:scale-150">
            </div>


            <div class="relative mb-5 flex items-center justify-between">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 transition-all duration-300 group-hover:bg-amber-100 motion-safe:group-hover:scale-105">

                    <i
                        data-lucide="megaphone"
                        stroke-width="1.8"
                        class="h-5 w-5 shrink-0 transition-transform duration-300 motion-safe:group-hover:scale-110">
                    </i>

                </div>


                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-700">

                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500">
                    </span>

                    Publicados

                </span>

            </div>


            <p class="relative text-2xl font-semibold leading-none text-foreground">

                {{ $avisosActivos ?? 0 }}

            </p>

            <p class="relative mt-2 text-sm text-muted-foreground">

                Avisos visibles en el portal

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

                <p class="text-sm text-muted-foreground mt-1">

                    Selecciona el apartado que deseas gestionar.

                </p>

            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">


                {{-- Administración de usuarios --}}

                <a
                    href="{{ route('usuarios.index') }}"
                    class="group block h-full">

                    <div class="relative h-full overflow-hidden bg-card rounded-2xl border border-border p-5 shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-500/10 motion-safe:hover:-translate-y-1">


                        {{-- Fondo degradado --}}

                        <div
                            class="absolute inset-0 pointer-events-none opacity-40 group-hover:opacity-100 transition-opacity duration-300"
                            style="
                                background: linear-gradient(
                                    135deg,
                                    rgba(239, 246, 255, 0.95) 0%,
                                    rgba(255, 255, 255, 0.20) 55%,
                                    rgba(238, 242, 255, 0.90) 100%
                                );
                            ">
                        </div>


                        <div class="relative z-10 h-full flex flex-col">

                            <div class="flex items-start justify-between mb-5">

                                <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-blue-100 transition-all duration-300 group-hover:bg-blue-200 motion-safe:group-hover:scale-105">

                                    <i
                                        data-lucide="users"
                                        stroke-width="1.8"
                                        class="w-5 h-5 text-blue-600 transition-transform duration-300 motion-safe:group-hover:scale-110">
                                    </i>

                                </div>


                                <i
                                    data-lucide="arrow-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4 text-blue-500 opacity-0 -translate-x-1 transition-all duration-300 group-hover:opacity-100 motion-safe:group-hover:translate-x-1">
                                </i>

                            </div>


                            <h3 class="text-sm font-semibold text-foreground mb-2">

                                Administración de usuarios

                            </h3>

                            <p class="text-xs text-muted-foreground leading-relaxed">

                                Consulta usuarios, asigna roles y controla el acceso al portal.

                            </p>


                            <div class="mt-auto pt-5 flex flex-wrap gap-2">

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-medium text-blue-700">

                                    <i
                                        data-lucide="list"
                                        stroke-width="1.8"
                                        class="w-3 h-3 shrink-0">
                                    </i>

                                    Ver usuarios

                                </span>

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100 px-2.5 py-1 text-[11px] font-medium text-indigo-700">

                                    <i
                                        data-lucide="shield"
                                        stroke-width="1.8"
                                        class="w-3 h-3 shrink-0">
                                    </i>

                                    Roles y accesos

                                </span>

                            </div>

                        </div>

                    </div>

                </a>



                {{-- Administración de solicitudes --}}

                <a
                    href="{{ route('admin.solicitudes') }}"
                    class="group block h-full">

                    <div class="relative h-full overflow-hidden bg-card rounded-2xl border border-border p-5 shadow-sm transition-all duration-300 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-500/10 motion-safe:hover:-translate-y-1">


                        {{-- Fondo degradado --}}

                        <div
                            class="absolute inset-0 pointer-events-none opacity-40 group-hover:opacity-100 transition-opacity duration-300"
                            style="
                                background: linear-gradient(
                                    135deg,
                                    rgba(236, 253, 245, 0.95) 0%,
                                    rgba(255, 255, 255, 0.20) 55%,
                                    rgba(240, 253, 250, 0.90) 100%
                                );
                            ">
                        </div>


                        <div class="relative z-10 h-full flex flex-col">

                            <div class="flex items-start justify-between mb-5">

                                <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 motion-safe:group-hover:scale-105">

                                    <i
                                        data-lucide="clipboard-list"
                                        stroke-width="1.8"
                                        class="w-5 h-5 text-emerald-600 transition-transform duration-300 motion-safe:group-hover:scale-110">
                                    </i>

                                </div>


                                <i
                                    data-lucide="arrow-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4 text-emerald-500 opacity-0 -translate-x-1 transition-all duration-300 group-hover:opacity-100 motion-safe:group-hover:translate-x-1">
                                </i>

                            </div>


                            <h3 class="text-sm font-semibold text-foreground mb-2">

                                Administración de solicitudes

                            </h3>

                            <p class="text-xs text-muted-foreground leading-relaxed">

                                Consulta los requerimientos registrados y actualiza su estado de seguimiento.

                            </p>


                            <div class="mt-auto pt-5 flex flex-wrap gap-2">

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-medium text-emerald-700">

                                    <i
                                        data-lucide="inbox"
                                        stroke-width="1.8"
                                        class="w-3 h-3 shrink-0">
                                    </i>

                                    Ver solicitudes

                                </span>

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-100 px-2.5 py-1 text-[11px] font-medium text-teal-700">

                                    <i
                                        data-lucide="circle-check"
                                        stroke-width="1.8"
                                        class="w-3 h-3 shrink-0">
                                    </i>

                                    Actualizar estados

                                </span>

                            </div>

                        </div>

                    </div>

                </a>



                {{-- Avisos TI --}}

                <a
                    href="{{ route('avisos.index') }}"
                    class="group block h-full">

                    <div class="relative h-full overflow-hidden bg-card rounded-2xl border border-border p-5 shadow-sm transition-all duration-300 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-500/10 motion-safe:hover:-translate-y-1">


                        {{-- Fondo degradado --}}

                        <div
                            class="absolute inset-0 pointer-events-none opacity-40 group-hover:opacity-100 transition-opacity duration-300"
                            style="
                                background: linear-gradient(
                                    135deg,
                                    rgba(255, 251, 235, 0.95) 0%,
                                    rgba(255, 255, 255, 0.20) 55%,
                                    rgba(255, 247, 237, 0.90) 100%
                                );
                            ">
                        </div>


                        <div class="relative z-10 h-full flex flex-col">

                            <div class="flex items-start justify-between mb-5">

                                <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-amber-100 transition-all duration-300 group-hover:bg-amber-200 motion-safe:group-hover:scale-105">

                                    <i
                                        data-lucide="megaphone"
                                        stroke-width="1.8"
                                        class="w-5 h-5 text-amber-600 transition-transform duration-300 motion-safe:group-hover:scale-110">
                                    </i>

                                </div>


                                <i
                                    data-lucide="arrow-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4 text-amber-500 opacity-0 -translate-x-1 transition-all duration-300 group-hover:opacity-100 motion-safe:group-hover:translate-x-1">
                                </i>

                            </div>


                            <h3 class="text-sm font-semibold text-foreground mb-2">

                                Avisos de TI

                            </h3>

                            <p class="text-xs text-muted-foreground leading-relaxed">

                                Publica y administra los avisos mostrados en la cinta informativa.

                            </p>


                            <div class="mt-auto pt-5 flex flex-wrap gap-2">

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-medium text-amber-700">

                                    <i
                                        data-lucide="plus-circle"
                                        stroke-width="1.8"
                                        class="w-3 h-3 shrink-0">
                                    </i>

                                    Crear aviso

                                </span>

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-2.5 py-1 text-[11px] font-medium text-orange-700">

                                    <i
                                        data-lucide="calendar-clock"
                                        stroke-width="1.8"
                                        class="w-3 h-3 shrink-0">
                                    </i>

                                    Controlar vigencia

                                </span>

                            </div>

                        </div>

                    </div>

                </a>

            </div>

        </section>



        {{-- Información --}}

        <section class="mt-10">

            <div class="group/info relative overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/50 p-5 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md motion-safe:hover:-translate-y-0.5">

                <span class="absolute -right-10 -top-12 w-32 h-32 rounded-full bg-primary/10 blur-3xl pointer-events-none transition-all duration-500 group-hover/info:bg-primary/20 motion-safe:group-hover/info:scale-125"></span>

                <div class="relative flex items-start gap-3.5">

                    <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 group-hover/info:bg-primary/15 motion-safe:group-hover/info:scale-105">

                        <i
                            data-lucide="info"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 transition-transform duration-300 motion-safe:group-hover/info:scale-110">
                        </i>

                    </div>

                    <div class="min-w-0 pt-0.5">

                        <h3 class="text-sm font-semibold text-foreground">

                            Acceso administrativo

                        </h3>

                        <p class="text-sm text-muted-foreground mt-1 leading-relaxed">

                            Los cambios realizados en usuarios, solicitudes y avisos pueden afectar directamente el acceso, seguimiento y la información mostrada dentro del portal.

                        </p>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection