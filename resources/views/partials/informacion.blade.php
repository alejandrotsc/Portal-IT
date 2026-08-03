{{-- Información importante + Soporte --}}

@php

    $avisosImportantes = (
        $avisosTicker
        ?? collect()
    )->take(2);

@endphp


<section class="grid grid-cols-1 lg:grid-cols-3 gap-6">


    {{-- Información importante --}}

    <div class="lg:col-span-2">

        <div class="flex items-center justify-between gap-4 mb-4">

            <h2 class="text-sm font-semibold text-foreground uppercase tracking-widest">

                Información importante

            </h2>


            <a
                href="{{ route('avisos.publicos') }}"
                class="group/all inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-primary transition-all duration-200 hover:bg-primary/5 hover:text-primary/80 motion-safe:hover:-translate-y-0.5">

                <span>
                    Ver todos los avisos
                </span>

                <i
                    data-lucide="chevron-right"
                    stroke-width="1.8"
                    class="w-3 h-3 shrink-0 transition-transform duration-200 group-hover/all:translate-x-0.5">
                </i>

            </a>

        </div>



        {{-- Avisos --}}

        <div class="space-y-3">

            @forelse($avisosImportantes as $aviso)

                @php

                    $colorAviso =
                        $loop->index % 4;

                @endphp


                <article
                    @class([
                        'group/notice relative overflow-hidden rounded-xl border border-l-4 bg-card px-5 py-4 shadow-sm transition-all duration-300 hover:shadow-lg motion-safe:hover:-translate-y-1',

                        'border-blue-200/70 border-l-blue-500 bg-gradient-to-r from-blue-50/50 via-white to-indigo-50/30 hover:border-blue-300 hover:shadow-blue-500/10 dark:border-blue-800/70 dark:border-l-blue-500 dark:from-blue-950/30 dark:via-slate-900 dark:to-indigo-950/20 dark:hover:border-blue-700 dark:hover:shadow-black/20' =>
                            $colorAviso === 0,

                        'border-amber-200/70 border-l-amber-500 bg-gradient-to-r from-amber-50/50 via-white to-orange-50/30 hover:border-amber-300 hover:shadow-amber-500/10 dark:border-amber-800/70 dark:border-l-amber-500 dark:from-amber-950/30 dark:via-slate-900 dark:to-orange-950/20 dark:hover:border-amber-700 dark:hover:shadow-black/20' =>
                            $colorAviso === 1,

                        'border-emerald-200/70 border-l-emerald-500 bg-gradient-to-r from-emerald-50/50 via-white to-teal-50/30 hover:border-emerald-300 hover:shadow-emerald-500/10 dark:border-emerald-800/70 dark:border-l-emerald-500 dark:from-emerald-950/30 dark:via-slate-900 dark:to-teal-950/20 dark:hover:border-emerald-700 dark:hover:shadow-black/20' =>
                            $colorAviso === 2,

                        'border-violet-200/70 border-l-violet-500 bg-gradient-to-r from-violet-50/50 via-white to-purple-50/30 hover:border-violet-300 hover:shadow-violet-500/10 dark:border-violet-800/70 dark:border-l-violet-500 dark:from-violet-950/30 dark:via-slate-900 dark:to-purple-950/20 dark:hover:border-violet-700 dark:hover:shadow-black/20' =>
                            $colorAviso === 3,
                    ])>


                    {{-- Decoración suave --}}

                    <span
                        @class([
                            'absolute -right-10 -top-12 w-28 h-28 rounded-full blur-3xl pointer-events-none transition-all duration-500 motion-safe:group-hover/notice:scale-150',

                            'bg-blue-500/10 group-hover/notice:bg-blue-500/20' =>
                                $colorAviso === 0,

                            'bg-amber-500/10 group-hover/notice:bg-amber-500/20' =>
                                $colorAviso === 1,

                            'bg-emerald-500/10 group-hover/notice:bg-emerald-500/20' =>
                                $colorAviso === 2,

                            'bg-violet-500/10 group-hover/notice:bg-violet-500/20' =>
                                $colorAviso === 3,
                        ])>
                    </span>


                    <div class="relative">


                        {{-- Título --}}

                        <div class="flex items-start gap-2 mb-1">

                            <span
                                @class([
                                    'w-2 h-2 shrink-0 rounded-full mt-1.5 transition-transform duration-300 motion-safe:group-hover/notice:scale-125',

                                    'bg-blue-500' =>
                                        $colorAviso === 0,

                                    'bg-amber-500' =>
                                        $colorAviso === 1,

                                    'bg-emerald-500' =>
                                        $colorAviso === 2,

                                    'bg-violet-500' =>
                                        $colorAviso === 3,
                                ])>
                            </span>

                            <h3
                                @class([
                                    'text-sm font-semibold text-foreground leading-relaxed transition-colors duration-200',

                                    'group-hover/notice:text-blue-800 dark:group-hover/notice:text-blue-300' =>
                                        $colorAviso === 0,

                                    'group-hover/notice:text-amber-800 dark:group-hover/notice:text-amber-300' =>
                                        $colorAviso === 1,

                                    'group-hover/notice:text-emerald-800 dark:group-hover/notice:text-emerald-300' =>
                                        $colorAviso === 2,

                                    'group-hover/notice:text-violet-800 dark:group-hover/notice:text-violet-300' =>
                                        $colorAviso === 3,
                                ])>

                                {{ $aviso->titulo }}

                            </h3>

                        </div>



                        {{-- Mensaje --}}

                        <p class="text-xs text-muted-foreground leading-relaxed mb-3 pl-4">

                            {{ $aviso->mensaje }}

                        </p>



                        {{-- Información inferior --}}

                        <div class="flex flex-wrap items-center gap-3 pl-4">

                            <span class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">

                                <i
                                    data-lucide="clock-3"
                                    stroke-width="1.8"
                                    class="w-3.5 h-3.5 shrink-0">
                                </i>

                                {{ $aviso->created_at
                                    ?->locale('es')
                                    ->diffForHumans()
                                    ?? 'Recientemente' }}

                            </span>


                            <span
                                @class([
                                    'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium transition-colors duration-200',

                                    'bg-blue-500/10 text-blue-700 group-hover/notice:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-300 dark:group-hover/notice:bg-blue-950/70' =>
                                        $colorAviso === 0,

                                    'bg-amber-500/10 text-amber-700 group-hover/notice:bg-amber-100 dark:bg-amber-500/15 dark:text-amber-300 dark:group-hover/notice:bg-amber-950/70' =>
                                        $colorAviso === 1,

                                    'bg-emerald-500/10 text-emerald-700 group-hover/notice:bg-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-300 dark:group-hover/notice:bg-emerald-950/70' =>
                                        $colorAviso === 2,

                                    'bg-violet-500/10 text-violet-700 group-hover/notice:bg-violet-100 dark:bg-violet-500/15 dark:text-violet-300 dark:group-hover/notice:bg-violet-950/70' =>
                                        $colorAviso === 3,
                                ])>

                                <i
                                    data-lucide="megaphone"
                                    stroke-width="1.8"
                                    class="w-3 h-3 shrink-0 transition-transform duration-300 motion-safe:group-hover/notice:scale-110">
                                </i>

                                Aviso TI

                            </span>

                        </div>

                    </div>

                </article>

            @empty

                {{-- Sin avisos --}}

                <div class="group/empty relative overflow-hidden rounded-xl border border-dashed border-primary/20 bg-gradient-to-br from-primary/[0.035] via-white to-blue-50/40 dark:from-blue-950/25 dark:via-slate-900 dark:to-slate-900 px-6 py-10 text-center shadow-sm transition-all duration-300 hover:border-primary/30 hover:shadow-md dark:hover:border-primary/40 dark:hover:shadow-black/20">

                    <span class="absolute -right-10 -top-12 w-28 h-28 rounded-full bg-primary/5 blur-3xl pointer-events-none transition-all duration-500 motion-safe:group-hover/empty:scale-150 group-hover/empty:bg-primary/10"></span>


                    <div class="relative">

                        <div class="flex items-center justify-center w-11 h-11 mx-auto rounded-full bg-primary/10 text-primary transition-all duration-300 group-hover/empty:bg-primary/15 motion-safe:group-hover/empty:scale-105">

                            <i
                                data-lucide="info"
                                stroke-width="1.8"
                                class="w-5 h-5 shrink-0 transition-transform duration-300 motion-safe:group-hover/empty:scale-110">
                            </i>

                        </div>

                        <h3 class="text-sm font-semibold text-foreground mt-3">

                            No hay avisos importantes

                        </h3>

                        <p class="text-xs text-muted-foreground mt-1">

                            Actualmente no hay comunicaciones activas por parte del equipo de TI.

                        </p>

                    </div>

                </div>

            @endforelse

        </div>

    </div>


{{-- Soporte de turno --}}
<div>

    <div class="mb-4 flex items-center gap-2">
        <span class="h-2 w-2 rounded-full bg-blue-500"></span>

        <h2 class="text-sm font-semibold uppercase tracking-widest text-foreground">
            Soporte de turno
        </h2>
    </div>

    <div
        class="group/support-card relative overflow-hidden rounded-2xl
               border border-border bg-card shadow-sm
               transition-all duration-300
               hover:-translate-y-0.5 hover:border-primary/30
               hover:shadow-lg hover:shadow-primary/10
               dark:border-slate-700/70 dark:bg-slate-900/70
               dark:hover:border-blue-700/60 dark:hover:shadow-black/20"
    >
        {{-- Brillo decorativo --}}
        <span
            class="pointer-events-none absolute -right-12 -top-14
                   h-32 w-32 rounded-full bg-blue-500/10 blur-3xl
                   transition-transform duration-700
                   motion-safe:group-hover/support-card:scale-150"
        ></span>

        {{-- Agente del día --}}
        <div
            class="group/agent relative border-b border-border px-5 py-4
                   transition-colors duration-300
                   hover:bg-blue-50/50
                   dark:border-slate-700/70 dark:hover:bg-blue-950/20"
        >
            <div class="mb-3 flex items-center gap-2">
                <i
                    data-lucide="calendar"
                    stroke-width="1.8"
                    class="h-[13px] w-[13px] text-muted-foreground
                           transition-colors duration-200
                           group-hover/agent:text-blue-600
                           dark:group-hover/agent:text-blue-400"
                ></i>

                <span
                    class="text-xs font-medium uppercase tracking-wide
                           text-muted-foreground"
                >
                    Agente del día
                </span>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center
                           rounded-full bg-blue-100 text-sm font-semibold
                           text-blue-700 ring-4 ring-blue-500/5
                           transition-all duration-300
                           motion-safe:group-hover/agent:scale-110
                           group-hover/agent:bg-blue-200
                           group-hover/agent:ring-blue-500/10
                           dark:bg-blue-950/60 dark:text-blue-300
                           dark:group-hover/agent:bg-blue-900/70"
                >
                    RC
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-foreground">
                        Roberto Castillo
                    </p>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Soporte Nivel 1 · Turno día
                    </p>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5 shrink-0">
                    <span
                        class="absolute inline-flex h-full w-full rounded-full
                               bg-emerald-400 opacity-70
                               motion-safe:animate-ping"
                    ></span>

                    <span
                        class="relative inline-flex h-2.5 w-2.5 rounded-full
                               bg-emerald-500"
                    ></span>
                </span>

                <span
                    class="text-xs font-medium text-emerald-600
                           dark:text-emerald-400"
                >
                    Disponible ahora
                </span>

                <span class="ml-auto text-xs text-muted-foreground">
                    08:00 – 17:00
                </span>
            </div>
        </div>

        {{-- Fin de semana --}}
        <div
            class="group/weekend relative px-5 py-4
                   transition-colors duration-300
                   hover:bg-violet-50/50
                   dark:hover:bg-violet-950/20"
        >
            <div class="mb-3 flex items-center gap-2">
                <i
                    data-lucide="headphones"
                    stroke-width="1.8"
                    class="h-[13px] w-[13px] text-muted-foreground
                           transition-colors duration-200
                           group-hover/weekend:text-violet-600
                           dark:group-hover/weekend:text-violet-400"
                ></i>

                <span
                    class="text-xs font-medium uppercase tracking-wide
                           text-muted-foreground"
                >
                    Fin de semana
                </span>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center
                           rounded-full bg-violet-100 text-sm font-semibold
                           text-violet-700 ring-4 ring-violet-500/5
                           transition-all duration-300
                           motion-safe:group-hover/weekend:scale-110
                           group-hover/weekend:bg-violet-200
                           group-hover/weekend:ring-violet-500/10
                           dark:bg-violet-950/60 dark:text-violet-300
                           dark:group-hover/weekend:bg-violet-900/70"
                >
                    LP
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-foreground">
                        Laura Pérez
                    </p>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Soporte Nivel 2 · Guardia
                    </p>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-1.5">
                <i
                    data-lucide="wifi-off"
                    stroke-width="1.8"
                    class="h-3 w-3 text-muted-foreground"
                ></i>

                <span class="text-xs text-muted-foreground">
                    Sáb–Dom · 09:00 – 18:00
                </span>
            </div>
        </div>

        {{-- Contactar soporte --}}
        <div
            class="border-t border-border bg-muted/40 px-5 py-3
                   dark:border-slate-700/70 dark:bg-slate-950/25"
        >
            <button
                type="button"
                class="group/contact flex w-full items-center justify-center
                       gap-1.5 rounded-lg py-1 text-xs font-medium
                       text-primary transition-all duration-200
                       hover:gap-2.5 hover:text-blue-700
                       dark:text-blue-400 dark:hover:text-blue-300"
            >
                Contactar soporte

                <i
                    data-lucide="chevron-right"
                    stroke-width="1.8"
                    class="h-3 w-3 transition-transform duration-200
                           group-hover/contact:translate-x-1"
                ></i>
            </button>
        </div>
    </div>

</div>