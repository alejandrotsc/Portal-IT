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

        <section class="mb-10">

            <span
                class="mb-4 inline-flex items-center gap-2
                       rounded-full border border-blue-200/60
                       bg-blue-500/[0.08] px-3 py-1.5
                       text-xs font-semibold text-blue-700
                       dark:border-blue-900/70
                       dark:bg-blue-950/60
                       dark:text-blue-300"
            >
                <i
                    data-lucide="user-cog"
                    stroke-width="1.8"
                    class="h-3.5 w-3.5 shrink-0"
                ></i>

                Gestión de usuarios
            </span>

            <h1
                class="text-2xl font-semibold tracking-tight
                       text-foreground"
            >
                Editar usuario
            </h1>

            <p
                class="mt-2 max-w-2xl text-sm leading-relaxed
                       text-muted-foreground"
            >
                Actualiza la información y los permisos de la cuenta
                seleccionada.
            </p>

        </section>


        {{-- Estado actual --}}

        <section
            class="group/status relative mb-5 overflow-hidden rounded-2xl
                   border border-blue-200/60
                   bg-gradient-to-br from-blue-50/80 via-white
                   to-indigo-50/60 p-5 shadow-sm
                   transition-all duration-300
                   hover:border-blue-300
                   hover:shadow-lg hover:shadow-blue-500/10
                   motion-safe:hover:-translate-y-0.5
                   dark:border-blue-900/60
                   dark:from-blue-950/30 dark:via-slate-900
                   dark:to-indigo-950/20
                   dark:hover:border-blue-800"
        >
            {{-- Elemento decorativo --}}

            <div
                class="pointer-events-none absolute -right-10 -top-10
                       h-28 w-28 rounded-full bg-blue-400/10
                       transition-all duration-500
                       group-hover/status:bg-blue-400/20
                       motion-safe:group-hover/status:scale-150"
            ></div>

            <div
                class="relative flex flex-col gap-4
                       sm:flex-row sm:items-center
                       sm:justify-between"
            >
                <div class="flex min-w-0 items-center gap-3">

                    <div
                        class="flex h-11 w-11 shrink-0 items-center
                               justify-center rounded-full bg-primary/10
                               text-sm font-semibold text-primary
                               transition-all duration-300
                               group-hover/status:bg-primary
                               group-hover/status:text-white
                               motion-safe:group-hover/status:scale-105"
                    >
                        {{
                            mb_strtoupper(
                                mb_substr(
                                    $usuario->nombre,
                                    0,
                                    1
                                )
                            )
                        }}
                    </div>

                    <div class="min-w-0">

                        <p
                            class="truncate text-sm font-semibold
                                   text-foreground"
                        >
                            {{ $usuario->nombre }}
                        </p>

                        <p
                            class="mt-0.5 truncate text-xs
                                   text-muted-foreground"
                        >
                            {{ $usuario->correo }}
                        </p>

                    </div>

                </div>


                <div class="flex flex-wrap items-center gap-2">

                    @if($usuario->activo)

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full border
                                   border-emerald-200/60
                                   bg-emerald-500/10 px-2.5 py-1
                                   text-xs font-medium text-emerald-700
                                   transition-colors duration-200
                                   hover:bg-emerald-100
                                   dark:border-emerald-900/60
                                   dark:text-emerald-400
                                   dark:hover:bg-emerald-950/70"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full
                                       bg-emerald-500"
                            ></span>

                            Activo
                        </span>

                    @else

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full border border-slate-200
                                   bg-slate-500/10 px-2.5 py-1
                                   text-xs font-medium text-slate-600
                                   transition-colors duration-200
                                   hover:bg-slate-200
                                   dark:border-slate-700
                                   dark:text-slate-400
                                   dark:hover:bg-slate-800"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full
                                       bg-slate-400"
                            ></span>

                            Inactivo
                        </span>

                    @endif


                    @if($usuario->correoEstaVerificado())

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full border border-blue-200/60
                                   bg-blue-500/10 px-2.5 py-1
                                   text-xs font-medium text-blue-700
                                   transition-colors duration-200
                                   hover:bg-blue-100
                                   dark:border-blue-900/60
                                   dark:text-blue-400
                                   dark:hover:bg-blue-950/70"
                        >
                            <i
                                data-lucide="badge-check"
                                stroke-width="1.8"
                                class="h-3.5 w-3.5"
                            ></i>

                            Correo verificado
                        </span>

                    @else

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full border border-amber-200/60
                                   bg-amber-500/10 px-2.5 py-1
                                   text-xs font-medium text-amber-700
                                   transition-colors duration-200
                                   hover:bg-amber-100
                                   dark:border-amber-900/60
                                   dark:text-amber-400
                                   dark:hover:bg-amber-950/70"
                        >
                            <span
                                class="relative flex h-1.5 w-1.5
                                       shrink-0"
                            >
                                <span
                                    class="absolute inline-flex h-full
                                           w-full animate-ping rounded-full
                                           bg-amber-400 opacity-60"
                                ></span>

                                <span
                                    class="relative inline-flex h-1.5
                                           w-1.5 rounded-full
                                           bg-amber-500"
                                ></span>
                            </span>

                            Verificación pendiente
                        </span>

                    @endif

                </div>

            </div>

        </section>


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
                class="flex items-center gap-3 border-b border-border
                       bg-gradient-to-r from-primary/[0.04]
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
                        data-lucide="settings-2"
                        stroke-width="1.8"
                        class="h-[18px] w-[18px]
                               transition-transform duration-300
                               motion-safe:group-hover/form:scale-110"
                    ></i>
                </div>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">
                        Información de la cuenta
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Modifica únicamente la información que sea
                        necesaria.
                    </p>

                </div>
            </div>


            <form
                method="POST"
                action="{{
                    route(
                        'usuarios.update',
                        $usuario
                    )
                }}"
                class="p-6"
            >
                @csrf
                @method('PUT')

                @include('usuarios.partials.form')

            </form>

        </section>


        {{-- Información adicional --}}

        <section
            class="group/info relative mt-5 overflow-hidden rounded-2xl
                   border border-primary/10
                   bg-gradient-to-br from-primary/[0.05]
                   via-white to-blue-50/50 p-5 shadow-sm
                   transition-all duration-300
                   hover:border-primary/20 hover:shadow-md
                   motion-safe:hover:-translate-y-0.5
                   dark:border-blue-900/70
                   dark:via-slate-900 dark:to-blue-950/20
                   dark:hover:border-blue-800/80"
        >
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
                        data-lucide="info"
                        stroke-width="1.8"
                        class="h-[18px] w-[18px]
                               transition-transform duration-300
                               motion-safe:group-hover/info:scale-110"
                    ></i>
                </div>

                <div class="min-w-0 pt-0.5">

                    <h3 class="text-sm font-semibold text-foreground">
                        Cambios en la cuenta
                    </h3>

                    <p
                        class="mt-1 text-sm leading-relaxed
                               text-muted-foreground"
                    >
                        Si modificas el correo electrónico, la dirección
                        anterior dejará de estar verificada y el usuario
                        deberá confirmar el nuevo correo.
                    </p>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection
