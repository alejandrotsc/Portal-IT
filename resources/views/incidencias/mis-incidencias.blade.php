@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-background">


<main class="max-w-5xl mx-auto px-6 py-8 space-y-8">



{{-- HEADER --}}
<section class="flex items-start justify-between gap-4">


<div>

<h1 class="text-xl font-semibold text-foreground">
    Mis incidencias
</h1>


<p class="text-sm text-muted-foreground mt-1">
    Consulta los reportes que has enviado al equipo TI.


</div>





<a
href="{{ route('incidencias.create') }}"
class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-medium hover:opacity-90 transition">


<i data-lucide="plus" class="w-4 h-4"></i>


Reportar incidencia


</a>


</section>








{{-- RESUMEN --}}
<section class="grid grid-cols-1 sm:grid-cols-3 gap-4">



<div class="bg-card border border-border rounded-2xl p-5">


<div class="flex items-center gap-3">


<div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">

<i data-lucide="clipboard-list" class="w-5 h-5 text-primary"></i>

</div>


<div>

<p class="text-xs text-muted-foreground uppercase tracking-widest">
Reportes enviados
</p>


<p class="text-xl font-semibold text-foreground">
{{ $incidencias->count() }}
</p>


</div>


</div>


</div>





<div class="bg-card border border-border rounded-2xl p-5">


<div class="flex items-center gap-3">


<div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">

<i data-lucide="image" class="w-5 h-5 text-primary"></i>

</div>


<div>

<p class="text-xs text-muted-foreground uppercase tracking-widest">
Evidencias
</p>


<p class="text-xl font-semibold text-foreground">

{{ $incidencias->sum(fn($i)=>$i->archivos->count()) }}

</p>


</div>


</div>


</div>





<div class="bg-card border border-border rounded-2xl p-5">


<div class="flex items-center gap-3">


<div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">

<i data-lucide="clock" class="w-5 h-5 text-primary"></i>

</div>


<div>

<p class="text-xs text-muted-foreground uppercase tracking-widest">
Último reporte
</p>


<p class="text-sm font-semibold text-foreground">


@if($incidencias->first())

{{ $incidencias->first()->created_at->format('d/m/Y') }}

@else

Sin registros

@endif


</p>


</div>


</div>


</div>




</section>









{{-- LISTADO --}}

<section class="space-y-4">


<div class="flex items-center justify-between">


<h2 class="text-sm font-semibold text-foreground uppercase tracking-widest">

Historial de incidencias

</h2>


</div>






@forelse($incidencias as $incidencia)



<div class="bg-card border border-border rounded-2xl p-5 hover:border-primary/40 transition">



<div class="flex items-start justify-between gap-4">



<div class="space-y-2">


<div class="flex items-center gap-2">


<span class="text-xs font-semibold text-primary">

{{ $incidencia->codigo }}

</span>


</div>



<h3 class="font-semibold text-foreground">

{{ $incidencia->titulo }}

</h3>



<p class="text-sm text-muted-foreground leading-relaxed">

{{ Str::limit($incidencia->descripcion,150) }}

</p>


</div>





<div class="text-right shrink-0">


<p class="text-xs text-muted-foreground">

{{ $incidencia->created_at->format('d/m/Y') }}

</p>


<p class="text-xs text-muted-foreground">

{{ $incidencia->created_at->format('H:i') }}

</p>


</div>



</div>







<div class="mt-5 pt-4 border-t border-border flex flex-wrap items-center justify-between gap-3">



<div class="flex flex-wrap gap-4 text-xs text-muted-foreground">



@if($incidencia->equipo)

<span class="inline-flex items-center gap-1">

<i data-lucide="laptop" class="w-3.5 h-3.5"></i>

{{ $incidencia->equipo }}

</span>

@endif





@if($incidencia->archivos->count())

<span class="inline-flex items-center gap-1">

<i data-lucide="paperclip" class="w-3.5 h-3.5"></i>

{{ $incidencia->archivos->count() }} evidencia(s)

</span>

@endif







</div>





<a
href="{{ route('incidencias.show',$incidencia) }}"
class="inline-flex items-center gap-2 text-sm text-primary font-medium hover:underline">


Ver detalle


<i data-lucide="arrow-right" class="w-4 h-4"></i>


</a>



</div>




</div>




@empty




<div class="bg-card border border-border rounded-2xl p-10 text-center">


<i data-lucide="inbox" class="w-10 h-10 mx-auto text-muted-foreground"></i>


<h3 class="mt-4 font-semibold text-foreground">

No tienes incidencias registradas

</h3>


<p class="text-sm text-muted-foreground mt-2">

Cuando reportes un problema aparecerá aquí para darle seguimiento.

</p>



<a
href="{{ route('incidencias.create') }}"
class="inline-flex items-center gap-2 mt-5 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-medium">


<i data-lucide="plus" class="w-4 h-4"></i>


Crear primera incidencia


</a>


</div>



@endforelse




</section>






</main>


</div>





<script>

lucide.createIcons();

</script>


@endsection