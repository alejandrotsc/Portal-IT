@php

    $editando = isset($usuario);

    $cuentaPropia =
        $editando
        && auth()->id() === $usuario->id;

    $rolUsuarioTI = $roles->firstWhere(
        'nombre',
        'UsuarioTI'
    );

    $rolSeleccionado = old(
        'rol_id',
        $usuario->rol_id ?? ''
    );

@endphp


<div
    x-data="{
        rolSeleccionado:
            @js((string) $rolSeleccionado),

        rolUsuarioTI:
            @js((string) ($rolUsuarioTI?->id ?? '')),

        esUsuarioTI() {
            return String(this.rolSeleccionado)
                ===
                String(this.rolUsuarioTI);
        }
    }"
    class="grid grid-cols-1 gap-6 md:grid-cols-2"
>


    {{-- Nombre --}}

    <div>

        <label
            for="nombre"
            class="mb-2 block text-xs font-semibold uppercase
                   tracking-widest text-muted-foreground"
        >
            Nombre completo

            <span class="text-primary">*</span>
        </label>


        <div
            @class([
                'group flex w-full items-center gap-2 rounded-lg
                 border bg-card px-3.5 shadow-sm
                 transition-all duration-200
                 focus-within:ring-2 focus-within:shadow-md
                 dark:border-slate-700',

                'border-red-300 hover:border-red-400
                 focus-within:border-red-500
                 focus-within:ring-red-500/10
                 dark:border-red-900/70
                 dark:hover:border-red-800
                 dark:focus-within:border-red-500' =>
                    $errors->has('nombre'),

                'border-border hover:border-primary/40
                 focus-within:border-primary
                 focus-within:ring-primary/10
                 dark:border-slate-700
                 dark:hover:border-blue-800
                 dark:focus-within:border-blue-500' =>
                    ! $errors->has('nombre'),
            ])
        >

            <i
                data-lucide="user"
                stroke-width="1.8"
                @class([
                    'h-4 w-4 shrink-0 pointer-events-none
                     transition-all duration-200
                     motion-safe:group-focus-within:scale-110',

                    'text-red-500' =>
                        $errors->has('nombre'),

                    'text-muted-foreground
                     group-focus-within:text-primary' =>
                        ! $errors->has('nombre'),
                ])
            ></i>

            <input
                type="text"
                id="nombre"
                name="nombre"
                value="{{
                    old(
                        'nombre',
                        $usuario->nombre ?? ''
                    )
                }}"
                maxlength="200"
                autocomplete="name"
                placeholder="Nombre completo"
                required
                class="w-full border-0 bg-transparent py-2.5
                       text-sm text-foreground
                       placeholder:text-muted-foreground
                       focus:outline-none focus:ring-0"
            >

        </div>


        @error('nombre')

            <p
                class="mt-2 flex items-start gap-1.5
                       text-xs text-red-600
                       dark:text-red-400"
            >
                <i
                    data-lucide="circle-alert"
                    stroke-width="1.8"
                    class="mt-px h-3.5 w-3.5 shrink-0"
                ></i>

                <span>
                    {{ $message }}
                </span>
            </p>

        @enderror

    </div>



    {{-- Correo --}}

    <div>

        <label
            for="correo"
            class="mb-2 block text-xs font-semibold uppercase
                   tracking-widest text-muted-foreground"
        >
            Correo electrónico

            <span class="text-primary">*</span>
        </label>


        <div
            @class([
                'group flex w-full items-center gap-2 rounded-lg
                 border bg-card px-3.5 shadow-sm
                 transition-all duration-200
                 focus-within:ring-2 focus-within:shadow-md
                 dark:border-slate-700',

                'border-red-300 hover:border-red-400
                 focus-within:border-red-500
                 focus-within:ring-red-500/10
                 dark:border-red-900/70
                 dark:hover:border-red-800
                 dark:focus-within:border-red-500' =>
                    $errors->has('correo'),

                'border-border hover:border-primary/40
                 focus-within:border-primary
                 focus-within:ring-primary/10
                 dark:border-slate-700
                 dark:hover:border-blue-800
                 dark:focus-within:border-blue-500' =>
                    ! $errors->has('correo'),
            ])
        >

            <i
                data-lucide="mail"
                stroke-width="1.8"
                @class([
                    'h-4 w-4 shrink-0 pointer-events-none
                     transition-all duration-200
                     motion-safe:group-focus-within:scale-110',

                    'text-red-500' =>
                        $errors->has('correo'),

                    'text-muted-foreground
                     group-focus-within:text-primary' =>
                        ! $errors->has('correo'),
                ])
            ></i>

            <input
                type="email"
                id="correo"
                name="correo"
                value="{{
                    old(
                        'correo',
                        $usuario->correo ?? ''
                    )
                }}"
                maxlength="200"
                autocomplete="email"
                placeholder="usuario@televicentro.com"
                required
                class="w-full border-0 bg-transparent py-2.5
                       text-sm text-foreground
                       placeholder:text-muted-foreground
                       focus:outline-none focus:ring-0"
            >

        </div>


        @error('correo')

            <p
                class="mt-2 flex items-start gap-1.5
                       text-xs text-red-600
                       dark:text-red-400"
            >
                <i
                    data-lucide="circle-alert"
                    stroke-width="1.8"
                    class="mt-px h-3.5 w-3.5 shrink-0"
                ></i>

                <span>
                    {{ $message }}
                </span>
            </p>

        @enderror


        @if($editando)

            <p
                class="mt-2 flex items-start gap-1.5
                       text-xs leading-relaxed
                       text-muted-foreground"
            >
                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="mt-0.5 h-3.5 w-3.5 shrink-0
                           text-primary"
                ></i>

                <span>
                    Si modificas el correo, el usuario deberá
                    verificar la nueva dirección.
                </span>
            </p>

        @endif

    </div>



    {{-- Rol --}}

    <div class="md:col-span-2">

        <label
            for="rol_id"
            class="mb-2 block text-xs font-semibold uppercase
                   tracking-widest text-muted-foreground"
        >
            Rol del usuario

            <span class="text-primary">*</span>
        </label>


        @if($cuentaPropia)

            <input
                type="hidden"
                name="rol_id"
                value="{{ $usuario->rol_id }}"
            >

        @endif


        <div
            @class([
                'group flex w-full items-center gap-2 rounded-lg
                 border bg-card px-3.5 shadow-sm
                 transition-all duration-200
                 focus-within:ring-2 dark:border-slate-700',

                'border-red-300 hover:border-red-400
                 focus-within:border-red-500
                 focus-within:ring-red-500/10
                 focus-within:shadow-md
                 dark:border-red-900/70
                 dark:hover:border-red-800
                 dark:focus-within:border-red-500' =>
                    $errors->has('rol_id'),

                'border-border hover:border-primary/40
                 focus-within:border-primary
                 focus-within:ring-primary/10
                 focus-within:shadow-md
                 dark:border-slate-700
                 dark:hover:border-blue-800
                 dark:focus-within:border-blue-500' =>
                    ! $errors->has('rol_id')
                    && ! $cuentaPropia,

                'border-border bg-muted/30 opacity-60
                 cursor-not-allowed shadow-none
                 dark:border-slate-700' =>
                    $cuentaPropia,
            ])
        >

            <i
                data-lucide="{{
                    $cuentaPropia
                        ? 'lock'
                        : 'shield'
                }}"
                stroke-width="1.8"
                @class([
                    'h-4 w-4 shrink-0 pointer-events-none
                     transition-all duration-200',

                    'text-red-500' =>
                        $errors->has('rol_id'),

                    'text-muted-foreground' =>
                        $cuentaPropia,

                    'text-muted-foreground
                     group-focus-within:text-primary
                     motion-safe:group-focus-within:scale-110' =>
                        ! $errors->has('rol_id')
                        && ! $cuentaPropia,
                ])
            ></i>


            <select
                id="rol_id"
                x-model="rolSeleccionado"
                @unless($cuentaPropia)
                    name="rol_id"
                @endunless
                required
                @disabled($cuentaPropia)
                class="w-full appearance-none border-0
                       bg-transparent py-2.5
                       text-sm text-foreground
                       focus:outline-none focus:ring-0
                       disabled:cursor-not-allowed
                       [&>option]:bg-white
                       [&>option]:text-slate-900
                       dark:[&>option]:bg-slate-900
                       dark:[&>option]:text-slate-100"
            >

                <option
                    value=""
                    disabled
                    @selected(
                        old(
                            'rol_id',
                            $usuario->rol_id ?? ''
                        ) === ''
                    )
                >
                    Selecciona un rol
                </option>


                @foreach($roles as $rol)

                    <option
                        value="{{ $rol->id }}"
                        @selected(
                            (string) old(
                                'rol_id',
                                $usuario->rol_id ?? ''
                            )
                            ===
                            (string) $rol->id
                        )
                    >
                        {{ $rol->nombre }}
                    </option>

                @endforeach

            </select>


            <i
                data-lucide="chevron-down"
                stroke-width="1.8"
                @class([
                    'h-4 w-4 shrink-0 pointer-events-none
                     transition-all duration-200',

                    'text-red-500' =>
                        $errors->has('rol_id'),

                    'text-muted-foreground' =>
                        $cuentaPropia,

                    'text-muted-foreground
                     group-focus-within:text-primary
                     motion-safe:group-focus-within:translate-y-0.5' =>
                        ! $errors->has('rol_id')
                        && ! $cuentaPropia,
                ])
            ></i>

        </div>


        @error('rol_id')

            <p
                class="mt-2 flex items-start gap-1.5
                       text-xs text-red-600
                       dark:text-red-400"
            >
                <i
                    data-lucide="circle-alert"
                    stroke-width="1.8"
                    class="mt-px h-3.5 w-3.5 shrink-0"
                ></i>

                <span>
                    {{ $message }}
                </span>
            </p>

        @enderror


        @if($cuentaPropia)

            <p
                class="mt-2 flex items-start gap-1.5
                       text-xs leading-relaxed
                       text-muted-foreground"
            >
                <i
                    data-lucide="lock"
                    stroke-width="1.8"
                    class="mt-0.5 h-3.5 w-3.5 shrink-0
                           text-primary"
                ></i>

                <span>
                    No puedes modificar el rol de tu propia
                    cuenta administrativa.
                </span>
            </p>

        @else

            <p
                class="mt-2 flex items-start gap-1.5
                       text-xs leading-relaxed
                       text-muted-foreground"
            >
                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="mt-0.5 h-3.5 w-3.5 shrink-0
                           text-primary"
                ></i>

                <span>
                    El rol determina las funciones disponibles
                    para el usuario dentro del portal.
                </span>
            </p>

        @endif

    </div>



    {{-- Extensión telefónica --}}

    <div
        x-show="esUsuarioTI()"
        x-cloak
        x-transition.opacity.duration.200ms
        class="md:col-span-2"
    >

        <label
            for="extension_telefonica"
            class="mb-2 block text-xs font-semibold uppercase
                   tracking-widest text-muted-foreground"
        >
            Extensión telefónica

            <span class="text-primary">*</span>
        </label>


        <div
            @class([
                'group flex w-full items-center gap-2 rounded-lg
                 border bg-card px-3.5 shadow-sm
                 transition-all duration-200
                 focus-within:ring-2 focus-within:shadow-md
                 dark:border-slate-700',

                'border-red-300 hover:border-red-400
                 focus-within:border-red-500
                 focus-within:ring-red-500/10
                 dark:border-red-900/70
                 dark:hover:border-red-800
                 dark:focus-within:border-red-500' =>
                    $errors->has('extension_telefonica'),

                'border-border hover:border-primary/40
                 focus-within:border-primary
                 focus-within:ring-primary/10
                 dark:border-slate-700
                 dark:hover:border-blue-800
                 dark:focus-within:border-blue-500' =>
                    ! $errors->has('extension_telefonica'),
            ])
        >

            <i
                data-lucide="phone"
                stroke-width="1.8"
                @class([
                    'h-4 w-4 shrink-0 pointer-events-none
                     transition-all duration-200
                     motion-safe:group-focus-within:scale-110',

                    'text-red-500' =>
                        $errors->has('extension_telefonica'),

                    'text-muted-foreground
                     group-focus-within:text-primary' =>
                        ! $errors->has('extension_telefonica'),
                ])
            ></i>

            <input
                type="text"
                id="extension_telefonica"
                name="extension_telefonica"
                value="{{
                    old(
                        'extension_telefonica',
                        $usuario->extension_telefonica ?? ''
                    )
                }}"
                inputmode="numeric"
                maxlength="10"
                autocomplete="off"
                placeholder="Ej. 1234"
                x-bind:required="esUsuarioTI()"
                x-bind:disabled="! esUsuarioTI()"
                class="w-full border-0 bg-transparent py-2.5
                       text-sm text-foreground
                       placeholder:text-muted-foreground
                       focus:outline-none focus:ring-0"
            >

        </div>


        @error('extension_telefonica')

            <p
                class="mt-2 flex items-start gap-1.5
                       text-xs text-red-600
                       dark:text-red-400"
            >
                <i
                    data-lucide="circle-alert"
                    stroke-width="1.8"
                    class="mt-px h-3.5 w-3.5 shrink-0"
                ></i>

                <span>
                    {{ $message }}
                </span>
            </p>

        @enderror


        <p
            class="mt-2 flex items-start gap-1.5
                   text-xs leading-relaxed
                   text-muted-foreground"
        >
            <i
                data-lucide="info"
                stroke-width="1.8"
                class="mt-0.5 h-3.5 w-3.5 shrink-0
                       text-primary"
            ></i>

            <span>
                La extensión se mostrará como medio de contacto
                en los turnos del equipo de soporte.
            </span>
        </p>

    </div>



    {{-- Acciones --}}

    <div
        class="mt-1 flex flex-col-reverse gap-3
               border-t border-border pt-5
               sm:flex-row sm:items-center sm:justify-end
               md:col-span-2
               dark:border-slate-700"
    >

        <a
            href="{{ route('usuarios.index') }}"
            class="group/cancel inline-flex items-center
                   justify-center gap-2 rounded-lg border
                   border-border bg-card px-4 py-2.5
                   text-sm font-semibold text-foreground
                   shadow-sm transition-all duration-200
                   hover:border-red-200 hover:bg-red-50
                   hover:text-red-600 hover:shadow
                   motion-safe:hover:-translate-y-0.5
                   active:translate-y-0 active:scale-[0.98]
                   dark:border-slate-700
                   dark:hover:border-red-900/70
                   dark:hover:bg-red-950/30
                   dark:hover:text-red-400"
        >
            <i
                data-lucide="x"
                stroke-width="1.8"
                class="h-4 w-4 shrink-0
                       transition-transform duration-200
                       group-hover/cancel:rotate-90"
            ></i>

            <span>
                Cancelar
            </span>
        </a>


        <button
            type="submit"
            class="group/submit inline-flex items-center
                   justify-center gap-2 rounded-lg
                   bg-primary px-5 py-2.5
                   text-sm font-semibold text-white
                   shadow-sm transition-all duration-200
                   hover:bg-primary/90 hover:shadow-md
                   motion-safe:hover:-translate-y-0.5
                   active:translate-y-0 active:scale-[0.98]"
        >
            <i
                data-lucide="{{
                    $editando
                        ? 'save'
                        : 'user-plus'
                }}"
                stroke-width="1.8"
                class="h-4 w-4 shrink-0
                       transition-transform duration-200
                       group-hover/submit:scale-110"
            ></i>

            <span>
                {{
                    $editando
                        ? 'Guardar cambios'
                        : 'Crear usuario'
                }}
            </span>
        </button>

    </div>

</div>