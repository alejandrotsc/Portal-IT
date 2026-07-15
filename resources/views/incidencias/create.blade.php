@extends('layouts.app')


@section('content')


<div class="min-h-screen bg-background">


<main class="max-w-5xl mx-auto px-6 py-8 space-y-8">



{{-- HEADER --}}

<section>

<div>

<h1 class="text-xl font-semibold text-foreground">
Reporte de incidencia
</h1>


<p class="text-sm text-muted-foreground mt-1">
Describe el problema que estás presentando y adjunta evidencia para que TI pueda ayudarte.
</p>


</div>

</section>





<form
id="incidenciaForm"
action="{{ route('incidencias.store') }}"
method="POST"
enctype="multipart/form-data"
class="space-y-6">


@csrf







{{-- INFORMACIÓN DEL PROBLEMA --}}


<div class="bg-card rounded-2xl border border-border overflow-hidden">


<div class="px-6 py-4 border-b border-border flex items-center gap-3">

<span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
1
</span>


<h2 class="text-sm font-semibold text-foreground">
Información del problema
</h2>


</div>





<div class="px-6 py-5 space-y-5">



<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

¿Qué problema estás presentando?

<span class="text-primary">*</span>

</label>


<input

type="text"

name="titulo"

required

value="{{ old('titulo') }}"

placeholder="Ej: No puedo ingresar al correo corporativo"

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">


</div>








<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

Describe lo ocurrido

<span class="text-primary">*</span>

</label>



<textarea

name="descripcion"

required

rows="5"

placeholder="Indica qué ocurrió, qué estabas intentando hacer y si aparece algún mensaje..."

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm resize-none focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">{{ old('descripcion') }}</textarea>


</div>








<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">


<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

¿Cuándo empezó?


</label>


<select

name="tiempo_problema"

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm">


<option value="">
Seleccione
</option>


<option value="hoy">
Hoy
</option>


<option value="ayer">
Ayer
</option>


<option value="varios_dias">
Hace varios días
</option>


</select>


</div>





<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

¿A quién afecta?


</label>


<select

name="afectacion"

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm">


<option value="solo">
Solo a mí
</option>


<option value="varios">
A varias personas
</option>


<option value="todos">
A toda el área
</option>


</select>


</div>


</div>



</div>


</div>









{{-- EVIDENCIA --}}



<div class="bg-card rounded-2xl border border-border overflow-hidden">


<div class="px-6 py-4 border-b border-border flex items-center gap-3">


<span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
2
</span>


<h2 class="text-sm font-semibold text-foreground">
Evidencia del problema
</h2>


</div>






<div class="px-6 py-5 space-y-5">






<div

id="dropzone"

class="flex flex-col items-center justify-center h-48 rounded-xl border-2 border-dashed border-border hover:border-primary hover:bg-primary/5 cursor-pointer transition-all">


<i data-lucide="image"
class="w-8 h-8 text-muted-foreground mb-3"></i>


<p class="text-sm text-muted-foreground">

Arrastra tus capturas aquí

</p>


<p class="text-xs text-muted-foreground mt-1">

o haz clic para seleccionar imágenes

</p>


<p class="text-xs text-muted-foreground mt-2">

PNG, JPG, JPEG, WEBP - máximo 10MB por imagen

</p>



<input

id="archivos"

type="file"

name="archivos[]"

multiple

accept="image/png,image/jpeg,image/jpg,image/webp"

class="hidden">


</div>







{{-- PREVIEW --}}


<div

id="preview"

class="grid grid-cols-2 sm:grid-cols-4 gap-4">

</div>







<div class="flex gap-3 bg-muted/50 rounded-xl p-4">


<i data-lucide="info"
class="w-5 h-5 text-primary"></i>


<p class="text-xs text-muted-foreground">

Las imágenes serán analizadas automáticamente para extraer mensajes de error y ayudar al equipo TI.

</p>


</div>



</div>


</div>









{{-- INFORMACIÓN ADICIONAL --}}


<div class="bg-card rounded-2xl border border-border overflow-hidden">


<div class="px-6 py-4 border-b border-border flex items-center gap-3">


<span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
3
</span>


<h2 class="text-sm font-semibold text-foreground">

Información adicional

</h2>


</div>






<div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">


<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

Equipo relacionado

</label>


<input

type="text"

name="equipo"

placeholder="Ej: Laptop Dell, impresora..."

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm">


</div>





<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

Lugar donde ocurre

</label>


<input

type="text"

name="ubicacion"

placeholder="Ej: Oficina, Producción..."

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm">


</div>


</div>


</div>










<div class="flex justify-end gap-3">


<a

href="{{ route('dashboard') }}"

class="px-5 py-2.5 rounded-xl border border-border text-sm text-muted-foreground hover:bg-muted">

Cancelar

</a>




<button

type="submit"

class="px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-medium flex items-center gap-2">


<i data-lucide="send"
class="w-4 h-4"></i>


Enviar reporte


</button>



</div>





</form>


</main>


</div>







<script>

lucide.createIcons();



const dropzone = document.getElementById('dropzone');
const input = document.getElementById('archivos');
const preview = document.getElementById('preview');


let archivosSeleccionados = [];



dropzone.addEventListener('click',()=>{

    input.click();

});




input.addEventListener('change',(e)=>{

    agregarArchivos(e.target.files);

});





dropzone.addEventListener('dragover',(e)=>{

    e.preventDefault();

    dropzone.classList.add('border-primary');

});




dropzone.addEventListener('dragleave',()=>{

    dropzone.classList.remove('border-primary');

});




dropzone.addEventListener('drop',(e)=>{


    e.preventDefault();


    dropzone.classList.remove('border-primary');


    agregarArchivos(e.dataTransfer.files);


});





function agregarArchivos(files){


    [...files].forEach(file=>{


        if(!file.type.startsWith('image/'))
            return;



        if(file.size > 10485760)
            return;



        archivosSeleccionados.push(file);



    });



    actualizarPreview();


}





function actualizarPreview(){


    preview.innerHTML="";


    const dataTransfer = new DataTransfer();



    archivosSeleccionados.forEach((file,index)=>{


        dataTransfer.items.add(file);



        const reader = new FileReader();


        reader.onload=(e)=>{


            preview.innerHTML += `

            <div class="relative rounded-xl overflow-hidden border border-border">

                <img 
                src="${e.target.result}"
                class="w-full h-28 object-cover">


                <button

                type="button"

                onclick="eliminarArchivo(${index})"

                class="absolute top-1 right-1 bg-black/50 text-white rounded-full w-6 h-6 text-xs">

                ×

                </button>

            </div>

            `;


        }


        reader.readAsDataURL(file);



    });



    input.files=dataTransfer.files;



}





function eliminarArchivo(index){


    archivosSeleccionados.splice(index,1);

    actualizarPreview();


}


</script>


@endsection