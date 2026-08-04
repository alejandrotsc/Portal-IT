@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

    <main class="mx-auto max-w-3xl px-6 py-10">

        {{-- Navegación --}}

        <a
            href="{{ route('usuarios.index') }}"
            class="group mb-6 inline-flex items-center gap-2
                   rounded-lg border border-primary/10
                   bg-primary/[0.06] px-3 py-2
                   text-sm font-medium text-primary shadow-sm
                   transition-all duration-200
                   hover:border-primary/20 hover:bg-primary/10
                   hover:shadow
                   motion-safe:hover:-translate-y-0.5
                   active:translate-y-0 active:scale-[0.98]
                   dark:border-blue-900/70
                   dark:hover:border-blue-800/80"
        >
            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="block h-4 w-4 shrink-0
                       transition-transform duration-200
                       group-hover:-translate-x-0.5"
            ></i>

            <span>Volver a usuarios</span>
        </a>


        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex items-center gap-4">

                <div
                    class="group flex h-12 w-12 shrink-0 items-center
                           justify-center rounded-xl
                           border border-primary/10 bg-primary/10
                           text-primary shadow-sm
                           transition-all duration-300
                           hover:border-primary/20
                           hover:bg-primary/15 hover:shadow-md
                           motion-safe:hover:-translate-y-0.5
                           dark:border-blue-900/70
                           dark:hover:border-blue-800/80"
                >
                    <i
                        data-lucide="user-plus"
                        stroke-width="1.8"
                        class="block h-6 w-6 shrink-0
                               transition-transform duration-300
                               group-hover:scale-110"
                    ></i>
                </div>

                <div class="min-w-0">

                    <h1
                        class="text-2xl font-semibold tracking-tight
                               text-foreground"
                    >
                        Nuevo usuario
                    </h1>

                    <p
                        class="mt-1.5 text-sm leading-relaxed
                               text-muted-foreground"
                    >
                        Registra una cuenta y asigna el nivel de acceso
                        correspondiente.
                    </p>

                </div>

            </div>

        </section>


        {{-- Errores generales --}}

        @if($errors->has('registro'))

            <div
                class="group mb-6 flex items-start gap-3
                       rounded-xl border border-red-200
                       bg-red-50 px-4 py-3.5
                       text-sm text-red-800 shadow-sm
                       transition-all duration-300
                       hover:border-red-300 hover:shadow-md
                       dark:border-red-900/60
                       dark:bg-red-950/30 dark:text-red-300
                       dark:hover:border-red-800"
            >
                <div
                    class="flex h-8 w-8 shrink-0 items-center
                           justify-center rounded-lg bg-red-100
                           text-red-600 transition-transform duration-300
                           motion-safe:group-hover:scale-105
                           dark:bg-red-950/60 dark:text-red-300"
                >
                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="block h-4 w-4 shrink-0"
                    ></i>
                </div>

                <p class="pt-1.5 leading-relaxed">
                    {{ $errors->first('registro') }}
                </p>
            </div>

        @endif


        {{-- Formulario --}}

        <section
            class="group/form overflow-hidden rounded-2xl
                   border border-border bg-card shadow-sm
                   transition-all duration-300
                   hover:border-primary/15 hover:shadow-md
                   dark:border-slate-700
                   dark:hover:border-blue-900/80"
        >
            <div
                class="flex items-center gap-3
                       border-b border-border
                       bg-gradient-to-r from-primary/[0.025]
                       via-transparent to-transparent px-6 py-5
                       dark:border-slate-700"
            >
                <div
                    class="flex h-9 w-9 shrink-0 items-center
                           justify-center rounded-lg bg-primary/10
                           text-primary transition-all duration-300
                           group-hover/form:bg-primary/15
                           motion-safe:group-hover/form:scale-105"
                >
                    <i
                        data-lucide="contact"
                        stroke-width="1.8"
                        class="block h-[18px] w-[18px] shrink-0
                               transition-transform duration-300
                               motion-safe:group-hover/form:scale-110"
                    ></i>
                </div>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">
                        Información del usuario
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Todos los campos marcados son obligatorios.
                    </p>

                </div>
            </div>


            <form
                method="POST"
                action="{{ route('usuarios.store') }}"
                class="p-6"
            >
                @csrf

                @include('usuarios.partials.form')

            </form>

        </section>


        {{-- Información adicional --}}

        <section
            class="group/info relative mt-5 overflow-hidden
                   rounded-2xl border border-primary/10
                   bg-gradient-to-br from-primary/[0.05]
                   via-white to-blue-50/50 p-5 shadow-sm
                   transition-all duration-300
                   hover:border-primary/20 hover:shadow-md
                   motion-safe:hover:-translate-y-0.5
                   dark:border-blue-900/70
                   dark:via-slate-900 dark:to-blue-950/20
                   dark:hover:border-blue-800/80"
        >
            {{-- Elemento decorativo --}}

            <div
                class="pointer-events-none absolute -right-10 -top-10
                       h-24 w-24 rounded-full bg-primary/5
                       transition-all duration-500
                       group-hover/info:bg-primary/10
                       motion-safe:group-hover/info:scale-150"
            ></div>


            <div class="relative flex items-start gap-3.5">

                <div
                    class="flex h-9 w-9 shrink-0 items-center
                           justify-center rounded-lg bg-primary/10
                           text-primary transition-all duration-300
                           group-hover/info:bg-primary/15
                           motion-safe:group-hover/info:scale-105"
                >
                    <i
                        data-lucide="mail-check"
                        stroke-width="1.8"
                        class="block h-[18px] w-[18px] shrink-0
                               transition-transform duration-300
                               motion-safe:group-hover/info:scale-110"
                    ></i>
                </div>

                <div class="min-w-0 pt-0.5">

                    <h3 class="text-sm font-semibold text-foreground">
                        Verificación del correo
                    </h3>

                    <p
                        class="mt-1 text-sm leading-relaxed
                               text-muted-foreground"
                    >
                        La cuenta se creará activa, pero el usuario deberá
                        verificar su correo antes de recibir su enlace de
                        acceso.
                    </p>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection