@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-7xl mx-auto px-6 py-10">


        {{-- Encabezado --}}

        <section class="mb-10">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <span class="mb-4 inline-flex items-center gap-2 rounded-full border border-blue-200/60 bg-blue-500/[0.08] px-3 py-1.5 text-xs font-semibold text-blue-700 dark:border-blue-900/70 dark:bg-blue-950/60 dark:text-blue-300">
                        <i data-lucide="users" stroke-width="1.8" class="h-3.5 w-3.5 shrink-0"></i>
                        Gestión de accesos
                    </span>

                    <h1 class="text-2xl font-semibold text-foreground tracking-tight">

                        Administración de usuarios

                    </h1>

                    <p class="text-sm text-muted-foreground mt-2 max-w-2xl leading-relaxed">

                        Consulta, registra y administra el acceso de los usuarios al Portal TI.

                    </p>

                </div>

            </div>

        </section>



        {{-- Mensajes --}}

        @if(session('success'))

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-300">

                    <i
                        data-lucide="circle-check"
                        stroke-width="1.8"
                        class="w-4 h-4">
                    </i>

                </div>

                <p class="pt-1.5 leading-relaxed">

                    {{ session('success') }}

                </p>

            </div>

        @endif


        @if(session('warning'))

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-300">

                    <i
                        data-lucide="triangle-alert"
                        stroke-width="1.8"
                        class="w-4 h-4">
                    </i>

                </div>

                <p class="pt-1.5 leading-relaxed">

                    {{ session('warning') }}

                </p>

            </div>

        @endif


        @if($errors->any())

            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">

                <div class="flex items-center justify-center w-8 h-8 shrink-0 rounded-lg bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-300">

                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="w-4 h-4">
                    </i>

                </div>

                <div class="pt-1.5 space-y-1">

                    @foreach($errors->all() as $error)

                        <p class="leading-relaxed">

                            {{ $error }}

                        </p>

                    @endforeach

                </div>

            </div>

        @endif



        {{-- Resumen --}}

        <section class="mb-10">

            <div class="mb-5">
                <h2 class="text-base font-semibold text-foreground">Resumen de usuarios</h2>
                <p class="mt-1 text-sm text-muted-foreground">Estado actual de las cuentas registradas en el portal.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">


                {{-- Total --}}

                <div class="group relative overflow-hidden rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50 via-white to-indigo-50/60 p-5 shadow-sm transition-all duration-300 motion-safe:hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10 dark:border-blue-900/60 dark:from-blue-950/30 dark:via-slate-900 dark:to-indigo-950/20">

                    <div class="pointer-events-none absolute -right-8 -top-8 w-24 h-24 rounded-full bg-blue-400/10 transition-all duration-500 motion-safe:group-hover:scale-150 group-hover:bg-blue-400/20"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-blue-500/10 text-blue-600 transition-all duration-300 motion-safe:group-hover:scale-105 group-hover:bg-blue-100 dark:text-blue-400 dark:group-hover:bg-blue-950/70">

                            <i
                                data-lucide="users"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-700 dark:text-blue-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            Total
                        </span>

                        <div class="mt-1 w-full min-w-0">

                            <p class="text-2xl font-semibold text-foreground leading-none">

                                {{ $resumen['total'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground mt-2">

                                Usuarios registrados

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Activos --}}

                <div class="group relative overflow-hidden rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50 via-white to-teal-50/50 p-5 shadow-sm transition-all duration-300 motion-safe:hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10 dark:border-emerald-900/60 dark:from-emerald-950/30 dark:via-slate-900 dark:to-teal-950/20">

                    <div class="pointer-events-none absolute -right-8 -top-8 w-24 h-24 rounded-full bg-emerald-400/10 transition-all duration-500 motion-safe:group-hover:scale-150 group-hover:bg-emerald-400/20"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-emerald-500/10 text-emerald-600 transition-all duration-300 motion-safe:group-hover:scale-105 group-hover:bg-emerald-100 dark:text-emerald-400 dark:group-hover:bg-emerald-950/70">

                            <i
                                data-lucide="user-check"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Activos
                        </span>

                        <div class="mt-1 w-full min-w-0">

                            <p class="text-2xl font-semibold text-foreground leading-none">

                                {{ $resumen['activos'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground mt-2">

                                Usuarios activos

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Inactivos --}}

                <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100/60 p-5 shadow-sm transition-all duration-300 motion-safe:hover:-translate-y-1 hover:border-slate-300 hover:shadow-lg hover:shadow-slate-500/10 dark:border-slate-700 dark:from-slate-800/70 dark:via-slate-900 dark:to-slate-800/40">

                    <div class="pointer-events-none absolute -right-8 -top-8 w-24 h-24 rounded-full bg-slate-400/10 transition-all duration-500 motion-safe:group-hover:scale-150 group-hover:bg-slate-400/20"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-slate-500/10 text-slate-600 transition-all duration-300 motion-safe:group-hover:scale-105 group-hover:bg-slate-200 dark:text-slate-400 dark:group-hover:bg-slate-800">

                            <i
                                data-lucide="user-x"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-500/10 px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                            Inactivos
                        </span>

                        <div class="mt-1 w-full min-w-0">

                            <p class="text-2xl font-semibold text-foreground leading-none">

                                {{ $resumen['inactivos'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground mt-2">

                                Usuarios inactivos

                            </p>

                        </div>

                    </div>

                </div>



                {{-- Pendientes --}}

                <div class="group relative overflow-hidden rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50 via-white to-orange-50/50 p-5 shadow-sm transition-all duration-300 motion-safe:hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10 dark:border-amber-900/60 dark:from-amber-950/30 dark:via-slate-900 dark:to-orange-950/20">

                    <div class="pointer-events-none absolute -right-8 -top-8 w-24 h-24 rounded-full bg-amber-400/10 transition-all duration-500 motion-safe:group-hover:scale-150 group-hover:bg-amber-400/20"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">

                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-amber-500/10 text-amber-600 transition-all duration-300 motion-safe:group-hover:scale-105 group-hover:bg-amber-100 dark:text-amber-400 dark:group-hover:bg-amber-950/70">

                            <i
                                data-lucide="mail-warning"
                                stroke-width="1.8"
                                class="w-5 h-5 transition-transform duration-300 motion-safe:group-hover:scale-110">
                            </i>

                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Pendientes
                        </span>

                        <div class="mt-1 w-full min-w-0">

                            <p class="text-2xl font-semibold text-foreground leading-none">

                                {{ $resumen['pendientes'] ?? 0 }}

                            </p>

                            <p class="text-sm text-muted-foreground mt-2">

                                Correos pendientes

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- Listado --}}

        <section class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-slate-700">


            {{-- Cabecera y filtros --}}

            <div class="border-b border-border px-5 py-5 dark:border-slate-700">

                <div class="mb-5">

                    <h2 class="text-base font-semibold text-foreground">

                        Usuarios registrados

                    </h2>

                    <p class="text-sm text-muted-foreground mt-1">

                        Busca usuarios o filtra los resultados por rol y estado.

                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('usuarios.index') }}"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[minmax(260px,1fr)_190px_190px_auto] gap-3">


                    {{-- Buscar --}}

                    <div class="group flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card shadow-sm transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 focus-within:shadow-md dark:border-slate-700 dark:focus-within:border-blue-500">

                        <i
                            data-lucide="search"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-colors duration-200 group-focus-within:text-primary">
                        </i>

                        <input
                            type="search"
                            name="buscar"
                            value="{{ $busqueda }}"
                            placeholder="Buscar por nombre o correo..."
                            autocomplete="off"
                            class="w-full py-2.5 bg-transparent border-0 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none">

                    </div>



                    {{-- Rol --}}

                    <div class="group flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card shadow-sm transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 focus-within:shadow-md dark:border-slate-700 dark:focus-within:border-blue-500">

                        <i
                            data-lucide="shield"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-colors duration-200 group-focus-within:text-primary">
                        </i>

                        <select
                            name="rol"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0">

                            <option value="">
                                Todos los roles
                            </option>

                            @foreach($roles as $rol)

                                <option
                                    value="{{ $rol->id }}"
                                    @selected(
                                        (string) $rolSeleccionado
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
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-colors duration-200 group-focus-within:text-primary">
                        </i>

                    </div>



                    {{-- Estado --}}

                    <div class="group flex items-center gap-2 w-full px-3.5 rounded-lg border border-border bg-card shadow-sm transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/10 focus-within:shadow-md dark:border-slate-700 dark:focus-within:border-blue-500">

                        <i
                            data-lucide="activity"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-colors duration-200 group-focus-within:text-primary">
                        </i>

                        <select
                            name="estado"
                            class="w-full py-2.5 bg-transparent border-0 appearance-none text-sm text-foreground focus:outline-none focus:ring-0">

                            <option value="">
                                Todos los estados
                            </option>

                            <option
                                value="activo"
                                @selected($estadoSeleccionado === 'activo')>

                                Activos

                            </option>

                            <option
                                value="inactivo"
                                @selected($estadoSeleccionado === 'inactivo')>

                                Inactivos

                            </option>

                            <option
                                value="pendiente"
                                @selected($estadoSeleccionado === 'pendiente')>

                                Pendientes de verificación

                            </option>

                        </select>

                        <i
                            data-lucide="chevron-down"
                            stroke-width="1.8"
                            class="w-4 h-4 shrink-0 text-muted-foreground pointer-events-none transition-colors duration-200 group-focus-within:text-primary">
                        </i>

                    </div>



                    {{-- Acciones de filtros --}}

                    <div class="flex items-stretch gap-2">

                        <button
                            type="submit"
                            class="group inline-flex flex-1 items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">

                            <i
                                data-lucide="filter"
                                stroke-width="1.8"
                                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-110">
                            </i>

                            Filtrar

                        </button>


                        @if(
                            $busqueda !== ''
                            || filled($rolSeleccionado)
                            || filled($estadoSeleccionado)
                        )

                            <a
                                href="{{ route('usuarios.index') }}"
                                title="Limpiar filtros"
                                class="group/clear inline-flex items-center justify-center w-10 shrink-0 rounded-lg border border-border bg-card text-muted-foreground shadow-sm transition-all duration-200 hover:text-red-600 hover:border-red-500/30 hover:bg-red-500/5 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] dark:border-slate-700">

                                <i
                                    data-lucide="x"
                                    stroke-width="1.8"
                                    class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/clear:rotate-90">
                                </i>

                            </a>

                        @endif

                    </div>

                </form>

            </div>



            {{-- Tabla --}}

            <div class="overflow-x-auto dark:bg-slate-900/30">

                <table class="w-full min-w-[1000px]">

                    <thead class="border-b border-border bg-muted/40 dark:border-slate-700 dark:bg-slate-900/80">

                        <tr class="text-left">

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Usuario
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Rol
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Verificación
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Acceso
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                Registro
                            </th>

                            <th class="px-5 py-3.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider text-right">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-border dark:divide-slate-800">

                        @forelse($usuarios as $usuario)

                            @php
                                $nombreRol = $usuario->rol?->nombre;
                            @endphp

                            <tr class="group/row transition-colors duration-200 hover:bg-primary/[0.025] dark:bg-slate-900/20 dark:hover:bg-slate-800/40">


                                {{-- Usuario --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-full bg-primary/10 text-primary text-sm font-semibold transition-all duration-300 group-hover/row:bg-primary group-hover/row:text-white motion-safe:group-hover/row:scale-105">

                                            {{ mb_strtoupper(
                                                mb_substr(
                                                    $usuario->nombre,
                                                    0,
                                                    1
                                                )
                                            ) }}

                                        </div>

                                        <div class="min-w-0">

                                            <div class="flex items-center gap-2">

                                                <p class="text-sm font-semibold text-foreground truncate">

                                                    {{ $usuario->nombre }}

                                                </p>

                                                @if(auth()->id() === $usuario->id)

                                                    <span class="shrink-0 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-semibold">

                                                        Tú

                                                    </span>

                                                @endif

                                            </div>

                                            <p class="text-xs text-muted-foreground mt-0.5 truncate">

                                                {{ $usuario->correo }}

                                            </p>

                                        </div>

                                    </div>

                                </td>



                                {{-- Rol --}}

                                <td class="px-5 py-4">

                                    <span
                                        @class([
                                            'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',

                                            'bg-violet-500/10 text-violet-700 dark:text-violet-400' =>
                                                $nombreRol === 'Administrador',

                                            'bg-blue-500/10 text-blue-700 dark:text-blue-400' =>
                                                $nombreRol === 'UsuarioTI',

                                            'bg-slate-500/10 text-slate-700 dark:text-slate-400' =>
                                                $nombreRol === 'Usuario',

                                            'bg-muted text-muted-foreground' =>
                                                ! in_array(
                                                    $nombreRol,
                                                    [
                                                        'Administrador',
                                                        'UsuarioTI',
                                                        'Usuario',
                                                    ],
                                                    true
                                                ),
                                        ])>

                                        {{ $nombreRol ?? 'Sin rol' }}

                                    </span>

                                </td>



                                {{-- Verificación --}}

                                <td class="px-5 py-4">

                                    @if($usuario->correoEstaVerificado())

                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">

                                            <i
                                                data-lucide="badge-check"
                                                stroke-width="1.8"
                                                class="w-4 h-4 shrink-0">
                                            </i>

                                            Verificado

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 dark:text-amber-400">

                                            <i
                                                data-lucide="clock-3"
                                                stroke-width="1.8"
                                                class="w-4 h-4 shrink-0">
                                            </i>

                                            Pendiente

                                        </span>

                                    @endif

                                </td>



                                {{-- Acceso --}}

                                <td class="px-5 py-4">

                                    @if($usuario->activo)

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 text-xs font-medium dark:text-emerald-400">

                                            <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-emerald-500"></span>

                                            Activo

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-500/10 text-slate-600 text-xs font-medium dark:text-slate-400">

                                            <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-slate-400"></span>

                                            Inactivo

                                        </span>

                                    @endif

                                </td>



                                {{-- Registro --}}

                                <td class="px-5 py-4">

                                    <p class="text-sm text-foreground">

                                        {{ $usuario->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('d/m/Y') }}

                                    </p>

                                    <p class="text-xs text-muted-foreground mt-0.5">

                                        {{ $usuario->created_at
                                            ?->timezone('America/Tegucigalpa')
                                            ->format('h:i A') }}

                                    </p>

                                </td>



                                {{-- Acciones --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-center justify-end gap-2">


                                        {{-- Editar --}}

                                        <a
                                            href="{{ route(
                                                'usuarios.edit',
                                                $usuario
                                            ) }}"
                                            title="Editar usuario"
                                            class="group/edit inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border border-border bg-card text-muted-foreground shadow-sm transition-all duration-200 hover:text-primary hover:border-primary/30 hover:bg-primary/5 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.95] dark:border-slate-700">

                                            <i
                                                data-lucide="pencil"
                                                stroke-width="1.8"
                                                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/edit:scale-110">
                                            </i>

                                        </a>



                                        {{-- Reenviar código --}}

                                        @if(
                                            ! $usuario->correoEstaVerificado()
                                            && $usuario->activo
                                        )

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'usuarios.resend-verification',
                                                    $usuario
                                                ) }}"
                                                class="contents"
                                                onsubmit="return confirm('¿Deseas enviar un nuevo código de verificación a este usuario?')">

                                                @csrf

                                                <button
                                                    type="submit"
                                                    title="Reenviar código"
                                                    class="group/mail inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border border-border bg-card text-muted-foreground shadow-sm transition-all duration-200 hover:text-amber-600 hover:border-amber-500/30 hover:bg-amber-500/5 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.95] dark:border-slate-700">

                                                    <i
                                                        data-lucide="mail-plus"
                                                        stroke-width="1.8"
                                                        class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/mail:scale-110">
                                                    </i>

                                                </button>

                                            </form>

                                        @endif



                                        {{-- Activar o desactivar --}}

                                        @if(auth()->id() !== $usuario->id)

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'usuarios.change-status',
                                                    $usuario
                                                ) }}"
                                                class="contents"
                                                onsubmit="return confirm('{{ $usuario->activo
                                                    ? '¿Deseas desactivar el acceso de este usuario?'
                                                    : '¿Deseas activar el acceso de este usuario?'
                                                }}')">

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    title="{{ $usuario->activo
                                                        ? 'Desactivar usuario'
                                                        : 'Activar usuario'
                                                    }}"
                                                    @class([
                                                        'group/status inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border bg-card shadow-sm transition-all duration-200 hover:shadow motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.95] dark:border-slate-700',

                                                        'border-border text-muted-foreground hover:text-red-600 hover:border-red-500/30 hover:bg-red-500/5' =>
                                                            $usuario->activo,

                                                        'border-border text-muted-foreground hover:text-emerald-600 hover:border-emerald-500/30 hover:bg-emerald-500/5' =>
                                                            ! $usuario->activo,
                                                    ])>

                                                    <i
                                                        data-lucide="{{ $usuario->activo
                                                            ? 'user-x'
                                                            : 'user-check'
                                                        }}"
                                                        stroke-width="1.8"
                                                        class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover/status:scale-110">
                                                    </i>

                                                </button>

                                            </form>

                                        @else

                                            <button
                                                type="button"
                                                disabled
                                                title="No puedes desactivar tu propia cuenta"
                                                class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border border-border bg-muted/30 text-muted-foreground/40 cursor-not-allowed">

                                                <i
                                                    data-lucide="user-x"
                                                    stroke-width="1.8"
                                                    class="w-4 h-4 shrink-0">
                                                </i>

                                            </button>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-16 text-center">

                                    <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-muted text-muted-foreground">

                                        <i
                                            data-lucide="users-round"
                                            stroke-width="1.8"
                                            class="w-5 h-5">
                                        </i>

                                    </div>

                                    <h3 class="text-sm font-semibold text-foreground mt-4">

                                        No se encontraron usuarios

                                    </h3>

                                    <p class="text-sm text-muted-foreground mt-1">

                                        Intenta modificar los filtros o registra un nuevo usuario.

                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>



            {{-- Paginación personalizada --}}

            @if($usuarios->hasPages())

                @php

                    $paginaActual = $usuarios->currentPage();
                    $ultimaPagina = $usuarios->lastPage();

                    $paginaInicial = max(
                        1,
                        $paginaActual - 2
                    );

                    $paginaFinal = min(
                        $ultimaPagina,
                        $paginaActual + 2
                    );

                    if ($paginaActual <= 3) {
                        $paginaFinal = min(
                            5,
                            $ultimaPagina
                        );
                    }

                    if (
                        $paginaActual
                        >=
                        $ultimaPagina - 2
                    ) {
                        $paginaInicial = max(
                            1,
                            $ultimaPagina - 4
                        );
                    }

                @endphp


                <div class="flex flex-col gap-4 px-5 py-4 border-t border-border bg-blue-50/20 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-blue-950/10">


                    {{-- Información --}}

                    <p class="text-xs text-muted-foreground">

                        Mostrando

                        <span class="font-semibold text-foreground">

                            {{ $usuarios->firstItem() }}

                        </span>

                        a

                        <span class="font-semibold text-foreground">

                            {{ $usuarios->lastItem() }}

                        </span>

                        de

                        <span class="font-semibold text-foreground">

                            {{ $usuarios->total() }}

                        </span>

                        usuarios

                    </p>



                    {{-- Controles --}}

                    <nav
                        aria-label="Paginación de usuarios"
                        class="flex flex-wrap items-center gap-1">


                        {{-- Anterior --}}

                        @if($usuarios->onFirstPage())

                            <span
                                aria-disabled="true"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed">

                                <i
                                    data-lucide="chevron-left"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </span>

                        @else

                            <a
                                href="{{ $usuarios->previousPageUrl() }}"
                                rel="prev"
                                aria-label="Página anterior"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:translate-y-0 dark:border-slate-700">

                                <i
                                    data-lucide="chevron-left"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </a>

                        @endif



                        {{-- Primera página --}}

                        @if($paginaInicial > 1)

                            <a
                                href="{{ $usuarios->url(1) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 dark:border-slate-700">

                                1

                            </a>


                            @if($paginaInicial > 2)

                                <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">

                                    …

                                </span>

                            @endif

                        @endif



                        {{-- Páginas --}}

                        @for(
                            $pagina = $paginaInicial;
                            $pagina <= $paginaFinal;
                            $pagina++
                        )

                            @if($pagina === $paginaActual)

                                <span
                                    aria-current="page"
                                    class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-primary bg-primary text-xs font-semibold text-white shadow-sm">

                                    {{ $pagina }}

                                </span>

                            @else

                                <a
                                    href="{{ $usuarios->url($pagina) }}"
                                    class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:translate-y-0 dark:border-slate-700">

                                    {{ $pagina }}

                                </a>

                            @endif

                        @endfor



                        {{-- Última página --}}

                        @if($paginaFinal < $ultimaPagina)

                            @if(
                                $paginaFinal
                                <
                                $ultimaPagina - 1
                            )

                                <span class="inline-flex items-center justify-center w-7 h-9 text-xs text-muted-foreground">

                                    …

                                </span>

                            @endif


                            <a
                                href="{{ $usuarios->url($ultimaPagina) }}"
                                class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg border border-border bg-card text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 dark:border-slate-700">

                                {{ $ultimaPagina }}

                            </a>

                        @endif



                        {{-- Siguiente --}}

                        @if($usuarios->hasMorePages())

                            <a
                                href="{{ $usuarios->nextPageUrl() }}"
                                rel="next"
                                aria-label="Página siguiente"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-card text-muted-foreground transition-all duration-200 hover:border-primary/30 hover:bg-primary/5 hover:text-primary hover:shadow-sm motion-safe:hover:-translate-y-0.5 active:translate-y-0 dark:border-slate-700">

                                <i
                                    data-lucide="chevron-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </a>

                        @else

                            <span
                                aria-disabled="true"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-slate-600">

                                <i
                                    data-lucide="chevron-right"
                                    stroke-width="1.8"
                                    class="w-4 h-4">
                                </i>

                            </span>

                        @endif

                    </nav>

                </div>

            @endif

        </section>

    </main>

</div>

@endsection
