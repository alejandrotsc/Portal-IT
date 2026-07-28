@extends('layouts.app')

@section('title', 'Nueva solicitud')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | ESTADO DE LA NOTIFICACIÓN
    |--------------------------------------------------------------------------
    */

    $emailComprobado = session()->has('email_sent');

    $emailEnviado =
        session('email_sent') === true;

    $outlookUrl = null;


    /*
    |--------------------------------------------------------------------------
    | GENERAR CORREO DE RESPALDO
    |--------------------------------------------------------------------------
    */

    if ($emailComprobado && ! $emailEnviado) {

        $destinatario =
            'helpdesk@televicentro.hn';


        /*
        |--------------------------------------------------------------------------
        | CATEGORÍAS LEGIBLES
        |--------------------------------------------------------------------------
        */

        $categorias = [

            'computadora' =>
                'Computadora o accesorios',

            'programa' =>
                'Instalar un programa',

            'acceso' =>
                'Solicitar un acceso',

            'vpn' =>
                'VPN / Acceso remoto',

            'impresora' =>
                'Impresoras',

            'cuenta' =>
                'Cuenta o contraseña',

            'cambio' =>
                'Cambio o configuración de equipo',

            'otra' =>
                'Otra solicitud',

        ];


        $categoriaValor =
            session('solicitud_categoria');


        $categoriaTexto =
            $categorias[$categoriaValor]
            ?? $categoriaValor
            ?? 'No especificada';


        /*
        |--------------------------------------------------------------------------
        | INFORMACIÓN DE LA SOLICITUD
        |--------------------------------------------------------------------------
        */

        $folio =
            session('folio')
            ?? 'Sin folio';


        $nombreUsuario =
            auth()->user()->nombre
            ?? 'No especificado';


        $correoUsuario =
            auth()->user()->correo
            ?? 'No especificado';


        $asuntoSolicitud =
            session('solicitud_asunto')
            ?? 'No especificado';


        $fechaSolicitud =
            now()
                ->timezone('America/Tegucigalpa')
                ->format('d/m/Y h:i A');


        /*
        |--------------------------------------------------------------------------
        | ASUNTO DE OUTLOOK
        |--------------------------------------------------------------------------
        */

        $asuntoOutlook =
            '[Portal TI] Seguimiento de solicitud '
            .$folio;


        /*
        |--------------------------------------------------------------------------
        | CUERPO DE OUTLOOK
        |--------------------------------------------------------------------------
        */

        $cuerpoOutlook = implode(
            "\r\n",
            [

                'Hola, equipo de Helpdesk:',

                '',

                'Registré una solicitud de servicio en el Portal TI, '
                .'pero el equipo de soporte no recibió la notificación automática.',

                '',

                'Datos del usuario',

                'Nombre: '.$nombreUsuario,

                'Correo: '.$correoUsuario,

                '',

                'Información de la solicitud',

                'Folio: '.$folio,

                'Categoría: '.$categoriaTexto,

                'Asunto: '.$asuntoSolicitud,

                'Fecha de la solicitud: '.$fechaSolicitud,

                '',

                'La solicitud quedó registrada correctamente en el Portal TI.',

                '',

                'Por favor, ayúdenme a darle seguimiento.',

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | ENLACE DE OUTLOOK 365
        |--------------------------------------------------------------------------
        */

        $outlookUrl =
            'https://outlook.office.com/mail/deeplink/compose?'
            .http_build_query(
                [
                    'to' =>
                        $destinatario,

                    'subject' =>
                        $asuntoOutlook,

                    'body' =>
                        $cuerpoOutlook,
                ],
                '',
                '&',
                PHP_QUERY_RFC3986
            );

    }


    /*
    |--------------------------------------------------------------------------
    | PRELLENADO DESDE EL CHATBOT
    |--------------------------------------------------------------------------
    */

    $categoriaChatbot = trim(
        (string) request()->query(
            'categoria',
            ''
        )
    );

    $categoriasChatbot = [
        'Computadora o accesorios' => 'computadora',
        'computadora' => 'computadora',

        'Instalar un programa' => 'programa',
        'programa' => 'programa',

        'Solicitar un acceso' => 'acceso',
        'Acceso a un sistema' => 'acceso',
        'acceso' => 'acceso',

        'VPN / Acceso remoto' => 'vpn',
        'VPN' => 'vpn',
        'vpn' => 'vpn',

        'Impresoras' => 'impresora',
        'Impresora' => 'impresora',
        'impresora' => 'impresora',

        'Cuenta o contraseña' => 'cuenta',
        'Cuenta de correo' => 'cuenta',
        'cuenta' => 'cuenta',

        'Cambio o configuración de equipo' => 'cambio',
        'Cambio de equipo' => 'cambio',
        'cambio' => 'cambio',

        'Otra solicitud' => 'otra',
        'otra' => 'otra',
    ];

    $categoriaInicial = old(
        'categoria',
        $categoriasChatbot[$categoriaChatbot]
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

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:bg-primary/15 motion-safe:hover:scale-105">

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
                        class="group/history inline-flex items-center justify-center gap-2 rounded-xl border border-primary/10 bg-primary/[0.06] px-4 py-2.5 text-sm font-medium text-primary shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow-md active:scale-[0.98]">

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

                <div class="relative mb-6 overflow-hidden rounded-2xl border border-red-200 bg-gradient-to-br from-red-50 via-white to-rose-50/50 p-4 shadow-sm">

                    <span class="pointer-events-none absolute -right-8 -top-10 h-24 w-24 rounded-full bg-red-500/10 blur-2xl"></span>

                    <div class="relative flex items-start gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">

                            <i
                                data-lucide="circle-alert"
                                stroke-width="1.8"
                                class="h-4 w-4">
                            </i>

                        </div>

                        <div>

                            <p class="text-sm font-semibold text-red-700">
                                Revisa la información ingresada
                            </p>

                            <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-red-600">

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
                <section class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md">

                    <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125"></span>

                    <div class="relative flex items-center gap-3 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4">

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
                            class="ml-auto hidden cursor-pointer rounded-lg border border-primary/10 bg-primary/[0.05] px-3 py-1.5 text-xs font-medium text-primary transition-all duration-200 hover:border-primary/20 hover:bg-primary/10"
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
                                    class="categoria-card group/category relative flex cursor-pointer flex-col items-start gap-3 overflow-hidden rounded-xl border border-border bg-white p-4 text-left shadow-sm transition-all duration-300 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/10 focus:outline-none focus:ring-2 focus:ring-primary/20 motion-safe:hover:-translate-y-1"
                                >

                                    <span
                                        class="check-categoria absolute right-2.5 top-2.5 hidden h-5 w-5 items-center justify-center rounded-full bg-primary text-white shadow-sm"
                                    >
                                        <i data-lucide="check" stroke-width="2.2" class="h-3 w-3"></i>
                                    </span>


                                    <div
                                        class="icon-container flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-300 motion-safe:group-hover/category:scale-110"
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
                    class="group relative hidden overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md"
                >

                    <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125"></span>

                    <div class="relative flex items-center gap-3 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4">

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
                                    'group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2',

                                    'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10' =>
                                        $errors->has('asunto'),

                                    'border-border focus-within:border-primary focus-within:ring-primary/10' =>
                                        ! $errors->has('asunto'),
                                ])>

                                <i
                                    data-lucide="text"
                                    stroke-width="1.8"
                                    class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-primary motion-safe:group-focus-within/field:scale-110">
                                </i>

                                <input
                                    id="asunto"
                                    type="text"
                                    name="asunto"
                                    value="{{ $asuntoInicial }}"
                                    data-required="true"
                                    maxlength="255"
                                    autocomplete="off"
                                    class="w-full border-0 bg-transparent py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0"
                                    placeholder="Describe brevemente tu solicitud">

                            </div>

                            @error('asunto')

                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600">

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
                                    'group/field flex w-full items-start gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2',

                                    'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10' =>
                                        $errors->has('descripcion'),

                                    'border-border focus-within:border-primary focus-within:ring-primary/10' =>
                                        ! $errors->has('descripcion'),
                                ])>

                                <i
                                    data-lucide="align-left"
                                    stroke-width="1.8"
                                    class="mt-3 h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-primary motion-safe:group-focus-within/field:scale-110">
                                </i>

                                <textarea
                                    id="descripcion"
                                    name="descripcion"
                                    data-required="true"
                                    rows="4"
                                    maxlength="2000"
                                    class="w-full resize-none border-0 bg-transparent py-2.5 text-sm leading-relaxed text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0"
                                    placeholder="Cuéntanos qué necesitas y para qué lo necesitas">{{ $descripcionInicial }}</textarea>

                            </div>

                            @error('descripcion')

                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600">

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


                {{-- ÚLTIMO ESTADO SMTP: permanece visible aunque se cierre el modal --}}

                @if($emailComprobado)

                    <div
                        @class([
                            'relative flex flex-col gap-3 overflow-hidden rounded-xl border p-4 shadow-sm sm:flex-row sm:items-center',

                            'border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50' =>
                                $emailEnviado,

                            'border-amber-200 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50' =>
                                ! $emailEnviado,
                        ])>

                        <div class="inline-flex items-center gap-2 text-xs font-medium {{ $emailEnviado ? 'text-emerald-700' : 'text-amber-700' }}">

                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $emailEnviado ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>

                            {{ $emailEnviado ? 'Último envío de correo SMTP correcto' : 'Último envío de correo SMTP fallido' }}
                        </div>

                        @if(! $emailEnviado && $outlookUrl)

                            <a
                                href="{{ $outlookUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group/outlook inline-flex items-center justify-center gap-1.5 rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-medium text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md active:scale-[0.98]"
                            >
                                <i data-lucide="external-link" stroke-width="1.8" class="h-3.5 w-3.5 transition-transform duration-200 motion-safe:group-hover/outlook:translate-x-0.5 motion-safe:group-hover/outlook:-translate-y-0.5"></i>

                                Reportar por Outlook 365
                            </a>

                        @endif

                    </div>

                @endif


                {{-- BOTONES --}}
<div
    id="accionesSolicitud"
    class="hidden flex flex-col-reverse justify-end gap-3 pb-10 sm:flex-row"
>

    <button
        type="button"
        id="btnCancelar"
        class="inline-flex items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-muted-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:text-foreground hover:shadow-md active:scale-[0.98]"
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
        class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/send:translate-x-0.5"
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
@if(session('success'))

    <div
        id="modalSolicitud"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-[2px]"
    >

        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-border bg-white shadow-2xl shadow-slate-950/20">

            <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl"></span>

            {{-- Cabecera --}}

            <div class="relative px-7 pb-6 pt-8 text-center">

                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border shadow-sm
                           {{ $emailEnviado ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}"
                >
                    <i
                        data-lucide="{{ $emailEnviado ? 'circle-check-big' : 'mail-warning' }}"
                        stroke-width="1.8"
                        class="h-8 w-8 {{ $emailEnviado ? 'text-emerald-600' : 'text-amber-600' }}"
                    ></i>
                </div>

                <h3 class="text-lg font-semibold text-foreground mt-5">
                    {{ $emailEnviado ? 'Solicitud enviada' : 'Solicitud registrada con advertencia' }}
                </h3>

                <p class="text-sm text-muted-foreground leading-relaxed mt-2 max-w-sm mx-auto">
                    {{ session('success') }}
                </p>

                @if(session('folio'))

                    <span class="inline-flex items-center rounded-full bg-muted px-3 py-1 text-xs font-semibold text-foreground mt-4">
                        {{ session('folio') }}
                    </span>

                @endif

            </div>

            {{-- Estado SMTP --}}

            <div class="relative px-7 pb-7">

                <div
                    class="rounded-2xl border p-5 text-left shadow-sm
                           {{ $emailEnviado
                                ? 'border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50'
                                : 'border-amber-200 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50'
                           }}"
                >
                    <div class="grid grid-cols-[40px_minmax(0,1fr)] items-start gap-4">

                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border bg-white shadow-sm
                                   {{ $emailEnviado ? 'border-emerald-200 text-emerald-600' : 'border-amber-200 text-amber-600' }}"
                        >
                            <i
                                data-lucide="{{ $emailEnviado ? 'mail-check' : 'mail-warning' }}"
                                stroke-width="1.8"
                                class="h-5 w-5"
                            ></i>
                        </div>

                        <div class="min-w-0">

                            <p class="text-sm font-semibold {{ $emailEnviado ? 'text-emerald-800' : 'text-amber-800' }}">
                                {{ $emailEnviado ? 'Correo enviado correctamente' : 'No se pudo enviar el correo' }}
                            </p>

                            <p class="mt-1.5 text-xs leading-relaxed {{ $emailEnviado ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $emailEnviado
                                    ? 'El servidor SMTP aceptó la notificación para el equipo de soporte TI.'
                                    : 'La solicitud quedó registrada. Puedes informar la falla mediante Outlook 365.' }}
                            </p>

                            @if(! $emailEnviado && $outlookUrl)

                                <a
                                    href="{{ $outlookUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group/outlook-modal mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-xs font-semibold text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md active:scale-[0.98]"
                                >
                                    <i data-lucide="external-link" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/outlook-modal:translate-x-0.5 motion-safe:group-hover/outlook-modal:-translate-y-0.5"></i>

                                    Reportar mediante Outlook 365
                                </a>

                            @endif

                        </div>

                    </div>

                </div>

                <div class="mt-5 flex items-start gap-3 rounded-xl border border-primary/10 bg-primary/[0.04] p-4">
                    <i data-lucide="info" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-primary"></i>

                    <p class="text-xs text-muted-foreground leading-relaxed">
                        La solicitud permanecerá disponible en el historial, incluso si la notificación no pudo enviarse.
                    </p>
                </div>

            </div>

            {{-- Acciones --}}

            <div class="border-t border-border bg-muted/20 px-7 py-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    <button
                        type="button"
                        id="cerrarModalSolicitud"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:shadow-md active:scale-[0.98]"
                    >
                        Cerrar
                    </button>

                    <a
                        href="{{ route('mis-solicitudes') }}"
                        class="group/history-modal inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md active:scale-[0.98]"
                    >
                        <i data-lucide="history" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/history-modal:-rotate-12"></i>

                        Mis solicitudes
                    </a>

                </div>

            </div>

        </div>

    </div>

@endif


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