@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background" x-data="{ imagenPreview:null }">

<main class="max-w-5xl mx-auto px-6 py-8 space-y-6">


{{-- CABECERA --}}
<div class="bg-card rounded-2xl border border-border overflow-hidden">


<div class="px-6 py-5 border-b border-border flex items-start justify-between gap-4">


<div class="space-y-2">


<span class="inline-flex items-center px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold">

{{ $incidencia->codigo }}

</span>



<h1 class="text-2xl font-semibold text-foreground leading-tight">

{{ $incidencia->titulo }}

</h1>



<p class="text-sm text-muted-foreground">

Reporte enviado desde el Portal TI el
{{ $incidencia->created_at->format('d/m/Y H:i') }}

</p>


</div>





<div class="flex flex-col items-end gap-3">


<div class="flex items-center gap-4">


<a
href="{{ route('mis-incidencias') }}"
class="text-xs text-primary hover:underline flex items-center gap-1">


<i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>

Mis incidencias


</a>




<a
href="{{ route('incidencias.create') }}"
class="text-xs text-primary hover:underline flex items-center gap-1">


<i data-lucide="plus" class="w-3.5 h-3.5"></i>

Nueva incidencia


</a>


</div>



</div>


</div>






<div class="p-6">


<div class="grid md:grid-cols-2 gap-6 text-sm">



<div>

<p class="text-muted-foreground flex items-center gap-2">

<i data-lucide="user" class="w-4 h-4"></i>

Reportado por

</p>


<p class="font-medium">

{{ $incidencia->usuario->nombre ?? 'N/A' }}

</p>

</div>




<div>

<p class="text-muted-foreground flex items-center gap-2">

<i data-lucide="calendar" class="w-4 h-4"></i>

Fecha

</p>


<p class="font-medium">

{{ $incidencia->created_at->format('d/m/Y H:i') }}

</p>

</div>




<div>

<p class="text-muted-foreground flex items-center gap-2">

<i data-lucide="monitor" class="w-4 h-4"></i>

Equipo afectado

</p>


<p class="font-medium">

{{ $incidencia->equipo ?? 'No especificado' }}

</p>

</div>




<div>

<p class="text-muted-foreground flex items-center gap-2">

<i data-lucide="map-pin" class="w-4 h-4"></i>

Ubicación

</p>


<p class="font-medium">

{{ $incidencia->ubicacion ?? 'No especificada' }}

</p>

</div>



</div>


</div>


</div>







{{-- DESCRIPCIÓN --}}

<div class="bg-card rounded-2xl border border-border overflow-hidden">


<div class="px-6 py-4 border-b border-border">

<h2 class="font-semibold text-foreground">

Descripción del problema

</h2>

</div>



<div class="p-6 text-sm leading-relaxed text-foreground">

{{ $incidencia->descripcion }}

</div>


</div>







{{-- DETALLES --}}

<div class="bg-card rounded-2xl border border-border overflow-hidden">


<div class="px-6 py-4 border-b border-border">

<h2 class="font-semibold text-foreground">

Detalles del reporte

</h2>

</div>



<div class="p-6 grid md:grid-cols-2 gap-6 text-sm">


<div>

<p class="text-muted-foreground">

¿Cuándo comenzó?

</p>


<p class="font-medium">

{{ $incidencia->tiempo_problema ?? 'No indicado' }}

</p>

</div>




<div>

<p class="text-muted-foreground">

¿A quién afecta?

</p>


<p class="font-medium">

{{ $incidencia->afectacion ?? 'No indicada' }}

</p>

</div>


</div>


</div>







{{-- EVIDENCIAS --}}

@if($incidencia->archivos->count())


<div class="bg-card rounded-2xl border border-border overflow-hidden">


<div class="px-6 py-4 border-b border-border">


<h2 class="font-semibold text-foreground">

Capturas adjuntas

</h2>


<p class="text-xs text-muted-foreground mt-1">

Haz clic en una imagen para verla completa.

</p>


</div>





<div class="p-6 grid md:grid-cols-3 gap-5">


@foreach($incidencia->archivos as $archivo)



<div class="border border-border rounded-xl overflow-hidden bg-background">



<img
src="{{ asset('storage/'.$archivo->ruta) }}"
class="w-full h-44 object-cover cursor-pointer hover:opacity-80 transition"
@click="imagenPreview='{{ asset('storage/'.$archivo->ruta) }}'">



<div class="p-4">


<p class="text-xs font-medium truncate">

{{ $archivo->nombre_original }}

</p>




@if($archivo->texto_ocr)


<details class="mt-3">


<summary class="cursor-pointer text-xs text-primary">

Texto identificado automáticamente

</summary>


<div class="mt-3 text-xs bg-muted rounded-lg p-3 whitespace-pre-line">

{{ $archivo->texto_ocr }}

</div>


</details>


@endif


</div>


</div>


@endforeach


</div>


</div>


@endif






<div class="flex justify-end">


<a
href="{{ route('mis-incidencias') }}"
class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-border text-sm font-medium hover:bg-muted transition">


<i data-lucide="arrow-left" class="w-4 h-4"></i>

Volver a mis incidencias


</a>


</div>



</main>







{{-- MODAL IMAGEN --}}

<div
x-show="imagenPreview"
x-transition
@click.self="imagenPreview=null"
class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
style="display:none;">


<button
@click="imagenPreview=null"
class="absolute top-6 right-8 text-white text-3xl">

×

</button>



<img
:src="imagenPreview"
class="max-h-[90vh] max-w-[90vw] rounded-2xl shadow-2xl">


</div>



</div>



<script>

lucide.createIcons();

</script>


@endsection