@extends('layouts.app')

@section('title', 'Reporte de incidencia')

@section('content')

<div class="min-h-screen bg-background">

<main class="max-w-5xl mx-auto px-6 py-8 space-y-8">

    {{-- HEADER --}}

    <section class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">

        <div>

            <h1 class="text-xl font-semibold text-foreground">
                Reporte de incidencia
            </h1>

            <p class="text-sm text-muted-foreground mt-1 max-w-2xl leading-relaxed">
                Describe el problema que estás presentando y adjunta evidencia
                para que TI pueda ayudarte.
            </p>

        </div>


        <a
            href="{{ route('mis-incidencias') }}"
            class="inline-flex items-center justify-center gap-2
                   px-4 py-2.5 rounded-xl border border-border
                   bg-white text-sm font-medium text-foreground
                   hover:bg-muted transition"
        >
            <i data-lucide="history" class="w-4 h-4"></i>

            Mis incidencias
        </a>

    </section>


    <form
        id="incidenciaForm"
        action="{{ route('incidencias.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf


        {{-- INFORMACIÓN DEL PROBLEMA --}}

        <section class="bg-card rounded-2xl border border-border overflow-hidden">

            <div class="px-6 py-4 border-b border-border flex items-center gap-3">

                <span
                    class="w-6 h-6 rounded-full bg-primary text-white
                           text-xs font-semibold flex items-center justify-center"
                >
                    1
                </span>

                <h2 class="text-sm font-semibold text-foreground">
                    Información del problema
                </h2>

            </div>


            <div class="px-6 py-5 space-y-5">

                <div>

                    <label
                        for="titulo"
                        class="block text-xs font-semibold text-muted-foreground
                               uppercase tracking-widest mb-1.5"
                    >
                        ¿Qué problema estás presentando?

                        <span class="text-primary">*</span>
                    </label>

                    <input
                        id="titulo"
                        type="text"
                        name="titulo"
                        required
                        maxlength="255"
                        value="{{ old('titulo', request('titulo')) }}"
                        placeholder="Ej: No puedo ingresar al correo corporativo"
                        class="w-full px-3.5 py-2.5 rounded-lg border
                               border-border bg-white text-sm
                               focus:outline-none focus:border-primary
                               focus:ring-2 focus:ring-primary/10"
                    >

                </div>


                <div>

                    <label
                        for="descripcion"
                        class="block text-xs font-semibold text-muted-foreground
                               uppercase tracking-widest mb-1.5"
                    >
                        Describe lo ocurrido

                        <span class="text-primary">*</span>
                    </label>

                    <textarea
                        id="descripcion"
                        name="descripcion"
                        required
                        rows="5"
                        placeholder="Indica qué ocurrió, qué estabas intentando hacer y si aparece algún mensaje..."
                        class="w-full px-3.5 py-2.5 rounded-lg border
                               border-border bg-white text-sm resize-none
                               focus:outline-none focus:border-primary
                               focus:ring-2 focus:ring-primary/10"
                    >{{ old('descripcion', request('descripcion')) }}</textarea>

                </div>


                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>

                        <label
                            for="tiempo_problema"
                            class="block text-xs font-semibold text-muted-foreground
                                   uppercase tracking-widest mb-1.5"
                        >
                            ¿Cuándo empezó?
                        </label>

                        <select
                            id="tiempo_problema"
                            name="tiempo_problema"
                            class="w-full px-3.5 py-2.5 rounded-lg border
                                   border-border bg-white text-sm"
                        >
                            <option value="">
                                Seleccione
                            </option>

                            <option
                                value="hoy"
                                @selected(old('tiempo_problema') === 'hoy')
                            >
                                Hoy
                            </option>

                            <option
                                value="ayer"
                                @selected(old('tiempo_problema') === 'ayer')
                            >
                                Ayer
                            </option>

                            <option
                                value="varios_dias"
                                @selected(old('tiempo_problema') === 'varios_dias')
                            >
                                Hace varios días
                            </option>
                        </select>

                    </div>


                    <div>

                        <label
                            for="afectacion"
                            class="block text-xs font-semibold text-muted-foreground
                                   uppercase tracking-widest mb-1.5"
                        >
                            ¿A quién afecta?
                        </label>

                        <select
                            id="afectacion"
                            name="afectacion"
                            class="w-full px-3.5 py-2.5 rounded-lg border
                                   border-border bg-white text-sm"
                        >
                            <option
                                value="solo"
                                @selected(old('afectacion', 'solo') === 'solo')
                            >
                                Solo a mí
                            </option>

                            <option
                                value="varios"
                                @selected(old('afectacion') === 'varios')
                            >
                                A varias personas
                            </option>

                            <option
                                value="todos"
                                @selected(old('afectacion') === 'todos')
                            >
                                A toda el área
                            </option>
                        </select>

                    </div>

                </div>

            </div>

        </section>


        {{-- EVIDENCIA --}}

        <section class="bg-card rounded-2xl border border-border overflow-hidden">

            <div class="px-6 py-4 border-b border-border flex items-center gap-3">

                <span
                    class="w-6 h-6 rounded-full bg-primary text-white
                           text-xs font-semibold flex items-center
                           justify-center shrink-0"
                >
                    2
                </span>

                <h2 class="text-sm font-semibold text-foreground">
                    Evidencia del problema
                </h2>

            </div>


            <div class="px-6 py-5 space-y-5">

                <div
                    id="dropzone"
                    class="flex flex-col items-center justify-center h-48
                           rounded-xl border-2 border-dashed border-border
                           hover:border-primary hover:bg-primary/5
                           cursor-pointer transition"
                >
                    <i
                        data-lucide="image"
                        class="w-8 h-8 text-muted-foreground mb-3"
                    ></i>

                    <p class="text-sm text-muted-foreground">
                        Arrastra tus capturas aquí
                    </p>

                    <p class="text-xs text-muted-foreground mt-1">
                        o haz clic para seleccionar imágenes
                    </p>

                    <p class="text-xs text-muted-foreground mt-2">
                        PNG, JPG, JPEG o WEBP — máximo 10 MB
                    </p>

                    <input
                        id="archivos"
                        type="file"
                        name="archivos[]"
                        multiple
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="hidden"
                    >
                </div>


                <div
                    id="preview"
                    class="grid grid-cols-2 sm:grid-cols-4 gap-4"
                ></div>


                <div class="flex items-start gap-3 bg-muted/50 rounded-xl p-4">

                    <i
                        data-lucide="info"
                        class="w-5 h-5 text-primary shrink-0"
                    ></i>

                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Las imágenes serán analizadas automáticamente mediante OCR
                        para detectar mensajes de error y facilitar la atención
                        del equipo TI.
                    </p>

                </div>

            </div>

        </section>


        {{-- INFORMACIÓN ADICIONAL --}}

        <section class="bg-card rounded-2xl border border-border overflow-hidden">

            <div class="px-6 py-4 border-b border-border flex items-center gap-3">

                <span
                    class="w-6 h-6 rounded-full bg-primary text-white
                           text-xs font-semibold flex items-center
                           justify-center shrink-0"
                >
                    3
                </span>

                <h2 class="text-sm font-semibold text-foreground">
                    Información adicional
                </h2>

            </div>


            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div>

                    <label
                        for="equipo"
                        class="block text-xs font-semibold text-muted-foreground
                               uppercase tracking-widest mb-1.5"
                    >
                        Equipo relacionado
                    </label>

                    <input
                        id="equipo"
                        type="text"
                        name="equipo"
                        value="{{ old('equipo', request('equipo')) }}"
                        placeholder="Ej: Laptop Dell, impresora..."
                        class="w-full px-3.5 py-2.5 rounded-lg border
                               border-border bg-white text-sm"
                    >

                </div>


                <div>

                    <label
                        for="ubicacion"
                        class="block text-xs font-semibold text-muted-foreground
                               uppercase tracking-widest mb-1.5"
                    >
                        Lugar donde ocurre
                    </label>

                    <input
                        id="ubicacion"
                        type="text"
                        name="ubicacion"
                        value="{{ old('ubicacion', request('ubicacion')) }}"
                        placeholder="Ej: Oficina, Producción..."
                        class="w-full px-3.5 py-2.5 rounded-lg border
                               border-border bg-white text-sm"
                    >

                </div>

            </div>

        </section>


        {{-- ESTADO SMTP Y ACCIONES --}}

        <div
            class="flex flex-col sm:flex-row sm:items-center
                   sm:justify-between gap-4"
        >
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">

                <div
                    id="smtpEstadoIncidencia"
                    class="inline-flex items-center gap-2
                           text-xs text-muted-foreground"
                >
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>

                    El correo SMTP se comprobará al enviar
                </div>


                <button
                    type="button"
                    id="btnReportarSmtpIncidenciaPersistente"
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


            <div class="flex justify-end gap-3">

                <button
                    id="btnCancelar"
                    type="button"
                    class="inline-flex items-center justify-center
                           px-5 py-2.5 rounded-xl border border-border
                           bg-white text-sm text-muted-foreground
                           hover:bg-muted transition-colors"
                >
                    Cancelar
                </button>


                <button
                    id="btnEnviar"
                    type="submit"
                    class="inline-flex items-center justify-center gap-2
                           px-5 py-2.5 rounded-xl bg-primary text-white
                           text-sm font-medium disabled:opacity-70
                           disabled:cursor-not-allowed"
                >
                    <i
                        id="btnEnviarIcono"
                        data-lucide="send"
                        class="w-4 h-4"
                    ></i>

                    <span id="btnEnviarTexto">
                        Enviar reporte
                    </span>
                </button>

            </div>

        </div>

    </form>

</main>

</div>


{{-- MODAL DE RESPUESTA --}}

<div
    id="modalIncidencia"
    class="fixed inset-0 bg-black/40 backdrop-blur-sm
           hidden items-center justify-center z-50 p-4"
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
                       border-green-200 flex items-center
                       justify-center mx-auto"
            >
                <i
                    data-lucide="check-circle"
                    class="w-8 h-8 text-green-600"
                ></i>
            </div>


            <h3
                id="modalTitulo"
                class="text-lg font-semibold text-foreground mt-5"
            >
                Incidencia registrada
            </h3>


            <p
                id="modalMensaje"
                class="text-sm text-muted-foreground leading-relaxed
                       mt-2 max-w-sm mx-auto"
            >
                El reporte fue registrado correctamente.
            </p>


            <div
                id="codigoIncidencia"
                class="hidden inline-flex items-center rounded-full
                       bg-muted px-3 py-1 text-xs font-semibold
                       text-foreground mt-4"
            ></div>

        </div>


        {{-- ESTADO SMTP --}}

        <div class="px-7 pb-7">

            <div
                id="estadoCorreoIncidencia"
                class="rounded-2xl border border-green-200
                       bg-green-50/70 p-5 text-left"
            >
                <div
                    class="grid grid-cols-[40px_minmax(0,1fr)]
                           items-start gap-4"
                >
                    <div
                        id="estadoCorreoIncidenciaIcono"
                        class="w-10 h-10 rounded-xl bg-white border
                               border-green-200 flex items-center
                               justify-center"
                    >
                        <i
                            data-lucide="mail-check"
                            class="w-5 h-5 text-green-600"
                        ></i>
                    </div>


                    <div class="min-w-0">

                        <p
                            id="estadoCorreoIncidenciaTitulo"
                            class="text-sm font-semibold text-green-800"
                        >
                            Correo enviado correctamente
                        </p>


                        <p
                            id="estadoCorreoIncidenciaMensaje"
                            class="text-xs text-green-700 leading-relaxed mt-1.5"
                        >
                            El servidor SMTP aceptó la notificación.
                        </p>


                        <button
                            type="button"
                            id="btnReportarSmtpIncidencia"
                            data-recipient="helpdesk@televicentro.hn"
                            data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                            data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                            class="hidden w-full mt-4 items-center
                                   justify-center gap-2 rounded-xl border
                                   border-amber-300 bg-white px-4 py-2.5
                                   text-xs font-semibold text-amber-800
                                   hover:bg-amber-100 transition"
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
                    La incidencia permanecerá registrada en el historial,
                    incluso si la notificación no pudo enviarse.
                </p>

            </div>

        </div>


        {{-- ACCIONES --}}

        <div class="border-t border-border bg-muted/20 px-7 py-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <button
                    type="button"
                    id="cerrarModalIncidencia"
                    class="w-full inline-flex items-center justify-center
                           px-5 py-2.5 rounded-xl border border-border
                           bg-white text-sm font-medium text-foreground
                           hover:bg-muted transition"
                >
                    Cerrar
                </button>


                <a
                    href="{{ route('mis-incidencias') }}"
                    class="w-full inline-flex items-center justify-center
                           gap-2 px-5 py-2.5 rounded-xl bg-primary
                           text-white text-sm font-medium hover:opacity-90
                           transition"
                >
                    <i data-lucide="history" class="w-4 h-4"></i>

                    Mis incidencias
                </a>

            </div>

        </div>

    </div>
</div>


<script src="{{ asset('js/incidencias.js') }}"></script>


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