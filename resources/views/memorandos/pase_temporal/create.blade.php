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

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:bg-primary/15 motion-safe:hover:scale-105">

                            <i
                                data-lucide="clock-3"
                                stroke-width="1.8"
                                class="h-6 w-6">
                            </i>

                        </div>

                        <div class="min-w-0">

                            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                                Pase menor a 24 horas
                            </h1>

                            <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-muted-foreground">
                                Complete los campos requeridos para solicitar una autorización
                                temporal de ingreso de equipo tecnológico.
                            </p>

                        </div>

                    </div>


                    <a
                        href="{{ route('memorandos.mis-pases') }}"
                        class="group/history inline-flex items-center justify-center gap-2 rounded-xl border border-primary/10 bg-primary/[0.06] px-4 py-2.5 text-sm font-medium text-primary shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow-md active:scale-[0.98]"
                    >
                        <i data-lucide="history" stroke-width="1.8" class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/history:-rotate-12"></i>

                        Mis pases
                    </a>

                </div>

            </section>


            {{-- TIPO DE GESTIÓN --}}

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
                            Tipo de gestión
                        </h2>

                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Confirma el tipo de pase que deseas solicitar.
                        </p>

                    </div>

                </div>


                <div class="relative px-6 py-5">

                    <div
                        class="group/type inline-flex w-full max-w-md items-center gap-3 rounded-xl border border-primary/30 bg-gradient-to-br from-primary/[0.08] via-white to-blue-50/70 px-4 py-3.5 shadow-sm transition-all duration-300 hover:border-primary/50 hover:shadow-md motion-safe:hover:-translate-y-0.5"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-all duration-300 group-hover/type:bg-primary/15 motion-safe:group-hover/type:scale-105"
                        >
                            <i
                                data-lucide="clock-3"
                                stroke-width="1.8"
                                class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/type:scale-110"
                            ></i>
                        </div>


                        <div class="mr-3 min-w-0 flex-1">

                            <p class="text-sm font-semibold text-primary">
                                {{ $tipoPase->nombre_visual ?? 'Pase menor a 24 horas' }}
                            </p>

                            <p class="text-xs text-muted-foreground mt-0.5">
                                Solicitud de acceso temporal de corta duración.
                            </p>

                        </div>


                        <div
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-primary bg-primary shadow-sm"
                        >
                            <div class="h-1.5 w-1.5 rounded-full bg-white"></div>
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

        </main>


        {{-- ESTADO SMTP Y BOTÓN DE ENVÍO --}}

        <div
            class="mx-auto flex max-w-[1300px] flex-col gap-4 px-6 pb-10 pt-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">

                {{-- Estado inicial/último resultado --}}

                <div
                    id="smtpEstadoPase"
                    class="inline-flex items-center gap-2 rounded-lg border border-border bg-white px-3 py-2 text-xs text-muted-foreground shadow-sm"
                >
                    <span class="relative flex h-2.5 w-2.5 shrink-0">

                        <span class="absolute inline-flex h-full w-full rounded-full bg-slate-300 opacity-60"></span>

                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-400"></span>

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
                    class="group/report hidden items-center justify-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md active:scale-[0.98]"
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
                    class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/send:translate-x-0.5 motion-safe:group-hover/send:-translate-y-0.5"
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
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4 backdrop-blur-[2px]"
>
    <div
        class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-border bg-white shadow-2xl shadow-slate-950/20"
    >

        <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl"></span>

        {{-- CABECERA --}}

        <div class="relative px-7 pb-6 pt-8 text-center">

            <div
                id="modalIcono"
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 shadow-sm"
            >
                <i
                    data-lucide="circle-check-big"
                    stroke-width="1.8"
                    class="h-8 w-8 text-emerald-600"
                ></i>
            </div>


            <h2
                id="modalTitulo"
                class="mt-5 text-lg font-semibold text-foreground"
            >
                Solicitud enviada
            </h2>


            <p
                id="modalMensaje"
                class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground"
            >
                La solicitud del pase menor a 24 horas fue registrada correctamente.
            </p>

        </div>


        {{-- ESTADO DEL CORREO --}}

        <div class="relative px-7 pb-7">

            <div
                id="estadoCorreoPase"
                class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 p-5 text-left shadow-sm"
            >
                {{-- Grid evita que el JS saque el icono de su cuadro --}}

                <div
                    class="grid grid-cols-[40px_minmax(0,1fr)]
                           items-start gap-4"
                >
                    <div
                        id="estadoCorreoPaseIconoContenedor"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-white text-emerald-600 shadow-sm"
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
                            class="text-sm font-semibold text-emerald-800"
                        >
                            Correo enviado correctamente
                        </p>


                        <p
                            id="estadoCorreoPaseMensaje"
                            class="mt-1.5 text-xs leading-relaxed text-emerald-700"
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
                            class="group/report-modal mt-4 hidden w-full items-center justify-center gap-2 rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-xs font-semibold text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md active:scale-[0.98]"
                        >
                            <i data-lucide="external-link" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/report-modal:translate-x-0.5 motion-safe:group-hover/report-modal:-translate-y-0.5"></i>

                            Reportar mediante Outlook 365
                        </button>

                    </div>

                </div>

            </div>


            <div class="mt-5 flex items-start gap-3 rounded-xl border border-primary/10 bg-primary/[0.04] p-4">

                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="mt-0.5 h-4 w-4 shrink-0 text-primary"
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
                    class="inline-flex w-full items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:shadow-md active:scale-[0.98]"
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