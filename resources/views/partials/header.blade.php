@php
    $enDashboard = request()->routeIs('dashboard');

    $tituloHeader = match(true) {
        request()->routeIs('memorandos.*') => 'Memorandos IT',
        default => 'Portal TI'
    };
@endphp


<header class="bg-card border-b border-border sticky top-0 z-40">


    <div class="max-w-[1300px] mx-auto px-6 h-16 flex items-center justify-between">



        {{-- IZQUIERDA --}}

        <div class="flex items-center gap-4">


            {{-- Botón regresar solamente fuera del dashboard --}}

            @if(!$enDashboard)

                <a
    href="{{ route('dashboard') }}"
    class="flex items-center gap-1.5 text-sm text-primary hover:text-primary/80 transition-colors group"
>

                    <i 
                        data-lucide="arrow-left"
                        class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform">
                    </i>


                    <span class="hidden sm:inline">
                        Volver al Dashboard
                    </span>

                </a>


                <div class="w-px h-5 bg-border"></div>

            @endif





            {{-- Logo --}}

            <div class="flex items-center gap-3">


                <div class="w-14 h-14 rounded-lg  flex items-center justify-center overflow-hidden">


                    <img
                        src="{{ asset('img/tvc.png') }}"
                        alt="Televicentro"
                        class="w-full h-full object-contain p-1"
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

            <button
                class="relative p-2 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
            >


                <i 
                    data-lucide="bell"
                    class="w-[18px] h-[18px]">
                </i>


                <span
                    class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-primary rounded-full">
                </span>


            </button>






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
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-foreground hover:bg-muted transition-colors"
                        >

                            <i 
                                data-lucide="user"
                                class="w-3.5 h-3.5 text-muted-foreground">
                            </i>


                            Mi perfil


                        </button>





                        <button
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-foreground hover:bg-muted transition-colors"
                        >


                            <i 
                                data-lucide="settings"
                                class="w-3.5 h-3.5 text-muted-foreground">
                            </i>


                            Configuración


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
<div class="bg-primary/5 border-b border-primary/10 overflow-hidden">

    <div class="flex items-center">

        {{-- Etiqueta fija --}}
        <div class="flex-shrink-0 flex items-center gap-2 px-4 py-2 bg-primary">

            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>

            <span class="text-white text-[11px] font-bold uppercase tracking-widest whitespace-nowrap">
                Avisos TI
            </span>

        </div>

        {{-- Banda --}}
        <div class="flex-1 overflow-hidden">

            <div
                class="ticker flex items-center w-max"
                onmouseenter="this.style.animationPlayState='paused'"
                onmouseleave="this.style.animationPlayState='running'"
            >

                @for($i = 0; $i < 2; $i++)

                    <div class="flex items-center shrink-0">

                        {{-- Aviso --}}
                        <div class="flex items-center gap-2 px-8 text-[12px]">

                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>

                            <strong>Degradación VPN:</strong>

                            <span class="text-muted-foreground">
                                Latencia elevada en accesos externos — en investigación.
                            </span>

                        </div>

                        <div class="w-px h-4 bg-border"></div>

                        {{-- Aviso --}}
                        <div class="flex items-center gap-2 px-8 text-[12px]">

                            <span class="w-2 h-2 rounded-full bg-blue-400"></span>

                            <strong>Mantenimiento:</strong>

                            <span class="text-muted-foreground">
                                Servidor de correo — sábado 19 jul, 00:00–04:00 hrs.
                            </span>

                        </div>

                        <div class="w-px h-4 bg-border"></div>

                        {{-- Aviso --}}
                        <div class="flex items-center gap-2 px-8 text-[12px]">

                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>

                            <strong>Restaurado:</strong>

                            <span class="text-muted-foreground">
                                Sistema MFA funcionando con normalidad.
                            </span>

                        </div>

                        <div class="w-px h-4 bg-border"></div>

                        {{-- Aviso --}}
                        <div class="flex items-center gap-2 px-8 text-[12px]">

                            <span class="w-2 h-2 rounded-full bg-primary"></span>

                            <strong>Recordatorio:</strong>

                            <span class="text-muted-foreground">
                                Actualizar contraseña corporativa antes del 31 de julio.
                            </span>

                        </div>

                    </div>

                @endfor

            </div>

        </div>

    </div>

</div>