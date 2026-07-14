{{-- Chatbot --}}
<section>

    <div class="bg-card rounded-2xl border border-border overflow-hidden">

        <div class="px-6 py-4 border-b border-border flex items-center gap-3">

            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">

                <i data-lucide="bot" class="w-[18px] h-[18px] text-primary"></i>

            </div>

            <div>

                <p class="text-sm font-semibold text-foreground">
                    Asistente TI
                </p>

                <div class="flex items-center gap-1.5 mt-0.5">

                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>

                    <span class="text-xs text-muted-foreground">
                        En línea
                    </span>

                </div>

            </div>

        </div>


        <div class="px-6 py-5 min-h-[140px] max-h-72 overflow-y-auto space-y-3">

            <div class="flex gap-3">

                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">

                    <i data-lucide="bot" class="w-[15px] h-[15px] text-primary"></i>

                </div>


                <div class="bg-muted rounded-2xl rounded-tl-sm px-4 py-3 max-w-lg">

                    <p class="text-sm text-foreground leading-relaxed">
                        ¡Hola, Alejandro! 👋 Soy el asistente del Portal TI. Puedo ayudarte a reportar incidencias y consultar información relacionada con los servicios tecnológicos.
                    </p>

                    <p class="text-sm text-foreground leading-relaxed mt-2">
                        ¿En qué te puedo ayudar hoy?
                    </p>

                </div>

            </div>

        </div>


        <div class="px-6 py-4 border-t border-border">

            <div class="flex items-center gap-3 rounded-xl border px-4 py-3 border-border bg-muted/50">

                <i data-lucide="search" class="w-4 h-4 text-muted-foreground flex-shrink-0"></i>


                <input 
                    type="text" 
                    placeholder="Escribe tu consulta o solicitud…"
                    class="flex-1 bg-transparent text-sm text-foreground placeholder:text-muted-foreground outline-none border-none focus:outline-none focus:ring-0">


                <button
                    class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center flex-shrink-0 hover:bg-blue-700 transition-colors"
                >

                    <i data-lucide="send" class="w-3.5 h-3.5 text-white"></i>

                </button>


            </div>


            <p class="text-xs text-muted-foreground mt-2 px-1">
                También puedes usar los accesos directos de abajo para solicitudes frecuentes.
            </p>


            <div class="flex flex-wrap gap-2 mt-3 px-1">

                <button class="px-3 py-1 rounded-full text-xs font-medium bg-muted text-foreground hover:bg-blue-600 hover:text-white transition-colors">
    Reportar incidencia
</button>

<button class="px-3 py-1 rounded-full text-xs font-medium bg-muted text-foreground hover:bg-blue-600 hover:text-white transition-colors">
    Consultar solicitud
</button>

<button class="px-3 py-1 rounded-full text-xs font-medium bg-muted text-foreground hover:bg-blue-600 hover:text-white transition-colors">
    Solicitar soporte
</button>
            </div>

        </div>

    </div>

</section>