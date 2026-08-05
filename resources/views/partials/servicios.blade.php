{{-- Servicios frecuentes --}}

<section>

    {{-- Encabezado --}}

    <div class="mb-4 flex items-center justify-between">

        <div>

            <h2
                class="text-sm font-semibold uppercase
                       tracking-widest text-foreground"
            >
                Servicios frecuentes
            </h2>

            <p class="mt-1 text-xs text-muted-foreground">
                Accesos rápidos a solicitudes comunes.
            </p>

        </div>

    </div>


    {{-- Cuadrícula responsive --}}

    <div
        class="grid grid-cols-1 gap-4
               sm:grid-cols-2
               lg:grid-cols-4"
    >


        {{-- Pase menor a 24 horas --}}

        <a
            href="{{ route('memorandos.pase_temporal') }}"
            class="group/card block h-full"
        >
            <article
                class="relative flex h-full min-h-[210px]
                       flex-col overflow-hidden rounded-2xl
                       border border-border bg-card
                       px-5 pb-5 pt-6 shadow-sm
                       transition-all duration-300
                       hover:-translate-y-1
                       hover:border-blue-200
                       hover:shadow-lg
                       hover:shadow-blue-500/10
                       dark:hover:border-blue-800
                       dark:hover:shadow-black/25"
            >

                {{-- Acento superior --}}

                <span
                    class="absolute left-5 right-5 top-0
                           h-1 rounded-b-full
                           bg-gradient-to-r
                           from-blue-500 to-cyan-400
                           transition-all duration-300
                           group-hover/card:left-4
                           group-hover/card:right-4"
                ></span>


                {{-- Brillo decorativo --}}

                <span
                    class="pointer-events-none absolute
                           -right-10 -top-10
                           h-28 w-28 rounded-full
                           bg-blue-500/[0.06] blur-3xl
                           transition-all duration-500
                           group-hover/card:scale-125
                           group-hover/card:bg-blue-500/10"
                ></span>


                {{-- Encabezado de la tarjeta --}}

                <div
                    class="relative mb-5 flex
                           items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 shrink-0
                               items-center justify-center
                               rounded-xl border
                               border-blue-200/70
                               bg-blue-50 text-blue-600
                               transition-all duration-300
                               group-hover/card:scale-105
                               group-hover/card:border-blue-300
                               group-hover/card:bg-blue-100
                               dark:border-blue-900
                               dark:bg-blue-950/50
                               dark:text-blue-400
                               dark:group-hover/card:border-blue-800
                               dark:group-hover/card:bg-blue-950/80"
                    >
                        <i
                            data-lucide="clock"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        ></i>
                    </div>


                    {{-- Flecha visible únicamente en hover --}}

                    <div
                        class="flex h-8 w-8 translate-x-1
                               items-center justify-center
                               rounded-lg
                               text-blue-600 opacity-0
                               transition-all duration-300
                               group-hover/card:translate-x-0
                               group-hover/card:bg-blue-50
                               group-hover/card:opacity-100
                               dark:text-blue-400
                               dark:group-hover/card:bg-blue-950/50"
                    >
                        <i
                            data-lucide="arrow-up-right"
                            stroke-width="1.8"
                            class="h-4 w-4
                                   transition-transform duration-300
                                   group-hover/card:translate-x-0.5
                                   group-hover/card:-translate-y-0.5"
                        ></i>
                    </div>

                </div>


                {{-- Contenido --}}

                <div class="relative flex flex-1 flex-col">

                    <h3
                        class="mb-2 text-sm font-semibold
                               leading-relaxed text-foreground
                               transition-colors duration-200
                               group-hover/card:text-blue-700
                               dark:group-hover/card:text-blue-300"
                    >
                        Pase menor a 24 horas
                    </h3>

                    <p
                        class="text-xs leading-relaxed
                               text-muted-foreground"
                    >
                        Solicitud de acceso temporal de corta duración.
                    </p>


                    {{-- Etiqueta --}}

                    <div class="mt-auto pt-5">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-lg border
                                   border-blue-200/70
                                   bg-blue-50/70 px-2.5 py-1
                                   text-[11px] font-medium
                                   text-blue-700
                                   transition-colors duration-200
                                   group-hover/card:border-blue-300
                                   group-hover/card:bg-blue-100
                                   dark:border-blue-900
                                   dark:bg-blue-950/40
                                   dark:text-blue-300
                                   dark:group-hover/card:border-blue-800
                                   dark:group-hover/card:bg-blue-950/70"
                        >
                            <i
                                data-lucide="key-round"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Acceso
                        </span>

                    </div>

                </div>

            </article>
        </a>



        {{-- Pase mayor a 24 horas --}}

        <a
            href="{{ route('memorandos.autorizacion') }}"
            class="group/card block h-full"
        >
            <article
                class="relative flex h-full min-h-[210px]
                       flex-col overflow-hidden rounded-2xl
                       border border-border bg-card
                       px-5 pb-5 pt-6 shadow-sm
                       transition-all duration-300
                       hover:-translate-y-1
                       hover:border-indigo-200
                       hover:shadow-lg
                       hover:shadow-indigo-500/10
                       dark:hover:border-indigo-800
                       dark:hover:shadow-black/25"
            >

                {{-- Acento superior --}}

                <span
                    class="absolute left-5 right-5 top-0
                           h-1 rounded-b-full
                           bg-gradient-to-r
                           from-indigo-500 to-violet-500
                           transition-all duration-300
                           group-hover/card:left-4
                           group-hover/card:right-4"
                ></span>


                {{-- Brillo decorativo --}}

                <span
                    class="pointer-events-none absolute
                           -right-10 -top-10
                           h-28 w-28 rounded-full
                           bg-indigo-500/[0.06] blur-3xl
                           transition-all duration-500
                           group-hover/card:scale-125
                           group-hover/card:bg-indigo-500/10"
                ></span>


                {{-- Encabezado de la tarjeta --}}

                <div
                    class="relative mb-5 flex
                           items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 shrink-0
                               items-center justify-center
                               rounded-xl border
                               border-indigo-200/70
                               bg-indigo-50 text-indigo-600
                               transition-all duration-300
                               group-hover/card:scale-105
                               group-hover/card:border-indigo-300
                               group-hover/card:bg-indigo-100
                               dark:border-indigo-900
                               dark:bg-indigo-950/50
                               dark:text-indigo-400
                               dark:group-hover/card:border-indigo-800
                               dark:group-hover/card:bg-indigo-950/80"
                    >
                        <i
                            data-lucide="file-check"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        ></i>
                    </div>


                    {{-- Flecha visible únicamente en hover --}}

                    <div
                        class="flex h-8 w-8 translate-x-1
                               items-center justify-center
                               rounded-lg
                               text-indigo-600 opacity-0
                               transition-all duration-300
                               group-hover/card:translate-x-0
                               group-hover/card:bg-indigo-50
                               group-hover/card:opacity-100
                               dark:text-indigo-400
                               dark:group-hover/card:bg-indigo-950/50"
                    >
                        <i
                            data-lucide="arrow-up-right"
                            stroke-width="1.8"
                            class="h-4 w-4
                                   transition-transform duration-300
                                   group-hover/card:translate-x-0.5
                                   group-hover/card:-translate-y-0.5"
                        ></i>
                    </div>

                </div>


                {{-- Contenido --}}

                <div class="relative flex flex-1 flex-col">

                    <h3
                        class="mb-2 text-sm font-semibold
                               leading-relaxed text-foreground
                               transition-colors duration-200
                               group-hover/card:text-indigo-700
                               dark:group-hover/card:text-indigo-300"
                    >
                        Pase mayor a 24 horas
                    </h3>

                    <p
                        class="text-xs leading-relaxed
                               text-muted-foreground"
                    >
                        Solicitud de memorando de autorización.
                    </p>


                    {{-- Etiqueta --}}

                    <div class="mt-auto pt-5">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-lg border
                                   border-indigo-200/70
                                   bg-indigo-50/70 px-2.5 py-1
                                   text-[11px] font-medium
                                   text-indigo-700
                                   transition-colors duration-200
                                   group-hover/card:border-indigo-300
                                   group-hover/card:bg-indigo-100
                                   dark:border-indigo-900
                                   dark:bg-indigo-950/40
                                   dark:text-indigo-300
                                   dark:group-hover/card:border-indigo-800
                                   dark:group-hover/card:bg-indigo-950/70"
                        >
                            <i
                                data-lucide="shield-check"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Acceso extendido
                        </span>

                    </div>

                </div>

            </article>
        </a>



        {{-- Solicitudes --}}

        <a
            href="{{ route('solicitudes.create') }}"
            class="group/card block h-full"
        >
            <article
                class="relative flex h-full min-h-[210px]
                       flex-col overflow-hidden rounded-2xl
                       border border-border bg-card
                       px-5 pb-5 pt-6 shadow-sm
                       transition-all duration-300
                       hover:-translate-y-1
                       hover:border-emerald-200
                       hover:shadow-lg
                       hover:shadow-emerald-500/10
                       dark:hover:border-emerald-800
                       dark:hover:shadow-black/25"
            >

                {{-- Acento superior --}}

                <span
                    class="absolute left-5 right-5 top-0
                           h-1 rounded-b-full
                           bg-gradient-to-r
                           from-emerald-500 to-teal-400
                           transition-all duration-300
                           group-hover/card:left-4
                           group-hover/card:right-4"
                ></span>


                {{-- Brillo decorativo --}}

                <span
                    class="pointer-events-none absolute
                           -right-10 -top-10
                           h-28 w-28 rounded-full
                           bg-emerald-500/[0.06] blur-3xl
                           transition-all duration-500
                           group-hover/card:scale-125
                           group-hover/card:bg-emerald-500/10"
                ></span>


                {{-- Encabezado de la tarjeta --}}

                <div
                    class="relative mb-5 flex
                           items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 shrink-0
                               items-center justify-center
                               rounded-xl border
                               border-emerald-200/70
                               bg-emerald-50 text-emerald-600
                               transition-all duration-300
                               group-hover/card:scale-105
                               group-hover/card:border-emerald-300
                               group-hover/card:bg-emerald-100
                               dark:border-emerald-900
                               dark:bg-emerald-950/50
                               dark:text-emerald-400
                               dark:group-hover/card:border-emerald-800
                               dark:group-hover/card:bg-emerald-950/80"
                    >
                        <i
                            data-lucide="clipboard-list"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        ></i>
                    </div>


                    {{-- Flecha visible únicamente en hover --}}

                    <div
                        class="flex h-8 w-8 translate-x-1
                               items-center justify-center
                               rounded-lg
                               text-emerald-600 opacity-0
                               transition-all duration-300
                               group-hover/card:translate-x-0
                               group-hover/card:bg-emerald-50
                               group-hover/card:opacity-100
                               dark:text-emerald-400
                               dark:group-hover/card:bg-emerald-950/50"
                    >
                        <i
                            data-lucide="arrow-up-right"
                            stroke-width="1.8"
                            class="h-4 w-4
                                   transition-transform duration-300
                                   group-hover/card:translate-x-0.5
                                   group-hover/card:-translate-y-0.5"
                        ></i>
                    </div>

                </div>


                {{-- Contenido --}}

                <div class="relative flex flex-1 flex-col">

                    <h3
                        class="mb-2 text-sm font-semibold
                               leading-relaxed text-foreground
                               transition-colors duration-200
                               group-hover/card:text-emerald-700
                               dark:group-hover/card:text-emerald-300"
                    >
                        Solicitudes
                    </h3>

                    <p
                        class="text-xs leading-relaxed
                               text-muted-foreground"
                    >
                        Gestiona requerimientos, accesos y servicios tecnológicos.
                    </p>


                    {{-- Etiqueta --}}

                    <div class="mt-auto pt-5">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-lg border
                                   border-emerald-200/70
                                   bg-emerald-50/70 px-2.5 py-1
                                   text-[11px] font-medium
                                   text-emerald-700
                                   transition-colors duration-200
                                   group-hover/card:border-emerald-300
                                   group-hover/card:bg-emerald-100
                                   dark:border-emerald-900
                                   dark:bg-emerald-950/40
                                   dark:text-emerald-300
                                   dark:group-hover/card:border-emerald-800
                                   dark:group-hover/card:bg-emerald-950/70"
                        >
                            <i
                                data-lucide="workflow"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Gestión
                        </span>

                    </div>

                </div>

            </article>
        </a>



        {{-- Reporte de incidencia --}}

        <a
            href="{{ route('incidencias.create') }}"
            class="group/card block h-full"
        >
            <article
                class="relative flex h-full min-h-[210px]
                       flex-col overflow-hidden rounded-2xl
                       border border-border bg-card
                       px-5 pb-5 pt-6 shadow-sm
                       transition-all duration-300
                       hover:-translate-y-1
                       hover:border-orange-200
                       hover:shadow-lg
                       hover:shadow-orange-500/10
                       dark:hover:border-orange-800
                       dark:hover:shadow-black/25"
            >

                {{-- Acento superior --}}

                <span
                    class="absolute left-5 right-5 top-0
                           h-1 rounded-b-full
                           bg-gradient-to-r
                           from-orange-500 to-red-400
                           transition-all duration-300
                           group-hover/card:left-4
                           group-hover/card:right-4"
                ></span>


                {{-- Brillo decorativo --}}

                <span
                    class="pointer-events-none absolute
                           -right-10 -top-10
                           h-28 w-28 rounded-full
                           bg-orange-500/[0.06] blur-3xl
                           transition-all duration-500
                           group-hover/card:scale-125
                           group-hover/card:bg-orange-500/10"
                ></span>


                {{-- Encabezado de la tarjeta --}}

                <div
                    class="relative mb-5 flex
                           items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 shrink-0
                               items-center justify-center
                               rounded-xl border
                               border-orange-200/70
                               bg-orange-50 text-orange-600
                               transition-all duration-300
                               group-hover/card:scale-105
                               group-hover/card:border-orange-300
                               group-hover/card:bg-orange-100
                               dark:border-orange-900
                               dark:bg-orange-950/50
                               dark:text-orange-400
                               dark:group-hover/card:border-orange-800
                               dark:group-hover/card:bg-orange-950/80"
                    >
                        <i
                            data-lucide="circle-alert"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        ></i>
                    </div>


                    {{-- Flecha visible únicamente en hover --}}

                    <div
                        class="flex h-8 w-8 translate-x-1
                               items-center justify-center
                               rounded-lg
                               text-orange-600 opacity-0
                               transition-all duration-300
                               group-hover/card:translate-x-0
                               group-hover/card:bg-orange-50
                               group-hover/card:opacity-100
                               dark:text-orange-400
                               dark:group-hover/card:bg-orange-950/50"
                    >
                        <i
                            data-lucide="arrow-up-right"
                            stroke-width="1.8"
                            class="h-4 w-4
                                   transition-transform duration-300
                                   group-hover/card:translate-x-0.5
                                   group-hover/card:-translate-y-0.5"
                        ></i>
                    </div>

                </div>


                {{-- Contenido --}}

                <div class="relative flex flex-1 flex-col">

                    <h3
                        class="mb-2 text-sm font-semibold
                               leading-relaxed text-foreground
                               transition-colors duration-200
                               group-hover/card:text-orange-700
                               dark:group-hover/card:text-orange-300"
                    >
                        Reporte de incidencia
                    </h3>

                    <p
                        class="text-xs leading-relaxed
                               text-muted-foreground"
                    >
                        Registro de fallas, errores o interrupciones de servicio.
                    </p>


                    {{-- Etiqueta --}}

                    <div class="mt-auto pt-5">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-lg border
                                   border-orange-200/70
                                   bg-orange-50/70 px-2.5 py-1
                                   text-[11px] font-medium
                                   text-orange-700
                                   transition-colors duration-200
                                   group-hover/card:border-orange-300
                                   group-hover/card:bg-orange-100
                                   dark:border-orange-900
                                   dark:bg-orange-950/40
                                   dark:text-orange-300
                                   dark:group-hover/card:border-orange-800
                                   dark:group-hover/card:bg-orange-950/70"
                        >
                            <i
                                data-lucide="headset"
                                stroke-width="1.8"
                                class="h-3 w-3"
                            ></i>

                            Soporte
                        </span>

                    </div>

                </div>

            </article>
        </a>

    </div>

</section>