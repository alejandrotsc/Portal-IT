@php
    $enDashboard = request()->routeIs('dashboard');

    $tituloHeader = match(true) {
        request()->routeIs('memorandos.*') => 'Pases TI',
        request()->routeIs('solicitudes.*') => 'Solicitudes TI',
        request()->routeIs('incidencias.*') => 'Incidencias TI',
        request()->routeIs('usuarios.*') => 'Usuarios TI',
        request()->routeIs('avisos.*') => 'Avisos TI',
        request()->routeIs(
        'admin.pases',
        'admin.pases.*'
        ) => 'Pases TI',
        request()->routeIs(
        'admin.solicitudes',
        'admin.solicitudes.*'
        ) => 'Solicitudes TI',
        request()->routeIs(
        'admin.incidencias',
        'admin.incidencias.*'
        ) => 'Incidencias TI',
        default => 'Portal TI'
    };

    $usuarioAutenticado =
        auth()->user();

    $notificacionesNoLeidas =
        $usuarioAutenticado
            ?->unreadNotifications()
            ->count()
        ?? 0;

    $ultimasNotificaciones =
        $usuarioAutenticado
            ?->notifications()
            ->latest('created_at')
            ->take(5)
            ->get()
        ?? collect();

@endphp


<header class="bg-card border-b border-border sticky top-0 z-40">


    <div class="max-w-[1300px] mx-auto px-6 h-16 flex items-center justify-between">



        {{-- IZQUIERDA --}}

        <div class="flex items-center gap-4">


            {{-- Botón regresar solamente fuera del dashboard --}}

            @if(!$enDashboard)

                <a
    href="{{ route('dashboard') }}"
    class="group flex items-center gap-1.5 text-sm text-primary transition-colors duration-200 hover:text-primary/80"
>

                    <i 
                        data-lucide="arrow-left"
                        class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-0.5">
                    </i>


                    <span class="hidden sm:inline">
                        Volver al Dashboard
                    </span>

                </a>


                <div class="w-px h-5 bg-border"></div>

            @endif





            {{-- Logo --}}

            <div class="flex items-center gap-3">


                <div class="group/logo w-14 h-14 rounded-lg flex items-center justify-center overflow-hidden">


                    <img
                        src="{{ asset('img/tvc.png') }}"
                        alt="Televicentro"
                        class="w-full h-full object-contain p-1 transition-transform duration-300 group-hover/logo:scale-105"
                    >


                </div>




                <div>


                    @if($enDashboard)


                        <span class="text-sm font-semibold text-foreground tracking-tight">

                            Portal TI

                        </span>


                        <span class="hidden sm:inline text-xs text-muted-foreground ml-2">

                            / Gestión de Servicios

                        </span>



                    @else


                        <span class="text-sm font-semibold text-foreground tracking-tight">

                            {{ $tituloHeader }}

                        </span>



                    @endif


                </div>


            </div>


        </div>





        {{-- DERECHA --}}

        <div class="flex items-center gap-2">



            {{-- Notificaciones --}}

<div
    id="notificaciones-widget"
    class="relative"
    x-data="{ notificationOpen: false }"
    data-usuario-id="{{ auth()->id() }}"
    data-no-leidas="{{ $notificacionesNoLeidas }}"
    data-url-abrir="{{ route(
        'notificaciones.abrir',
        ['notification' => '__NOTIFICATION_ID__']
    ) }}"
>

    <button
        id="notificaciones-boton"
        type="button"
        @click="notificationOpen = !notificationOpen"
        class="group/bell relative p-2 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
        aria-label="Abrir notificaciones"
    >

        <i
            data-lucide="bell"
            class="w-[18px] h-[18px] transition-transform duration-300 group-hover/bell:rotate-12"
        ></i>


        {{--
        |--------------------------------------------------------------------------
        | Contador
        |--------------------------------------------------------------------------
        |
        | Se mantiene siempre en el HTML para que JavaScript pueda mostrarlo
        | cuando llegue la primera notificación en tiempo real.
        |
        --}}

        <span
            id="notificaciones-contador"
            @class([
                'absolute -top-1 -right-1 min-w-4 h-4 px-1 rounded-full bg-primary text-[9px] font-bold leading-none text-white flex items-center justify-center',
                'hidden' => $notificacionesNoLeidas === 0,
            ])
        >
            {{ $notificacionesNoLeidas > 9
                ? '9+'
                : $notificacionesNoLeidas
            }}
        </span>

    </button>


    {{-- Dropdown de notificaciones --}}

    <div
        x-show="notificationOpen"
        x-cloak
        @click.outside="notificationOpen = false"
        class="absolute right-0 top-full mt-2 w-[360px] max-w-[calc(100vw-2rem)] bg-card border border-border rounded-xl shadow-lg shadow-black/5 overflow-hidden z-50"
    >

        <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-border">

            <div>

                <h3 class="text-sm font-semibold text-foreground">
                    Notificaciones
                </h3>

                <p
                    id="notificaciones-resumen"
                    class="mt-0.5 text-xs text-muted-foreground"
                >
                    {{ $notificacionesNoLeidas === 1
                        ? '1 notificación sin leer'
                        : $notificacionesNoLeidas.' notificaciones sin leer'
                    }}
                </p>

            </div>


            {{--
            |--------------------------------------------------------------------------
            | Marcar todas
            |--------------------------------------------------------------------------
            |
            | El formulario siempre existe. Cuando no hay notificaciones sin leer
            | permanece oculto y JavaScript puede mostrarlo cuando llegue una.
            |
            --}}

            <form
                id="notificaciones-marcar-todas"
                method="POST"
                action="{{ route('notificaciones.marcar-todas') }}"
                @class([
                    'hidden' => $notificacionesNoLeidas === 0,
                ])
            >

                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="text-xs font-medium text-primary hover:text-primary/80 transition-colors"
                >
                    Marcar todas
                </button>

            </form>

        </div>


        <div
            id="notificaciones-lista"
            class="max-h-[360px] overflow-y-auto"
        >

            @foreach($ultimasNotificaciones as $notificacion)

                @php

                    $datosNotificacion =
                        $notificacion->data;

                    $estaSinLeer =
                        $notificacion->unread();

                @endphp


                <a
                    href="{{ route(
                        'notificaciones.abrir',
                        $notificacion->id
                    ) }}"
                    data-notificacion-item
                    @class([
                        'flex items-start gap-3 px-4 py-3 border-b border-border last:border-b-0 transition-colors duration-200 hover:bg-muted/60',
                        'bg-primary/[0.035]' =>
                            $estaSinLeer,
                    ])
                >

                    <div
                        @class([
                            'mt-0.5 flex items-center justify-center w-9 h-9 shrink-0 rounded-lg',
                            'bg-primary/10 text-primary' =>
                                $estaSinLeer,
                            'bg-muted text-muted-foreground' =>
                                ! $estaSinLeer,
                        ])
                    >

                        <i
                            data-lucide="{{ $datosNotificacion['icono'] ?? 'bell' }}"
                            stroke-width="1.8"
                            class="w-[17px] h-[17px]"
                        ></i>

                    </div>


                    <div class="min-w-0 flex-1">

                        <div class="flex items-start gap-2">

                            <p class="min-w-0 flex-1 text-sm font-medium text-foreground leading-snug">

                                {{ $datosNotificacion['titulo']
                                    ?? 'Nueva notificación'
                                }}

                            </p>


                            @if($estaSinLeer)

                                <span
                                    class="mt-1.5 w-2 h-2 shrink-0 rounded-full bg-primary"
                                ></span>

                            @endif

                        </div>


                        <p class="mt-1 text-xs leading-relaxed text-muted-foreground">

                            {{ $datosNotificacion['mensaje']
                                ?? 'Tienes una nueva actualización.'
                            }}

                        </p>


                        <p class="mt-1.5 text-[11px] text-muted-foreground">

                            {{ $notificacion->created_at
                                ?->timezone('America/Tegucigalpa')
                                ->diffForHumans()
                            }}

                        </p>

                    </div>

                </a>

            @endforeach


            {{--
            |--------------------------------------------------------------------------
            | Estado vacío
            |--------------------------------------------------------------------------
            --}}

            <div
                id="notificaciones-vacio"
                @class([
                    'px-6 py-10 text-center',
                    'hidden' => $ultimasNotificaciones->isNotEmpty(),
                ])
            >

                <div class="flex items-center justify-center w-11 h-11 mx-auto rounded-xl bg-primary/5 text-primary">

                    <i
                        data-lucide="bell-off"
                        stroke-width="1.8"
                        class="w-5 h-5"
                    ></i>

                </div>

                <p class="mt-3 text-sm font-medium text-foreground">
                    No tienes notificaciones
                </p>

                <p class="mt-1 text-xs text-muted-foreground">
                    Aquí aparecerán las actualizaciones de tus gestiones.
                </p>

            </div>

        </div>


        <div class="px-4 py-3 border-t border-border bg-muted/20">

            <a
                href="{{ route('notificaciones.index') }}"
                class="flex items-center justify-center gap-2 text-xs font-semibold text-primary hover:text-primary/80 transition-colors"
            >

                Ver todas las notificaciones

                <i
                    data-lucide="arrow-right"
                    stroke-width="1.8"
                    class="w-3.5 h-3.5"
                ></i>

            </a>

        </div>

    </div>

</div>

            {{-- Usuario --}}

            <div class="relative">


                <button
                    @click="profileOpen = !profileOpen"
                    class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-lg hover:bg-muted transition-colors"
                >


                    <div
                        class="w-7 h-7 rounded-full bg-primary flex items-center justify-center text-white text-xs font-semibold"
                    >

                        {{ strtoupper(substr(auth()->user()->nombre,0,2)) }}

                    </div>



                    <span class="hidden sm:block text-sm font-medium text-foreground">

                        {{ auth()->user()->nombre }}

                    </span>



                    <i
                        data-lucide="chevron-down"
                        class="w-3.5 h-3.5 text-muted-foreground transition-transform"
                        :class="profileOpen ? 'rotate-180' : ''"
                    >
                    </i>



                </button>






                {{-- Dropdown --}}

                <div
                    x-show="profileOpen"
                    x-cloak
                    @click.outside="profileOpen = false"
                    class="absolute right-0 top-full mt-1 w-52 bg-card border border-border rounded-xl shadow-lg shadow-black/5 overflow-hidden z-50"
                >



                    <div class="px-4 py-3 border-b border-border">


                        <p class="text-sm font-medium text-foreground">

                            {{ auth()->user()->nombre }}

                        </p>


                        <p class="text-xs text-muted-foreground mt-0.5">

                            {{ auth()->user()->correo }}

                        </p>


                    </div>





                    <div class="py-1">


                        <button
    type="button"
    id="theme-toggle"
    class="flex w-full items-center gap-2.5 px-4 py-2
           text-sm text-foreground transition-colors
           hover:bg-muted"
    aria-label="Activar modo oscuro"
    aria-pressed="false"
>
    {{-- Sol --}}
    <span
        id="theme-sun-icon"
        class="inline-flex h-4 w-4 shrink-0
               items-center justify-center"
    >
        <i
            data-lucide="sun"
            class="h-3.5 w-3.5 text-amber-500"
        ></i>
    </span>

    {{-- Luna --}}
    <span
        id="theme-moon-icon"
        class="hidden h-4 w-4 shrink-0
               items-center justify-center"
    >
        <i
            data-lucide="moon"
            class="h-3.5 w-3.5 text-blue-500"
        ></i>
    </span>

    <span class="flex-1 text-left">
        Modo oscuro
    </span>

    {{-- Switch --}}
    <span
        id="theme-switch-track"
        class="relative block h-5 w-10 shrink-0
               overflow-hidden rounded-full bg-slate-300
               transition-colors duration-200"
        aria-hidden="true"
    >
        <span
            id="theme-switch-thumb"
            class="absolute left-0.5 top-0.5 block
                   h-4 w-4 rounded-full bg-white
                   shadow-sm transition-transform duration-200"
        ></span>
    </span>
</button>






                        <div class="border-t border-border mt-1 pt-1">



                            <form method="POST" action="{{ route('logout') }}">

                                @csrf


                                <button
                                    type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                >


                                    <i 
                                        data-lucide="log-out"
                                        class="w-3.5 h-3.5">
                                    </i>


                                    Cerrar sesión


                                </button>


                            </form>



                        </div>




                    </div>



                </div>



            </div>




        </div>



    </div>


</header>

{{-- Cinta de avisos TI --}}

@php
    $avisosDisponibles =
        $avisosTicker
        ?? collect();
@endphp


@if($avisosDisponibles->isNotEmpty())

    <div
        class="overflow-hidden border-b border-primary/10 bg-primary/5
               dark:border-slate-700/70 dark:bg-slate-900/80"
    >
        <div class="flex items-center">

            {{-- Etiqueta fija --}}
            <div
                class="relative z-10 flex shrink-0 items-center gap-2
                       bg-primary px-4 py-2 shadow-sm
                       dark:bg-blue-600"
            >
                <span class="relative flex h-2 w-2 shrink-0">
                    <span
                        class="absolute inline-flex h-full w-full
                               rounded-full bg-white/70
                               motion-safe:animate-ping"
                    ></span>

                    <span
                        class="relative inline-flex h-2 w-2
                               rounded-full bg-white"
                    ></span>
                </span>

                <span
                    class="whitespace-nowrap text-[11px] font-bold
                           uppercase tracking-widest text-white"
                >
                    Avisos TI
                </span>
            </div>


            {{-- Banda animada --}}
            <div class="min-w-0 flex-1 overflow-hidden">

                <div
                    class="ticker flex w-max items-center"
                    onmouseenter="this.style.animationPlayState='paused'"
                    onmouseleave="this.style.animationPlayState='running'"
                >
                    @for(
                        $repeticion = 0;
                        $repeticion < 2;
                        $repeticion++
                    )

                        <div
                            class="flex shrink-0 items-center"
                            @if($repeticion === 1)
                                aria-hidden="true"
                            @endif
                        >
                            @foreach($avisosDisponibles as $aviso)

                                @php
                                    $colorAviso =
                                        $loop->index % 4;
                                @endphp

                                {{-- Aviso --}}
                                <div
                                    @class([
                                        'flex items-center gap-2 rounded-md px-8 py-1 text-[12px] whitespace-nowrap transition-colors duration-200',

                                        'hover:bg-blue-500/[0.06] dark:hover:bg-blue-500/10' =>
                                            $colorAviso === 0,

                                        'hover:bg-amber-500/[0.06] dark:hover:bg-amber-500/10' =>
                                            $colorAviso === 1,

                                        'hover:bg-emerald-500/[0.06] dark:hover:bg-emerald-500/10' =>
                                            $colorAviso === 2,

                                        'hover:bg-violet-500/[0.06] dark:hover:bg-violet-500/10' =>
                                            $colorAviso === 3,
                                    ])
                                >
                                    {{-- Indicador --}}
                                    <span
                                        @class([
                                            'h-2 w-2 shrink-0 rounded-full',

                                            'bg-blue-500 dark:bg-blue-400' =>
                                                $colorAviso === 0,

                                            'bg-amber-500 dark:bg-amber-400' =>
                                                $colorAviso === 1,

                                            'bg-emerald-500 dark:bg-emerald-400' =>
                                                $colorAviso === 2,

                                            'bg-violet-500 dark:bg-violet-400' =>
                                                $colorAviso === 3,
                                        ])
                                    ></span>

                                    {{-- Título --}}
                                    <strong
                                        @class([
                                            'font-semibold',

                                            'text-blue-700 dark:text-blue-300' =>
                                                $colorAviso === 0,

                                            'text-amber-700 dark:text-amber-300' =>
                                                $colorAviso === 1,

                                            'text-emerald-700 dark:text-emerald-300' =>
                                                $colorAviso === 2,

                                            'text-violet-700 dark:text-violet-300' =>
                                                $colorAviso === 3,
                                        ])
                                    >
                                        {{ $aviso->titulo }}:
                                    </strong>

                                    {{-- Mensaje --}}
                                    <span
                                        class="text-muted-foreground
                                               dark:text-slate-300"
                                    >
                                        {{ $aviso->mensaje }}
                                    </span>
                                </div>

                                {{-- Separador --}}
                                <div
                                    class="h-4 w-px shrink-0 bg-border
                                           dark:bg-slate-700"
                                ></div>

                            @endforeach
                        </div>

                    @endfor
                </div>

            </div>

        </div>
    </div>

    <script src="{{ asset('js/switch.js') }}?v=2"></script>

@endif