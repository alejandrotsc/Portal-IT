@php

    $editando = isset($usuario);

    $cuentaPropia = $editando
        && auth()->id() === $usuario->id;

@endphp


<div class="grid grid-cols-1 md:grid-cols-2 gap-6">


    {{-- Nombre --}}

    <div>

        <label
            for="nombre"
            class="block mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-widest">

            Nombre completo

            <span class="text-primary">*</span>

        </label>


        <div
            @class([
                'group flex items-center gap-2 w-full px-3.5 rounded-lg border bg-card shadow-sm transition-all duration-200 focus-within:ring-2 focus-within:shadow-md dark:border-slate-700',

                'border-red-300 hover:border-red-400 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-900/70 dark:hover:border-red-800 dark:focus-within:border-red-500' =>
                    $errors->has('nombre'),

                'border-border hover:border-primary/40 focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700 dark:hover:border-blue-800 dark:focus-within:border-blue-500' =>
                    ! $errors->has('nombre'),
            ])>

            <i
                data-lucide="user"
                stroke-width="1.8"
                @class([
                    'w-4 h-4 shrink-0 pointer-events-none transition-all duration-200 motion-safe:group-focus-within:scale-110',

                    'text-red-500' =>
                        $errors->has('nombre'),

                    'text-muted-foreground group-focus-within:text-primary' =>
                        ! $errors->has('nombre'),
                ])>
            </i>

            <input
                type="text"
                id="nombre"
                name="nombre"
                value="{{ old(
                    'nombre',
                    $usuario->nombre ?? ''
                ) }}"
                maxlength="200"
                autocomplete="name"
                placeholder="Nombre completo"
                required
                class="w-full py-2.5 bg-transparent border-0 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0">

        </div>


        @error('nombre')

            <p class="flex items-start gap-1.5 mt-2 text-xs text-red-600 dark:text-red-400">

                <i
                    data-lucide="circle-alert"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5 shrink-0 mt-px">
                </i>

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
            class="block mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-widest">

            Correo electrónico

            <span class="text-primary">*</span>

        </label>


        <div
            @class([
                'group flex items-center gap-2 w-full px-3.5 rounded-lg border bg-card shadow-sm transition-all duration-200 focus-within:ring-2 focus-within:shadow-md dark:border-slate-700',

                'border-red-300 hover:border-red-400 focus-within:border-red-500 focus-within:ring-red-500/10 dark:border-red-900/70 dark:hover:border-red-800 dark:focus-within:border-red-500' =>
                    $errors->has('correo'),

                'border-border hover:border-primary/40 focus-within:border-primary focus-within:ring-primary/10 dark:border-slate-700 dark:hover:border-blue-800 dark:focus-within:border-blue-500' =>
                    ! $errors->has('correo'),
            ])>

            <i
                data-lucide="mail"
                stroke-width="1.8"
                @class([
                    'w-4 h-4 shrink-0 pointer-events-none transition-all duration-200 motion-safe:group-focus-within:scale-110',

                    'text-red-500' =>
                        $errors->has('correo'),

                    'text-muted-foreground group-focus-within:text-primary' =>
                        ! $errors->has('correo'),
                ])>
            </i>

            <input
                type="email"
                id="correo"
                name="correo"
                value="{{ old(
                    'correo',
                    $usuario->correo ?? ''
                ) }}"
                maxlength="200"
                autocomplete="email"
                placeholder="usuario@televicentro.com"
                required
                class="w-full py-2.5 bg-transparent border-0 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0">

        </div>


        @error('correo')

            <p class="flex items-start gap-1.5 mt-2 text-xs text-red-600 dark:text-red-400">

                <i
                    data-lucide="circle-alert"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5 shrink-0 mt-px">
                </i>

                <span>
                    {{ $message }}
                </span>

            </p>

        @enderror


        @if($editando)

            <p class="flex items-start gap-1.5 mt-2 text-xs text-muted-foreground leading-relaxed">

                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5 shrink-0 mt-0.5 text-primary">
                </i>

                <span>
                    Si modificas el correo, el usuario deberá verificar la nueva dirección.
                </span>

            </p>

        @endif

    </div>



    {{-- Rol --}}

    <div class="md:col-span-2">

        <label
            for="rol_id"
            class="block mb-2 text-xs font-semibold text-muted-foreground uppercase tracking-widest">

            Rol del usuario

            <span class="text-primary">*</span>

        </label>


        @if($cuentaPropia)

            <input
                type="hidden"
                name="rol_id"
                value="{{ $usuario->rol_id }}">

        @endif


        <div
            @class([
                'group flex items-center gap-2 w-full px-3.5 rounded-lg border bg-card shadow-sm transition-all duration-200 focus-within:ring-2 dark:border-slate-700',

                'border-red-300 hover:border-red-400 focus-within:border-red-500 focus-within:ring-red-500/10 focus-within:shadow-md dark:border-red-900/70 dark:hover:border-red-800 dark:focus-within:border-red-500' =>
                    $errors->has('rol_id'),

                'border-border hover:border-primary/40 focus-within:border-primary focus-within:ring-primary/10 focus-within:shadow-md dark:border-slate-700 dark:hover:border-blue-800 dark:focus-within:border-blue-500' =>
                    ! $errors->has('rol_id')
                    && ! $cuentaPropia,

                'border-border bg-muted/30 opacity-60 cursor-not-allowed shadow-none dark:border-slate-700' =>
                    $cuentaPropia,
            ])>

            <i
                data-lucide="{{ $cuentaPropia
                    ? 'lock'
                    : 'shield'
                }}"
                stroke-width="1.8"
                @class([
                    'w-4 h-4 shrink-0 pointer-events-none transition-all duration-200',

                    'text-red-500' =>
                        $errors->has('rol_id'),

                    'text-muted-foreground' =>
                        $cuentaPropia,

                    'text-muted-foreground group-focus-within:text-primary motion-safe:group-focus-within:scale-110' =>
                        ! $errors->has('rol_id')
                        && ! $cuentaPropia,
                ])>
            </i>


            <select
                id="rol_id"
                @unless($cuentaPropia)
                    name="rol_id"
                @endunless
                required
                @disabled($cuentaPropia)
                class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0 disabled:cursor-not-allowed [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-slate-100">

                <option
                    value=""
                    disabled
                    @selected(
                        old(
                            'rol_id',
                            $usuario->rol_id ?? ''
                        ) === ''
                    )>

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
                        )>

                        {{ $rol->nombre }}

                    </option>

                @endforeach

            </select>


            <i
                data-lucide="chevron-down"
                stroke-width="1.8"
                @class([
                    'w-4 h-4 shrink-0 pointer-events-none transition-all duration-200',

                    'text-red-500' =>
                        $errors->has('rol_id'),

                    'text-muted-foreground' =>
                        $cuentaPropia,

                    'text-muted-foreground group-focus-within:text-primary motion-safe:group-focus-within:translate-y-0.5' =>
                        ! $errors->has('rol_id')
                        && ! $cuentaPropia,
                ])>
            </i>

        </div>


        @error('rol_id')

            <p class="flex items-start gap-1.5 mt-2 text-xs text-red-600 dark:text-red-400">

                <i
                    data-lucide="circle-alert"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5 shrink-0 mt-px">
                </i>

                <span>
                    {{ $message }}
                </span>

            </p>

        @enderror


        @if($cuentaPropia)

            <p class="flex items-start gap-1.5 mt-2 text-xs text-muted-foreground leading-relaxed">

                <i
                    data-lucide="lock"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5 shrink-0 mt-0.5 text-primary">
                </i>

                <span>
                    No puedes modificar el rol de tu propia cuenta administrativa.
                </span>

            </p>

        @else

            <p class="flex items-start gap-1.5 mt-2 text-xs text-muted-foreground leading-relaxed">

                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5 shrink-0 mt-0.5 text-primary">
                </i>

                <span>
                    El rol determina las funciones disponibles para el usuario dentro del portal.
                </span>

            </p>

        @endif

    </div>



    {{-- Acciones --}}

    <div class="md:col-span-2 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-5 mt-1 border-t border-border dark:border-slate-700">

        <a
            href="{{ route('usuarios.index') }}"
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
                    : 'user-plus'
                }}"
                stroke-width="1.8"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/submit:scale-110">
            </i>

            <span>
                {{ $editando
                    ? 'Guardar cambios'
                    : 'Crear usuario'
                }}
            </span>

        </button>

    </div>

</div>
