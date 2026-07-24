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

            <div class="flex items-center gap-4">

                <div class="group flex items-center justify-center w-12 h-12 shrink-0 rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:border-primary/20 hover:bg-primary/15 hover:shadow-md motion-safe:hover:-translate-y-0.5">

                    <i
                        data-lucide="user-plus"
                        stroke-width="1.8"
                        class="block w-6 h-6 shrink-0 transition-transform duration-300 group-hover:scale-110">
                    </i>

                </div>

                <div class="min-w-0">

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">

                        Nuevo usuario

                    </h1>

                    <p class="text-sm text-muted-foreground mt-1.5 leading-relaxed">

                        Registra una cuenta y asigna el nivel de acceso correspondiente.

                    </p>

                </div>

            </div>

        </section>



        {{-- Errores generales --}}

        @if($errors->has('registro'))

            <div class="group mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800 shadow-sm transition-all duration-300 hover:border-red-300 hover:shadow-md">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-red-100 text-red-600 transition-transform duration-300 motion-safe:group-hover:scale-105">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="block w-4 h-4 shrink-0">
                    </i>

                </div>

                <p class="pt-1.5 leading-relaxed">

                    {{ $errors->first('registro') }}

                </p>

            </div>

        @endif



        {{-- Formulario --}}

        <section class="group/form bg-card rounded-2xl border border-border shadow-sm overflow-hidden transition-all duration-300 hover:border-primary/15 hover:shadow-md">

            <div class="flex items-center gap-3 px-6 py-5 border-b border-border bg-gradient-to-r from-primary/[0.025] via-transparent to-transparent">

                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 motion-safe:group-hover/form:scale-105 group-hover/form:bg-primary/15">

                    <i
                        data-lucide="contact"
                        stroke-width="1.8"
                        class="block w-[18px] h-[18px] shrink-0 transition-transform duration-300 motion-safe:group-hover/form:scale-110">
                    </i>

                </div>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">

                        Información del usuario

                    </h2>

                    <p class="text-xs text-muted-foreground mt-1">

                        Todos los campos marcados son obligatorios.

                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('usuarios.store') }}"
                class="p-6">

                @csrf

                @include('usuarios.partials.form')

            </form>

        </section>



        {{-- Información adicional --}}

        <section class="group/info relative mt-5 overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.05] via-white to-blue-50/50 p-5 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md motion-safe:hover:-translate-y-0.5">

            {{-- Elemento decorativo --}}

            <div class="absolute -right-10 -top-10 w-24 h-24 rounded-full bg-primary/5 transition-all duration-500 motion-safe:group-hover/info:scale-150 group-hover/info:bg-primary/10"></div>


            <div class="relative flex items-start gap-3.5">

                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 motion-safe:group-hover/info:scale-105 group-hover/info:bg-primary/15">

                    <i
                        data-lucide="mail-check"
                        stroke-width="1.8"
                        class="block w-[18px] h-[18px] shrink-0 transition-transform duration-300 motion-safe:group-hover/info:scale-110">
                    </i>

                </div>

                <div class="min-w-0 pt-0.5">

                    <h3 class="text-sm font-semibold text-foreground">

                        Verificación del correo

                    </h3>

                    <p class="text-sm text-muted-foreground mt-1 leading-relaxed">

                        La cuenta se creará activa, pero el usuario deberá verificar su correo antes de recibir su enlace de acceso.

                    </p>

                </div>

            </div>

        </section>

    </main>

</div>

@endsection