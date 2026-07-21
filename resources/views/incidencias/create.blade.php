@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
<main class="max-w-5xl mx-auto px-6 py-8 space-y-8">

    {{-- HEADER --}}
    <section class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-foreground">Reporte de incidencia</h1>
            <p class="text-sm text-muted-foreground mt-1">Describe el problema que estás presentando y adjunta evidencia para que TI pueda ayudarte.</p>
        </div>

        <a href="{{ route('mis-incidencias') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border text-sm font-medium text-foreground hover:bg-muted transition">
            <i data-lucide="history" class="w-4 h-4"></i> Mis incidencias
        </a>
    </section>

    <form id="incidenciaForm" action="{{ route('incidencias.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- INFORMACIÓN DEL PROBLEMA --}}
        <div class="bg-card rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">1</span>
                <h2 class="text-sm font-semibold text-foreground">Información del problema</h2>
            </div>

            <div class="px-6 py-5 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">
                        ¿Qué problema estás presentando? <span class="text-primary">*</span>
                    </label>
                    <input type="text" name="titulo" required value="{{ old('titulo') }}"
                        placeholder="Ej: No puedo ingresar al correo corporativo"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">
                        Describe lo ocurrido <span class="text-primary">*</span>
                    </label>
                    <textarea name="descripcion" required rows="5"
                        placeholder="Indica qué ocurrió, qué estabas intentando hacer y si aparece algún mensaje..."
                        class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm resize-none focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">{{ old('descripcion') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">¿Cuándo empezó?</label>
                        <select name="tiempo_problema" class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm">
                            <option value="">Seleccione</option>
                            <option value="hoy">Hoy</option>
                            <option value="ayer">Ayer</option>
                            <option value="varios_dias">Hace varios días</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">¿A quién afecta?</label>
                        <select name="afectacion" class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm">
                            <option value="solo">Solo a mí</option>
                            <option value="varios">A varias personas</option>
                            <option value="todos">A toda el área</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- EVIDENCIA --}}
        <div class="bg-card rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center shrink-0">2</span>
                <h2 class="text-sm font-semibold text-foreground">Evidencia del problema</h2>
            </div>

            <div class="px-6 py-5 space-y-5">
                <div id="dropzone" class="flex flex-col items-center justify-center h-48 rounded-xl border-2 border-dashed border-border hover:border-primary hover:bg-primary/5 cursor-pointer transition">
                    <i data-lucide="image" class="w-8 h-8 text-muted-foreground mb-3"></i>
                    <p class="text-sm text-muted-foreground">Arrastra tus capturas aquí</p>
                    <p class="text-xs text-muted-foreground mt-1">o haz clic para seleccionar imágenes</p>
                    <p class="text-xs text-muted-foreground mt-2">PNG, JPG, JPEG, WEBP - máximo 10MB</p>
                    <input id="archivos" type="file" name="archivos[]" multiple accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden">
                </div>

                <div id="preview" class="grid grid-cols-2 sm:grid-cols-4 gap-4"></div>

                <div class="flex gap-3 bg-muted/50 rounded-xl p-4">
                    <i data-lucide="info" class="w-5 h-5 text-primary"></i>
                    <p class="text-xs text-muted-foreground">Las imágenes serán analizadas automáticamente mediante OCR para detectar mensajes de error y facilitar la atención del equipo TI.</p>
                </div>
            </div>
        </div>

        {{-- INFORMACIÓN ADICIONAL --}}
        <div class="bg-card rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center shrink-0">3</span>
                <h2 class="text-sm font-semibold text-foreground">Información adicional</h2>
            </div>

            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">Equipo relacionado</label>
                    <input type="text" name="equipo" placeholder="Ej: Laptop Dell, impresora..." class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">Lugar donde ocurre</label>
                    <input type="text" name="ubicacion" placeholder="Ej: Oficina, Producción..." class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm">
                </div>
            </div>
        </div>

        {{-- BOTONES --}}
<div class="flex justify-end gap-3">

    <button
        id="btnCancelar"
        type="button"
        class="inline-flex items-center gap-2
               px-5 py-2.5 rounded-xl
               border border-border
               text-sm text-muted-foreground
               hover:bg-muted transition-colors"
    >
        Cancelar
    </button>

    <button
        id="btnEnviar"
        type="submit"
        class="px-5 py-2.5 rounded-xl
               bg-primary text-white
               text-sm font-medium
               flex items-center gap-2
               disabled:opacity-70
               disabled:cursor-not-allowed">
        <i
            id="btnEnviarIcono"
            data-lucide="mail"
            class="w-4 h-4"
        ></i>

        <span id="btnEnviarTexto">
            Enviar reporte
        </span>
    </button>

    </div>
    </form>
</main>
</div>

{{-- MODAL RESPUESTA --}}
<div id="modalIncidencia" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">

    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-5 p-6 text-center">

        <div id="modalIcono"
             class="w-14 h-14 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto mb-4">

            <i data-lucide="check-circle" class="w-8 h-8"></i>

        </div>


        <h3 id="modalTitulo"
            class="text-lg font-semibold text-gray-900">
            Incidencia enviada
        </h3>


        <p id="modalMensaje"
           class="text-sm text-gray-500 mt-2">
            Se notificó al equipo de soporte TI mediante correo.
        </p>


        <div id="codigoIncidencia"
             class="mt-4 bg-gray-100 rounded-xl py-3 text-sm font-semibold text-gray-700">
        </div>


        <button onclick="cerrarModal()"
            class="mt-6 w-full px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-medium">

            Entendido

        </button>

    </div>

</div>

<script src="{{ asset('js/incidencias.js') }}"></script>

<style>
.spinner-envio {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #fff;
    border-radius: 50%;
    display: inline-block;
    animation: girar-spinner 0.6s linear infinite;
}

@keyframes girar-spinner {
    to { transform: rotate(360deg); }
}
</style>
@endsection