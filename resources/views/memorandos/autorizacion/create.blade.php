@extends('layouts.app')

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



    <div class="max-w-[1300px] mx-auto px-6  space-y-5">


        {{-- TITULO DE PAGINA --}}

        <div class="mb-8">



            <div class="flex items-start justify-between gap-4">


                <div>

                    <h1 class="text-xl font-semibold text-foreground tracking-tight">
                        Generación de memorandos internos
                    </h1>


                    <p class="text-sm text-muted-foreground mt-1">
                        Complete los campos requeridos para generar el documento oficial de autorización.
                    </p>

                </div>



                <a
    href="{{ route('memorandos.mis-pases') }}"
    class="inline-flex items-center gap-2 px-4 py-2.5
           rounded-xl border border-border bg-white
           text-sm font-medium text-foreground
           hover:bg-muted transition-colors"
>
    <i data-lucide="history" class="w-4 h-4"></i>

    Mis pases
</a>

            </div>


        </div>




        {{-- TIPO DE GESTIÓN --}}


        <div class="bg-card rounded-2xl border border-border overflow-hidden">


            <div class="px-6 py-4 border-b border-border flex items-center gap-3">


                <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
                    1
                </span>


                <h2 class="text-sm font-semibold text-foreground">
                    Tipo de gestión
                </h2>


            </div>



            <div class="px-6 py-5">

<div class="inline-flex items-center gap-3 px-4 py-3.5 rounded-xl border-2 border-primary bg-primary/5 cursor-pointer">

    {{-- Icono --}}
    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">

        <i data-lucide="file-text"
           class="w-4 h-4 text-primary">
        </i>

    </div>


    {{-- Información --}}
    <div class="mr-3">

        <p class="text-sm font-semibold text-primary">
            {{ $tipoAutorizacion->nombre_visual }}
        </p>


        <p class="text-xs text-muted-foreground mt-0.5">
            Solicitud de memorando de autorización
        </p>

    </div>


    {{-- Radio seleccionado --}}
    <div class="w-4 h-4 rounded-full border-2 border-primary bg-primary flex items-center justify-center">

        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>

    </div>


</div>


            </div>


        </div>




        {{-- INFORMACIÓN DOCUMENTO --}}

        @include('memorandos.partials.informacion_documento')




        {{-- FORMULARIO ESPECÍFICO --}}

        @include('memorandos.formularios.autorizacion')



    </div>




    {{-- BOTONES --}}

    <div class="max-w-[1300px] mx-auto px-6 py-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">

            <div id="smtpEstadoPrevio" class="inline-flex items-center gap-2 text-xs text-muted-foreground">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-slate-300 opacity-60"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                </span>

                El correo SMTP se comprobará al enviar
            </div>

            <button
                type="button"
                id="btnReportarSmtpPersistente"
                data-recipient="helpdesk@televicentro.hn"
                data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                class="hidden items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-800 hover:bg-amber-100 transition">

                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>

                Reportar por Outlook 365

            </button>

        </div>

        <div class="flex justify-end gap-3">


        <button
            type="button"
            id="btnPreview"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-border bg-white text-sm font-medium text-foreground hover:bg-muted transition">


            <i data-lucide="eye"
               class="w-4 h-4">
            </i>


            Ver preview


        </button>




        <button
            type="submit"
            id="btnGenerar"
            class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-medium hover:opacity-90 transition disabled:opacity-70 disabled:cursor-not-allowed">


            <i id="btnGenerarIcono" data-lucide="send"
               class="w-4 h-4">
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
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">


        {{-- Header del modal --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-border flex-shrink-0">

            <div class="flex items-center gap-3">

                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <i data-lucide="eye" class="w-4 h-4 text-primary"></i>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-foreground">Preview del documento</h3>
                    <p class="text-xs text-muted-foreground">Así quedará el memorando</p>
                </div>

            </div>

            <button
                id="btnCerrarPreview"
                class="p-2 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

        </div>


        {{-- Contenido del preview --}}
        <div id="contenidoPreview" class="overflow-y-auto flex-1 p-6">

            <div class="flex items-center justify-center py-12">
                <div class="flex flex-col items-center gap-3 text-muted-foreground">
                    <svg class="animate-spin w-6 h-6" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-sm">Cargando preview...</span>
                </div>
            </div>

        </div>


        {{-- Footer del modal --}}
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-border bg-muted/30 flex-shrink-0">

            <button
                id="btnCerrarPreview2"
                class="px-4 py-2 rounded-xl border border-border text-sm font-medium text-foreground hover:bg-muted transition">
                Cerrar
            </button>

            <button
                type="button"
                id="btnGenerarDesdePreview"
                class="flex items-center gap-2 px-5 py-2 rounded-xl bg-primary text-white text-sm font-medium hover:opacity-90 transition">
                <i data-lucide="send" class="w-4 h-4"></i>
                Generar y enviar
            </button>

        </div>


    </div>

</div>


{{-- MODAL: RESULTADO DEL DOCUMENTO Y DEL CORREO --}}

<div
    id="modalDescarga"
    class="fixed inset-0 z-50 hidden items-center justify-center
           bg-black/40 backdrop-blur-sm p-4"
>
    <div
        class="bg-white rounded-2xl shadow-2xl
               max-w-lg w-full overflow-hidden"
    >

        {{-- CABECERA --}}

        <div class="px-7 pt-8 pb-6 text-center">

            <div
                id="modalResultadoIcono"
                class="w-16 h-16 rounded-2xl bg-green-50 border
                       border-green-200 flex items-center justify-center
                       mx-auto"
            >
                <i
                    data-lucide="check-circle"
                    class="w-8 h-8 text-green-600"
                ></i>
            </div>


            <h2
                id="modalResultadoTitulo"
                class="text-lg font-semibold text-foreground mt-5"
            >
                Documento generado y enviado
            </h2>


            <p
                id="modalResultadoMensaje"
                class="text-sm text-muted-foreground leading-relaxed
                       mt-2 max-w-sm mx-auto"
            >
                El memorando fue generado correctamente y enviado al equipo responsable.
            </p>

        </div>


        {{-- CONTENIDO --}}

        <div class="px-7 pb-7">

            <div
                id="estadoCorreoAutorizacion"
                class="rounded-2xl border border-green-200
                       bg-green-50/70 p-5 text-left"
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
                        class="w-10 h-10 rounded-xl bg-white border
                               border-border flex items-center
                               justify-center shrink-0"
                    >
                        <i
                            data-lucide="mail-check"
                            class="w-5 h-5 text-green-600"
                        ></i>
                    </div>


                    {{-- INFORMACIÓN DEL ESTADO --}}

                    <div class="min-w-0 flex-1">

                        <p
                            id="estadoCorreoTitulo"
                            class="text-sm font-semibold text-green-800"
                        >
                            Correo enviado correctamente
                        </p>


                        <p
                            id="estadoCorreoMensaje"
                            class="text-xs text-green-700 leading-relaxed mt-1.5"
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
                            class="hidden w-full mt-4 items-center justify-center
                                   gap-2 rounded-xl border border-amber-300
                                   bg-white px-4 py-2.5 text-xs font-semibold
                                   text-amber-800 hover:bg-amber-100
                                   hover:border-amber-400 transition"
                        >
                            <i
                                data-lucide="external-link"
                                class="w-4 h-4"
                            ></i>

                            Reportar mediante Outlook 365
                        </button>

                    </div>

                </div>
            </div>


            {{-- INFORMACIÓN ADICIONAL --}}

            <div class="flex items-start gap-3 mt-5 px-1">

                <i
                    data-lucide="info"
                    class="w-4 h-4 text-muted-foreground shrink-0 mt-0.5"
                ></i>

                <p class="text-xs text-muted-foreground leading-relaxed">

                    El documento permanecerá disponible en el historial de pases,
                    incluso si la notificación por correo no pudo enviarse.

                </p>

            </div>

        </div>


        {{-- ACCIONES --}}

        <div
            class="border-t border-border bg-muted/20
                   px-7 py-5"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <button
                    type="button"
                    id="btnCerrarDescarga"
                    class="w-full inline-flex items-center justify-center
                           px-5 py-2.5 rounded-xl border border-border
                           bg-white text-sm font-medium text-foreground
                           hover:bg-muted transition"
                >
                    Cerrar
                </button>


                <a
                    id="linkDescarga"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full inline-flex items-center justify-center
                           gap-2 px-5 py-2.5 rounded-xl bg-primary
                           text-white text-sm font-medium
                           hover:opacity-90 transition"
                >
                    <i
                        data-lucide="download"
                        class="w-4 h-4"
                    ></i>

                    Descargar PDF
                </a>

            </div>


            <div class="text-center mt-4">

                <a
                    href="{{ route('memorandos.mis-pases') }}"
                    class="inline-flex items-center justify-center gap-1.5
                           text-xs font-medium text-primary hover:underline"
                >
                    <i
                        data-lucide="history"
                        class="w-3.5 h-3.5"
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
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">

    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center">


        <div class="w-16 h-16 rounded-full bg-red-50 border-2 border-red-200 flex items-center justify-center mx-auto mb-5">
            <i data-lucide="x-circle" class="w-8 h-8 text-red-500"></i>
        </div>


        <h2 class="text-lg font-semibold text-foreground mb-2">
            Error al generar
        </h2>

        <p id="textoErrorAutorizacion" class="text-sm text-muted-foreground mb-6">
            Ocurrió un error al generar el documento.
        </p>


        <button
            id="btnCerrarErrorAutorizacion"
            class="px-5 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:opacity-90 transition">

            Cerrar

        </button>


    </div>

</div>



<script>
    // Pasamos la URL del preview al JS
    window.autorizacionPreviewUrl = '{{ route('memorandos.preview', 'autorizacion') }}';
</script>

<script src="{{ asset('js/autorizacion.js') }}"></script>


@endsection