@php

    $editando = isset($aviso);

    $fechaInicio = old(
        'fecha_inicio',
        isset($aviso) && $aviso->fecha_inicio
            ? $aviso->fecha_inicio
                ->timezone('America/Tegucigalpa')
                ->format('Y-m-d\TH:i')
            : ''
    );

    $fechaFin = old(
        'fecha_fin',
        isset($aviso) && $aviso->fecha_fin
            ? $aviso->fecha_fin
                ->timezone('America/Tegucigalpa')
                ->format('Y-m-d\TH:i')
            : ''
    );

    /*
    |--------------------------------------------------------------------------
    | Fecha mínima permitida
    |--------------------------------------------------------------------------
    |
    | Permite seleccionar cualquier hora del día actual, pero impide
    | escoger una fecha correspondiente a un día anterior.
    |--------------------------------------------------------------------------
    */

    $fechaMinima = now()
        ->timezone('America/Tegucigalpa')
        ->startOfDay()
        ->format('Y-m-d\TH:i');

    $avisoActivo = (bool) old(
        'activo',
        $aviso->activo ?? true
    );

@endphp


<div class="space-y-6">


    {{-- Título --}}

    <div>

        <label
            for="titulo"
            class="block mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-widest">

            Título del aviso

            <span class="text-primary">*</span>

        </label>


        <div
            @class([
                'group flex items-center gap-2 w-full px-3.5 rounded-lg border bg-card shadow-sm transition-all duration-200 focus-within:ring-2 focus-within:shadow-md dark:border-slate-700',

                'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-900/70 dark:focus-within:border-red-500' =>
                    $errors->has('titulo'),

                'border-border focus-within:border-primary focus-within:ring-primary/10 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                    ! $errors->has('titulo'),
            ])>

            <i
                data-lucide="type"
                stroke-width="1.8"
                @class([
                    'w-4 h-4 shrink-0 pointer-events-none transition-all duration-200 motion-safe:group-focus-within:scale-110',

                    'text-red-500' =>
                        $errors->has('titulo'),

                    'text-muted-foreground group-focus-within:text-primary' =>
                        ! $errors->has('titulo'),
                ])>
            </i>

            <input
                type="text"
                id="titulo"
                name="titulo"
                value="{{ old(
                    'titulo',
                    $aviso->titulo ?? ''
                ) }}"
                maxlength="150"
                autocomplete="off"
                placeholder="Ejemplo: Mantenimiento programado"
                required
                class="w-full py-2.5 bg-transparent border-0 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0">

        </div>


        <div class="flex items-start justify-between gap-4 mt-2">

            @error('titulo')

                <p class="flex items-start gap-1.5 text-xs text-red-600">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="w-3.5 h-3.5 shrink-0 mt-px">
                    </i>

                    <span>
                        {{ $message }}
                    </span>

                </p>

            @else

                <p class="text-xs text-muted-foreground">

                    Utiliza un título breve y fácil de identificar.

                </p>

            @enderror

            <span class="text-xs text-muted-foreground shrink-0">

                Máximo 150

            </span>

        </div>

    </div>



    {{-- Mensaje --}}

    <div>

        <label
            for="mensaje"
            class="block mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-widest">

            Mensaje

            <span class="text-primary">*</span>

        </label>


        <div
            @class([
                'group flex items-start gap-2 w-full px-3.5 rounded-lg border bg-card shadow-sm transition-all duration-200 focus-within:ring-2 focus-within:shadow-md dark:border-slate-700',

                'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-900/70 dark:focus-within:border-red-500' =>
                    $errors->has('mensaje'),

                'border-border focus-within:border-primary focus-within:ring-primary/10 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                    ! $errors->has('mensaje'),
            ])>

            <i
                data-lucide="align-left"
                stroke-width="1.8"
                @class([
                    'w-4 h-4 shrink-0 mt-3 pointer-events-none transition-all duration-200 motion-safe:group-focus-within:scale-110',

                    'text-red-500' =>
                        $errors->has('mensaje'),

                    'text-muted-foreground group-focus-within:text-primary' =>
                        ! $errors->has('mensaje'),
                ])>
            </i>

            <textarea
                id="mensaje"
                name="mensaje"
                rows="5"
                maxlength="500"
                placeholder="Escribe el mensaje que se mostrará en la cinta informativa..."
                required
                class="w-full py-2.5 resize-y bg-transparent border-0 text-sm text-foreground placeholder:text-muted-foreground leading-relaxed focus:outline-none focus:ring-0">{{ old(
                    'mensaje',
                    $aviso->mensaje ?? ''
                ) }}</textarea>

        </div>


        <div class="flex items-start justify-between gap-4 mt-2">

            @error('mensaje')

                <p class="flex items-start gap-1.5 text-xs text-red-600">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="w-3.5 h-3.5 shrink-0 mt-px">
                    </i>

                    <span>
                        {{ $message }}
                    </span>

                </p>

            @else

                <p class="text-xs text-muted-foreground">

                    Este texto será visible para los usuarios del portal.

                </p>

            @enderror

            <span class="text-xs text-muted-foreground shrink-0">

                Máximo 500

            </span>

        </div>

    </div>



    {{-- Vigencia --}}

    <section class="group/calendar relative overflow-hidden rounded-xl border border-primary/10 bg-gradient-to-br from-primary/[0.035] via-white to-blue-50/40 p-5 transition-all duration-300 hover:border-primary/20 hover:shadow-sm dark:border-slate-700 dark:from-blue-950/25 dark:via-slate-900 dark:to-slate-900/80 dark:hover:border-blue-800/70">

        <span class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-primary/5 pointer-events-none transition-all duration-500 group-hover/calendar:bg-primary/10 motion-safe:group-hover/calendar:scale-150"></span>


        <div class="relative">

            <div class="flex items-start gap-3 mb-5">

                <div class="flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-primary/10 text-primary transition-all duration-300 group-hover/calendar:bg-primary/15 motion-safe:group-hover/calendar:scale-105">

                    <i
                        data-lucide="calendar-range"
                        stroke-width="1.8"
                        class="w-4 h-4 transition-transform duration-300 motion-safe:group-hover/calendar:scale-110">
                    </i>

                </div>

                <div>

                    <h3 class="text-sm font-semibold text-foreground">

                        Vigencia del aviso

                    </h3>

                    <p class="text-xs text-muted-foreground mt-1">

                        Define cuándo debe aparecer y desaparecer el aviso.

                    </p>

                </div>

            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                {{-- Fecha de inicio --}}

                <div>

                    <label
                        for="fecha_inicio"
                        class="block mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-widest">

                        Fecha de inicio

                    </label>


                    <div
                        @class([
                            'group flex items-center gap-2 w-full px-3.5 rounded-lg border bg-card shadow-sm transition-all duration-200 focus-within:ring-2 focus-within:shadow-md dark:border-slate-700',

                            'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-900/70 dark:focus-within:border-red-500' =>
                                $errors->has('fecha_inicio'),

                            'border-border focus-within:border-primary focus-within:ring-primary/10 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                                ! $errors->has('fecha_inicio'),
                        ])>

                        <i
                            data-lucide="calendar-clock"
                            stroke-width="1.8"
                            @class([
                                'w-4 h-4 shrink-0 pointer-events-none transition-all duration-200 motion-safe:group-focus-within:scale-110',

                                'text-red-500' =>
                                    $errors->has('fecha_inicio'),

                                'text-muted-foreground group-focus-within:text-primary' =>
                                    ! $errors->has('fecha_inicio'),
                            ])>
                        </i>

                        <input
    type="datetime-local"
    id="fecha_inicio"
    name="fecha_inicio"
    value="{{ $fechaInicio }}"
    @unless($editando)
        min="{{ $fechaMinima }}"
    @endunless
    inputmode="none"
    onkeydown="event.preventDefault()"
    onpaste="event.preventDefault()"
    onclick="this.showPicker && this.showPicker()"
    onfocus="this.showPicker && this.showPicker()"
    class="w-full py-2.5 bg-transparent border-0 text-sm text-foreground focus:outline-none focus:ring-0 dark:[color-scheme:dark] cursor-pointer">

                    </div>


                    @error('fecha_inicio')

                        <p class="flex items-start gap-1.5 mt-2 text-xs text-red-600">

                            <i
                                data-lucide="circle-alert"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 shrink-0 mt-px">
                            </i>

                            <span>
                                {{ $message }}
                            </span>

                        </p>

                    @else

                        <p class="flex items-start gap-1.5 mt-2 text-xs text-muted-foreground">

                            <i
                                data-lucide="info"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 shrink-0 mt-px text-primary">
                            </i>

                            <span>
                                Déjalo vacío para publicarlo inmediatamente.
                            </span>

                        </p>

                    @enderror

                </div>



                {{-- Fecha de finalización --}}

                <div>

                    <label
                        for="fecha_fin"
                        class="block mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-widest">

                        Fecha de finalización

                    </label>


                    <div
                        @class([
                            'group flex items-center gap-2 w-full px-3.5 rounded-lg border bg-card shadow-sm transition-all duration-200 focus-within:ring-2 focus-within:shadow-md dark:border-slate-700',

                            'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-900/70 dark:focus-within:border-red-500' =>
                                $errors->has('fecha_fin'),

                            'border-border focus-within:border-primary focus-within:ring-primary/10 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                                ! $errors->has('fecha_fin'),
                        ])>

                        <i
                            data-lucide="calendar-x"
                            stroke-width="1.8"
                            @class([
                                'w-4 h-4 shrink-0 pointer-events-none transition-all duration-200 motion-safe:group-focus-within:scale-110',

                                'text-red-500' =>
                                    $errors->has('fecha_fin'),

                                'text-muted-foreground group-focus-within:text-primary' =>
                                    ! $errors->has('fecha_fin'),
                            ])>
                        </i>

                        <input
    type="datetime-local"
    id="fecha_fin"
    name="fecha_fin"
    value="{{ $fechaFin }}"
    min="{{ $fechaMinima }}"
    inputmode="none"
    onkeydown="event.preventDefault()"
    onpaste="event.preventDefault()"
    onclick="this.showPicker && this.showPicker()"
    onfocus="this.showPicker && this.showPicker()"
    class="w-full py-2.5 bg-transparent border-0 text-sm text-foreground focus:outline-none focus:ring-0 dark:[color-scheme:dark] cursor-pointer">

                    </div>


                    @error('fecha_fin')

                        <p class="flex items-start gap-1.5 mt-2 text-xs text-red-600">

                            <i
                                data-lucide="circle-alert"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 shrink-0 mt-px">
                            </i>

                            <span>
                                {{ $message }}
                            </span>

                        </p>

                    @else

                        <p class="flex items-start gap-1.5 mt-2 text-xs text-muted-foreground">

                            <i
                                data-lucide="info"
                                stroke-width="1.8"
                                class="w-3.5 h-3.5 shrink-0 mt-px text-primary">
                            </i>

                            <span>
                                Déjalo vacío para mantenerlo sin vencimiento.
                            </span>

                        </p>

                    @enderror

                </div>

            </div>

        </div>

    </section>



    {{-- Estado del aviso --}}

    <section
        x-data="{
            activo: @js($avisoActivo)
        }"
        class="group/status relative overflow-hidden rounded-xl border border-primary/10 bg-gradient-to-br from-primary/[0.06] via-white to-blue-50/60 p-4 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md dark:border-slate-700 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900/80 dark:hover:border-blue-800/70">


        {{-- Decoración --}}

        <span class="absolute -right-8 -top-10 w-28 h-28 rounded-full bg-primary/10 blur-3xl pointer-events-none transition-all duration-500 group-hover/status:bg-primary/20 motion-safe:group-hover/status:scale-125"></span>


        <div class="relative flex items-start gap-3">


            {{-- Valor enviado al controlador --}}

            <input
                type="hidden"
                name="activo"
                x-bind:value="activo ? '1' : '0'">


            {{-- Interruptor --}}

            <div
                role="switch"
                tabindex="0"
                x-on:click="activo = ! activo"
                x-on:keydown.space.prevent="activo = ! activo"
                x-on:keydown.enter.prevent="activo = ! activo"
                x-bind:aria-checked="activo ? 'true' : 'false'"
                x-bind:title="activo
                    ? 'Desactivar aviso'
                    : 'Activar aviso'"
                x-bind:class="activo
                    ? 'bg-primary'
                    : 'bg-slate-300 dark:bg-slate-700'"
                class="relative shrink-0 mt-0.5 rounded-full cursor-pointer transition-all duration-200 outline-none focus:outline-none focus-visible:outline-none hover:shadow-sm active:scale-95"
                style="
                    width: 44px;
                    height: 24px;
                    min-width: 44px;
                    padding: 0;
                    box-shadow: none;
                ">


                <span
                    x-bind:style="{
                        transform: activo
                            ? 'translateX(20px)'
                            : 'translateX(0)'
                    }"
                    class="absolute rounded-full bg-white shadow-sm pointer-events-none transition-transform duration-200 ease-out"
                    style="
                        width: 16px;
                        height: 16px;
                        top: 4px;
                        left: 4px;
                        display: block;
                    ">
                </span>

            </div>



            {{-- Información --}}

            <div class="min-w-0">

                <div class="flex flex-wrap items-center gap-2">

                    <span class="text-sm font-semibold text-foreground">

                        Aviso activo

                    </span>


                    <span
                        x-text="activo
                            ? 'Activado'
                            : 'Desactivado'"
                        x-bind:class="activo
                            ? 'bg-primary/10 text-primary dark:bg-blue-500/15 dark:text-blue-400'
                            : 'bg-slate-500/10 text-slate-500 dark:bg-slate-700 dark:text-slate-300'"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold transition-colors duration-200">
                    </span>

                </div>


                <p
                    x-text="activo
                        ? 'El aviso se mostrará cuando se encuentre dentro de su periodo de vigencia.'
                        : 'El aviso permanecerá oculto para los usuarios del portal.'"
                    class="text-xs text-muted-foreground mt-1 leading-relaxed">
                </p>

            </div>

        </div>

    </section>



    {{-- Acciones --}}

    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-5 border-t border-border dark:border-slate-700">

        <a
            href="{{ route('avisos.index') }}"
            class="group/cancel inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-border bg-card text-sm font-semibold text-foreground shadow-sm transition-all duration-200 hover:border-red-200 hover:bg-red-50 hover:text-red-600 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] dark:border-slate-700 dark:hover:border-red-900/70 dark:hover:bg-red-950/30 dark:hover:text-red-400">

            <i
                data-lucide="x"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/cancel:rotate-90">
            </i>

            <span>
                Cancelar
            </span>

        </a>


        <button
            type="submit"
            class="group/submit inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">

            <i
                data-lucide="{{ $editando
                    ? 'save'
                    : 'send'
                }}"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 motion-safe:group-hover/submit:scale-110">
            </i>

            <span>

                {{ $editando
                    ? 'Guardar cambios'
                    : 'Publicar aviso'
                }}

            </span>

        </button>

    </div>

</div>
