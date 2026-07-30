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

                        'border-blue-200/70 border-l-blue-500 bg-gradient-to-r from-blue-50/50 via-white to-indigo-50/30 hover:border-blue-300 hover:shadow-blue-500/10' =>
                            $colorAviso === 0,

                        'border-amber-200/70 border-l-amber-500 bg-gradient-to-r from-amber-50/50 via-white to-orange-50/30 hover:border-amber-300 hover:shadow-amber-500/10' =>
                            $colorAviso === 1,

                        'border-emerald-200/70 border-l-emerald-500 bg-gradient-to-r from-emerald-50/50 via-white to-teal-50/30 hover:border-emerald-300 hover:shadow-emerald-500/10' =>
                            $colorAviso === 2,

                        'border-violet-200/70 border-l-violet-500 bg-gradient-to-r from-violet-50/50 via-white to-purple-50/30 hover:border-violet-300 hover:shadow-violet-500/10' =>
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

                                    'group-hover/notice:text-blue-800' =>
                                        $colorAviso === 0,

                                    'group-hover/notice:text-amber-800' =>
                                        $colorAviso === 1,

                                    'group-hover/notice:text-emerald-800' =>
                                        $colorAviso === 2,

                                    'group-hover/notice:text-violet-800' =>
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

                                    'bg-blue-500/10 text-blue-700 group-hover/notice:bg-blue-100' =>
                                        $colorAviso === 0,

                                    'bg-amber-500/10 text-amber-700 group-hover/notice:bg-amber-100' =>
                                        $colorAviso === 1,

                                    'bg-emerald-500/10 text-emerald-700 group-hover/notice:bg-emerald-100' =>
                                        $colorAviso === 2,

                                    'bg-violet-500/10 text-violet-700 group-hover/notice:bg-violet-100' =>
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

                <div class="group/empty relative overflow-hidden rounded-xl border border-dashed border-primary/20 bg-gradient-to-br from-primary/[0.035] via-white to-blue-50/40 px-6 py-10 text-center shadow-sm transition-all duration-300 hover:border-primary/30 hover:shadow-md">

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



    {{-- Atención de TI --}}

<div>

    <div class="mb-4">

        <h2 class="text-sm font-semibold text-foreground uppercase tracking-widest">
            Atención de TI
        </h2>

    </div>


    <div class="group/support relative overflow-hidden bg-card rounded-2xl border border-border shadow-sm transition-all duration-300 hover:border-primary/15 hover:shadow-md motion-safe:hover:-translate-y-0.5">


        {{-- Decoración --}}

        <span class="absolute -right-12 -top-14 w-32 h-32 rounded-full bg-primary/5 blur-3xl pointer-events-none transition-all duration-500 group-hover/support:bg-primary/10 motion-safe:group-hover/support:scale-150"></span>



        {{-- Horario de atención --}}

        <div class="relative px-5 py-4 border-b border-border transition-colors duration-200 hover:bg-blue-50/30">

            <div class="flex items-center gap-2 mb-3">

                <i
                    data-lucide="clock-3"
                    stroke-width="1.8"
                    class="w-[13px] h-[13px] shrink-0 text-primary">
                </i>

                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                    Horario de atención
                </span>

            </div>


            <div class="flex items-center gap-3">

                <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-full bg-blue-100 text-blue-700 transition-all duration-300 group-hover/support:bg-blue-200 motion-safe:group-hover/support:scale-105">

                    <i
                        data-lucide="calendar-days"
                        stroke-width="1.8"
                        class="w-[18px] h-[18px]">
                    </i>

                </div>

                <div class="min-w-0">

                    <p class="text-sm font-semibold text-foreground">
                        Lunes a viernes
                    </p>

                    <p class="text-xs text-muted-foreground mt-0.5">
                        Atención regular del equipo de TI
                    </p>

                </div>

            </div>


            <div class="flex items-center gap-1.5 mt-3">

                <span class="relative flex w-2 h-2 shrink-0">

                    <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>

                    <span class="relative inline-flex w-2 h-2 rounded-full bg-emerald-500"></span>

                </span>

                <span class="text-xs text-emerald-600 font-medium">
                    Disponible en horario laboral
                </span>

                <span class="text-xs text-muted-foreground ml-auto">
                    09:00 – 18:00
                </span>

            </div>

        </div>



        {{-- Canal de contacto --}}

        <div class="relative px-5 py-4 border-b border-border transition-colors duration-200 hover:bg-violet-50/30">

            <div class="flex items-center gap-2 mb-3">

                <i
                    data-lucide="mail"
                    stroke-width="1.8"
                    class="w-[13px] h-[13px] shrink-0 text-violet-500">
                </i>

                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                    Canal de contacto
                </span>

            </div>


            <div class="flex items-center gap-3">

                <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-full bg-violet-100 text-violet-700 transition-all duration-300 group-hover/support:bg-violet-200 motion-safe:group-hover/support:scale-105">

                    <i
                        data-lucide="headphones"
                        stroke-width="1.8"
                        class="w-[18px] h-[18px]">
                    </i>

                </div>

                <div class="min-w-0">

                    <p class="text-sm font-semibold text-foreground">
                        Mesa de ayuda TI
                    </p>

                    <p class="text-xs text-muted-foreground mt-0.5 truncate">
                        helpdesk@televicentro.hn
                    </p>

                </div>

            </div>


            <div class="flex items-start gap-1.5 mt-3">

                <i
                    data-lucide="info"
                    stroke-width="1.8"
                    class="w-3 h-3 shrink-0 mt-0.5 text-muted-foreground">
                </i>

                <span class="text-xs text-muted-foreground leading-relaxed">
                    Fuera del horario de atención, puedes registrar una incidencia desde el portal.
                </span>

            </div>

        </div>



        {{-- Contactar soporte --}}

@php
    $correoSoporte = 'helpdesk@televicentro.hn';

    $asuntoSoporte = 'Consulta de soporte desde el Portal TI';

    $cuerpoSoporte = implode("\r\n", [
        'Hola, equipo de soporte TI:',
        '',
        'Solicito su apoyo con la siguiente consulta:',
        '',
        'DATOS DEL USUARIO',
        'Nombre: ' . (auth()->user()->nombre ?? 'N/A'),
        'Correo: ' . (auth()->user()->correo ?? 'N/A'),
        '',
        'DETALLE DE LA CONSULTA',
        'Describa brevemente lo que necesita:',
        '',
        '',
        'Indique si recibió algún mensaje de error:',
        '',
        '',
        'Gracias.',
    ]);

    $outlookUrl =
        'https://outlook.office.com/mail/deeplink/compose'
        . '?to=' . rawurlencode($correoSoporte)
        . '&subject=' . rawurlencode($asuntoSoporte)
        . '&body=' . rawurlencode($cuerpoSoporte);
@endphp


        <div class="relative px-5 py-3 bg-muted/40">

            <a
                href="{{ $outlookUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="group/contact w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs text-primary font-medium transition-all duration-200 hover:bg-primary/5 hover:text-primary/80 active:scale-[0.98]"
            >

                <span>
                    Contactar soporte
                </span>

                <i
                    data-lucide="external-link"
                    stroke-width="1.8"
                    class="w-3 h-3 shrink-0 transition-transform duration-200">
                </i>

            </a>

        </div>

    </div>

</div>

</section>