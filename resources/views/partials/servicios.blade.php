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

        <button class="text-xs text-primary hover:underline flex items-center gap-1">

            Ver catálogo completo
            <i data-lucide="chevron-right" class="w-3 h-3"></i>

        </button>

    </div>



    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">


        {{-- Pase menor --}}
        <a href="{{ route('memorandos.pase_temporal') }}" class="group">

            <div class="relative overflow-hidden bg-card rounded-2xl border border-border p-5
                        transition-all duration-300
                        hover:-translate-y-1
                        hover:shadow-lg
                        hover:border-blue-200">

                {{-- Fondo suave --}}
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100
                            transition-opacity duration-300
                            bg-gradient-to-br from-blue-50 via-transparent to-cyan-50">
                </div>

                <div class="relative">

                    <div class="flex items-start justify-between mb-5">

                        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">

                            <i data-lucide="clock"
                               class="w-5 h-5 text-blue-600"></i>

                        </div>

                        <i data-lucide="arrow-right"
                           class="w-4 h-4 text-blue-500 opacity-0 transition-all duration-300
                                  group-hover:opacity-100 group-hover:translate-x-1"></i>

                    </div>


                    <h3 class="text-sm font-semibold text-foreground mb-2">

                        Pase menor a 24 horas

                    </h3>

                    <p class="text-xs text-muted-foreground leading-relaxed">

                        Solicitud de acceso temporal de corta duración.

                    </p>

                    <div class="mt-5">

                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1
                                     text-[11px] font-medium text-blue-700">

                            Acceso

                        </span>

                    </div>

                </div>

            </div>

        </a>




        {{-- Pase mayor --}}
        <a href="{{ route('memorandos.autorizacion') }}" class="group">

            <div class="relative overflow-hidden bg-card rounded-2xl border border-border p-5
                        transition-all duration-300
                        hover:-translate-y-1
                        hover:shadow-lg
                        hover:border-indigo-200">

                <div class="absolute inset-0 opacity-0 group-hover:opacity-100
                            transition-opacity duration-300
                            bg-gradient-to-br from-indigo-50 via-transparent to-violet-50">
                </div>

                <div class="relative">

                    <div class="flex items-start justify-between mb-5">

                        <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center">

                            <i data-lucide="file-check"
                               class="w-5 h-5 text-indigo-600"></i>

                        </div>

                        <i data-lucide="arrow-right"
                           class="w-4 h-4 text-indigo-500 opacity-0 transition-all duration-300
                                  group-hover:opacity-100 group-hover:translate-x-1"></i>

                    </div>


                    <h3 class="text-sm font-semibold text-foreground mb-2">

                        Pase mayor a 24 horas

                    </h3>

                    <p class="text-xs text-muted-foreground leading-relaxed">

                        Solicitud de memorando de autorización.

                    </p>

                    <div class="mt-5">

                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-1
                                     text-[11px] font-medium text-indigo-700">

                            Acceso extendido

                        </span>

                    </div>

                </div>

            </div>

        </a>





        {{-- Reporte de incidencia --}}
        <a href="{{ route('incidencias.create') }}" class="group">

            <div class="relative overflow-hidden bg-card rounded-2xl border border-border p-5
                        transition-all duration-300
                        hover:-translate-y-1
                        hover:shadow-lg
                        hover:border-orange-200">

                <div class="absolute inset-0 opacity-0 group-hover:opacity-100
                            transition-opacity duration-300
                            bg-gradient-to-br from-orange-50 via-transparent to-red-50">
                </div>

                <div class="relative">

                    <div class="flex items-start justify-between mb-5">

                        <div class="w-11 h-11 rounded-xl bg-orange-100 flex items-center justify-center">

                            <i data-lucide="alert-circle"
                               class="w-5 h-5 text-orange-600"></i>

                        </div>

                        <i data-lucide="arrow-right"
                           class="w-4 h-4 text-orange-500 opacity-0 transition-all duration-300
                                  group-hover:opacity-100 group-hover:translate-x-1"></i>

                    </div>


                    <h3 class="text-sm font-semibold text-foreground mb-2">

                        Reporte de incidencia

                    </h3>

                    <p class="text-xs text-muted-foreground leading-relaxed">

                        Registro de fallas, errores o interrupciones de servicio.

                    </p>

                    <div class="mt-5">

                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-1
                                     text-[11px] font-medium text-orange-700">

                            Soporte

                        </span>

                    </div>

                </div>

            </div>

        </a>


    </div>

</section>