@extends('layouts.app')

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


        <div class="max-w-[1300px] mx-auto px-6 space-y-5">

            {{-- HEADER --}}

            <div class="mb-8">

                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">

                    <div>

                        <h1 class="text-xl font-semibold text-foreground tracking-tight">
                            Pase menor a 24 horas
                        </h1>

                        <p class="text-sm text-muted-foreground mt-1 max-w-2xl leading-relaxed">
                            Complete los campos requeridos para solicitar una autorización
                            temporal de ingreso de equipo tecnológico.
                        </p>

                    </div>


                    <a
                        href="{{ route('memorandos.mis-pases') }}"
                        class="inline-flex items-center justify-center gap-2
                               px-4 py-2.5 rounded-xl border border-border
                               bg-white text-sm font-medium text-foreground
                               hover:bg-muted transition-colors"
                    >
                        <i data-lucide="history" class="w-4 h-4"></i>

                        Mis pases
                    </a>

                </div>

            </div>


            {{-- TIPO DE GESTIÓN --}}

            <section class="bg-card rounded-2xl border border-border overflow-hidden">

                <div class="px-6 py-4 border-b border-border flex items-center gap-3">

                    <span
                        class="w-6 h-6 rounded-full bg-primary text-white
                               text-xs font-semibold flex items-center justify-center"
                    >
                        1
                    </span>

                    <h2 class="text-sm font-semibold text-foreground">
                        Tipo de gestión
                    </h2>

                </div>


                <div class="px-6 py-5">

                    <div
                        class="inline-flex items-center gap-3 px-4 py-3.5
                               rounded-xl border-2 border-primary bg-primary/5"
                    >
                        <div
                            class="w-8 h-8 rounded-lg bg-primary/10
                                   flex items-center justify-center"
                        >
                            <i
                                data-lucide="clock"
                                class="w-4 h-4 text-primary"
                            ></i>
                        </div>


                        <div class="mr-3">

                            <p class="text-sm font-semibold text-primary">
                                {{ $tipoPase->nombre_visual ?? 'Pase menor a 24 horas' }}
                            </p>

                            <p class="text-xs text-muted-foreground mt-0.5">
                                Solicitud de acceso temporal de corta duración.
                            </p>

                        </div>


                        <div
                            class="w-4 h-4 rounded-full border-2 border-primary
                                   bg-primary flex items-center justify-center"
                        >
                            <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                        </div>

                    </div>

                </div>

            </section>


            {{-- INFORMACIÓN DEL DOCUMENTO --}}

            @include('memorandos.partials.informacion_documento')


            {{-- FORMULARIO DEL PASE --}}

            @include('memorandos.formularios.autorizacion')


            {{-- OBSERVACIONES --}}

            @include('memorandos.partials.observaciones')

        </div>


        {{-- ESTADO SMTP Y BOTÓN DE ENVÍO --}}

        <div
            class="max-w-[1300px] mx-auto px-6 py-10
                   flex flex-col sm:flex-row sm:items-center
                   sm:justify-between gap-4"
        >
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">

                {{-- Estado inicial/último resultado --}}

                <div
                    id="smtpEstadoPase"
                    class="inline-flex items-center gap-2
                           text-xs text-muted-foreground"
                >
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>

                    El correo SMTP se comprobará al enviar
                </div>


                {{-- Se mantiene disponible después de cerrar el modal --}}

                <button
                    type="button"
                    id="btnReportarSmtpPasePersistente"
                    data-recipient="helpdesk@televicentro.hn"
                    data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                    data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                    class="hidden items-center justify-center gap-1.5
                           rounded-lg border border-amber-300 bg-amber-50
                           px-3 py-1.5 text-xs font-medium text-amber-800
                           hover:bg-amber-100 transition"
                >
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>

                    Reportar por Outlook 365
                </button>

            </div>


            <button
                id="btnEnviar"
                type="submit"
                class="inline-flex items-center justify-center gap-2
                       px-6 py-2.5 rounded-xl bg-primary text-white
                       text-sm font-medium hover:opacity-90 transition
                       disabled:opacity-70 disabled:cursor-not-allowed"
            >
                <i
                    id="btnEnviarIcono"
                    data-lucide="send"
                    class="w-4 h-4"
                ></i>

                <span id="btnEnviarTexto">
                    Enviar solicitud
                </span>
            </button>

        </div>

    </form>

</div>


{{-- MODAL DE RESULTADO --}}

<div
    id="modalResultado"
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
                id="modalIcono"
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
                id="modalTitulo"
                class="text-lg font-semibold text-foreground mt-5"
            >
                Solicitud enviada
            </h2>


            <p
                id="modalMensaje"
                class="text-sm text-muted-foreground leading-relaxed
                       mt-2 max-w-sm mx-auto"
            >
                La solicitud del pase menor a 24 horas fue registrada correctamente.
            </p>

        </div>


        {{-- ESTADO DEL CORREO --}}

        <div class="px-7 pb-7">

            <div
                id="estadoCorreoPase"
                class="rounded-2xl border border-green-200
                       bg-green-50/70 p-5 text-left"
            >
                {{-- Grid evita que el JS saque el icono de su cuadro --}}

                <div
                    class="grid grid-cols-[40px_minmax(0,1fr)]
                           items-start gap-4"
                >
                    <div
                        id="estadoCorreoPaseIconoContenedor"
                        class="w-10 h-10 rounded-xl bg-white border
                               border-border flex items-center
                               justify-center shrink-0"
                    >
                        <i
                            data-lucide="mail-check"
                            class="w-5 h-5 text-green-600"
                        ></i>
                    </div>


                    <div class="min-w-0">

                        <p
                            id="estadoCorreoPaseTitulo"
                            class="text-sm font-semibold text-green-800"
                        >
                            Correo enviado correctamente
                        </p>


                        <p
                            id="estadoCorreoPaseMensaje"
                            class="text-xs text-green-700 leading-relaxed mt-1.5"
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
                            class="hidden w-full mt-4 items-center justify-center
                                   gap-2 rounded-xl border border-amber-300
                                   bg-white px-4 py-2.5 text-xs font-semibold
                                   text-amber-800 hover:bg-amber-100
                                   hover:border-amber-400 transition"
                        >
                            <i data-lucide="external-link" class="w-4 h-4"></i>

                            Reportar mediante Outlook 365
                        </button>

                    </div>

                </div>

            </div>


            <div class="flex items-start gap-3 mt-5 px-1">

                <i
                    data-lucide="info"
                    class="w-4 h-4 text-muted-foreground shrink-0 mt-0.5"
                ></i>

                <p class="text-xs text-muted-foreground leading-relaxed">
                    La gestión permanecerá registrada en el historial de pases,
                    incluso si la notificación por correo no pudo enviarse.
                </p>

            </div>

        </div>


        {{-- ACCIONES --}}

        <div class="border-t border-border bg-muted/20 px-7 py-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <button
                    type="button"
                    id="cerrarModal"
                    class="w-full inline-flex items-center justify-center
                           px-5 py-2.5 rounded-xl border border-border
                           bg-white text-sm font-medium text-foreground
                           hover:bg-muted transition"
                >
                    Cerrar
                </button>


                <a
                    href="{{ route('memorandos.mis-pases') }}"
                    class="w-full inline-flex items-center justify-center
                           gap-2 px-5 py-2.5 rounded-xl bg-primary
                           text-white text-sm font-medium
                           hover:opacity-90 transition"
                >
                    <i data-lucide="history" class="w-4 h-4"></i>

                    Ver mis pases
                </a>

            </div>

        </div>

    </div>
</div>


{{-- Solamente el JavaScript específico del pase temporal --}}

<script src="{{ asset('js/pase_temporal.js') }}"></script>

@endsection