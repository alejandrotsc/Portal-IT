@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-3xl mx-auto px-6 py-10">


        {{-- Navegación --}}

        <a
            href="{{ route('usuarios.index') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/10 bg-primary/[0.06] text-sm font-medium text-primary shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">

            <i
                data-lucide="arrow-left"
                stroke-width="1.8"
                class="block w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5">
            </i>

            <span>
                Volver a usuarios
            </span>

        </a>



        {{-- Encabezado --}}

        <section class="mb-8">

            <div class="flex items-start gap-4">

                <div class="group flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:border-primary/20 hover:bg-primary/15 hover:shadow-md motion-safe:hover:-translate-y-0.5">

                    <i
                        data-lucide="user-cog"
                        stroke-width="1.8"
                        class="w-6 h-6 transition-transform duration-300 group-hover:scale-110">
                    </i>

                </div>

                <div class="min-w-0">

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">

                        Editar usuario

                    </h1>

                    <p class="text-sm text-muted-foreground mt-1.5 leading-relaxed">

                        Actualiza la información y los permisos de la cuenta seleccionada.

                    </p>

                </div>

            </div>

        </section>



        {{-- Estado actual --}}

        <section class="group/status relative mb-5 overflow-hidden rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50/80 via-white to-indigo-50/60 p-5 shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10 motion-safe:hover:-translate-y-0.5">

            {{-- Elemento decorativo --}}

            <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-blue-400/10 transition-all duration-500 motion-safe:group-hover/status:scale-150 group-hover/status:bg-blue-400/20"></div>


            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3 min-w-0">

                    <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-full bg-primary/10 text-primary text-sm font-semibold transition-all duration-300 group-hover/status:bg-primary group-hover/status:text-white motion-safe:group-hover/status:scale-105">

                        {{ mb_strtoupper(
                            mb_substr(
                                $usuario->nombre,
                                0,
                                1
                            )
                        ) }}

                    </div>

                    <div class="min-w-0">

                        <p class="text-sm font-semibold text-foreground truncate">

                            {{ $usuario->nombre }}

                        </p>

                        <p class="text-xs text-muted-foreground mt-0.5 truncate">

                            {{ $usuario->correo }}

                        </p>

                    </div>

                </div>


                <div class="flex flex-wrap items-center gap-2">

                    @if($usuario->activo)

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-emerald-200/60 bg-emerald-500/10 text-emerald-700 text-xs font-medium transition-colors duration-200 hover:bg-emerald-100">

                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                            Activo

                        </span>

                    @else

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-slate-200 bg-slate-500/10 text-slate-600 text-xs font-medium transition-colors duration-200 hover:bg-slate-200">

                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>

                            Inactivo

                        </span>

                    @endif


                    @if($usuario->correoEstaVerificado())

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-blue-200/60 bg-blue-500/10 text-blue-700 text-xs font-medium transition-colors duration-200 hover:bg-blue-100">

                            <i
                                data-lucide="badge-check"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5">
                            </i>

                            Correo verificado

                        </span>

                    @else

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-amber-200/60 bg-amber-500/10 text-amber-700 text-xs font-medium transition-colors duration-200 hover:bg-amber-100">

                            <span class="relative flex w-1.5 h-1.5 shrink-0">

                                <span class="absolute inline-flex w-full h-full rounded-full bg-amber-400 opacity-60 animate-ping"></span>

                                <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                            </span>

                            Verificación pendiente

                        </span>

                    @endif

                </div>

            </div>

        </section>



        {{-- Formulario --}}

        <section class="group/form rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

            <div class="flex items-center gap-3 px-6 py-5 border-b border-border bg-gradient-to-r from-primary/[0.025] via-transparent to-transparent">

                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 group-hover/form:bg-primary/15 motion-safe:group-hover/form:scale-105">

                    <i
                        data-lucide="settings-2"
                        stroke-width="1.8"
                        class="w-[18px] h-[18px] transition-transform duration-300 motion-safe:group-hover/form:scale-110">
                    </i>

                </div>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">

                        Información de la cuenta

                    </h2>

                    <p class="text-sm text-muted-foreground mt-1">

                        Modifica únicamente la información que sea necesaria.

                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route(
                    'usuarios.update',
                    $usuario
                ) }}"
                class="p-6">

                @csrf
                @method('PUT')

                @include('usuarios.partials.form')

            </form>

        </section>



        {{-- Información adicional --}}

        <section class="group/info relative mt-5 overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/50 p-5 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md motion-safe:hover:-translate-y-0.5">

            <div class="absolute -right-10 -top-10 w-24 h-24 rounded-full bg-primary/5 transition-all duration-500 motion-safe:group-hover/info:scale-150 group-hover/info:bg-primary/10"></div>


            <div class="relative flex items-start gap-3.5">

                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 motion-safe:group-hover/info:scale-105 group-hover/info:bg-primary/15">

                    <i
                        data-lucide="info"
                        stroke-width="1.8"
                        class="w-[18px] h-[18px] transition-transform duration-300 motion-safe:group-hover/info:scale-110">
                    </i>

                </div>

                <div class="min-w-0 pt-0.5">

                    <h3 class="text-sm font-semibold text-foreground">

                        Cambios en la cuenta

                    </h3>

                    <p class="text-sm text-muted-foreground mt-1 leading-relaxed">

                        Si modificas el correo electrónico, la dirección anterior dejará de estar verificada y el usuario deberá confirmar el nuevo correo.

                    </p>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection