{{-- Servicios frecuentes --}}
<section>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-sm font-semibold text-foreground uppercase tracking-widest">Servicios frecuentes</h2>
            <p class="text-xs text-muted-foreground mt-1">Accesos rápidos a solicitudes comunes.</p>
        </div>

        <button class="text-xs text-primary hover:underline flex items-center gap-1">
            Ver catálogo completo <i data-lucide="chevron-right" class="w-3 h-3"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">


        <a href="{{ route('memorandos.pase_temporal') }}" class="group">
        <div class="group bg-card rounded-2xl border border-border p-5 text-left hover:border-primary/30 hover:shadow-md hover:shadow-primary/5 transition-all duration-200">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4 bg-primary/10">
                <i data-lucide="clock" class="w-[18px] h-[18px] text-primary"></i>
            </div>

            <h3 class="text-sm font-semibold text-foreground leading-snug mb-1.5">
                Pase menor a 24 horas
            </h3>

            <p class="text-xs text-muted-foreground leading-relaxed mb-3">
                Solicitud de acceso temporal de corta duración
            </p>

            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-600">
                Acceso
            </span>
        </div>
</a>


        <a href="{{ route('memorandos.autorizacion') }}" class="group">
        <div class="group bg-card rounded-2xl border border-border p-5 text-left hover:border-primary/30 hover:shadow-md hover:shadow-primary/5 transition-all duration-200">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4 bg-primary/10">
                <i data-lucide="file-check" class="w-[18px] h-[18px] text-primary"></i>
            </div>

            <h3 class="text-sm font-semibold text-foreground leading-snug mb-1.5">
                Pase mayor a 24 horas
            </h3>

            <p class="text-xs text-muted-foreground leading-relaxed mb-3">
                Solicitud de memorando de autorización
            </p>

            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-600">
                Acceso extendido
            </span>
        </div>
        </a>

        <a href="{{ route('memorandos.create.compra') }}" class="group">
        <div class="group bg-card rounded-2xl border border-border p-5 text-left hover:border-primary/30 hover:shadow-md hover:shadow-primary/5 transition-all duration-200">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4 bg-primary/10">
                <i data-lucide="alert-circle" class="w-[18px] h-[18px] text-primary"></i>
            </div>

            <h3 class="text-sm font-semibold text-foreground leading-snug mb-1.5">
                Reporte de incidencia
            </h3>

            <p class="text-xs text-muted-foreground leading-relaxed mb-3">
                Registro de fallas, errores o interrupciones de servicio
            </p>

            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-600">
                Soporte
            </span>
        </div>
</a>

    </div>
</section>