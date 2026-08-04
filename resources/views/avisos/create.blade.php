@extends('layouts.app')

@section('title', 'Avisos TI')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-3xl mx-auto px-6 py-10">


        {{-- Navegación --}}

        <a
            href="{{ route('avisos.index') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/10 bg-primary/[0.06] text-sm font-medium text-primary shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] dark:border-blue-800/60 dark:bg-blue-950/30 dark:hover:border-blue-700/70 dark:hover:bg-blue-900/30">

            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5">
            </i>

            <span>
                Volver a avisos
            </span>

        </a>



        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex items-center gap-4">

                <div class="group flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:border-primary/20 hover:bg-primary/15 hover:shadow-md motion-safe:hover:-translate-y-0.5 dark:border-blue-800/60 dark:bg-blue-950/40 dark:hover:border-blue-700/70 dark:hover:bg-blue-900/40">

                    <i
                        data-lucide="megaphone"
                        stroke-width="1.8"
                        class="w-6 h-6 shrink-0 transition-transform duration-300 motion-safe:group-hover:scale-110">
                    </i>

                </div>

                <div class="min-w-0">

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">
                        Nuevo aviso
                    </h1>

                    <p class="text-sm text-muted-foreground mt-1.5 leading-relaxed">
                        Publica un mensaje en la cinta informativa del Portal TI.
                    </p>

                </div>

            </div>

        </section>



        {{-- Errores generales --}}

        @if($errors->has('aviso'))

            <div class="group/error mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800 shadow-sm transition-all duration-300 hover:border-red-300 hover:shadow-md dark:border-red-900/70 dark:bg-red-950/30 dark:text-red-300 dark:hover:border-red-800">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-red-100 text-red-600 transition-transform duration-300 motion-safe:group-hover/error:scale-105 dark:bg-red-900/40 dark:text-red-400">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0">
                    </i>

                </div>

                <p class="pt-1.5 leading-relaxed">
                    {{ $errors->first('aviso') }}
                </p>

            </div>

        @endif



        {{-- Formulario --}}

        <section class="group/form rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md dark:border-slate-700 dark:hover:border-blue-800/70">

            <div class="relative overflow-hidden flex items-center gap-3 px-6 py-5 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 dark:border-slate-700 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900/80">


                {{-- Decoración --}}

                <span class="absolute -right-8 -top-10 w-28 h-28 rounded-full bg-primary/10 blur-3xl pointer-events-none transition-all duration-500 group-hover/form:bg-primary/20 motion-safe:group-hover/form:scale-125"></span>


                <div class="relative flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 group-hover/form:bg-primary/15 motion-safe:group-hover/form:scale-105 dark:bg-blue-950/60 dark:group-hover/form:bg-blue-900/50">

                    <i
                        data-lucide="file-pen-line"
                        stroke-width="1.8"
                        class="w-[18px] h-[18px] shrink-0 transition-transform duration-300 motion-safe:group-hover/form:scale-110">
                    </i>

                </div>

                <div class="relative min-w-0">

                    <h2 class="text-sm font-semibold text-foreground">
                        Información del aviso
                    </h2>

                    <p class="text-xs text-muted-foreground mt-1">
                        Define el contenido y periodo de publicación.
                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('avisos.store') }}"
                class="p-6">

                @csrf

                @include('avisos.partials.form')

            </form>

        </section>



        {{-- Información adicional --}}

        <section class="group/info relative overflow-hidden mt-5 rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/50 p-5 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md motion-safe:hover:-translate-y-0.5 dark:border-slate-700 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900/80 dark:hover:border-blue-800/70">

            <span class="absolute -right-10 -top-12 w-32 h-32 rounded-full bg-primary/10 blur-3xl pointer-events-none transition-all duration-500 group-hover/info:bg-primary/20 motion-safe:group-hover/info:scale-125"></span>


            <div class="relative flex items-start gap-3.5">

                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 group-hover/info:bg-primary/15 motion-safe:group-hover/info:scale-105 dark:bg-blue-950/60 dark:group-hover/info:bg-blue-900/50">

                    <i
                        data-lucide="info"
                        stroke-width="1.8"
                        class="w-4 h-4 shrink-0 transition-transform duration-300 motion-safe:group-hover/info:scale-110">
                    </i>

                </div>

                <div class="min-w-0 pt-0.5">

                    <h3 class="text-sm font-semibold text-foreground">
                        Publicación del aviso
                    </h3>

                    <p class="text-sm text-muted-foreground mt-1 leading-relaxed">
                        El aviso se mostrará únicamente cuando esté activo y se encuentre dentro de las fechas de vigencia seleccionadas.
                    </p>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection