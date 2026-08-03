@extends('layouts.app')

@section('title', 'Pase mayor a 24 horas')

@section('content')

<link rel="stylesheet" href="{{ asset('css/pases.css') }}">
<link rel="stylesheet" href="{{ asset('css/autorizacion.css') }}">

<div class="min-h-screen bg-background">


<form
    id="documentForm"
    method="POST"
    action="{{ route('memorandos.store') }}">

    @csrf


    <input
        type="hidden"
        name="tipo_documento"
        value="autorizacion">


    <input
        type="hidden"
        name="tipo_id"
        value="{{ $tipoAutorizacion->id }}">



    <main class="max-w-[1300px] mx-auto px-6 py-10 space-y-6">


        {{-- TITULO DE PAGINA --}}

        <section class="mb-8">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:bg-primary/15 dark:border-blue-800/60 dark:bg-blue-950/45 dark:text-blue-400 dark:hover:border-blue-700/70 dark:hover:bg-blue-900/50 motion-safe:hover:scale-105">

                        <i
                            data-lucide="file-signature"
                            stroke-width="1.8"
                            class="h-6 w-6">
                        </i>

                    </div>

                    <div class="min-w-0">

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight dark:text-slate-100">
                        Pase mayor a 24 horas
                    </h1>


                    <p class="text-sm text-muted-foreground mt-1.5 leading-relaxed dark:text-slate-400">
                        Complete los campos requeridos para solicitar una autorización de ingreso de equipo tecnológico.
                    </p>

                    </div>

                </div>



                <a
                    href="{{ route('memorandos.mis-pases') }}"
                    class="group/history inline-flex items-center justify-center gap-2 rounded-xl border border-primary/10 bg-primary/[0.06] px-4 py-2.5 text-sm font-medium text-primary shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow-md dark:border-blue-800/60 dark:bg-blue-950/35 dark:text-blue-300 dark:hover:border-blue-700/80 dark:hover:bg-blue-900/50 dark:hover:text-blue-200 dark:hover:shadow-black/20 active:scale-[0.98]">

                    <i
                        data-lucide="history"
                        stroke-width="1.8"
                        class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/history:-rotate-12">
                    </i>

                    Mis pases

                </a>

            </div>


        </section>




        {{-- TIPO DE GESTIÓN --}}


        <section class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700/70 dark:hover:border-blue-700/70 dark:hover:shadow-black/20">

            <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125"></span>


            <div class="relative flex items-center gap-3 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4 dark:border-slate-700/70 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900">


                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white shadow-sm transition-transform duration-300 motion-safe:group-hover:scale-105">
                    1
                </span>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">
                        Tipo de gestión
                    </h2>

                </div>


            </div>



            <div class="relative px-6 py-5">

<div class="group/type inline-flex w-full max-w-md items-center gap-3 rounded-xl border border-primary/30 bg-gradient-to-br from-primary/[0.08] via-white to-blue-50/70 px-4 py-3.5 shadow-sm transition-all duration-300 hover:border-primary/50 hover:shadow-md dark:border-blue-800/60 dark:from-blue-950/35 dark:via-slate-900 dark:to-blue-950/25 dark:hover:border-blue-700/80 dark:hover:shadow-black/20 motion-safe:hover:-translate-y-0.5">

    {{-- Icono --}}
    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-all duration-300 group-hover/type:bg-primary/15 motion-safe:group-hover/type:scale-105">

        <i data-lucide="file-text"
           stroke-width="1.8"
           class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/type:scale-110">
        </i>

    </div>


    {{-- Información --}}
    <div class="mr-3 min-w-0 flex-1">

        <p class="text-sm font-semibold text-primary">
            {{ $tipoAutorizacion->nombre_visual }}
        </p>


        <p class="text-xs text-muted-foreground mt-0.5">
            Solicitud de memorando de autorización
        </p>

    </div>


    {{-- Radio seleccionado --}}
    <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-primary bg-primary shadow-sm">

        <div class="h-1.5 w-1.5 rounded-full bg-white"></div>

    </div>


</div>


            </div>


        </section>




        {{-- INFORMACIÓN DOCUMENTO --}}

        @include('memorandos.partials.informacion_documento')




        {{-- FORMULARIO ESPECÍFICO --}}

        @include('memorandos.formularios.autorizacion')



    </main>




    {{-- BOTONES --}}

    <div class="mx-auto flex max-w-[1300px] flex-col gap-4 px-6 pb-10 pt-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">

            <div id="smtpEstadoPrevio" class="inline-flex items-center gap-2 rounded-lg border border-border bg-white px-3 py-2 text-xs text-muted-foreground shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-300 dark:shadow-black/20">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-slate-300 opacity-60 dark:bg-slate-500/60"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                </span>

                El correo SMTP se comprobará al enviar
            </div>

            <button
                type="button"
                id="btnReportarSmtpPersistente"
                data-recipient="helpdesk@televicentro.hn"
                data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                class="group/report hidden items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md dark:border-amber-800 dark:bg-amber-950/45 dark:text-amber-300 dark:hover:border-amber-700 dark:hover:bg-amber-900/55 active:scale-[0.98]">

                <i data-lucide="external-link" stroke-width="1.8" class="h-3.5 w-3.5 transition-transform duration-200 motion-safe:group-hover/report:translate-x-0.5 motion-safe:group-hover/report:-translate-y-0.5"></i>

                Reportar por Outlook 365

            </button>

        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">




        <button
            type="submit"
            id="btnGenerar"
            class="group/send inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md hover:shadow-primary/25 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:shadow-sm">


            <i id="btnGenerarIcono" data-lucide="send"
               stroke-width="1.8"
               class="h-4 w-4 transition-transform duration-200">
            </i>


            <span id="btnGenerarTexto">Generar y enviar</span>


        </button>

        </div>


    </div>



</form>

</div>



{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL: Preview del documento                               --}}
{{-- ═══════════════════════════════════════════════════════════ --}}

<div
    id="modalPreview"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tituloModalPreview"
    class="fixed inset-0 z-[9998] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm dark:bg-black/70">

    <div class="relative flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-border bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-700/70 dark:bg-slate-900 dark:shadow-black/50">


        {{-- Header del modal --}}
        <div class="relative flex flex-shrink-0 items-center justify-between border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4 dark:border-slate-700/70 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i data-lucide="eye" stroke-width="1.8" class="h-4 w-4"></i>
                </div>

                <div>
                    <h3 id="tituloModalPreview" class="text-sm font-semibold text-foreground dark:text-slate-100">Preview del documento</h3>
                    <p class="text-xs text-muted-foreground dark:text-slate-400">Así quedará el memorando</p>
                </div>

            </div>

            <button
                type="button"
                id="btnCerrarPreview"
                aria-label="Cerrar vista previa"
                class="group/close inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-white text-muted-foreground transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/10 dark:border-slate-700/70 dark:bg-slate-800 dark:text-slate-400 dark:hover:border-red-800 dark:hover:bg-red-950/45 dark:hover:text-red-400">
                <i data-lucide="x" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/close:rotate-90"></i>
            </button>

        </div>


        {{-- Contenido del preview --}}
        <div id="contenidoPreview" class="flex-1 overflow-y-auto bg-slate-50/40 p-6 dark:bg-slate-950/45">

            <div class="flex items-center justify-center py-12">
                <div class="flex flex-col items-center gap-3 text-muted-foreground dark:text-slate-400">
                    <svg class="animate-spin w-6 h-6" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-sm">Cargando preview...</span>
                </div>
            </div>

        </div>


        {{-- Footer del modal --}}
        <div class="flex flex-shrink-0 flex-col-reverse justify-end gap-3 border-t border-border bg-white px-6 py-4 dark:border-slate-700/70 dark:bg-slate-900 sm:flex-row">

            <button
                type="button"
                id="btnCerrarPreview2"
                class="rounded-xl border border-border bg-white px-4 py-2.5 text-sm font-medium text-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:shadow-md dark:border-slate-700/70 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-700 active:scale-[0.98]">
                Cerrar
            </button>

            <button
                type="button"
                id="btnGenerarDesdePreview"
                class="group/send-preview inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md active:scale-[0.98]">
                <i data-lucide="send" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/send-preview:translate-x-0.5 motion-safe:group-hover/send-preview:-translate-y-0.5"></i>
                Generar y enviar
            </button>

        </div>


    </div>

</div>


{{-- MODAL: RESULTADO DEL DOCUMENTO Y DEL CORREO --}}

<div
    id="modalDescarga"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalResultadoTitulo"
    aria-describedby="modalResultadoMensaje"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm dark:bg-black/70"
>
    <div
        class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-border bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-700/70 dark:bg-slate-900 dark:shadow-black/50"
    >

        <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl"></span>

        {{-- CABECERA --}}

        <div class="relative px-7 pb-6 pt-8 text-center">

            <div
                id="modalResultadoIcono"
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/45"
            >
                <i
                    data-lucide="circle-check-big"
                    stroke-width="1.8"
                    class="h-8 w-8 text-emerald-600 dark:text-emerald-400"
                ></i>
            </div>


            <h2
                id="modalResultadoTitulo"
                class="mt-5 text-lg font-semibold text-foreground dark:text-slate-100"
            >
                Documento generado
            </h2>


            <p
                id="modalResultadoMensaje"
                class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground dark:text-slate-400"
            >
                El memorando fue generado correctamente. La notificación por correo se está procesando.
            </p>

        </div>


        {{-- CONTENIDO --}}

        <div class="relative px-7 pb-7">

            <div
                id="estadoCorreoAutorizacion"
                class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 p-5 text-left shadow-sm dark:border-emerald-800 dark:from-emerald-950/45 dark:via-slate-900 dark:to-teal-950/30"
            >
                {{--
                    Se usa grid en lugar de flex para que autorizacion.js
                    encuentre como primer .flex el cuadrito del icono y no
                    mueva el icono fuera de este contenedor.
                --}}
                <div class="grid grid-cols-[40px_minmax(0,1fr)] items-start gap-4">

                    {{-- ICONO DE ESTADO --}}

                    <div
                        id="estadoCorreoIconoContenedor"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-white text-emerald-600 shadow-sm dark:border-emerald-800 dark:bg-slate-900 dark:text-emerald-400"
                    >
                        <i
                            data-lucide="mail-check"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        ></i>
                    </div>


                    {{-- INFORMACIÓN DEL ESTADO --}}

                    <div class="min-w-0 flex-1">

                        <p
                            id="estadoCorreoTitulo"
                            class="text-sm font-semibold text-emerald-800 dark:text-emerald-300"
                        >
                            Correo enviado correctamente
                        </p>


                        <p
                            id="estadoCorreoMensaje"
                            class="mt-1.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400"
                        >
                            El servidor SMTP aceptó la notificación.
                        </p>


                        {{-- APARECE SOLAMENTE CUANDO SMTP FALLA --}}

                        <button
                            type="button"
                            id="btnReportarSmtp"
                            data-recipient="helpdesk@televicentro.hn"
                            data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                            data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                            class="group/report-modal mt-4 hidden w-full items-center justify-center gap-2 rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-xs font-semibold text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md dark:border-amber-800 dark:bg-slate-900 dark:text-amber-300 dark:hover:border-amber-700 dark:hover:bg-amber-900/55 active:scale-[0.98]"
                        >
                            <i
                                data-lucide="external-link"
                                stroke-width="1.8"
                                class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/report-modal:translate-x-0.5 motion-safe:group-hover/report-modal:-translate-y-0.5"
                            ></i>

                            Reportar mediante Outlook 365
                        </button>

                    </div>

                </div>
            </div>


            {{-- INFORMACIÓN ADICIONAL --}}

            <div class="mt-5 flex items-start gap-3 rounded-xl border border-blue-200/70 bg-blue-50/60 p-4 dark:border-blue-800/60 dark:bg-blue-950/25">

                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400"
                ></i>

                <p class="text-xs text-muted-foreground leading-relaxed dark:text-slate-400">

                    El documento permanecerá disponible en el historial de pases,
                    incluso si la notificación por correo no pudo enviarse.

                </p>

            </div>

        </div>


        {{-- ACCIONES --}}

        <div
            class="border-t border-border bg-muted/20 px-7 py-5 dark:border-slate-700/70 dark:bg-slate-950/30"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <button
                    type="button"
                    id="btnCerrarDescarga"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:shadow-md dark:border-slate-700/70 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-700 active:scale-[0.98]"
                >
                    Cerrar
                </button>


                <a
                    id="linkDescarga"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group/download inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md active:scale-[0.98]"
                >
                    <i
                        data-lucide="download"
                        stroke-width="1.8"
                        class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/download:translate-y-0.5"
                    ></i>

                    Descargar PDF
                </a>

            </div>


            <div class="text-center mt-4">

                <a
                    href="{{ route('memorandos.mis-pases') }}"
                    class="group/history-link inline-flex items-center justify-center gap-1.5 text-xs font-medium text-primary hover:underline"
                >
                    <i
                        data-lucide="history"
                        stroke-width="1.8"
                        class="h-3.5 w-3.5 transition-transform duration-200 motion-safe:group-hover/history-link:-rotate-12"
                    ></i>

                    Consultar historial de pases
                </a>

            </div>

        </div>

    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL: Error                                               --}}
{{-- ═══════════════════════════════════════════════════════════ --}}

<div
    id="modalErrorAutorizacion"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tituloErrorAutorizacion"
    aria-describedby="textoErrorAutorizacion"
    class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm dark:bg-black/70">

    <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-red-200 bg-white p-8 text-center shadow-2xl shadow-slate-950/20 dark:border-red-900/70 dark:bg-slate-900 dark:shadow-black/50">

        <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-red-500/10 blur-3xl"></span>


        <div class="relative mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-red-200 bg-red-50 text-red-600 shadow-sm dark:border-red-800 dark:bg-red-950/45 dark:text-red-400">
            <i data-lucide="circle-x" stroke-width="1.8" class="h-8 w-8"></i>
        </div>


        <h2 id="tituloErrorAutorizacion" class="relative mb-2 text-lg font-semibold text-foreground dark:text-slate-100">
            Error al generar
        </h2>

        <p id="textoErrorAutorizacion" class="relative mb-6 text-sm leading-relaxed text-muted-foreground dark:text-slate-400">
            Ocurrió un error al generar el documento.
        </p>


        <button
            type="button"
            id="btnCerrarErrorAutorizacion"
            class="relative inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-red-500/20 transition-all duration-200 hover:bg-red-700 hover:shadow-md active:scale-[0.98]">

            Cerrar

        </button>


    </div>

</div>



<script>
    /*
    |--------------------------------------------------------------------------
    | URLs utilizadas por autorizacion.js
    |--------------------------------------------------------------------------
    */

    window.autorizacionPreviewUrl =
        @json(
            route(
                'memorandos.preview',
                'autorizacion'
            )
        );

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

<script src="{{ asset('js/autorizacion.js') }}?v=2"></script>


@endsection