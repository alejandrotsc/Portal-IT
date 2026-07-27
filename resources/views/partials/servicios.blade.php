{{-- Servicios frecuentes --}}
<section>

    <div class="flex items-center justify-between mb-4">

        <div>

            <h2 class="text-sm font-semibold text-foreground uppercase tracking-widest">
                Servicios frecuentes
            </h2>

            <p class="text-xs text-muted-foreground mt-1">
                Accesos rápidos a solicitudes comunes.
            </p>

        </div>

    </div>



    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">


{{-- Pase menor a 24 horas --}}
<a
    href="{{ route('memorandos.pase_temporal') }}"
    class="group block h-full"
>
    <div
        class="relative h-full overflow-hidden
               bg-card rounded-2xl border border-border p-5
               transition-all duration-300
               hover:-translate-y-1
               hover:shadow-lg
               hover:border-blue-200"
    >

        {{-- Fondo degradado azul --}}
        <div
            class="absolute inset-0 pointer-events-none
                   opacity-40 group-hover:opacity-100
                   transition-opacity duration-300"
            style="
                background: linear-gradient(
                    135deg,
                    rgba(239, 246, 255, 0.95) 0%,
                    rgba(255, 255, 255, 0.20) 55%,
                    rgba(236, 254, 255, 0.90) 100%
                );
            "
        ></div>


        {{-- Contenido --}}
        <div class="relative z-10 h-full flex flex-col">

            <div class="flex items-start justify-between mb-5">

                <div
                    class="w-11 h-11 rounded-xl
                           bg-blue-100
                           flex items-center justify-center
                           transition-transform duration-300
                           group-hover:scale-105"
                >
                    <i
                        data-lucide="clock"
                        class="w-5 h-5 text-blue-600"
                    ></i>
                </div>

                <i
                    data-lucide="arrow-right"
                    class="w-4 h-4 text-blue-500
                           opacity-0 -translate-x-1
                           transition-all duration-300
                           group-hover:opacity-100
                           group-hover:translate-x-1"
                ></i>

            </div>


            <h3 class="text-sm font-semibold text-foreground mb-2">
                Pase menor a 24 horas
            </h3>


            <p class="text-xs text-muted-foreground leading-relaxed">
                Solicitud de acceso temporal de corta duración.
            </p>


            <div class="mt-auto pt-5">

                <span
                    class="inline-flex items-center rounded-full
                           bg-blue-100 px-2.5 py-1
                           text-[11px] font-medium text-blue-700"
                >
                    Acceso
                </span>

            </div>

        </div>

    </div>
</a>



{{-- Pase mayor a 24 horas --}}
<a
    href="{{ route('memorandos.autorizacion') }}"
    class="group block h-full"
>
    <div
        class="relative h-full overflow-hidden
               bg-card rounded-2xl border border-border p-5
               transition-all duration-300
               hover:-translate-y-1
               hover:shadow-lg
               hover:border-indigo-200"
    >

        {{-- Fondo degradado índigo --}}
        <div
            class="absolute inset-0 pointer-events-none
                   opacity-40 group-hover:opacity-100
                   transition-opacity duration-300"
            style="
                background: linear-gradient(
                    135deg,
                    rgba(238, 242, 255, 0.95) 0%,
                    rgba(255, 255, 255, 0.20) 55%,
                    rgba(245, 243, 255, 0.90) 100%
                );
            "
        ></div>


        {{-- Contenido --}}
        <div class="relative z-10 h-full flex flex-col">

            <div class="flex items-start justify-between mb-5">

                <div
                    class="w-11 h-11 rounded-xl
                           bg-indigo-100
                           flex items-center justify-center
                           transition-transform duration-300
                           group-hover:scale-105"
                >
                    <i
                        data-lucide="file-check"
                        class="w-5 h-5 text-indigo-600"
                    ></i>
                </div>

                <i
                    data-lucide="arrow-right"
                    class="w-4 h-4 text-indigo-500
                           opacity-0 -translate-x-1
                           transition-all duration-300
                           group-hover:opacity-100
                           group-hover:translate-x-1"
                ></i>

            </div>


            <h3 class="text-sm font-semibold text-foreground mb-2">
                Pase mayor a 24 horas
            </h3>


            <p class="text-xs text-muted-foreground leading-relaxed">
                Solicitud de memorando de autorización.
            </p>


            <div class="mt-auto pt-5">

                <span
                    class="inline-flex items-center rounded-full
                           bg-indigo-100 px-2.5 py-1
                           text-[11px] font-medium text-indigo-700"
                >
                    Acceso extendido
                </span>

            </div>

        </div>

    </div>
</a>



{{-- Solicitudes --}}
<a
    href="{{ route('solicitudes.create') }}"
    class="group block h-full"
>
    <div
        class="relative h-full overflow-hidden
               bg-card rounded-2xl border border-border p-5
               transition-all duration-300
               hover:-translate-y-1
               hover:shadow-lg
               hover:border-emerald-200"
    >

        {{-- Fondo degradado verde --}}
        <div
            class="absolute inset-0 pointer-events-none
                   opacity-40 group-hover:opacity-100
                   transition-opacity duration-300"
            style="
                background: linear-gradient(
                    135deg,
                    rgba(236, 253, 245, 0.95) 0%,
                    rgba(255, 255, 255, 0.20) 55%,
                    rgba(240, 253, 250, 0.90) 100%
                );
            "
        ></div>


        {{-- Contenido --}}
        <div class="relative z-10 h-full flex flex-col">

            <div class="flex items-start justify-between mb-5">

                <div
                    class="w-11 h-11 rounded-xl
                           bg-emerald-100
                           flex items-center justify-center
                           transition-transform duration-300
                           group-hover:scale-105"
                >
                    <i
                        data-lucide="clipboard-list"
                        class="w-5 h-5 text-emerald-600"
                    ></i>
                </div>

                <i
                    data-lucide="arrow-right"
                    class="w-4 h-4 text-emerald-500
                           opacity-0 -translate-x-1
                           transition-all duration-300
                           group-hover:opacity-100
                           group-hover:translate-x-1"
                ></i>

            </div>


            <h3 class="text-sm font-semibold text-foreground mb-2">
                Solicitudes
            </h3>


            <p class="text-xs text-muted-foreground leading-relaxed">
                Gestiona requerimientos, accesos y servicios tecnológicos.
            </p>


            <div class="mt-auto pt-5">

                <span
                    class="inline-flex items-center rounded-full
                           bg-emerald-100 px-2.5 py-1
                           text-[11px] font-medium text-emerald-700"
                >
                    Gestión
                </span>

            </div>

        </div>

    </div>
</a>



{{-- Reporte de incidencia --}}
<a
    href="{{ route('incidencias.create') }}"
    class="group block h-full"
>
    <div
        class="relative h-full overflow-hidden
               bg-card rounded-2xl border border-border p-5
               transition-all duration-300
               hover:-translate-y-1
               hover:shadow-lg
               hover:border-orange-200"
    >

        {{-- Fondo degradado naranja --}}
        <div
            class="absolute inset-0 pointer-events-none
                   opacity-40 group-hover:opacity-100
                   transition-opacity duration-300"
            style="
                background: linear-gradient(
                    135deg,
                    rgba(255, 247, 237, 0.95) 0%,
                    rgba(255, 255, 255, 0.20) 55%,
                    rgba(254, 242, 242, 0.90) 100%
                );
            "
        ></div>


        {{-- Contenido --}}
        <div class="relative z-10 h-full flex flex-col">

            <div class="flex items-start justify-between mb-5">

                <div
                    class="w-11 h-11 rounded-xl
                           bg-orange-100
                           flex items-center justify-center
                           transition-transform duration-300
                           group-hover:scale-105"
                >
                    <i
                        data-lucide="circle-alert"
                        class="w-5 h-5 text-orange-600"
                    ></i>
                </div>

                <i
                    data-lucide="arrow-right"
                    class="w-4 h-4 text-orange-500
                           opacity-0 -translate-x-1
                           transition-all duration-300
                           group-hover:opacity-100
                           group-hover:translate-x-1"
                ></i>

            </div>


            <h3 class="text-sm font-semibold text-foreground mb-2">
                Reporte de incidencia
            </h3>


            <p class="text-xs text-muted-foreground leading-relaxed">
                Registro de fallas, errores o interrupciones de servicio.
            </p>


            <div class="mt-auto pt-5">

                <span
                    class="inline-flex items-center rounded-full
                           bg-orange-100 px-2.5 py-1
                           text-[11px] font-medium text-orange-700"
                >
                    Soporte
                </span>

            </div>

        </div>

    </div>
</a>


    </div>

</section>