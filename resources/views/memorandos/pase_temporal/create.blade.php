@extends('layouts.app')

@section('title', 'Pase menor a 24 horas')

@section('content')

<link
    rel="stylesheet"
    href="{{ asset('css/pases.css') }}"
>

<link
    rel="stylesheet"
    href="{{ asset('css/autorizacion.css') }}"
>


<div class="min-h-screen bg-background">

    <form
        id="documentForm"
        method="POST"
        action="{{ route('memorandos.pase_temporal.store') }}"
    >
        @csrf


        <input
            type="hidden"
            name="tipo_documento"
            value="pase_temporal"
        >


        <input
            type="hidden"
            name="tipo_id"
            value="{{ $tipoPase->id ?? '' }}"
        >


        <main class="mx-auto max-w-[1300px] space-y-6 px-6 py-10">

            {{-- HEADER --}}

<section class="mb-8">

    <div
        class="flex flex-col gap-5
               sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex items-center gap-4">

            {{-- Icono --}}
            <div
                class="flex h-12 w-12 shrink-0 items-center
                       justify-center rounded-xl
                       border border-primary/10 bg-primary/10
                       text-primary shadow-sm
                       transition-all duration-300
                       hover:bg-primary/15
                       dark:border-blue-800/60
                       dark:bg-blue-950/45
                       dark:text-blue-400
                       dark:hover:border-blue-700/70
                       dark:hover:bg-blue-900/50
                       motion-safe:hover:scale-105"
            >
                <i
                    data-lucide="clock-3"
                    stroke-width="1.8"
                    class="h-6 w-6"
                ></i>
            </div>

            {{-- Título y descripción --}}
            <div class="min-w-0">

                <h1
                    class="text-2xl font-semibold tracking-tight
                           text-foreground dark:text-slate-100"
                >
                    Pase menor a 24 horas
                </h1>

                <p
                    class="mt-1.5 max-w-2xl text-sm leading-relaxed
                           text-muted-foreground dark:text-slate-400"
                >
                    Complete los campos requeridos para solicitar una
                    autorización temporal de ingreso de equipo tecnológico.
                </p>

            </div>

        </div>

        {{-- Mis pases --}}
        <a
            href="{{ route('memorandos.mis-pases') }}"
            class="group/history inline-flex items-center
                   justify-center gap-2 rounded-xl
                   border border-primary/10 bg-primary/[0.06]
                   px-4 py-2.5 text-sm font-medium
                   text-primary shadow-sm
                   transition-all duration-200
                   hover:border-primary/20 hover:bg-primary/10
                   hover:shadow-md
                   dark:border-blue-800/60
                   dark:bg-blue-950/35
                   dark:text-blue-300
                   dark:hover:border-blue-700/80
                   dark:hover:bg-blue-900/50
                   dark:hover:text-blue-200
                   dark:hover:shadow-black/20
                   active:scale-[0.98]"
        >
            <i
                data-lucide="history"
                stroke-width="1.8"
                class="h-4 w-4 transition-transform duration-300
                       motion-safe:group-hover/history:-rotate-12"
            ></i>

            Mis pases
        </a>

    </div>

</section>


            {{-- INFORMACIÓN DEL DOCUMENTO --}}

            @include('memorandos.partials.informacion_documento')


            {{-- FORMULARIO DEL PASE --}}

            @include('memorandos.formularios.autorizacion')

        </main>


        {{-- ESTADO SMTP Y BOTÓN DE ENVÍO --}}

        <div
            class="mx-auto flex max-w-[1300px] flex-col gap-4 px-6 pb-10 pt-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">

                {{-- Estado inicial/último resultado --}}

                <div
                    id="smtpEstadoPase"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-300 dark:shadow-black/20"
                >
                    <span class="relative flex h-2.5 w-2.5 shrink-0">

                        <span class="absolute inline-flex h-full w-full rounded-full bg-slate-300 opacity-60 dark:bg-slate-500/60"></span>

                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>

                    </span>

                    El correo SMTP se comprobará al enviar
                </div>


                {{-- Se mantiene disponible después de cerrar el modal --}}

                <button
                    type="button"
                    id="btnReportarSmtpPasePersistente"
                    data-recipient="helpdesk@televicentro.hn"
                    data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                    data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                    class="group/report hidden items-center justify-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800 dark:border-amber-800 dark:bg-amber-950/45 dark:text-amber-300 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 dark:hover:border-amber-700 dark:hover:bg-amber-900/55 hover:shadow-md active:scale-[0.98]"
                >
                    <i data-lucide="external-link" stroke-width="1.8" class="h-3.5 w-3.5 transition-transform duration-200 motion-safe:group-hover/report:translate-x-0.5 motion-safe:group-hover/report:-translate-y-0.5"></i>

                    Reportar por Outlook 365
                </button>

            </div>


            <button
                id="btnEnviar"
                type="submit"
                class="group/send inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md hover:shadow-primary/25 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:shadow-sm"
            >
                <i
                    id="btnEnviarIcono"
                    data-lucide="send"
                    stroke-width="1.8"
                    class="h-4 w-4 transition-transform duration-200"
                ></i>

                <span id="btnEnviarTexto">
                    Enviar solicitud
                </span>
            </button>

        </div>

    </form>

    @include('partials.support-widget')

</div>




{{-- MODAL DE RESULTADO --}}

<div
    id="modalResultado"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalTitulo"
    aria-describedby="modalMensaje"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm dark:bg-black/70"
>
    <div
        class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-border bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-700/70 dark:bg-slate-900 dark:shadow-black/50"
    >

        <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl"></span>

        {{-- CABECERA --}}

        <div class="relative px-7 pb-6 pt-8 text-center">

            <div
                id="modalIcono"
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/45"
            >
                <i
                    data-lucide="circle-check-big"
                    stroke-width="1.8"
                    class="h-8 w-8 text-emerald-600 dark:text-emerald-400"
                ></i>
            </div>


            <h2
                id="modalTitulo"
                class="mt-5 text-lg font-semibold text-foreground dark:text-slate-100"
            >
                Solicitud enviada
            </h2>


            <p
                id="modalMensaje"
                class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground dark:text-slate-400"
            >
                La solicitud del pase menor a 24 horas fue registrada correctamente.
            </p>

        </div>


        {{-- ESTADO DEL CORREO --}}

        <div class="relative px-7 pb-7">

            <div
                id="estadoCorreoPase"
                class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 dark:border-emerald-800 dark:from-emerald-950/45 dark:via-slate-900 dark:to-teal-950/30 p-5 text-left shadow-sm"
            >
                {{-- Grid evita que el JS saque el icono de su cuadro --}}

                <div
                    class="grid grid-cols-[40px_minmax(0,1fr)]
                           items-start gap-4"
                >
                    <div
                        id="estadoCorreoPaseIconoContenedor"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-white text-emerald-600 dark:border-emerald-800 dark:bg-slate-900 dark:text-emerald-400 shadow-sm"
                    >
                        <i
                            data-lucide="mail-check"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        ></i>
                    </div>


                    <div class="min-w-0">

                        <p
                            id="estadoCorreoPaseTitulo"
                            class="text-sm font-semibold text-emerald-800 dark:text-emerald-300"
                        >
                            Correo enviado correctamente
                        </p>


                        <p
                            id="estadoCorreoPaseMensaje"
                            class="mt-1.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400"
                        >
                            El servidor SMTP aceptó la notificación.
                        </p>


                        {{-- Solo aparece cuando falle SMTP --}}

                        <button
                            type="button"
                            id="btnReportarSmtpPase"
                            data-recipient="helpdesk@televicentro.hn"
                            data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                            data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                            class="group/report-modal mt-4 hidden w-full items-center justify-center gap-2 rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-xs font-semibold text-amber-800 dark:border-amber-800 dark:bg-slate-900 dark:text-amber-300 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 dark:hover:border-amber-700 dark:hover:bg-amber-900/55 hover:shadow-md active:scale-[0.98]"
                        >
                            <i data-lucide="external-link" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/report-modal:translate-x-0.5 motion-safe:group-hover/report-modal:-translate-y-0.5"></i>

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
                    La gestión permanecerá registrada en el historial de pases,
                    incluso si la notificación por correo no pudo enviarse.
                </p>

            </div>

        </div>


        {{-- ACCIONES --}}

        <div class="border-t border-border bg-muted/20 px-7 py-5 dark:border-slate-700/70 dark:bg-slate-950/30">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <button
                    type="button"
                    id="cerrarModal"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:shadow-md dark:border-slate-700/70 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-700 active:scale-[0.98]"
                >
                    Cerrar
                </button>


                <a
                    href="{{ route('memorandos.mis-pases') }}"
                    class="group/history-modal inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md active:scale-[0.98]"
                >
                    <i data-lucide="history" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/history-modal:-rotate-12"></i>

                    Ver mis pases
                </a>

            </div>

        </div>

    </div>
</div>


{{-- Solamente el JavaScript específico del pase temporal --}}

<script src="{{ asset('js/pase_temporal.js') }}"></script>

@endsection