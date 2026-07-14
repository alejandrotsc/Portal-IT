@extends('layouts.app')


@section('content')


<div class="min-h-screen bg-background">


<main class="max-w-5xl mx-auto px-6 py-8 space-y-8">



{{-- HEADER --}}

<section>


<div class="flex items-center justify-between">


<div>


<h1 class="text-xl font-semibold text-foreground">
Reporte de incidencia
</h1>


<p class="text-sm text-muted-foreground mt-1">
Describe el problema que estás presentando y el equipo TI te ayudará a solucionarlo.
</p>


</div>


</div>


</section>






<form
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

required

type="text"

name="titulo"

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

required

name="descripcion"

rows="5"

placeholder="Indica qué ocurrió, qué estabas intentando hacer y si aparece algún mensaje de error..."

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm resize-none focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">{{ old('descripcion') }}</textarea>


</div>








<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">



<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

¿Cuándo empezó el problema?

</label>


<select

name="tiempo_problema"

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm focus:outline-none focus:border-primary">


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

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm focus:outline-none focus:border-primary">


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

Evidencia

</h2>


</div>






<div class="px-6 py-5 space-y-5">



<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest">

Adjuntar capturas o archivos

</label>




<label

for="archivos"

class="flex flex-col items-center justify-center h-40 rounded-xl border border-dashed border-border hover:border-primary hover:bg-primary/5 cursor-pointer transition-all">


<i data-lucide="upload-cloud"
class="w-7 h-7 text-muted-foreground mb-3"></i>


<p class="text-sm text-muted-foreground">

Selecciona imágenes o documentos

</p>


<p class="text-xs text-muted-foreground mt-1">

PNG, JPG o PDF hasta 10MB

</p>



<input

id="archivos"

type="file"

name="archivos[]"

multiple

class="hidden">


</label>






<div class="flex gap-3 bg-muted/50 rounded-xl p-4">


<i data-lucide="info"
class="w-5 h-5 text-primary"></i>


<p class="text-xs text-muted-foreground leading-relaxed">

Las capturas de pantalla o mensajes de error ayudan al equipo TI a encontrar una solución más rápido.

</p>


</div>



</div>


</div>









{{-- INFORMACIÓN DEL EQUIPO --}}


<div class="bg-card rounded-2xl border border-border overflow-hidden">



<div class="px-6 py-4 border-b border-border flex items-center gap-3">


<span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
3
</span>



<h2 class="text-sm font-semibold text-foreground">

Información adicional

</h2>



</div>






<div class="px-6 py-5 space-y-5">





<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">



<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

Equipo relacionado

</label>


<input

type="text"

name="equipo"

placeholder="Ej: Laptop Dell, teléfono, impresora..."

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm focus:outline-none focus:border-primary">


</div>






<div>


<label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

Lugar donde ocurre

</label>


<input

type="text"

name="ubicacion"

placeholder="Ej: Oficina, sala, área..."

class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm focus:outline-none focus:border-primary">


</div>


</div>





</div>


</div>









{{-- BOTONES --}}


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

</script>


@endsection