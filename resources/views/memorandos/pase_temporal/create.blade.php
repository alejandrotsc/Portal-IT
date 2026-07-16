@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/pases.css') }}">
<link rel="stylesheet" href="{{ asset('css/autorizacion.css') }}">


<div class="min-h-screen bg-background">


<form 
    id="documentForm"
    method="POST"
    action="{{ route('memorandos.pase_temporal.store') }}"
>

@csrf


<input 
    type="hidden" 
    name="tipo_documento" 
    value="pase_temporal"
>


<input 
    type="hidden" 
    name="tipo_id" 
    value="{{ $tipoPase->id ?? '' }}"
>



<div class="max-w-[1300px] mx-auto px-6 space-y-5">


{{-- HEADER --}}

<div class="mb-8">

    <div class="flex items-start justify-between gap-4">

        <div>

            <h1 class="text-xl font-semibold text-foreground tracking-tight">
                Generación de memorandos internos
            </h1>


            <p class="text-sm text-muted-foreground mt-1">
                Complete los campos requeridos para solicitar autorización de ingreso de equipo tecnológico.
            </p>

        </div>



        <span 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-100"
        >

            <i data-lucide="mail" class="w-3.5 h-3.5"></i>

            Correo

        </span>


    </div>


</div>





{{-- TIPO DE GESTIÓN --}}

<div class="bg-card rounded-2xl border border-border overflow-hidden">


<div class="px-6 py-4 border-b border-border flex items-center gap-3">


<span 
class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center"
>
1
</span>


<h2 class="text-sm font-semibold text-foreground">
Tipo de gestión
</h2>


</div>



<div class="px-6 py-5">


<div 
class="inline-flex items-center gap-3 px-4 py-3.5 rounded-xl border-2 border-primary bg-primary/5"
>


<div 
class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"
>

<i 
data-lucide="file-text" 
class="w-4 h-4 text-primary"
></i>

</div>



<div class="mr-3">


<p class="text-sm font-semibold text-primary">

{{ $tipoAutorizacion->nombre_visual ?? 'Pase menor a 24 horas' }}

</p>


<p class="text-xs text-muted-foreground mt-0.5">

Solicitud de acceso temporal de corta duración

</p>


</div>




<div 
class="w-4 h-4 rounded-full border-2 border-primary bg-primary flex items-center justify-center"
>

<div class="w-1.5 h-1.5 rounded-full bg-white"></div>

</div>


</div>


</div>


</div>






{{-- INFORMACIÓN DOCUMENTO --}}

@include('memorandos.partials.informacion_documento')






{{-- FORMULARIO AUTORIZACIÓN --}}

@include('memorandos.formularios.autorizacion')







{{-- OBSERVACIONES --}}

@include('memorandos.partials.observaciones')




</div>





{{-- BOTÓN ENVÍO --}}

<div class="max-w-[1300px] mx-auto px-6 py-10 flex justify-end">


<button
    id="btnEnviar"
    type="submit"
    class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-medium hover:opacity-90 transition"
>


<i data-lucide="send" class="w-4 h-4"></i>


<span>
Enviar solicitud
</span>


</button>



</div>



</form>



</div>






{{-- MODAL RESULTADO --}}


<div 
id="modalResultado"
class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm"
>


<div 
class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
>



<div class="flex items-center gap-3 mb-4">


<div 
id="modalIcono"
class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center"
>


<i 
data-lucide="check-circle"
class="w-5 h-5 text-green-600"
></i>


</div>



<div>

<h3 
id="modalTitulo"
class="font-semibold text-lg"
>
Solicitud enviada
</h3>


<p 
id="modalMensaje"
class="text-sm text-muted-foreground"
>
La solicitud del pase menor a 24 horas fue enviada correctamente.
</p>


</div>



</div>




<div class="flex justify-end">


<button
type="button"
id="cerrarModal"
class="px-5 py-2 rounded-xl bg-primary text-white text-sm"
>

Aceptar

</button>


</div>



</div>


</div>

<script src="{{ asset('js/autorizacion.js') }}"></script>
<script src="{{ asset('js/pase_temporal.js') }}"></script>


@endsection