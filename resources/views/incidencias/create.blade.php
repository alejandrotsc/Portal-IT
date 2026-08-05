@extends('layouts.app')

@section('title', 'Reporte de incidencia')

@section('content')

<div class="min-h-screen bg-background">

<main class="mx-auto max-w-5xl space-y-8 px-6 py-10">

    {{-- HEADER --}}

    <section class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary shadow-sm transition-all duration-300 hover:bg-primary/15 dark:border-blue-800/70 dark:bg-blue-950/40 dark:text-blue-400 dark:hover:bg-blue-900/50 motion-safe:hover:scale-105">

                <i
                    data-lucide="triangle-alert"
                    stroke-width="1.8"
                    class="h-6 w-6">
                </i>

            </div>

            <div class="min-w-0">

                <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                    Reporte de incidencia
                </h1>

                <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-muted-foreground">
                    Describe el problema que estás presentando y adjunta evidencia
                    para que TI pueda ayudarte.
                </p>

            </div>

        </div>


        <a
            href="{{ route('mis-incidencias') }}"
            class="group/history inline-flex items-center justify-center gap-2 rounded-xl border border-primary/10 bg-primary/[0.06] px-4 py-2.5 text-sm font-medium text-primary shadow-sm transition-all duration-200 hover:border-primary/20 hover:bg-primary/10 hover:shadow-md dark:border-slate-700 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:border-blue-500/40 dark:hover:bg-blue-500/15 active:scale-[0.98]"
        >
            <i data-lucide="history" stroke-width="1.8" class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/history:-rotate-12"></i>

            Mis incidencias
        </a>

    </section>


    @if (
        request()->filled('titulo')
        || request()->filled('descripcion')
        || request()->filled('tiempo_problema')
        || request()->filled('afectacion')
        || request()->filled('equipo')
        || request()->filled('ubicacion')
    )
        <section
            class="flex items-start gap-3 rounded-2xl border border-primary/20 bg-primary/[0.05] px-5 py-4 shadow-sm dark:border-blue-800/70 dark:bg-blue-950/30"
            role="status"
        >
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <i
                    data-lucide="sparkles"
                    stroke-width="1.8"
                    class="h-4.5 w-4.5"
                ></i>
            </div>

            <div class="min-w-0">
                <p class="text-sm font-semibold text-foreground">
                    Incidencia preparada por el Asistente TI
                </p>

                <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                    Revisa y corrige los datos sugeridos antes de enviar el reporte.
                    La incidencia no se registrará hasta que presiones “Enviar reporte”.
                </p>
            </div>
        </section>
    @endif


    <form
        id="incidenciaForm"
        action="{{ route('incidencias.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf


        {{-- INFORMACIÓN DEL PROBLEMA --}}

        <section class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

            <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125"></span>

            <div class="relative flex items-center gap-3 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4 dark:border-slate-700/70 dark:from-blue-950/35 dark:via-slate-900 dark:to-slate-900">

                <span
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white shadow-sm transition-transform duration-300 motion-safe:group-hover:scale-105"
                >
                    1
                </span>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">
                        Información del problema
                    </h2>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Explícanos qué sucede y desde cuándo lo estás experimentando.
                    </p>

                </div>

            </div>


            <div class="relative space-y-5 px-6 py-5">

                <div>

                    <label
                        for="titulo"
                        class="block text-xs font-semibold text-muted-foreground
                               uppercase tracking-widest mb-1.5"
                    >
                        ¿Qué problema estás presentando?

                        <span class="text-primary">*</span>
                    </label>

                    <div
                        @class([
                            'group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2 dark:bg-slate-900/80',
                            'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500' => $errors->has('titulo'),
                            'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500' => ! $errors->has('titulo'),
                        ])>

                        <i data-lucide="text" stroke-width="1.8" class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110"></i>

                        <input
                            id="titulo"
                            type="text"
                            name="titulo"
                            required
                            maxlength="255"
                            value="{{ old('titulo', request('titulo')) }}"
                            placeholder="Ej: No puedo ingresar al correo corporativo"
                            class="w-full border-0 bg-transparent py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 dark:text-slate-200 dark:placeholder:text-slate-500">

                    </div>

                    @error('titulo')

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
                               uppercase tracking-widest mb-1.5"
                    >
                        Describe lo ocurrido

                        <span class="text-primary">*</span>
                    </label>

                    <div
                        @class([
                            'group/field flex w-full items-start gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2 dark:bg-slate-900/80',
                            'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500' => $errors->has('descripcion'),
                            'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500' => ! $errors->has('descripcion'),
                        ])>

                        <i data-lucide="align-left" stroke-width="1.8" class="mt-3 h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110"></i>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            required
                            rows="5"
                            maxlength="3000"
                            placeholder="Indica qué ocurrió, qué estabas intentando hacer y si aparece algún mensaje..."
                            class="w-full resize-none border-0 bg-transparent py-2.5 text-sm leading-relaxed text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 dark:text-slate-200 dark:placeholder:text-slate-500">{{ old('descripcion', request('descripcion')) }}</textarea>

                    </div>

                    @error('descripcion')

                        <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600">
                            <i data-lucide="circle-alert" stroke-width="1.8" class="h-3.5 w-3.5 shrink-0"></i>
                            {{ $message }}
                        </p>

                    @enderror

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

                        <div class="group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border border-border bg-white px-3.5 transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700/70 dark:bg-slate-900/80 dark:focus-within:border-blue-500">

                            <i data-lucide="clock-3" stroke-width="1.8" class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110"></i>

                        <select
                            id="tiempo_problema"
                            name="tiempo_problema"
                            class="w-full appearance-none border-0 bg-transparent py-2.5 text-sm text-foreground focus:outline-none focus:ring-0 dark:text-slate-200 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-200">

                            <option
                                value="hoy"
                                @selected(old('tiempo_problema', request('tiempo_problema', 'hoy')) === 'hoy')
                            >
                                Hoy
                            </option>

                            <option
                                value="ayer"
                                @selected(old('tiempo_problema', request('tiempo_problema')) === 'ayer')
                            >
                                Ayer
                            </option>

                            <option
                                value="varios_dias"
                                @selected(old('tiempo_problema', request('tiempo_problema')) === 'varios_dias')
                            >
                                Hace varios días
                            </option>
                        </select>

                            <i data-lucide="chevron-down" stroke-width="1.8" class="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200 group-focus-within/field:rotate-180 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400"></i>

                        </div>

                    </div>


                    <div>

                        <label
                            for="afectacion"
                            class="block text-xs font-semibold text-muted-foreground
                                   uppercase tracking-widest mb-1.5"
                        >
                            ¿A quién afecta?
                        </label>

                        <div class="group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border border-border bg-white px-3.5 transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700/70 dark:bg-slate-900/80 dark:focus-within:border-blue-500">

                            <i data-lucide="users-round" stroke-width="1.8" class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110"></i>

                        <select
                            id="afectacion"
                            name="afectacion"
                            class="w-full appearance-none border-0 bg-transparent py-2.5 text-sm text-foreground focus:outline-none focus:ring-0 dark:text-slate-200 [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-200">
                            <option
                                value="solo"
                                @selected(old('afectacion', request('afectacion', 'solo')) === 'solo')
                            >
                                Solo a mí
                            </option>

                            <option
                                value="varios"
                                @selected(old('afectacion', request('afectacion')) === 'varios')
                            >
                                A varias personas
                            </option>

                            <option
                                value="todos"
                                @selected(old('afectacion', request('afectacion')) === 'todos')
                            >
                                A toda el área
                            </option>
                        </select>

                            <i data-lucide="chevron-down" stroke-width="1.8" class="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200 group-focus-within/field:rotate-180 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400"></i>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- EVIDENCIA --}}

        <section class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

            <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125"></span>

            <div class="relative flex items-center gap-3 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4 dark:border-slate-700/70 dark:from-blue-950/35 dark:via-slate-900 dark:to-slate-900">

                <span
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white shadow-sm transition-transform duration-300 motion-safe:group-hover:scale-105"
                >
                    2
                </span>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">
                        Evidencia del problema
                    </h2>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Añade capturas que ayuden a identificar lo sucedido.
                    </p>

                </div>

            </div>


            <div class="relative space-y-5 px-6 py-5">

                <div
                    id="dropzone"
                    role="button"
                    tabindex="0"
                    class="group/dropzone flex h-48 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-primary/20 bg-gradient-to-br from-primary/[0.03] via-white to-blue-50/40 transition-all duration-300 hover:border-primary/50 hover:bg-primary/[0.06] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-blue-800/70 dark:from-blue-950/25 dark:via-slate-900 dark:to-slate-900 dark:hover:border-blue-500/60 dark:hover:from-blue-950/40 dark:hover:via-slate-900 dark:hover:to-slate-900"
                >
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all duration-300 group-hover/dropzone:bg-primary/15 motion-safe:group-hover/dropzone:scale-110">

                        <i
                            data-lucide="image-up"
                            stroke-width="1.8"
                            class="h-6 w-6">
                        </i>

                    </div>

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


                <div class="flex items-start gap-3 rounded-xl border border-primary/10 bg-primary/[0.04] p-4 dark:border-blue-800/60 dark:bg-blue-950/25">

                    <i
                        data-lucide="info"
                        stroke-width="1.8"
                        class="h-4 w-4 shrink-0 text-primary mt-0.5"
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

        <section class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20">

            <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125"></span>

            <div class="relative flex items-center gap-3 border-b border-border bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 px-6 py-4 dark:border-slate-700/70 dark:from-blue-950/35 dark:via-slate-900 dark:to-slate-900">

                <span
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white shadow-sm transition-transform duration-300 motion-safe:group-hover:scale-105"
                >
                    3
                </span>

                <div>

                    <h2 class="text-sm font-semibold text-foreground">
                        Información adicional
                    </h2>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Estos datos ayudarán a ubicar y revisar el problema.
                    </p>

                </div>

            </div>


            <div class="relative grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">

                <div>

                    <label
                        for="equipo"
                        class="block text-xs font-semibold text-muted-foreground
                               uppercase tracking-widest mb-1.5"
                    >
                        Equipo relacionado
                    </label>

                    <div class="group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border border-border bg-white px-3.5 transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700/70 dark:bg-slate-900/80 dark:focus-within:border-blue-500">

                        <i data-lucide="monitor" stroke-width="1.8" class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110"></i>

                        <input
                            id="equipo"
                            type="text"
                            name="equipo"
                            maxlength="200"
                            value="{{ old('equipo', request('equipo')) }}"
                            placeholder="Ej: Laptop Dell, impresora..."
                            class="w-full border-0 bg-transparent py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 dark:text-slate-200 dark:placeholder:text-slate-500">

                    </div>

                </div>


                <div>

                    <label
                        for="ubicacion"
                        class="block text-xs font-semibold text-muted-foreground
                               uppercase tracking-widest mb-1.5"
                    >
                        Lugar donde ocurre
                    </label>

                    <div class="group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border border-border bg-white px-3.5 transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 dark:border-slate-700/70 dark:bg-slate-900/80 dark:focus-within:border-blue-500">

                        <i data-lucide="map-pin" stroke-width="1.8" class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110"></i>

                        <input
                            id="ubicacion"
                            type="text"
                            name="ubicacion"
                            maxlength="200"
                            value="{{ old('ubicacion', request('ubicacion')) }}"
                            placeholder="Ej: Oficina, Producción..."
                            class="w-full border-0 bg-transparent py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 dark:text-slate-200 dark:placeholder:text-slate-500">

                    </div>

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
                    class="inline-flex items-center gap-2 rounded-lg border border-border bg-white px-3 py-2 text-xs text-muted-foreground shadow-sm dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-400"
                >
                    <span class="relative flex h-2.5 w-2.5 shrink-0">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-slate-300 opacity-60"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                    </span>

                    El correo SMTP se comprobará al enviar
                </div>


                <button
                    type="button"
                    id="btnReportarSmtpIncidenciaPersistente"
                    data-recipient="helpdesk@televicentro.hn"
                    data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                    data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                    class="group/report hidden items-center justify-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-300 dark:hover:border-amber-700 dark:hover:bg-amber-900/45 active:scale-[0.98]"
                >
                    <i data-lucide="external-link" stroke-width="1.8" class="h-3.5 w-3.5 transition-transform duration-200 motion-safe:group-hover/report:translate-x-0.5 motion-safe:group-hover/report:-translate-y-0.5"></i>

                    Reportar por Outlook 365
                </button>

            </div>


            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <button
                    id="btnCancelar"
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-muted-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:text-foreground hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-100 active:scale-[0.98]"
                >
                    Cancelar
                </button>


                <button
                    id="btnEnviar"
                    type="submit"
                    class="group/send inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md hover:shadow-primary/25 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:shadow-sm"
                >
                    <i
                        id="btnEnviarIcono"
                        data-lucide="send"
                        stroke-width="1.8"
                        class="h-4 w-4 transition-transform duration-200"
                    ></i>

                    <span id="btnEnviarTexto">
                        Enviar reporte
                    </span>
                </button>

            </div>

        </div>

    </form>

    @include('partials.support-widget')

</main>

</div>


{{-- MODAL DE RESPUESTA --}}

<div
    id="modalIncidencia"
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
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 shadow-sm dark:border-blue-800 dark:bg-blue-950/45"
            >
                <i
                    data-lucide="clock-3"
                    stroke-width="1.8"
                    class="h-8 w-8 text-blue-600 dark:text-blue-400"
                ></i>
            </div>


            <h3
                id="modalTitulo"
                class="mt-5 text-lg font-semibold text-foreground dark:text-slate-100"
            >
                Incidencia registrada
            </h3>


            <p
                id="modalMensaje"
                class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground dark:text-slate-400"
            >
                El reporte fue registrado correctamente. La notificación por correo se está procesando.
            </p>


            <div
                id="codigoIncidencia"
                class="hidden inline-flex items-center rounded-full
                       bg-muted px-3 py-1 text-xs font-semibold
                       text-foreground mt-4 dark:bg-slate-800 dark:text-slate-200"
            ></div>

        </div>


        {{-- ESTADO SMTP --}}

        <div class="relative px-7 pb-7">

            <div
                id="estadoCorreoIncidencia"
                class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/50 p-5 text-left shadow-sm dark:border-blue-800 dark:from-blue-950/45 dark:via-slate-900 dark:to-sky-950/30"
            >
                <div
                    class="grid grid-cols-[40px_minmax(0,1fr)]
                           items-start gap-4"
                >
                    <div
                        id="estadoCorreoIncidenciaIcono"
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
                            id="estadoCorreoIncidenciaTitulo"
                            class="text-sm font-semibold text-blue-800 dark:text-blue-300"
                        >
                            Correo en procesamiento
                        </p>


                        <p
                            id="estadoCorreoIncidenciaMensaje"
                            class="mt-1.5 text-xs leading-relaxed text-blue-700 dark:text-blue-400"
                        >
                            La notificación fue agregada a la cola y será enviada en segundo plano.
                        </p>


                        <button
                            type="button"
                            id="btnReportarSmtpIncidencia"
                            data-recipient="helpdesk@televicentro.hn"
                            data-user-name="{{ auth()->user()->nombre ?? 'N/A' }}"
                            data-user-email="{{ auth()->user()->correo ?? 'N/A' }}"
                            class="group/report-modal mt-4 hidden w-full items-center justify-center gap-2 rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-xs font-semibold text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md dark:border-amber-800 dark:bg-slate-900 dark:text-amber-300 dark:hover:border-amber-700 dark:hover:bg-amber-900/55 active:scale-[0.98]"
                        >
                            <i data-lucide="external-link" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/report-modal:translate-x-0.5 motion-safe:group-hover/report-modal:-translate-y-0.5"></i>

                            Reportar mediante Outlook 365
                        </button>

                    </div>

                </div>

            </div>


            <div class="mt-5 flex items-start gap-3 rounded-xl border border-primary/10 bg-primary/[0.04] p-4 dark:border-blue-800/60 dark:bg-blue-950/25">

                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="mt-0.5 h-4 w-4 shrink-0 text-primary"
                ></i>

                <p class="text-xs text-muted-foreground leading-relaxed">
                    La incidencia permanecerá registrada en el historial,
                    incluso si la notificación no pudo enviarse.
                </p>

            </div>

        </div>


        {{-- ACCIONES --}}

        <div class="border-t border-border bg-muted/20 px-7 py-5 dark:border-slate-700/70 dark:bg-slate-950/25">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <button
                    type="button"
                    id="cerrarModalIncidencia"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-medium text-foreground shadow-sm transition-all duration-200 hover:bg-muted hover:shadow-md dark:border-slate-700/70 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-700 active:scale-[0.98]"
                >
                    Cerrar
                </button>


                <a
                    href="{{ route('mis-incidencias') }}"
                    class="group/history-modal inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary/20 transition-all duration-200 hover:bg-primary/90 hover:shadow-md active:scale-[0.98]"
                >
                    <i data-lucide="history" stroke-width="1.8" class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/history-modal:-rotate-12"></i>

                    Mis incidencias
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
    src="{{ asset('js/incidencias.js') }}?v={{ filemtime(public_path('js/incidencias.js')) }}"
></script>


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