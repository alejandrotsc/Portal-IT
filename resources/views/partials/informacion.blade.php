{{-- Información importante + Soporte --}}

@php

    $avisosImportantes = (
        $avisosTicker
        ?? collect()
    )->take(2);

@endphp


<section class="grid grid-cols-1 lg:grid-cols-3 gap-6">


    {{-- Información importante --}}

    <div class="lg:col-span-2">

        <div class="flex items-center justify-between gap-4 mb-4">

            <h2 class="text-sm font-semibold text-foreground uppercase tracking-widest">

                Información importante

            </h2>


            <a
                href="{{ route('avisos.publicos') }}"
                class="group/all inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-primary transition-all duration-200 hover:bg-primary/5 hover:text-primary/80 motion-safe:hover:-translate-y-0.5">

                <span>
                    Ver todos los avisos
                </span>

                <i
                    data-lucide="chevron-right"
                    stroke-width="1.8"
                    class="w-3 h-3 shrink-0 transition-transform duration-200 group-hover/all:translate-x-0.5">
                </i>

            </a>

        </div>



        {{-- Avisos --}}

        <div class="space-y-3">

            @forelse($avisosImportantes as $aviso)

                @php

                    $colorAviso =
                        $loop->index % 4;

                @endphp


                <article
                    @class([
                        'group/notice relative overflow-hidden rounded-xl border border-l-4 bg-card px-5 py-4 shadow-sm transition-all duration-300 hover:shadow-lg motion-safe:hover:-translate-y-1',

                        'border-blue-200/70 border-l-blue-500 bg-gradient-to-r from-blue-50/50 via-white to-indigo-50/30 hover:border-blue-300 hover:shadow-blue-500/10 dark:border-blue-800/70 dark:border-l-blue-500 dark:from-blue-950/30 dark:via-slate-900 dark:to-indigo-950/20 dark:hover:border-blue-700 dark:hover:shadow-black/20' =>
                            $colorAviso === 0,

                        'border-amber-200/70 border-l-amber-500 bg-gradient-to-r from-amber-50/50 via-white to-orange-50/30 hover:border-amber-300 hover:shadow-amber-500/10 dark:border-amber-800/70 dark:border-l-amber-500 dark:from-amber-950/30 dark:via-slate-900 dark:to-orange-950/20 dark:hover:border-amber-700 dark:hover:shadow-black/20' =>
                            $colorAviso === 1,

                        'border-emerald-200/70 border-l-emerald-500 bg-gradient-to-r from-emerald-50/50 via-white to-teal-50/30 hover:border-emerald-300 hover:shadow-emerald-500/10 dark:border-emerald-800/70 dark:border-l-emerald-500 dark:from-emerald-950/30 dark:via-slate-900 dark:to-teal-950/20 dark:hover:border-emerald-700 dark:hover:shadow-black/20' =>
                            $colorAviso === 2,

                        'border-violet-200/70 border-l-violet-500 bg-gradient-to-r from-violet-50/50 via-white to-purple-50/30 hover:border-violet-300 hover:shadow-violet-500/10 dark:border-violet-800/70 dark:border-l-violet-500 dark:from-violet-950/30 dark:via-slate-900 dark:to-purple-950/20 dark:hover:border-violet-700 dark:hover:shadow-black/20' =>
                            $colorAviso === 3,
                    ])>


                    {{-- Decoración suave --}}

                    <span
                        @class([
                            'absolute -right-10 -top-12 w-28 h-28 rounded-full blur-3xl pointer-events-none transition-all duration-500 motion-safe:group-hover/notice:scale-150',

                            'bg-blue-500/10 group-hover/notice:bg-blue-500/20' =>
                                $colorAviso === 0,

                            'bg-amber-500/10 group-hover/notice:bg-amber-500/20' =>
                                $colorAviso === 1,

                            'bg-emerald-500/10 group-hover/notice:bg-emerald-500/20' =>
                                $colorAviso === 2,

                            'bg-violet-500/10 group-hover/notice:bg-violet-500/20' =>
                                $colorAviso === 3,
                        ])>
                    </span>


                    <div class="relative">


                        {{-- Título --}}

                        <div class="flex items-start gap-2 mb-1">

                            <span
                                @class([
                                    'w-2 h-2 shrink-0 rounded-full mt-1.5 transition-transform duration-300 motion-safe:group-hover/notice:scale-125',

                                    'bg-blue-500' =>
                                        $colorAviso === 0,

                                    'bg-amber-500' =>
                                        $colorAviso === 1,

                                    'bg-emerald-500' =>
                                        $colorAviso === 2,

                                    'bg-violet-500' =>
                                        $colorAviso === 3,
                                ])>
                            </span>

                            <h3
                                @class([
                                    'text-sm font-semibold text-foreground leading-relaxed transition-colors duration-200',

                                    'group-hover/notice:text-blue-800 dark:group-hover/notice:text-blue-300' =>
                                        $colorAviso === 0,

                                    'group-hover/notice:text-amber-800 dark:group-hover/notice:text-amber-300' =>
                                        $colorAviso === 1,

                                    'group-hover/notice:text-emerald-800 dark:group-hover/notice:text-emerald-300' =>
                                        $colorAviso === 2,

                                    'group-hover/notice:text-violet-800 dark:group-hover/notice:text-violet-300' =>
                                        $colorAviso === 3,
                                ])>

                                {{ $aviso->titulo }}

                            </h3>

                        </div>



                        {{-- Mensaje --}}

                        <p class="text-xs text-muted-foreground leading-relaxed mb-3 pl-4">

                            {{ $aviso->mensaje }}

                        </p>



                        {{-- Información inferior --}}

                        <div class="flex flex-wrap items-center gap-3 pl-4">

                            <span class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">

                                <i
                                    data-lucide="clock-3"
                                    stroke-width="1.8"
                                    class="w-3.5 h-3.5 shrink-0">
                                </i>

                                {{ $aviso->created_at
                                    ?->locale('es')
                                    ->diffForHumans()
                                    ?? 'Recientemente' }}

                            </span>


                            <span
                                @class([
                                    'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium transition-colors duration-200',

                                    'bg-blue-500/10 text-blue-700 group-hover/notice:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-300 dark:group-hover/notice:bg-blue-950/70' =>
                                        $colorAviso === 0,

                                    'bg-amber-500/10 text-amber-700 group-hover/notice:bg-amber-100 dark:bg-amber-500/15 dark:text-amber-300 dark:group-hover/notice:bg-amber-950/70' =>
                                        $colorAviso === 1,

                                    'bg-emerald-500/10 text-emerald-700 group-hover/notice:bg-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-300 dark:group-hover/notice:bg-emerald-950/70' =>
                                        $colorAviso === 2,

                                    'bg-violet-500/10 text-violet-700 group-hover/notice:bg-violet-100 dark:bg-violet-500/15 dark:text-violet-300 dark:group-hover/notice:bg-violet-950/70' =>
                                        $colorAviso === 3,
                                ])>

                                <i
                                    data-lucide="megaphone"
                                    stroke-width="1.8"
                                    class="w-3 h-3 shrink-0 transition-transform duration-300 motion-safe:group-hover/notice:scale-110">
                                </i>

                                Aviso TI

                            </span>

                        </div>

                    </div>

                </article>

            @empty

                {{-- Sin avisos --}}

                <div class="group/empty relative overflow-hidden rounded-xl border border-dashed border-primary/20 bg-gradient-to-br from-primary/[0.035] via-white to-blue-50/40 dark:from-blue-950/25 dark:via-slate-900 dark:to-slate-900 px-6 py-10 text-center shadow-sm transition-all duration-300 hover:border-primary/30 hover:shadow-md dark:hover:border-primary/40 dark:hover:shadow-black/20">

                    <span class="absolute -right-10 -top-12 w-28 h-28 rounded-full bg-primary/5 blur-3xl pointer-events-none transition-all duration-500 motion-safe:group-hover/empty:scale-150 group-hover/empty:bg-primary/10"></span>


                    <div class="relative">

                        <div class="flex items-center justify-center w-11 h-11 mx-auto rounded-full bg-primary/10 text-primary transition-all duration-300 group-hover/empty:bg-primary/15 motion-safe:group-hover/empty:scale-105">

                            <i
                                data-lucide="info"
                                stroke-width="1.8"
                                class="w-5 h-5 shrink-0 transition-transform duration-300 motion-safe:group-hover/empty:scale-110">
                            </i>

                        </div>

                        <h3 class="text-sm font-semibold text-foreground mt-3">

                            No hay avisos importantes

                        </h3>

                        <p class="text-xs text-muted-foreground mt-1">

                            Actualmente no hay comunicaciones activas por parte del equipo de TI.

                        </p>

                    </div>

                </div>

            @endforelse

        </div>

    </div>


{{-- Soporte de turno --}}

<div>

    <div class="mb-4 flex items-center gap-2">

        <span class="h-2 w-2 rounded-full bg-blue-500"></span>

        <h2
            class="text-sm font-semibold uppercase tracking-widest
                   text-foreground"
        >
            Soporte de turno
        </h2>

    </div>


    <div
        class="group/support-card relative overflow-hidden rounded-2xl
               border border-border bg-card shadow-sm
               transition-all duration-300
               hover:-translate-y-0.5 hover:border-primary/30
               hover:shadow-lg hover:shadow-primary/10
               dark:border-slate-700/70 dark:bg-slate-900/70
               dark:hover:border-blue-700/60
               dark:hover:shadow-black/20"
    >
        {{-- Brillo decorativo --}}

        <span
            class="pointer-events-none absolute -right-12 -top-14
                   h-32 w-32 rounded-full bg-blue-500/10 blur-3xl
                   transition-transform duration-700
                   motion-safe:group-hover/support-card:scale-150"
        ></span>


        @php

            /*
            |--------------------------------------------------------------------------
            | Iniciales del agente
            |--------------------------------------------------------------------------
            */

            $obtenerIniciales = function (?string $nombre): string {
                if (! $nombre) {
                    return 'TI';
                }

                return collect(
                    preg_split(
                        '/\s+/',
                        trim($nombre)
                    )
                )
                    ->filter()
                    ->take(2)
                    ->map(
                        fn ($parte) =>
                            mb_strtoupper(
                                mb_substr(
                                    $parte,
                                    0,
                                    1
                                )
                            )
                    )
                    ->implode('');
            };


            /*
            |--------------------------------------------------------------------------
            | Destinatario dinámico
            |--------------------------------------------------------------------------
            |
            | Si hoy es fin de semana y existe una guardia, el destinatario
            | será el correo individual del UsuarioTI asignado.
            |
            | Entre semana o sin asignación se utilizará Helpdesk.
            |
            */

            $correoGeneralSoporte =
                'helpdesk@televicentro.hn';

            $agenteContacto =
                $esFinDeSemana
                    ? $guardiaHoy?->agente
                    : null;

            $correoSoporte =
                $agenteContacto?->correo
                ?? $correoGeneralSoporte;

            $nombreContacto =
                $agenteContacto?->nombre
                ?? 'equipo de soporte TI';

            $asuntoSoporte =
                $agenteContacto
                    ? 'Consulta al agente de guardia - Portal TI'
                    : 'Consulta desde el Portal TI';

            $cuerpoSoporte = implode(
                "\n",
                [
                    'Hola, '.$nombreContacto.':',
                    '',
                    'Deseo realizar la siguiente consulta:',
                    '',
                    'Usuario: '
                        .(auth()->user()->nombre ?? 'N/A'),

                    'Correo: '
                        .(auth()->user()->correo ?? 'N/A'),

                    '',
                    'Detalle de la consulta:',
                    '',
                ]
            );

            $outlookUrl =
                'https://outlook.office.com/mail/deeplink/compose'
                .'?to='.rawurlencode($correoSoporte)
                .'&subject='.rawurlencode($asuntoSoporte)
                .'&body='.rawurlencode($cuerpoSoporte);

        @endphp


        {{-- Disponibilidad actual --}}

        <div
            class="group/agent relative border-b border-border px-5 py-4
                   transition-colors duration-300
                   hover:bg-blue-50/50
                   dark:border-slate-700/70
                   dark:hover:bg-blue-950/20"
        >
            <div class="mb-3 flex items-center gap-2">

                <i
                    data-lucide="{{
                        $esFinDeSemana
                            ? 'headphones'
                            : 'users'
                    }}"
                    stroke-width="1.8"
                    class="h-[13px] w-[13px] text-muted-foreground
                           transition-colors duration-200
                           group-hover/agent:text-blue-600
                           dark:group-hover/agent:text-blue-400"
                ></i>

                <span
                    class="text-xs font-medium uppercase tracking-wide
                           text-muted-foreground"
                >
                    {{
                        $esFinDeSemana
                            ? 'Soporte de turno hoy'
                            : 'Soporte disponible'
                    }}
                </span>

            </div>


            @if(! $esFinDeSemana)

                {{-- Equipo disponible entre semana --}}

<div class="flex items-center gap-3">

    <div
        class="flex h-11 w-11 shrink-0 items-center
               justify-center rounded-full bg-blue-100
               text-blue-700 ring-4 ring-blue-500/5
               transition-all duration-300
               motion-safe:group-hover/agent:scale-110
               group-hover/agent:bg-blue-200
               group-hover/agent:ring-blue-500/10
               dark:bg-blue-950/60 dark:text-blue-300
               dark:group-hover/agent:bg-blue-900/70"
    >
        <i
            data-lucide="headset"
            stroke-width="1.8"
            class="h-5 w-5"
        ></i>
    </div>


    <div class="min-w-0 flex-1">

        <div
            class="flex items-center justify-between gap-3"
        >
            <p
                class="min-w-0 truncate text-[15px]
                       font-semibold text-foreground"
            >
                Equipo de Soporte TI
            </p>

            <span
                class="inline-flex shrink-0 items-center gap-1.5
                       rounded-full bg-emerald-500/10
                       px-2 py-1 text-[11px] font-semibold
                       text-emerald-600
                       dark:text-emerald-400"
            >
                <span class="relative flex h-2 w-2 shrink-0">

                    <span
                        class="absolute inline-flex h-full w-full
                               rounded-full bg-emerald-400
                               opacity-70 motion-safe:animate-ping"
                    ></span>

                    <span
                        class="relative inline-flex h-2 w-2
                               rounded-full bg-emerald-500"
                    ></span>

                </span>

                Disponible
            </span>
        </div>


        <p
            class="mt-0.5 text-xs text-muted-foreground"
        >
            Atención general · Lunes a viernes
        </p>

    </div>

</div>


{{-- Horario general --}}

<div
    class="mt-3 flex items-center justify-end gap-1.5
           text-xs text-muted-foreground"
>
    <i
        data-lucide="clock"
        stroke-width="1.8"
        class="h-3.5 w-3.5 shrink-0"
    ></i>

    <span>
        09:00 – 18:00
    </span>

                </div>

            @elseif($guardiaHoy)

    {{-- Agente asignado para hoy --}}

    <div class="flex items-center gap-3">

        {{-- Iniciales --}}

        <div
            class="flex h-11 w-11 shrink-0 items-center
                   justify-center rounded-full bg-blue-100
                   text-sm font-semibold text-blue-700
                   ring-4 ring-blue-500/5
                   transition-all duration-300
                   motion-safe:group-hover/agent:scale-110
                   group-hover/agent:bg-blue-200
                   group-hover/agent:ring-blue-500/10
                   dark:bg-blue-950/60
                   dark:text-blue-300
                   dark:group-hover/agent:bg-blue-900/70"
        >
            {{
                $obtenerIniciales(
                    $guardiaHoy->agente->nombre
                )
            }}
        </div>


        <div class="min-w-0 flex-1">

            {{-- Nombre y disponibilidad --}}

            <div class="flex items-center justify-between gap-3">

                <p
                    class="min-w-0 truncate text-[15px]
                           font-semibold text-foreground"
                >
                    {{ $guardiaHoy->agente->nombre }}
                </p>


                @if($guardiaDisponibleAhora ?? false)

                    <span
                        class="inline-flex shrink-0 items-center gap-1.5
                               rounded-full bg-emerald-500/10
                               px-2 py-1 text-[11px] font-semibold
                               text-emerald-600
                               dark:text-emerald-400"
                    >
                        <span
                            class="relative flex h-2 w-2 shrink-0"
                        >
                            <span
                                class="absolute inline-flex h-full w-full
                                       rounded-full bg-emerald-400
                                       opacity-70
                                       motion-safe:animate-ping"
                            ></span>

                            <span
                                class="relative inline-flex h-2 w-2
                                       rounded-full bg-emerald-500"
                            ></span>
                        </span>

                        Disponible
                    </span>

                @else

                    <span
                        class="inline-flex shrink-0 items-center gap-1.5
                               rounded-full bg-amber-500/10
                               px-2 py-1 text-[11px] font-semibold
                               text-amber-600
                               dark:text-amber-400"
                    >
                        <i
                            data-lucide="clock"
                            stroke-width="1.8"
                            class="h-3 w-3 shrink-0"
                        ></i>

                        Fuera de horario
                    </span>

                @endif

            </div>


            {{-- Correo individual --}}

            <a
                href="mailto:{{ $guardiaHoy->agente->correo }}"
                class="mt-0.5 inline-flex max-w-full
                       items-center gap-1 text-xs font-medium
                       text-blue-600 transition-colors
                       hover:text-blue-700 hover:underline
                       dark:text-blue-400
                       dark:hover:text-blue-300"
            >
                <i
                    data-lucide="mail"
                    stroke-width="1.8"
                    class="h-3 w-3 shrink-0"
                ></i>

                <span class="truncate">
                    {{ $guardiaHoy->agente->correo }}
                </span>
            </a>


            {{-- Ubicación y horario --}}

            <div
                class="mt-1 flex flex-wrap items-center
                       gap-x-3 gap-y-1"
            >
                <span
                    class="inline-flex items-center gap-1
                           text-xs text-muted-foreground"
                >
                    <i
                        data-lucide="map-pin"
                        stroke-width="1.8"
                        class="h-3 w-3 shrink-0"
                    ></i>

                    {{ $guardiaHoy->ubicacion }}
                </span>

                <span
                    class="inline-flex items-center gap-1
                           text-xs text-muted-foreground"
                >
                    <i
                        data-lucide="clock"
                        stroke-width="1.8"
                        class="h-3 w-3 shrink-0"
                    ></i>

                    {{ $guardiaHoy->horario }}
                </span>
            </div>

        </div>

    </div>

@else

    {{-- Fin de semana sin asignación --}}

    <div class="flex items-center gap-3">

        <div
            class="flex h-11 w-11 shrink-0 items-center
                   justify-center rounded-full bg-slate-100
                   text-slate-500 ring-4 ring-slate-500/5
                   transition-all duration-300
                   motion-safe:group-hover/agent:scale-105
                   dark:bg-slate-800
                   dark:text-slate-400"
        >
            <i
                data-lucide="user-round-x"
                stroke-width="1.8"
                class="h-5 w-5"
            ></i>
        </div>


        <div class="min-w-0 flex-1">

            {{-- Estado sin asignación --}}

            <div class="flex items-center justify-between gap-3">

                <p
                    class="min-w-0 text-sm font-semibold
                           leading-snug text-foreground"
                >
                    Sin agente asignado
                </p>

                <span
                    class="inline-flex shrink-0 items-center gap-1.5
                           rounded-full bg-amber-500/10
                           px-2 py-1 text-[11px] font-semibold
                           text-amber-600
                           dark:text-amber-400"
                >
                    <i
                        data-lucide="circle-alert"
                        stroke-width="1.8"
                        class="h-3 w-3 shrink-0"
                    ></i>

                    Pendiente
                </span>

            </div>

            <p
                class="mt-1 text-xs leading-relaxed
                       text-muted-foreground"
            >
                Aún no se ha programado una guardia para hoy.
            </p>

        </div>

    </div>

@endif

        </div>


       {{-- Próximas guardias --}}

<div
    class="group/weekend relative px-5 py-4
           transition-colors duration-300
           hover:bg-violet-50/50
           dark:hover:bg-violet-950/20"
>
    <div class="mb-4 flex items-center gap-2">

        <i
            data-lucide="calendar-range"
            stroke-width="1.8"
            class="h-[13px] w-[13px]
                   text-muted-foreground
                   transition-colors duration-200
                   group-hover/weekend:text-violet-600
                   dark:group-hover/weekend:text-violet-400"
        ></i>

        <span
            class="text-xs font-medium uppercase tracking-wide
                   text-muted-foreground"
        >
            Próximos turnos
        </span>

    </div>


    <div class="space-y-4">

        @foreach($proximasFechasGuardia as $fechaProxima)

            @php

                $guardiaProxima =
                    $guardiasProximas->get(
                        $fechaProxima->format(
                            'Y-m-d'
                        )
                    );

            @endphp


            <div class="flex items-start gap-3">

                {{-- Día y número --}}

                <div
                    class="flex h-11 w-11 shrink-0 flex-col
                           items-center justify-center rounded-xl
                           bg-violet-100 text-violet-700
                           transition-all duration-300
                           motion-safe:group-hover/weekend:scale-105
                           dark:bg-violet-950/60
                           dark:text-violet-300"
                >
                    <span
                        class="text-[9px] font-semibold uppercase
                               leading-none tracking-wide"
                    >
                        {{
                            $fechaProxima
                                ->locale('es')
                                ->isoFormat('ddd')
                        }}
                    </span>

                    <span
                        class="mt-1 text-base font-semibold
                               leading-none"
                    >
                        {{ $fechaProxima->format('d') }}
                    </span>
                </div>


                <div class="min-w-0 flex-1">

                    {{-- Fecha completa --}}

                    <p
                        class="truncate text-sm font-semibold
                               capitalize text-foreground"
                    >
                        {{
                            $fechaProxima
                                ->locale('es')
                                ->isoFormat(
                                    'dddd D [de] MMMM'
                                )
                        }}
                    </p>


                    @if($guardiaProxima)

                        {{-- Agente asignado --}}

                        <p
                            class="mt-1 truncate text-sm
                                   font-semibold text-foreground"
                        >
                            {{ $guardiaProxima->agente->nombre }}
                        </p>


                        {{-- Correo individual --}}

                        <a
                            href="mailto:{{
                                $guardiaProxima->agente->correo
                            }}"
                            class="mt-0.5 inline-flex max-w-full
                                   items-center gap-1 text-xs
                                   font-medium text-blue-600
                                   transition-colors
                                   hover:text-blue-700
                                   hover:underline
                                   dark:text-blue-400
                                   dark:hover:text-blue-300"
                        >
                            <i
                                data-lucide="mail"
                                stroke-width="1.8"
                                class="h-3 w-3 shrink-0"
                            ></i>

                            <span class="truncate">
                                {{
                                    $guardiaProxima
                                        ->agente
                                        ->correo
                                }}
                            </span>
                        </a>


                        {{-- Horario y ubicación --}}

                        <div
                            class="mt-1.5 flex flex-wrap items-center
                                   gap-x-3 gap-y-1"
                        >
                            <span
                                class="inline-flex items-center gap-1
                                       text-xs text-muted-foreground"
                            >
                                <i
                                    data-lucide="clock"
                                    stroke-width="1.8"
                                    class="h-3 w-3"
                                ></i>

                                {{ $guardiaProxima->horario }}
                            </span>

                            <span
                                class="inline-flex items-center gap-1
                                       text-xs font-medium
                                       text-muted-foreground"
                            >
                                <i
                                    data-lucide="map-pin"
                                    stroke-width="1.8"
                                    class="h-3 w-3"
                                ></i>

                                {{ $guardiaProxima->ubicacion }}
                            </span>
                        </div>

                    @else

                        {{-- Sin asignación --}}

                        <div
                            class="mt-1.5 flex items-center gap-1.5
                                   text-amber-600
                                   dark:text-amber-400"
                        >
                            <i
                                data-lucide="user-round-x"
                                stroke-width="1.8"
                                class="h-3.5 w-3.5 shrink-0"
                            ></i>

                            <p class="text-xs font-medium">
                                Aún no se ha asignado un agente
                            </p>
                        </div>

                    @endif

                </div>

            </div>


            @unless($loop->last)

                <div
                    class="border-t border-border
                           dark:border-slate-700/70"
                ></div>

            @endunless

        @endforeach

    </div>

</div>


        {{-- Contactar soporte --}}

        <div
            class="border-t border-border bg-muted/40 px-5 py-3
                   dark:border-slate-700/70
                   dark:bg-slate-950/25"
        >
            <a
                href="{{ $outlookUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="group/contact flex w-full items-center
                       justify-center gap-1 rounded-lg py-1
                       text-xs font-medium text-primary
                       transition-all duration-200
                       hover:gap-1.5 hover:text-blue-700
                       dark:text-blue-400
                       dark:hover:text-blue-300"
            >
                {{
                    $agenteContacto
                        ? 'Contactar al agente de guardia'
                        : 'Contactar soporte'
                }}

                <i
                    data-lucide="external-link"
                    stroke-width="1.8"
                    class="h-3 w-3 transition-transform duration-200"
                ></i>
            </a>
        </div>

    </div>

</div>

</section>