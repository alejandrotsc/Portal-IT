@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">

<main class="max-w-5xl mx-auto px-6 py-8 space-y-8">


{{-- HEADER --}}
<section class="flex items-start justify-between gap-4">

    <div>

        <h1 class="text-xl font-semibold text-foreground">
            Crear solicitud
        </h1>

        <p class="text-sm text-muted-foreground mt-1">
            Solicita accesos, servicios o recursos al equipo de TI.
        </p>

    </div>

    {{-- href="{{ route('mis-solicitudes') }}" --}}
    <a 
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border text-sm font-medium text-foreground hover:bg-muted transition">

        <i data-lucide="history" class="w-4 h-4"></i>

        Mis solicitudes

    </a>

</section>




<form id="solicitudForm"
      action="{{ route('solicitudes.store') }}"
      method="POST"
      enctype="multipart/form-data"
      class="space-y-6">

@csrf




{{-- INFORMACIÓN DE LA SOLICITUD --}}
<div class="bg-card rounded-2xl border border-border overflow-hidden">


    <div class="px-6 py-4 border-b border-border flex items-center gap-3">

        <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
            1
        </span>


        <h2 class="text-sm font-semibold text-foreground">
            Información de la solicitud
        </h2>


    </div>




    <div class="px-6 py-5 space-y-5">



        {{-- TIPO DE SOLICITUD --}}
        <div>

            <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

                Tipo de solicitud <span class="text-primary">*</span>

            </label>


            <select name="tipo_solicitud"
                    id="tipoSolicitud"
                    required

                    class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">


                <option value="">
                    Seleccione una opción
                </option>


                <option value="acceso">
                    Acceso o permisos
                </option>


                <option value="software">
                    Instalación de software
                </option>


                <option value="equipo">
                    Solicitud de equipo
                </option>


                <option value="cuenta">
                    Creación o modificación de cuenta
                </option>


                <option value="servicio">
                    Servicio TI
                </option>


                <option value="otro">
                    Otro requerimiento
                </option>


            </select>


        </div>





        {{-- TITULO --}}
        <div>

            <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

                ¿Qué necesitas? <span class="text-primary">*</span>

            </label>


            <input type="text"
                   name="titulo"
                   required
                   value="{{ old('titulo') }}"

                   placeholder="Ej: Solicito acceso al sistema de inventarios"

                   class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">


        </div>





        {{-- DESCRIPCIÓN --}}
        <div>

            <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

                Detalle de la solicitud <span class="text-primary">*</span>

            </label>


            <textarea name="descripcion"
                      required
                      rows="5"

                      placeholder="Describe qué necesitas, para quién será y el motivo de la solicitud..."

                      class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm resize-none focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">{{ old('descripcion') }}</textarea>


        </div>



    </div>


</div>








{{-- ARCHIVOS ADJUNTOS --}}
<div class="bg-card rounded-2xl border border-border overflow-hidden">


    <div class="px-6 py-4 border-b border-border flex items-center gap-3">


        <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
            2
        </span>


        <h2 class="text-sm font-semibold text-foreground">
            Archivos adjuntos
        </h2>


    </div>





    <div class="px-6 py-5">


        <div id="dropzone"

             class="flex flex-col items-center justify-center h-40 rounded-xl border-2 border-dashed border-border hover:border-primary hover:bg-primary/5 cursor-pointer transition">


            <i data-lucide="paperclip"
               class="w-8 h-8 text-muted-foreground mb-3"></i>


            <p class="text-sm text-muted-foreground">

                Adjunta documentos relacionados

            </p>


            <p class="text-xs text-muted-foreground mt-1">

                PDF, PNG, JPG, DOCX - máximo 10MB

            </p>


            <input id="archivos"
                   type="file"
                   name="archivos[]"
                   multiple
                   class="hidden">


        </div>




        <div id="preview"
             class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
        </div>



    </div>


</div>







{{-- BOTONES --}}
<div class="flex justify-end gap-3">


<a href="{{ route('dashboard') }}"
   class="px-5 py-2.5 rounded-xl border border-border text-sm text-muted-foreground hover:bg-muted">

    Cancelar

</a>




<button id="btnEnviar"
        type="submit"

        class="px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-medium flex items-center gap-2">


    <i data-lucide="send"
       class="w-4 h-4"></i>


    Enviar solicitud


</button>


</div>




</form>


</main>

</div>




<script>

lucide.createIcons();

</script>


@endsection