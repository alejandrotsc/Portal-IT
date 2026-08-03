@extends('layouts.app')

@section('title', 'Nueva solicitud')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Prellenado desde el chatbot
    |--------------------------------------------------------------------------
    */

    $categoriaRecibida = trim(
        (string) request()->query('categoria', '')
    );

    $categoriaNormalizada = strtolower(
        \Illuminate\Support\Str::ascii(
            $categoriaRecibida
        )
    );

    $categoriaNormalizada = trim(
        preg_replace(
            '/[^a-z0-9]+/',
            ' ',
            $categoriaNormalizada
        )
    );

    $categoriasChatbot = [
        'computadora' => 'computadora',
        'computadoras' => 'computadora',
        'computadora o accesorios' => 'computadora',
        'equipo' => 'computadora',
        'equipo de computo' => 'computadora',

        'programa' => 'programa',
        'programas' => 'programa',
        'instalar un programa' => 'programa',
        'instalacion de programa' => 'programa',
        'software' => 'programa',

        'acceso' => 'acceso',
        'solicitar un acceso' => 'acceso',
        'acceso a un sistema' => 'acceso',
        'permisos' => 'acceso',

        'vpn' => 'vpn',
        'acceso remoto' => 'vpn',
        'vpn acceso remoto' => 'vpn',

        'impresora' => 'impresora',
        'impresoras' => 'impresora',
        'solicitud impresora' => 'impresora',

        'cuenta' => 'cuenta',
        'cuenta o contrasena' => 'cuenta',
        'cuenta de correo' => 'cuenta',
        'contrasena' => 'cuenta',

        'cambio' => 'cambio',
        'cambio de equipo' => 'cambio',
        'configuracion de equipo' => 'cambio',
        'cambio o configuracion de equipo' => 'cambio',

        'otra' => 'otra',
        'otra solicitud' => 'otra',
        'general' => 'otra',
    ];

    $categoriaInicial = old(
        'categoria',
        $categoriasChatbot[$categoriaNormalizada]
            ?? ''
    );

    $asuntoInicial = old(
        'asunto',
        trim(
            (string) request()->query(
                'asunto',
                ''
            )
        )
    );

    $descripcionInicial = old(
        'descripcion',
        trim(
            (string) request()->query(
                'descripcion',
                ''
            )
        )
    );

@endphp

<form
    id="solicitudForm"
    method="POST"
    action="{{ route('solicitudes.store') }}"
    data-prefill-categoria="{{ $categoriaInicial }}"
    data-prefill-asunto="{{ $asuntoInicial }}"
    data-prefill-descripcion="{{ $descripcionInicial }}"
>
    @csrf

    <div class="min-h-screen bg-background">

        <main class="max-w-5xl mx-auto px-6 py-10">

            {{-- Encabezado --}}

            <section class="mb-8">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:bg-primary/15 dark:border-blue-800/60 dark:bg-blue-950/45 dark:text-blue-400 dark:hover:border-blue-700/70 dark:hover:bg-blue-900/50 motion-safe:hover:scale-105">

                            <i
                                data-lucide="clipboard-plus"
                                stroke-width="1.8"
                                class="h-6 w-6">
                            </i>

                        </div>

                        <div class="min-w-0">

                            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                                Nueva solicitud de servicio
                            </h1>

                            <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">
                                Selecciona el servicio que necesitas y completa la información.
                            </p>

                        </div>

                    </div>


                    <a
                        href="{{ route('mis-solicitudes') }}"
                        class="group/history inline-flex items-center justify-center gap-2 rounded-xl border border-primary/10 bg-primary/[0.06] px-4 py-2.5 text-sm font-medium text-primary shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow-md dark:border-blue-800/60 dark:bg-blue-950/35 dark:text-blue-300 dark:hover:border-blue-700/80 dark:hover:bg-blue-900/50 dark:hover:text-blue-200 active:scale-[0.98]">

                        <i
                            data-lucide="history"
                            stroke-width="1.8"
                            class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/history:-rotate-12">
                        </i>

                        Mis solicitudes

                    </a>

                </div>

            </section>


            {{-- ERRORES DE VALIDACIÓN --}}
            @if($errors->any())

                <div class="relative mb-6 overflow-hidden rounded-2xl border border-red-200 bg-gradient-to-br from-red-50 via-white to-rose-50/50 p-4 shadow-sm dark:border-red-900/70 dark:from-red-950/40 dark:via-slate-900 dark:to-rose-950/25">

                    <span class="pointer-events-none absolute -right-8 -top-10 h-24 w-24 rounded-full bg-red-500/10 blur-2xl"></span>

                    <div class="relative flex items-start gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400">

                            <i
                                data-lucide="circle-alert"
                                stroke-width="1.8"
                                class="h-4 w-4">
                            </i>

                        </div>

                        <div>

                            <p class="text-sm font-semibold text-red-700 dark:text-red-300">
                                Revisa la información ingresada
                            </p>

                            <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-red-600 dark:text-red-400">

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            <div class="space-y-8">

                {{-- PASO 1 --}}
                <section class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700/70 dark:hover:border-blue-700/70 dark:hover:shadow-black/20">

                    <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125"></span>

                    <div class="relative flex items-center gap-3 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4 dark:border-slate-700/70 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900">

                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white shadow-sm transition-transform duration-300 motion-safe:group-hover:scale-105"
                        >
                            1
                        </span>

                        <div>

                            <h2 class="text-sm font-semibold text-foreground">
                                ¿Qué necesitas hoy?
                            </h2>

                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Selecciona la categoría que mejor describa tu solicitud.
                            </p>

                        </div>

                        <span
                            id="cambiarCategoria"

                            class="ml-auto hidden cursor-pointer rounded-lg border border-primary/10 bg-primary/[0.05] px-3 py-1.5 text-xs font-medium text-primary transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 dark:border-slate-700 dark:bg-blue-500/10 dark:hover:border-blue-500/40 dark:hover:bg-blue-500/15"                        
                            >
                            Cambiar selección
                        </span>

                    </div>


                    <div class="relative p-5">

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                            @php
                                $categorias = [
                                    [
                                        'id' => 'computadora',
                                        'icon' => 'monitor',
                                        'title' => 'Computadora o accesorios',
                                        'desc' => 'Teclado, mouse, pantalla, audífonos u otro periférico',
                                        'color' => '#2563eb',
                                        'bg' => '#eff6ff',
                                    ],
                                    [
                                        'id' => 'programa',
                                        'icon' => 'package',
                                        'title' => 'Instalar un programa',
                                        'desc' => 'Solicitar instalación de una aplicación en tu equipo',
                                        'color' => '#7c3aed',
                                        'bg' => '#f5f3ff',
                                    ],
                                    [
                                        'id' => 'acceso',
                                        'icon' => 'key-round',
                                        'title' => 'Solicitar un acceso',
                                        'desc' => 'Permisos a sistemas, carpetas o recursos de red',
                                        'color' => '#0891b2',
                                        'bg' => '#ecfeff',
                                    ],
                                    [
                                        'id' => 'vpn',
                                        'icon' => 'wifi',
                                        'title' => 'VPN / Acceso remoto',
                                        'desc' => 'Configurar o solicitar acceso a la red desde fuera de la oficina',
                                        'color' => '#059669',
                                        'bg' => '#ecfdf5',
                                    ],
                                    [
                                        'id' => 'impresora',
                                        'icon' => 'printer',
                                        'title' => 'Impresoras',
                                        'desc' => 'Configurar o conectar impresora',
                                        'color' => '#d97706',
                                        'bg' => '#fffbeb',
                                    ],
                                    [
                                        'id' => 'cuenta',
                                        'icon' => 'shield-check',
                                        'title' => 'Cuenta o contraseña',
                                        'desc' => 'Restablecer contraseña, desbloquear cuenta o acceso al sistema',
                                        'color' => '#dc2626',
                                        'bg' => '#fef2f2',
                                    ],
                                    [
                                        'id' => 'cambio',
                                        'icon' => 'refresh-cw',
                                        'title' => 'Cambio o configuración de equipo',
                                        'desc' => 'Reemplazar equipo o cambiar configuración',
                                        'color' => '#0d9488',
                                        'bg' => '#f0fdfa',
                                    ],
                                    [
                                        'id' => 'otra',
                                        'icon' => 'help-circle',
                                        'title' => 'Otra solicitud',
                                        'desc' => 'Cualquier otra necesidad de TI no listada',
                                        'color' => '#64748b',
                                        'bg' => '#f8fafc',
                                    ],
                                ];
                            @endphp


                            @foreach($categorias as $categoria)

                                <button
                                    type="button"
                                    data-id="{{ $categoria['id'] }}"
                                    data-color="{{ $categoria['color'] }}"
                                    data-bg="{{ $categoria['bg'] }}"
                                    aria-pressed="false"
                                    class="categoria-card group/category relative flex cursor-pointer flex-col items-start gap-3 overflow-hidden rounded-xl border border-border bg-white p-4 text-left shadow-sm transition-all duration-300 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/10 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-slate-700/70 dark:bg-slate-900/80 dark:hover:border-blue-700/70 dark:hover:shadow-black/20 dark:focus:ring-blue-500/20 motion-safe:hover:-translate-y-1"
                                >

                                    <span
                                        class="check-categoria absolute right-2.5 top-2.5 hidden h-5 w-5 items-center justify-center rounded-full bg-primary text-white shadow-sm"
                                    >
                                        <i data-lucide="check" stroke-width="2.2" class="h-3 w-3"></i>
                                    </span>


                                    <div
                                        class="icon-container flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-300 dark:!bg-slate-800 motion-safe:group-hover/category:scale-110"
                                        style="background-color: {{ $categoria['bg'] }}"
                                    >
                                        <i
                                            data-lucide="{{ $categoria['icon'] }}"
                                            stroke-width="1.8"
                                            class="h-[18px] w-[18px]"
                                            style="color: {{ $categoria['color'] }}"
                                        ></i>
                                    </div>


                                    <div>

                                        <p class="text-xs font-semibold text-foreground leading-snug">
                                            {{ $categoria['title'] }}
                                        </p>

                                        <p class="text-[11px] text-muted-foreground mt-1 leading-relaxed hidden sm:block">
                                            {{ $categoria['desc'] }}
                                        </p>

                                    </div>

                                </button>

                            @endforeach

                        </div>

                    </div>

                </section>


                {{-- PASO 2 --}}
                <section
                    id="formularioSolicitud"
                    class="group relative hidden overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700/70 dark:hover:border-blue-700/70 dark:hover:shadow-black/20"
                >

                    <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125"></span>

                    <div class="relative flex items-center gap-3 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4 dark:border-slate-700/70 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900">

                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white shadow-sm transition-transform duration-300 motion-safe:group-hover:scale-105"
                        >
                            2
                        </span>

                        <div>

                            <h2 class="text-sm font-semibold text-foreground">
                                Cuéntanos un poco más
                            </h2>

                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Completa la información necesaria para procesar tu solicitud.
                            </p>

                        </div>

                        <span
                            id="categoriaSeleccionada"
                            class="ml-auto hidden rounded-full border px-2.5 py-1 text-xs font-medium"
                        ></span>

                    </div>


                    <div class="relative space-y-5 px-6 py-5">

                        <input
                            type="hidden"
                            name="categoria"
                            id="categoria"
                            value="{{ $categoriaInicial }}"
                        >


                        <div>

                            <label
                                for="asunto"
                                class="block text-xs font-semibold text-muted-foreground
                                       uppercase tracking-widest mb-2"
                            >
                                Asunto

                                <span class="text-primary">
                                    *
                                </span>
                            </label>

                            <div
                                @class([
                                    'group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2 dark:bg-slate-900/80',

                                    'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500 dark:focus-within:ring-red-500/20' =>
                                        $errors->has('asunto'),

                                    'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                                        ! $errors->has('asunto'),
                                ])>

                                <i
                                    data-lucide="text"
                                    stroke-width="1.8"
                                    class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:text-slate-400 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110">
                                </i>

                                <input
                                    id="asunto"
                                    type="text"
                                    name="asunto"
                                    value="{{ $asuntoInicial }}"
                                    data-required="true"
                                    maxlength="255"
                                    autocomplete="off"
                                    class="w-full border-0 bg-transparent py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 dark:text-slate-200 dark:placeholder:text-slate-500"
                                    placeholder="Describe brevemente tu solicitud">

                            </div>

                            @error('asunto')

                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">

                                    <i data-lucide="circle-alert" stroke-width="1.8" class="h-3.5 w-3.5 shrink-0"></i>

                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <div>

                            <label
                                for="descripcion"
                                class="block text-xs font-semibold text-muted-foreground
                                       uppercase tracking-widest mb-2"
                            >
                                Descripción

                                <span class="text-primary">
                                    *
                                </span>
                            </label>

                            <div
                                @class([
                                    'group/field flex w-full items-start gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2 dark:bg-slate-900/80',

                                    'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500 dark:focus-within:ring-red-500/20' =>
                                        $errors->has('descripcion'),

                                    'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                                        ! $errors->has('descripcion'),
                                ])>

                                <i
                                    data-lucide="align-left"
                                    stroke-width="1.8"
                                    class="mt-3 h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:text-slate-400 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110">
                                </i>

                                <textarea
                                    id="descripcion"
                                    name="descripcion"
                                    data-required="true"
                                    rows="4"
                                    maxlength="2000"
                                    class="w-full resize-none border-0 bg-transparent py-2.5 text-sm leading-relaxed text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 dark:text-slate-200 dark:placeholder:text-slate-500"
                                    placeholder="Cuéntanos qué necesitas y para qué lo necesitas">{{ $descripcionInicial }}</textarea>

                            </div>

                            @error('descripcion')

                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">

                                    <i data-lucide="circle-alert" stroke-width="1.8" class="h-3.5 w-3.5 shrink-0"></i>

                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Los campos según la categoría se insertan mediante JS --}}
                        <div
                            id="camposDinamicos"
                            class="space-y-5"
                        ></div>

                    </div>

                </section>


                {{-- ESTADO DEL CORREO: permanece visible aunque se cierre el modal --}}

                <div
                    id="estadoPersistenteSolicitud"
                    class="hidden flex-col gap-3 overflow-hidden rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/50 p-4 shadow-sm dark:border-blue-800 dark:from-blue-950/45 dark:via-slate-900 dark:to-sky-950/30 sm:flex-row sm:items-center"
                >
                    <div
                        id="smtpEstadoSolicitud"
                        class="inline-flex items-center gap-2 text-xs font-medium text-blue-700 dark:text-blue-300"
                    >
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500"></span>

                        Correo pendiente en la cola
                    </div>

                    <button
                        type="button"
                        id="btnReportarSmtpSolicitudPersistente"
                        data-recipient="helpdesk@televicentro.hn"
                        data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                        data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                        class="group/outlook hidden items-center justify-center gap-1.5 rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-medium text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md dark:border-amber-800 dark:bg-slate-900 dark:text-amber-300 dark:hover:border-amber-700 dark:hover:bg-amber-900/55 active:scale-[0.98]"
                    >
                        <i
                            data-lucide="external-link"
                            stroke-width="1.8"
                            class="h-3.5 w-3.5 transition-transform duration-200 motion-safe:group-hover/outlook:translate-x-0.5 motion-safe:group-hover/outlook:-translate-y-0.5"
                        ></i>

                        Reportar por Outlook 365
                    </button>
                </div>


                {{-- BOTONES --}}
<div
    id="accionesSolicitud"
    class="hidden flex flex-col-reverse justify-end gap-3 pb-10 sm:flex-row"
>

    <button
        type="button"
        id="btnCancelar"
        class="inline-flex items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-muted-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:text-foreground hover:shadow-md dark:border-slate-700/70 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-100 active:scale-[0.98]"
    >
        Cancelar
    </button>


    <button
    type="submit"
    id="btnEnviar"
    class="group/send inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md hover:shadow-primary/25 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:shadow-sm"
>
    <i
        id="btnEnviarIcono"
        data-lucide="mail"
        stroke-width="1.8"
        class="h-4 w-4 transition-transform duration-200"
    ></i>

    <span id="btnEnviarTexto">
        Enviar solicitud
    </span>
</button>

</div>

            </div>

        </main>

    </div>

</form>


{{-- MODAL RESPUESTA --}}

<div
    id="modalSolicitud"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalSolicitudTitulo"
    aria-describedby="modalSolicitudMensaje"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm dark:bg-black/70"
>
    <div
        class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-border bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-700/70 dark:bg-slate-900 dark:shadow-black/50"
    >
        <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl"></span>

        {{-- Cabecera --}}

        <div class="relative px-7 pb-6 pt-8 text-center">

            <div
                id="modalSolicitudIcono"
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 shadow-sm dark:border-blue-800 dark:bg-blue-950/45"
            >
                <i
                    data-lucide="clock-3"
                    stroke-width="1.8"
                    class="h-8 w-8 text-blue-600 dark:text-blue-400"
                ></i>
            </div>

            <h3
                id="modalSolicitudTitulo"
                class="mt-5 text-lg font-semibold text-foreground dark:text-slate-100"
            >
                Solicitud registrada
            </h3>

            <p
                id="modalSolicitudMensaje"
                class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground dark:text-slate-400"
            >
                La solicitud fue registrada correctamente. La notificación por correo se está procesando.
            </p>

            <span
                id="modalSolicitudFolio"
                class="mt-4 hidden items-center rounded-full bg-muted px-3 py-1 text-xs font-semibold text-foreground"
            ></span>

        </div>


        {{-- Estado del correo --}}

        <div class="relative px-7 pb-7">

            <div
                id="estadoCorreoSolicitud"
                class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/50 p-5 text-left shadow-sm dark:border-blue-800 dark:from-blue-950/45 dark:via-slate-900 dark:to-sky-950/30"
            >
                <div class="grid grid-cols-[40px_minmax(0,1fr)] items-start gap-4">

                    <div
                        id="estadoCorreoSolicitudIcono"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-200 bg-white text-blue-600 shadow-sm dark:border-blue-800 dark:bg-slate-900 dark:text-blue-400"
                    >
                        <i
                            data-lucide="mail"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        ></i>
                    </div>

                    <div class="min-w-0">

                        <p
                            id="estadoCorreoSolicitudTitulo"
                            class="text-sm font-semibold text-blue-800 dark:text-blue-300"
                        >
                            Correo en procesamiento
                        </p>

                        <p
                            id="estadoCorreoSolicitudMensaje"
                            class="mt-1.5 text-xs leading-relaxed text-blue-700 dark:text-blue-400"
                        >
                            La notificación fue agregada a la cola y será enviada en segundo plano.
                        </p>

                        <button
                            type="button"
                            id="btnReportarSmtpSolicitud"
                            data-recipient="helpdesk@televicentro.hn"
                            data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                            data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                            class="group/outlook-modal mt-4 hidden w-full items-center justify-center gap-2 rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-xs font-semibold text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md dark:border-amber-800 dark:bg-slate-900 dark:text-amber-300 dark:hover:border-amber-700 dark:hover:bg-amber-900/55 active:scale-[0.98]"
                        >
                            <i
                                data-lucide="external-link"
                                stroke-width="1.8"
                                class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/outlook-modal:translate-x-0.5 motion-safe:group-hover/outlook-modal:-translate-y-0.5"
                            ></i>

                            Reportar mediante Outlook 365
                        </button>

                    </div>

                </div>
            </div>

            <div class="mt-5 flex items-start gap-3 rounded-xl border border-blue-200/70 bg-blue-50/60 p-4 dark:border-blue-800/60 dark:bg-blue-950/25">
                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400"
                ></i>

                <p class="text-xs leading-relaxed text-muted-foreground dark:text-slate-400">
                    La solicitud permanecerá disponible en el historial, incluso si la notificación no pudo enviarse.
                </p>
            </div>

        </div>


        {{-- Acciones --}}

        <div class="border-t border-border bg-muted/20 px-7 py-5 dark:border-slate-700/70 dark:bg-slate-950/30">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                <button
                    type="button"
                    id="cerrarModalSolicitud"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:shadow-md dark:border-slate-700/70 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-700 active:scale-[0.98]"
                >
                    Cerrar
                </button>

                <a
                    href="{{ route('mis-solicitudes') }}"
                    class="group/history-modal inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md active:scale-[0.98]"
                >
                    <i
                        data-lucide="history"
                        stroke-width="1.8"
                        class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/history-modal:-rotate-12"
                    ></i>

                    Mis solicitudes
                </a>

            </div>

        </div>

    </div>
</div>


<script>
    /*
    |--------------------------------------------------------------------------
    | URL para consultar el estado del correo
    |--------------------------------------------------------------------------
    */

    window.emailDeliveryStatusUrl =
        @json(
            route(
                'email-deliveries.status',
                [
                    'emailDelivery' =>
                        '__DELIVERY_ID__',
                ]
            )
        );
</script>


<script
    src="{{ asset('js/solicitudes.js') }}?v={{ filemtime(public_path('js/solicitudes.js')) }}"
></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form =
            document.getElementById(
                'solicitudForm'
            );

        if (!form) {
            return;
        }

        const categoria = String(
            form.dataset.prefillCategoria
            ?? ''
        ).trim();

        const asunto = String(
            form.dataset.prefillAsunto
            ?? ''
        ).trim();

        const descripcion = String(
            form.dataset.prefillDescripcion
            ?? ''
        ).trim();

        const asuntoInput =
            document.getElementById(
                'asunto'
            );

        const descripcionInput =
            document.getElementById(
                'descripcion'
            );

        /*
         * Los valores antiguos de validación ya vienen
         * colocados desde Blade y tienen prioridad.
         */
        if (
            asuntoInput
            && !asuntoInput.value.trim()
            && asunto
        ) {
            asuntoInput.value = asunto;
        }

        if (
            descripcionInput
            && !descripcionInput.value.trim()
            && descripcion
        ) {
            descripcionInput.value =
                descripcion;
        }

        if (!categoria) {
            return;
        }

        const card = Array.from(
            document.querySelectorAll(
                '.categoria-card'
            )
        ).find((element) => {
            return String(
                element.dataset.id
                ?? ''
            ).trim() === categoria;
        });

        if (!card) {
            return;
        }

        /*
         * Utilizar el mismo evento del formulario garantiza
         * que solicitudes.js cargue los campos dinámicos,
         * muestre el paso 2 y actualice la selección visual.
         */
        card.click();

        card.setAttribute(
            'aria-pressed',
            'true'
        );

        window.requestAnimationFrame(() => {
            const formulario =
                document.getElementById(
                    'formularioSolicitud'
                );

            formulario?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        });
    });
</script>


<style>
    .spinner-envio {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-top-color: #ffffff;
        border-radius: 50%;
        display: inline-block;
        animation: girar-spinner 0.6s linear infinite;
    }

    @keyframes girar-spinner {
        to {
            transform: rotate(360deg);
        }
    }
</style>

@endsection