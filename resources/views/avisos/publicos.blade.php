@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-10">


        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <span class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 rounded-full border border-primary/10 bg-primary/[0.06] text-primary text-xs font-semibold">

                        <i
                            data-lucide="megaphone"
                            stroke-width="1.8"
                            class="w-3.5 h-3.5 shrink-0">
                        </i>

                        Comunicaciones TI

                    </span>

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">

                        Avisos del Portal TI

                    </h1>

                    <p class="text-sm text-muted-foreground mt-2 max-w-2xl leading-relaxed">

                        Consulta las comunicaciones y novedades publicadas por el equipo de Tecnología de la Información.

                    </p>

                </div>



                {{-- Acceso administrativo --}}

                @if(
                    auth()->user()
                        ->rol
                        ?->nombre === 'Administrador'
                )

                    <a
                        href="{{ route('avisos.index') }}"
                        class="group/admin inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-primary/10 bg-primary/[0.06] text-primary text-sm font-semibold shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">

                        <i
                            data-lucide="settings-2"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover/admin:rotate-90">
                        </i>

                        <span>
                            Administrar avisos
                        </span>

                    </a>

                @endif

            </div>

        </section>



        {{-- Cantidad de avisos --}}

        <section class="mb-6">

            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-primary/10 bg-primary/[0.04] text-xs text-muted-foreground">

                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5 shrink-0 text-primary">
                </i>

                <span>

                    {{ $avisos->total() }}

                    {{ $avisos->total() === 1
                        ? 'aviso vigente'
                        : 'avisos vigentes'
                    }}

                </span>

            </div>

        </section>



        {{-- Listado de avisos --}}

        <section class="space-y-4">

            @forelse($avisos as $aviso)

                @php

                    $posicionAviso =
                        (
                            $avisos->firstItem()
                            + $loop->index
                            - 1
                        ) % 4;

                @endphp


                <article
                    @class([
                        'group relative overflow-hidden rounded-2xl border border-l-4 bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-lg motion-safe:hover:-translate-y-1',

                        'border-blue-200/70 border-l-blue-500 bg-gradient-to-r from-blue-50/50 via-white to-indigo-50/30 hover:border-blue-300 hover:shadow-blue-500/10' =>
                            $posicionAviso === 0,

                        'border-amber-200/70 border-l-amber-500 bg-gradient-to-r from-amber-50/50 via-white to-orange-50/30 hover:border-amber-300 hover:shadow-amber-500/10' =>
                            $posicionAviso === 1,

                        'border-emerald-200/70 border-l-emerald-500 bg-gradient-to-r from-emerald-50/50 via-white to-teal-50/30 hover:border-emerald-300 hover:shadow-emerald-500/10' =>
                            $posicionAviso === 2,

                        'border-violet-200/70 border-l-violet-500 bg-gradient-to-r from-violet-50/50 via-white to-purple-50/30 hover:border-violet-300 hover:shadow-violet-500/10' =>
                            $posicionAviso === 3,
                    ])>


                    {{-- Decoración suave --}}

                    <span
                        @class([
                            'absolute -right-12 -top-14 w-36 h-36 rounded-full blur-3xl pointer-events-none transition-all duration-500 motion-safe:group-hover:scale-150',

                            'bg-blue-500/10 group-hover:bg-blue-500/20' =>
                                $posicionAviso === 0,

                            'bg-amber-500/10 group-hover:bg-amber-500/20' =>
                                $posicionAviso === 1,

                            'bg-emerald-500/10 group-hover:bg-emerald-500/20' =>
                                $posicionAviso === 2,

                            'bg-violet-500/10 group-hover:bg-violet-500/20' =>
                                $posicionAviso === 3,
                        ])>
                    </span>


                    <div class="relative flex items-start gap-4">


                        {{-- Icono --}}

                        <div
                            @class([
                                'flex items-center justify-center w-11 h-11 shrink-0 rounded-xl transition-all duration-300 motion-safe:group-hover:scale-105',

                                'bg-blue-500/10 text-blue-600 group-hover:bg-blue-100' =>
                                    $posicionAviso === 0,

                                'bg-amber-500/10 text-amber-600 group-hover:bg-amber-100' =>
                                    $posicionAviso === 1,

                                'bg-emerald-500/10 text-emerald-600 group-hover:bg-emerald-100' =>
                                    $posicionAviso === 2,

                                'bg-violet-500/10 text-violet-600 group-hover:bg-violet-100' =>
                                    $posicionAviso === 3,
                            ])>

                            <i
                                data-lucide="megaphone"
                                stroke-width="1.8"
                                class="w-5 h-5 shrink-0 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>



                        {{-- Contenido --}}

                        <div class="min-w-0 flex-1">


                            {{-- Título --}}

                            <h2
                                @class([
                                    'text-sm font-semibold text-foreground leading-relaxed transition-colors duration-200',

                                    'group-hover:text-blue-800' =>
                                        $posicionAviso === 0,

                                    'group-hover:text-amber-800' =>
                                        $posicionAviso === 1,

                                    'group-hover:text-emerald-800' =>
                                        $posicionAviso === 2,

                                    'group-hover:text-violet-800' =>
                                        $posicionAviso === 3,
                                ])>

                                {{ $aviso->titulo }}

                            </h2>


                            {{-- Mensaje --}}

                            <p class="text-sm text-muted-foreground mt-1.5 leading-relaxed">

                                {{ $aviso->mensaje }}

                            </p>



                            {{-- Información inferior --}}

                            <div class="flex flex-wrap items-center gap-3 mt-4">


                                {{-- Tiempo desde su publicación --}}

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



                                {{-- Tipo --}}

                                <span
                                    @class([
                                        'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium transition-colors duration-200',

                                        'bg-blue-500/10 text-blue-700 group-hover:bg-blue-100' =>
                                            $posicionAviso === 0,

                                        'bg-amber-500/10 text-amber-700 group-hover:bg-amber-100' =>
                                            $posicionAviso === 1,

                                        'bg-emerald-500/10 text-emerald-700 group-hover:bg-emerald-100' =>
                                            $posicionAviso === 2,

                                        'bg-violet-500/10 text-violet-700 group-hover:bg-violet-100' =>
                                            $posicionAviso === 3,
                                    ])>

                                    <i
                                        data-lucide="megaphone"
                                        stroke-width="1.8"
                                        class="w-3 h-3 shrink-0">
                                    </i>

                                    Aviso TI

                                </span>

                            </div>

                        </div>

                    </div>

                </article>

            @empty

                {{-- Estado vacío --}}

                <div class="group/empty relative overflow-hidden rounded-2xl border border-dashed border-primary/20 bg-gradient-to-br from-primary/[0.035] via-white to-blue-50/40 px-6 py-16 text-center shadow-sm transition-all duration-300 hover:border-primary/30 hover:shadow-md">

                    <span class="absolute -right-12 -top-14 w-36 h-36 rounded-full bg-primary/5 blur-3xl pointer-events-none transition-all duration-500 motion-safe:group-hover/empty:scale-150 group-hover/empty:bg-primary/10"></span>


                    <div class="relative">

                        <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-primary/10 text-primary transition-all duration-300 motion-safe:group-hover/empty:scale-105 group-hover/empty:bg-primary/15">

                            <i
                                data-lucide="megaphone-off"
                                stroke-width="1.8"
                                class="w-5 h-5 shrink-0 transition-transform duration-300 motion-safe:group-hover/empty:scale-110">
                            </i>

                        </div>

                        <h2 class="text-sm font-semibold text-foreground mt-4">

                            No hay avisos vigentes

                        </h2>

                        <p class="text-sm text-muted-foreground mt-1">

                            Actualmente no hay comunicaciones activas por parte del equipo de TI.

                        </p>

                    </div>

                </div>

            @endforelse

        </section>



        {{-- Paginación personalizada --}}

        @if($avisos->hasPages())

            @php

                $paginaActual = $avisos->currentPage();
                $ultimaPagina = $avisos->lastPage();

                $paginaInicial = max(
                    1,
                    $paginaActual - 2
                );

                $paginaFinal = min(
                    $ultimaPagina,
                    $paginaActual + 2
                );

                if ($paginaActual <= 3) {
                    $paginaFinal = min(
                        5,
                        $ultimaPagina
                    );
                }

                if (
                    $paginaActual
                    >=
                    $ultimaPagina - 2
                ) {
                    $paginaInicial = max(
                        1,
                        $ultimaPagina - 4
                    );
                }

            @endphp


            <div class="flex flex-col gap-4 mt-6 px-5 py-4 rounded-2xl border border-border bg-blue-50/20 shadow-sm sm:flex-row sm:items-center sm:justify-between">


                {{-- Información --}}

                <p class="text-xs text-muted-foreground">

                    Mostrando

                    <span class="font-semibold text-foreground">
                        {{ $avisos->firstItem() }}
                    </span>

                    a

                    <span class="font-semibold text-foreground">
                        {{ $avisos->lastItem() }}
                    </span>

                    de

                    <span class="font-semibold text-foreground">
                        {{ $avisos->total() }}
                    </span>

                    avisos

                </p>



                {{-- Controles --}}

                <nav
                    aria-label="Paginación de avisos"
                    class="flex flex-wrap items-center gap-1">


                    {{-- Anterior --}}

                    @if($avisos->onFirstPage())

                        <span
                            aria-disabled="true"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed">

                            <i
                                data-lucide="chevron-left"
                                stroke-width="1.8"
                                class="w-4 h-4">
                            </i>

                        </span>

                    @else

                        <a
                            href="{{ $avisos->previousPageUrl() }}"
                            rel="prev"
                            aria-label="Página anterior"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5">

                            <i
                                data-lucide="chevron-left"
                                stroke-width="1.8"
                                class="w-4 h-4">
                            </i>

                        </a>

                    @endif



                    {{-- Primera página --}}

                    @if($paginaInicial > 1)

                        <a
                            href="{{ $avisos->url(1) }}"
                            class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5">

                            1

                        </a>

                        @if($paginaInicial > 2)

                            <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">
                                …
                            </span>

                        @endif

                    @endif



                    {{-- Páginas --}}

                    @for(
                        $pagina = $paginaInicial;
                        $pagina <= $paginaFinal;
                        $pagina++
                    )

                        @if($pagina === $paginaActual)

                            <span
                                aria-current="page"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-primary bg-primary text-xs font-semibold text-white shadow-sm">

                                {{ $pagina }}

                            </span>

                        @else

                            <a
                                href="{{ $avisos->url($pagina) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5">

                                {{ $pagina }}

                            </a>

                        @endif

                    @endfor



                    {{-- Última página --}}

                    @if($paginaFinal < $ultimaPagina)

                        @if(
                            $paginaFinal
                            <
                            $ultimaPagina - 1
                        )

                            <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">
                                …
                            </span>

                        @endif


                        <a
                            href="{{ $avisos->url($ultimaPagina) }}"
                            class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5">

                            {{ $ultimaPagina }}

                        </a>

                    @endif



                    {{-- Siguiente --}}

                    @if($avisos->hasMorePages())

                        <a
                            href="{{ $avisos->nextPageUrl() }}"
                            rel="next"
                            aria-label="Página siguiente"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5">

                            <i
                                data-lucide="chevron-right"
                                stroke-width="1.8"
                                class="w-4 h-4">
                            </i>

                        </a>

                    @else

                        <span
                            aria-disabled="true"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed">

                            <i
                                data-lucide="chevron-right"
                                stroke-width="1.8"
                                class="w-4 h-4">
                            </i>

                        </span>

                    @endif

                </nav>

            </div>

        @endif

    </main>

</div>

@endsection