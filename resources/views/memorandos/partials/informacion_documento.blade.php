{{-- Información del documento --}}

<div
    class="group relative overflow-hidden rounded-2xl
           border border-border bg-card shadow-sm
           transition-all duration-300
           hover:border-primary/20 hover:shadow-md
           dark:border-slate-700/70
           dark:hover:border-blue-700/70
           dark:hover:shadow-black/20"
>
    {{-- Decoración --}}

    <span
        class="pointer-events-none absolute -right-12 -top-14
               h-36 w-36 rounded-full bg-primary/10 blur-3xl
               transition-all duration-500
               motion-safe:group-hover:scale-125"
    ></span>

    {{-- Encabezado --}}

    <div
        class="relative flex items-center gap-3
               border-b border-border
               bg-gradient-to-r from-primary/[0.06]
               via-white to-blue-50/40 px-6 py-4
               dark:border-slate-700/70
               dark:from-blue-950/30
               dark:via-slate-900
               dark:to-slate-900"
    >
        <span
            class="flex h-7 w-7 shrink-0 items-center
                   justify-center rounded-full bg-primary
                   text-xs font-semibold text-white shadow-sm
                   transition-transform duration-300
                   motion-safe:group-hover:scale-105"
        >
            1
        </span>

        <div class="min-w-0">

            <h2
                class="text-sm font-semibold text-foreground
                       dark:text-slate-100"
            >
                Información del documento
            </h2>

            <p
                class="mt-0.5 text-xs text-muted-foreground
                       dark:text-slate-400"
            >
                Completa los datos que aparecerán en el memorando.
            </p>

        </div>

    </div>

    {{-- Contenido --}}

    <div class="relative space-y-6 px-6 py-5">

        {{-- PARA / CC --}}

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

            {{-- PARA --}}

            <div>

                <label
                    for="para_nombre_visual"
                    class="mb-2 block text-xs font-semibold
                           uppercase tracking-widest
                           text-muted-foreground
                           dark:text-slate-400"
                >
                    Para
                </label>

                <div
                    id="para_nombre_visual"
                    class="flex min-h-11 w-full items-center
                           gap-2.5 rounded-lg border border-border
                           bg-muted/50 px-3.5 py-2.5
                           text-sm font-medium text-foreground
                           dark:border-slate-700/70
                           dark:bg-slate-900/60
                           dark:text-slate-200"
                >
                    <i
                        data-lucide="user-round-check"
                        stroke-width="1.8"
                        class="h-4 w-4 shrink-0 text-primary
                               dark:text-blue-400"
                    ></i>

                    <span class="leading-relaxed">
                        Lic. Byron Castro — Director de Seguridad
                    </span>
                </div>

                <input
                    type="hidden"
                    name="para_nombre"
                    value="Lic. Byron Castro — Director de Seguridad"
                >

            </div>

            {{-- CC --}}

            <div>

                <label
                    for="cc_nombre"
                    class="mb-2 block text-xs font-semibold
                           uppercase tracking-widest
                           text-muted-foreground
                           dark:text-slate-400"
                >
                    CC

                    <span class="text-primary dark:text-blue-400">
                        *
                    </span>
                </label>

                <div
                    @class([
                        'group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2 dark:bg-slate-900/80',

                        'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500 dark:focus-within:ring-red-500/20' =>
                            $errors->has('cc_nombre'),

                        'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                            ! $errors->has('cc_nombre'),
                    ])
                >
                    <i
                        data-lucide="users-round"
                        stroke-width="1.8"
                        class="h-4 w-4 shrink-0
                               text-muted-foreground
                               transition-all duration-200
                               group-focus-within/field:text-primary
                               dark:text-slate-400
                               dark:group-focus-within/field:text-blue-400
                               motion-safe:group-focus-within/field:scale-110"
                    ></i>

                    <select
                        id="cc_nombre"
                        name="cc_nombre"
                        required
                        class="w-full appearance-none border-0
                               bg-transparent py-2.5 text-sm
                               text-foreground
                               focus:outline-none focus:ring-0
                               dark:text-slate-200
                               dark:[color-scheme:dark]"
                    >
                        <option
                            value="Lic. Wesly López — Director Senior de TI"
                            @selected(
                                old(
                                    'cc_nombre',
                                    'Lic. Wesly López — Director Senior de TI'
                                )
                                ===
                                'Lic. Wesly López — Director Senior de TI'
                            )
                        >
                            Lic. Wesly López — Director Senior de TI
                        </option>

                        <option
                            value="Lic. Fernando Figueroa — Coordinador de Infraestructura TI"
                            @selected(
                                old('cc_nombre')
                                ===
                                'Lic. Fernando Figueroa — Coordinador de Infraestructura TI'
                            )
                        >
                            Lic. Fernando Figueroa — Coordinador de
                            Infraestructura TI
                        </option>
                    </select>

                    <i
                        data-lucide="chevron-down"
                        stroke-width="1.8"
                        class="h-4 w-4 shrink-0
                               text-muted-foreground
                               transition-transform duration-200
                               group-focus-within/field:rotate-180
                               group-focus-within/field:text-primary
                               dark:text-slate-400
                               dark:group-focus-within/field:text-blue-400"
                    ></i>

                </div>

                @error('cc_nombre')

                    <p
                        class="mt-2 flex items-center gap-1.5
                               text-xs text-red-600
                               dark:text-red-400"
                    >
                        <i
                            data-lucide="circle-alert"
                            stroke-width="1.8"
                            class="h-3.5 w-3.5 shrink-0"
                        ></i>

                        {{ $message }}
                    </p>

                @enderror

            </div>

        </div>

        {{-- DE / FECHA --}}

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

            {{-- DE --}}

            <div>

                <label
                    for="de_nombre"
                    class="mb-2 block text-xs font-semibold
                           uppercase tracking-widest
                           text-muted-foreground
                           dark:text-slate-400"
                >
                    De — Nombre y cargo

                    <span class="text-primary dark:text-blue-400">
                        *
                    </span>
                </label>

                <div
                    @class([
                        'group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2 dark:bg-slate-900/80',

                        'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500 dark:focus-within:ring-red-500/20' =>
                            $errors->has('de_nombre'),

                        'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                            ! $errors->has('de_nombre'),
                    ])
                >
                    <i
                        data-lucide="user-pen"
                        stroke-width="1.8"
                        class="h-4 w-4 shrink-0
                               text-muted-foreground
                               transition-all duration-200
                               group-focus-within/field:text-primary
                               dark:text-slate-400
                               dark:group-focus-within/field:text-blue-400
                               motion-safe:group-focus-within/field:scale-110"
                    ></i>

                    <input
                        type="text"
                        id="de_nombre"
                        name="de_nombre"
                        value="{{ old('de_nombre') }}"
                        maxlength="200"
                        autocomplete="name"
                        placeholder="Ej: Ing. Ana Martínez — Coordinadora de Sistemas"
                        required
                        class="w-full border-0 bg-transparent
                               py-2.5 text-sm text-foreground
                               placeholder:text-muted-foreground
                               focus:outline-none focus:ring-0
                               dark:text-slate-200
                               dark:placeholder:text-slate-500"
                    >
                </div>

                @error('de_nombre')

                    <p
                        class="mt-2 flex items-center gap-1.5
                               text-xs text-red-600
                               dark:text-red-400"
                    >
                        <i
                            data-lucide="circle-alert"
                            stroke-width="1.8"
                            class="h-3.5 w-3.5 shrink-0"
                        ></i>

                        {{ $message }}
                    </p>

                @enderror

            </div>

            {{-- Fecha --}}

            <div>

                <label
                    for="fecha_documento"
                    class="mb-2 block text-xs font-semibold
                           uppercase tracking-widest
                           text-muted-foreground
                           dark:text-slate-400"
                >
                    Fecha

                    <span class="text-primary dark:text-blue-400">
                        *
                    </span>
                </label>

                <div
                    @class([
                        'group/field flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-white px-3.5 transition-all duration-200 focus-within:ring-2 dark:bg-slate-900/80',

                        'border-red-300 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-800 dark:focus-within:border-red-500 dark:focus-within:ring-red-500/20' =>
                            $errors->has('fecha_documento'),

                        'border-border focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700/70 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15' =>
                            ! $errors->has('fecha_documento'),
                    ])
                >
                    <i
                        data-lucide="calendar-days"
                        stroke-width="1.8"
                        class="h-4 w-4 shrink-0
                               text-muted-foreground
                               transition-all duration-200
                               group-focus-within/field:text-primary
                               dark:text-slate-400
                               dark:group-focus-within/field:text-blue-400
                               motion-safe:group-focus-within/field:scale-110"
                    ></i>

                    <input
                        type="date"
                        id="fecha_documento"
                        name="fecha_documento"
                        value="{{ old(
                            'fecha_documento',
                            now()->format('Y-m-d')
                        ) }}"
                        min="{{ now()->format('Y-m-d') }}"
                        required
                        class="w-full border-0 bg-transparent
                               py-2.5 text-sm text-foreground
                               focus:outline-none focus:ring-0
                               dark:text-slate-200
                               dark:[color-scheme:dark]"
                    >
                </div>

                @error('fecha_documento')

                    <p
                        class="mt-2 flex items-center gap-1.5
                               text-xs text-red-600
                               dark:text-red-400"
                    >
                        <i
                            data-lucide="circle-alert"
                            stroke-width="1.8"
                            class="h-3.5 w-3.5 shrink-0"
                        ></i>

                        {{ $message }}
                    </p>

                @enderror

            </div>

        </div>

        {{-- ASUNTO --}}

        <div>

            <label
                for="asunto"
                class="mb-2 block text-xs font-semibold
                       uppercase tracking-widest
                       text-muted-foreground
                       dark:text-slate-400"
            >
                Asunto

                <span class="text-primary dark:text-blue-400">
                    *
                </span>
            </label>

            <div
                @class([
                    'flex min-h-11 w-full items-center gap-2.5 rounded-lg border bg-muted/50 px-3.5 transition-all duration-200 dark:bg-slate-900/60',

                    'border-red-300 dark:border-red-800' =>
                        $errors->has('asunto'),

                    'border-border dark:border-slate-700/70' =>
                        ! $errors->has('asunto'),
                ])
            >
                <i
                    data-lucide="file-text"
                    stroke-width="1.8"
                    class="h-4 w-4 shrink-0
                           text-muted-foreground
                           dark:text-slate-400"
                ></i>

                <input
                    type="text"
                    id="asunto"
                    name="asunto"
                    value="Autorización de ingreso de equipo"
                    readonly
                    aria-readonly="true"
                    required
                    class="w-full cursor-default border-0
                           bg-transparent py-2.5
                           text-sm font-medium text-foreground
                           focus:outline-none focus:ring-0
                           dark:text-slate-200"
                >
            </div>

            @error('asunto')

                <p
                    class="mt-2 flex items-center gap-1.5
                           text-xs text-red-600
                           dark:text-red-400"
                >
                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="h-3.5 w-3.5 shrink-0"
                    ></i>

                    {{ $message }}
                </p>

            @enderror

        </div>

    </div>

</div>