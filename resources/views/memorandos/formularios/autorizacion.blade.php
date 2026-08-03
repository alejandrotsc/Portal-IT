<div
    class="formulario-dinamico space-y-6"
    data-formulario="autorizacion">


    {{-- Información de autorización --}}

    <div class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700/70 dark:hover:border-blue-700/70 dark:hover:shadow-black/20">


        {{-- Decoración --}}

        <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125">
        </span>


        {{-- Encabezado --}}

        <div class="relative flex items-center gap-3 border-b border-border dark:border-slate-700/70 bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900 px-6 py-4">

            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white shadow-sm transition-transform duration-300 motion-safe:group-hover:scale-105">

                3

            </span>

            <div class="min-w-0">

                <h2 class="text-sm font-semibold text-foreground">

                    Información de autorización

                </h2>

                <p class="mt-0.5 text-xs text-muted-foreground">

                    Indica quién será responsable del equipo y el motivo de ingreso.

                </p>

            </div>

        </div>



        {{-- Contenido --}}

        <div class="relative space-y-5 px-6 py-5">


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">


                {{-- Responsable del equipo --}}

                <div>

                    <label
                        for="colaborador"
                        class="mb-2 block text-xs font-semibold uppercase tracking-widest text-muted-foreground">

                        Responsable del equipo

                        <span class="text-primary">*</span>

                    </label>


                    <div
                        @class([
                            'group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-white dark:bg-slate-900/80 px-3.5 transition-all duration-200 focus-within:ring-2',

                            'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500 dark:focus-within:ring-red-500/20' =>
                                $errors->has('colaborador'),

                            'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                                ! $errors->has('colaborador'),
                        ])>

                        <i
                            data-lucide="user-round"
                            stroke-width="1.8"
                            class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:text-slate-400 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110">
                        </i>

                        <input
                            type="text"
                            id="colaborador"
                            name="colaborador"
                            value="{{ old('colaborador') }}"
                            maxlength="200"
                            autocomplete="name"
                            placeholder="Nombre completo del colaborador"
                            required
                            class="w-full border-0 bg-transparent py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0">

                    </div>


                    @error('colaborador')

                        <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">

                            <i
                                data-lucide="circle-alert"
                                stroke-width="1.8"
                                class="h-3.5 w-3.5 shrink-0">
                            </i>

                            {{ $message }}

                        </p>

                    @enderror

                </div>



                {{-- Cargo / Área --}}

                <div>

                    <label
                        for="cargo_area"
                        class="mb-2 block text-xs font-semibold uppercase tracking-widest text-muted-foreground">

                        Cargo / Área

                        <span class="text-primary">*</span>

                    </label>


                    <div
                        @class([
                            'group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-white dark:bg-slate-900/80 px-3.5 transition-all duration-200 focus-within:ring-2',

                            'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500 dark:focus-within:ring-red-500/20' =>
                                $errors->has('cargo_area'),

                            'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                                ! $errors->has('cargo_area'),
                        ])>

                        <i
                            data-lucide="briefcase-business"
                            stroke-width="1.8"
                            class="h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:text-slate-400 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110">
                        </i>

                        <input
                            type="text"
                            id="cargo_area"
                            name="cargo_area"
                            value="{{ old('cargo_area') }}"
                            maxlength="200"
                            autocomplete="organization-title"
                            placeholder="Ej: Practicante de Infraestructura"
                            required
                            class="w-full border-0 bg-transparent py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0">

                    </div>


                    @error('cargo_area')

                        <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">

                            <i
                                data-lucide="circle-alert"
                                stroke-width="1.8"
                                class="h-3.5 w-3.5 shrink-0">
                            </i>

                            {{ $message }}

                        </p>

                    @enderror

                </div>

            </div>



            {{-- Motivo de autorización --}}

            <div>

                <label
                    for="motivo_autorizacion"
                    class="mb-2 block text-xs font-semibold uppercase tracking-widest text-muted-foreground">

                    Motivo de autorización

                    <span class="text-primary">*</span>

                </label>


                <div
                    @class([
                        'group/field flex w-full items-start gap-2.5 rounded-lg border bg-white dark:bg-slate-900/80 px-3.5 transition-all duration-200 focus-within:ring-2',

                        'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500 dark:focus-within:ring-red-500/20' =>
                            $errors->has('motivo_autorizacion'),

                        'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                            ! $errors->has('motivo_autorizacion'),
                    ])>

                    <i
                        data-lucide="notebook-pen"
                        stroke-width="1.8"
                        class="mt-3 h-4 w-4 shrink-0 text-muted-foreground transition-all duration-200 group-focus-within/field:text-blue-600 dark:text-slate-400 dark:group-focus-within/field:text-blue-400 motion-safe:group-focus-within/field:scale-110">
                    </i>

                    <textarea
                        id="motivo_autorizacion"
                        name="motivo_autorizacion"
                        rows="4"
                        maxlength="1500"
                        placeholder="Describe el motivo por el cual se requiere autorización de ingreso del equipo..."
                        required
                        class="w-full resize-none border-0 bg-transparent py-2.5 text-sm leading-relaxed text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0">{{ old('motivo_autorizacion') }}</textarea>

                </div>


                @error('motivo_autorizacion')

                    <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">

                        <i
                            data-lucide="circle-alert"
                            stroke-width="1.8"
                            class="h-3.5 w-3.5 shrink-0">
                        </i>

                        {{ $message }}

                    </p>

                @enderror

            </div>

        </div>

    </div>



    {{-- Información del equipo --}}

    <div class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700/70 dark:hover:border-blue-700/70 dark:hover:shadow-black/20">


        {{-- Decoración --}}

        <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl transition-all duration-500 motion-safe:group-hover:scale-125">
        </span>


        {{-- Encabezado --}}

        <div class="relative flex items-center gap-3 border-b border-border dark:border-slate-700/70 bg-gradient-to-r from-primary/[0.06] via-white to-blue-50/40 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900 px-6 py-4">

            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white shadow-sm transition-transform duration-300 motion-safe:group-hover:scale-105">

                4

            </span>

            <div class="min-w-0">

                <h2 class="text-sm font-semibold text-foreground">

                    Información del equipo

                </h2>

                <p class="mt-0.5 text-xs text-muted-foreground">

                    Registra los datos de cada equipo que requiere autorización.

                </p>

            </div>

        </div>



        {{-- Contenido --}}

        <div class="relative px-6 py-5">


            {{-- Tabla de equipos --}}

            <div class="overflow-hidden rounded-xl border border-border bg-white dark:border-slate-700/70 dark:bg-slate-900/70">

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[850px] border-collapse text-sm">

                        <thead>

                            <tr class="border-b border-border bg-muted/50 dark:border-slate-700/70 dark:bg-slate-800/70">

                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">

                                    Equipo

                                </th>

                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">

                                    Marca

                                </th>

                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">

                                    Modelo

                                </th>

                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">

                                    <div class="flex items-center gap-1.5">

                                        <span>
                                            Serie
                                        </span>

                                        <button
                                            type="button"
                                            id="btnAyudaSerie"
                                            onclick="abrirAyudaSerie()"
                                            title="¿Dónde encontrar el número de serie?"
                                            aria-label="Mostrar ayuda para encontrar el número de serie"
                                            class="group/help inline-flex h-6 w-6 items-center justify-center rounded-md text-muted-foreground transition-all duration-200 hover:bg-primary/10 hover:text-primary dark:text-slate-400 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-primary/20">

                                            <i
                                                data-lucide="circle-help"
                                                stroke-width="1.8"
                                                class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/help:scale-110">
                                            </i>

                                        </button>

                                    </div>

                                </th>

                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">

                                    Color

                                </th>

                                <th class="w-14 px-2 py-3">

                                    <span class="sr-only">
                                        Acciones
                                    </span>

                                </th>

                            </tr>

                        </thead>


                        <tbody
                            id="equipoFilas"
                            class="divide-y divide-border dark:divide-slate-700/70">

                            <tr class="fila-equipo">

                                <td class="px-2 py-2.5">

                                    <input
                                        type="text"
                                        name="equipos[0][descripcion]"
                                        value="{{ old('equipos.0.descripcion') }}"
                                        placeholder="Laptop"
                                        maxlength="100"
                                        required
                                        class="input-equipo w-full min-w-[120px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

                                </td>

                                <td class="px-2 py-2.5">

                                    <input
                                        type="text"
                                        name="equipos[0][marca]"
                                        value="{{ old('equipos.0.marca') }}"
                                        placeholder="Dell"
                                        maxlength="100"
                                        required
                                        class="input-equipo w-full min-w-[110px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

                                </td>

                                <td class="px-2 py-2.5">

                                    <input
                                        type="text"
                                        name="equipos[0][modelo]"
                                        value="{{ old('equipos.0.modelo') }}"
                                        placeholder="Latitude 5420"
                                        maxlength="100"
                                        required
                                        class="input-equipo w-full min-w-[130px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

                                </td>

                                <td class="px-2 py-2.5">

                                    <input
                                        type="text"
                                        name="equipos[0][codigo]"
                                        value="{{ old('equipos.0.codigo') }}"
                                        placeholder="SN123456"
                                        maxlength="100"
                                        required
                                        class="input-equipo w-full min-w-[125px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

                                </td>

                                <td class="px-2 py-2.5">

                                    <input
                                        type="text"
                                        name="equipos[0][color]"
                                        value="{{ old('equipos.0.color') }}"
                                        placeholder="Negro"
                                        maxlength="50"
                                        required
                                        class="input-equipo w-full min-w-[100px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

                                </td>

                                <td class="px-2 py-2.5 text-center">

                                    <button
                                        type="button"
                                        title="Eliminar equipo"
                                        aria-label="Eliminar equipo"
                                        class="btn-remove-fila group/remove inline-flex h-9 w-9 items-center justify-center rounded-lg border border-transparent text-muted-foreground transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-800 dark:hover:bg-red-950/45 dark:hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-red-500/10 disabled:cursor-not-allowed disabled:opacity-40">

                                        <i
                                            data-lucide="trash-2"
                                            stroke-width="1.8"
                                            class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/remove:scale-110">
                                        </i>

                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>



            {{-- Error general de equipos --}}

            @error('equipos')

                <p class="mt-3 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="h-3.5 w-3.5 shrink-0">
                    </i>

                    {{ $message }}

                </p>

            @enderror



            {{-- Agregar equipo --}}

            <button
                type="button"
                id="agregarFila"
                class="group/add mt-4 inline-flex items-center justify-center gap-2 rounded-xl border border-solid border-blue-500/30 bg-blue-50/60 px-4 py-2.5 text-sm font-medium text-blue-600 shadow-sm transition-all duration-200 hover:border-blue-500/60 hover:bg-blue-100/70 hover:shadow-md dark:border-blue-500/40 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:border-blue-400/70 dark:hover:bg-blue-500/15 active:scale-[0.98]">

                <i
                    data-lucide="plus"
                    stroke-width="2"
                    class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/add:rotate-90 motion-safe:group-hover/add:scale-110">
                </i>

                Agregar equipo

            </button>

        </div>

    </div>

</div>



{{-- Plantilla para nuevas filas --}}

<template id="templateEquipo">

    <tr class="fila-equipo">

        <td class="px-2 py-2.5">

            <input
                type="text"
                name="equipos[INDEX][descripcion]"
                placeholder="Laptop"
                maxlength="100"
                required
                class="input-equipo w-full min-w-[120px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

        </td>

        <td class="px-2 py-2.5">

            <input
                type="text"
                name="equipos[INDEX][marca]"
                placeholder="Dell"
                maxlength="100"
                required
                class="input-equipo w-full min-w-[110px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

        </td>

        <td class="px-2 py-2.5">

            <input
                type="text"
                name="equipos[INDEX][modelo]"
                placeholder="Latitude 5420"
                maxlength="100"
                required
                class="input-equipo w-full min-w-[130px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

        </td>

        <td class="px-2 py-2.5">

            <input
                type="text"
                name="equipos[INDEX][codigo]"
                placeholder="SN123456"
                maxlength="100"
                required
                class="input-equipo w-full min-w-[125px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

        </td>

        <td class="px-2 py-2.5">

            <input
                type="text"
                name="equipos[INDEX][color]"
                placeholder="Negro"
                maxlength="50"
                required
                class="input-equipo w-full min-w-[100px] rounded-lg border border-border bg-white px-3 py-2 dark:border-slate-700/70 dark:bg-slate-900/80 dark:text-slate-200 dark:placeholder:text-slate-500 text-sm text-foreground placeholder:text-muted-foreground transition-all duration-200 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10 dark:focus:border-blue-500 dark:focus:ring-blue-500/15">

        </td>

        <td class="px-2 py-2.5 text-center">

            <button
                type="button"
                title="Eliminar equipo"
                aria-label="Eliminar equipo"
                class="btn-remove-fila group/remove inline-flex h-9 w-9 items-center justify-center rounded-lg border border-transparent text-muted-foreground transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-800 dark:hover:bg-red-950/45 dark:hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-red-500/10">

                <i
                    data-lucide="trash-2"
                    stroke-width="1.8"
                    class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/remove:scale-110">
                </i>

            </button>

        </td>

    </tr>

</template>



{{-- Modal de ayuda para el número de serie --}}

<div
    id="modalSerie"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tituloModalSerie"
    class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4 backdrop-blur-[2px]">


    {{-- Contenido del modal --}}

    <div class="relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-border bg-white shadow-2xl dark:border-slate-700/70 dark:bg-slate-900 dark:shadow-black/50 shadow-slate-950/20">


        {{-- Decoración --}}

        <span class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-primary/10 blur-3xl">
        </span>


        {{-- Encabezado --}}

        <div class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-border bg-white/95 dark:border-slate-700/70 dark:bg-slate-900/95 px-6 py-4 backdrop-blur">

            <div class="flex min-w-0 items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">

                    <i
                        data-lucide="circle-help"
                        stroke-width="1.8"
                        class="h-5 w-5">
                    </i>

                </div>

                <div class="min-w-0">

                    <h3
                        id="tituloModalSerie"
                        class="text-sm font-semibold text-foreground">

                        ¿Dónde encontrar el número de serie?

                    </h3>

                    <p class="mt-0.5 text-xs text-muted-foreground">

                        Guía rápida para identificar el equipo.

                    </p>

                </div>

            </div>


            <button
                type="button"
                onclick="cerrarAyudaSerie()"
                title="Cerrar ayuda"
                aria-label="Cerrar ayuda"
                class="group/close inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-white text-muted-foreground dark:border-slate-700/70 dark:bg-slate-800 dark:text-slate-400 transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-800 dark:hover:bg-red-950/45 dark:hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-red-500/10">

                <i
                    data-lucide="x"
                    stroke-width="1.8"
                    class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/close:rotate-90">
                </i>

            </button>

        </div>



        {{-- Contenido --}}

        <div class="relative p-6">

            <video
                controls
                preload="metadata"
                poster="{{ asset('img/snhelp.avif') }}"
                class="mx-auto max-h-[60vh] w-auto max-w-full rounded-xl border border-border bg-slate-950 dark:border-slate-700/70 shadow-sm">

                <source
                    src="{{ asset('videos/snhelp_fixed.mp4') }}"
                    type="video/mp4">

                <img
                    src="{{ asset('img/snhelp.avif') }}"
                    alt="Ejemplo de la ubicación del número de serie"
                    class="w-full rounded-xl">

                Tu navegador no puede reproducir este video.

            </video>


            <div class="mt-5 rounded-xl border border-primary/10 bg-primary/[0.04] p-4">

                <div class="flex items-start gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">

                        <i
                            data-lucide="info"
                            stroke-width="1.8"
                            class="h-4 w-4">
                        </i>

                    </div>

                    <div class="space-y-2">

                        <p class="text-xs leading-relaxed text-muted-foreground">

                            Esta guía muestra un ejemplo para equipos

                            <strong class="font-semibold text-foreground">
                                Dell
                            </strong>.

                            La ubicación puede cambiar dependiendo de la marca y el modelo del equipo.

                        </p>

                        <p class="text-xs leading-relaxed text-muted-foreground">

                            Busca una etiqueta identificada como

                            <strong class="font-semibold text-foreground">
                                Serial Number
                            </strong>,

                            <strong class="font-semibold text-foreground">
                                Service Tag
                            </strong>

                            o

                            <strong class="font-semibold text-foreground">
                                S/N
                            </strong>.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>