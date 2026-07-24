@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-3xl mx-auto px-6 py-10">


        {{-- Navegación --}}

        <a
            href="{{ route('avisos.index') }}"
            class="group inline-flex items-center gap-2 mb-6 px-3 py-2 rounded-lg border border-primary/10 bg-primary/[0.06] text-sm font-medium text-primary shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">

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

                <div class="group flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:border-primary/20 hover:bg-primary/15 hover:shadow-md motion-safe:hover:-translate-y-0.5">

                    <i
                        data-lucide="file-pen-line"
                        stroke-width="1.8"
                        class="w-6 h-6 shrink-0 transition-transform duration-300 motion-safe:group-hover:scale-110">
                    </i>

                </div>

                <div class="min-w-0">

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">

                        Editar aviso

                    </h1>

                    <p class="text-sm text-muted-foreground mt-1.5 leading-relaxed">

                        Actualiza el contenido o periodo de vigencia del aviso.

                    </p>

                </div>

            </div>

        </section>



        {{-- Mensajes de error --}}

        @if($errors->has('aviso'))

            <div class="group/error mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800 shadow-sm transition-all duration-300 hover:border-red-300 hover:shadow-md">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-red-100 text-red-600 transition-transform duration-300 motion-safe:group-hover/error:scale-105">

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



        {{-- Estado actual --}}

        <section class="group/status relative overflow-hidden mb-5 rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50/80 via-white to-indigo-50/60 p-5 shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10 motion-safe:hover:-translate-y-0.5">


            {{-- Decoración --}}

            <span class="absolute -right-10 -top-12 w-32 h-32 rounded-full bg-blue-400/10 blur-2xl pointer-events-none transition-all duration-500 group-hover/status:bg-blue-400/20 motion-safe:group-hover/status:scale-125"></span>


            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3 min-w-0">

                    <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-primary/10 text-primary transition-all duration-300 group-hover/status:bg-primary/15 motion-safe:group-hover/status:scale-105">

                        <i
                            data-lucide="megaphone"
                            stroke-width="1.8"
                            class="w-5 h-5 shrink-0 transition-transform duration-300 motion-safe:group-hover/status:scale-110">
                        </i>

                    </div>

                    <div class="min-w-0">

                        <p class="text-sm font-semibold text-foreground truncate">

                            {{ $aviso->titulo }}

                        </p>

                        <p class="flex items-center gap-1.5 text-xs text-muted-foreground mt-1">

                            <i
                                data-lucide="calendar"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 shrink-0 text-primary">
                            </i>

                            <span>

                                Creado el

                                {{ $aviso->created_at
                                    ?->timezone('America/Tegucigalpa')
                                    ->format('d/m/Y h:i A') }}

                            </span>

                        </p>

                    </div>

                </div>



                {{-- Estado del aviso --}}

                <div class="shrink-0">

                    @if($aviso->estaVisible())

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-emerald-200/60 bg-emerald-500/10 text-emerald-700 text-xs font-medium transition-colors duration-200 hover:bg-emerald-100">

                            <span class="relative flex w-1.5 h-1.5 shrink-0">

                                <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>

                                <span class="relative inline-flex w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                            </span>

                            Visible

                        </span>

                    @elseif($aviso->estaProgramado())

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-amber-200/60 bg-amber-500/10 text-amber-700 text-xs font-medium transition-colors duration-200 hover:bg-amber-100">

                            <i
                                data-lucide="clock-3"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 shrink-0">
                            </i>

                            Programado

                        </span>

                    @elseif($aviso->estaFinalizado())

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-slate-200 bg-slate-500/10 text-slate-600 text-xs font-medium transition-colors duration-200 hover:bg-slate-200">

                            <i
                                data-lucide="calendar-x"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 shrink-0">
                            </i>

                            Finalizado

                        </span>

                    @else

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-slate-200 bg-slate-500/10 text-slate-600 text-xs font-medium transition-colors duration-200 hover:bg-slate-200">

                            <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-slate-400"></span>

                            Inactivo

                        </span>

                    @endif

                </div>

            </div>

        </section>



        {{-- Formulario --}}

        <section class="group/form rounded-2xl border border-border bg-card shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

            <div class="relative overflow-hidden flex items-center gap-3 px-6 py-5 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40">


                {{-- Decoración --}}

                <span class="absolute -right-8 -top-10 w-28 h-28 rounded-full bg-primary/10 blur-3xl pointer-events-none transition-all duration-500 group-hover/form:bg-primary/20 motion-safe:group-hover/form:scale-125"></span>


                <div class="relative flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 group-hover/form:bg-primary/15 motion-safe:group-hover/form:scale-105">

                    <i
                        data-lucide="settings-2"
                        stroke-width="1.8"
                        class="w-[18px] h-[18px] shrink-0 transition-transform duration-300 motion-safe:group-hover/form:scale-110">
                    </i>

                </div>

                <div class="relative min-w-0">

                    <h2 class="text-sm font-semibold text-foreground">

                        Información del aviso

                    </h2>

                    <p class="text-xs text-muted-foreground mt-1">

                        Modifica únicamente la información necesaria.

                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route(
                    'avisos.update',
                    $aviso
                ) }}"
                class="p-6">

                @csrf
                @method('PUT')

                @include('avisos.partials.form')

            </form>

        </section>



        {{-- Información adicional --}}

        <section class="group/info relative overflow-hidden mt-5 rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/50 p-5 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md motion-safe:hover:-translate-y-0.5">

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

                        Actualización del aviso

                    </h3>

                    <p class="text-sm text-muted-foreground mt-1 leading-relaxed">

                        Los cambios se reflejarán en la cinta informativa cuando el aviso esté activo y dentro de su periodo de vigencia.

                    </p>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection