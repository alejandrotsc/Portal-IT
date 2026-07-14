@extends('layouts.app')


@section('content')


<div class="min-h-screen bg-background">


<main class="max-w-7xl mx-auto px-6 py-8 space-y-8">



{{-- ===========================
 ESTADO DEL SOPORTE
=========================== --}}


<section class="grid grid-cols-1 lg:grid-cols-3 gap-6">


<div class="lg:col-span-2 bg-card border border-border rounded-2xl p-6">


<div class="flex justify-between">


<div>

<p class="text-xs uppercase tracking-widest text-muted-foreground">
Mi turno actual
</p>


<h2 class="text-xl font-semibold mt-2">
En servicio
</h2>


</div>


<span class="flex items-center gap-2 text-xs text-emerald-600 font-medium">

<span class="w-2 h-2 bg-emerald-500 rounded-full"></span>

Disponible

</span>


</div>




<div class="flex items-center gap-4 mt-6">


<div class="w-14 h-14 rounded-full bg-primary flex items-center justify-center">

<span class="text-white font-semibold">
RC
</span>

</div>


<div>

<h3 class="font-semibold">
Roberto Castillo
</h3>

<p class="text-sm text-muted-foreground">
Usuario TI · Soporte Nivel 1
</p>


</div>


</div>




<div class="grid grid-cols-2 gap-4 mt-6">


<div class="bg-muted rounded-xl p-4">

<div class="flex gap-2 items-center">

<i data-lucide="clock" class="w-4 h-4"></i>

<span class="text-xs text-muted-foreground">
Horario
</span>

</div>


<p class="text-sm font-medium mt-2">
09:00 - 18:00
</p>

</div>




<div class="bg-muted rounded-xl p-4">


<div class="flex gap-2 items-center">

<i data-lucide="calendar" class="w-4 h-4"></i>

<span class="text-xs text-muted-foreground">
Días
</span>

</div>


<p class="text-sm font-medium mt-2">
Lunes - Viernes
</p>


</div>



</div>



</div>





<div class="bg-card border border-border rounded-2xl p-6">


<div class="flex gap-2 items-center mb-5">

<i data-lucide="activity"
class="w-5 h-5 text-primary"></i>

<h3 class="font-semibold">
Actividad
</h3>


</div>



<div class="space-y-4">


<div class="flex justify-between">

<span class="text-sm text-muted-foreground">
Casos tomados
</span>

<strong>
8
</strong>

</div>



<div class="flex justify-between">

<span class="text-sm text-muted-foreground">
En atención
</span>

<strong>
3
</strong>

</div>



<div class="flex justify-between">

<span class="text-sm text-muted-foreground">
Resueltos hoy
</span>

<strong>
14
</strong>

</div>



</div>


</div>



</section>





{{-- ===========================
 TURNOS
=========================== --}}


<section class="grid lg:grid-cols-3 gap-6">


<div class="lg:col-span-2">


<div class="flex justify-between mb-4">

<h2 class="text-sm uppercase tracking-widest font-semibold">
Próximos turnos
</h2>


<button class="text-xs text-primary">
Ver calendario
</button>


</div>



<div class="bg-card border border-border rounded-2xl overflow-hidden">



@foreach([
[
'dia'=>'Miércoles 16 Julio',
'hora'=>'09:00 - 18:00',
'tipo'=>'Turno normal'
],
[
'dia'=>'Viernes 18 Julio',
'hora'=>'09:00 - 18:00',
'tipo'=>'Turno normal'
]

] as $turno)


<div class="px-6 py-5 border-b border-border flex justify-between">


<div>

<p class="text-sm font-medium">
{{$turno['dia']}}
</p>

<p class="text-xs text-muted-foreground">
{{$turno['hora']}}
</p>

</div>


<span class="text-xs px-3 py-1 rounded-lg bg-blue-50 text-blue-700">

{{$turno['tipo']}}

</span>


</div>


@endforeach


</div>


</div>





<div>


<h2 class="text-sm uppercase tracking-widest font-semibold mb-4">
Guardias disponibles
</h2>


<div class="bg-card border border-border rounded-2xl p-5">


<div class="flex gap-3">


<div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">

<i data-lucide="headphones"
class="w-5 h-5 text-violet-600"></i>

</div>


<div>

<p class="text-sm font-medium">
Sábado 19 Julio
</p>

<p class="text-xs text-muted-foreground">
09:00 - 18:00
</p>


</div>


</div>



<button
class="w-full mt-5 py-2 rounded-xl bg-primary text-white text-xs">

Tomar guardia

</button>



</div>



</div>


</section>









{{-- ===========================
 INCIDENCIAS DISPONIBLES
=========================== --}}


<section>


<div class="flex justify-between mb-4">


<h2 class="text-sm uppercase tracking-widest font-semibold">
Bandeja de incidencias
</h2>


<button class="text-xs text-primary">
Ver todas
</button>


</div>




<div class="bg-card border border-border rounded-2xl overflow-hidden">


@foreach([
[
'codigo'=>'INC-00125',
'titulo'=>'VPN no conecta',
'usuario'=>'María Alvarado',
'prioridad'=>'Alta'
],

[
'codigo'=>'INC-00126',
'titulo'=>'Error Outlook',
'usuario'=>'Carlos Mejía',
'prioridad'=>'Media'
]

] as $inc)


<div class="px-6 py-5 border-b border-border flex justify-between">


<div>


<p class="text-sm font-semibold">
{{$inc['codigo']}}
</p>


<p class="text-sm mt-1">
{{$inc['titulo']}}
</p>


<p class="text-xs text-muted-foreground">
{{$inc['usuario']}}
</p>


</div>



<div class="text-right">


<span class="text-xs px-3 py-1 rounded-lg bg-red-50 text-red-700">
{{$inc['prioridad']}}
</span>



<button
class="block text-xs text-primary mt-3">

Tomar caso

</button>



</div>


</div>


@endforeach


</div>


</section>








{{-- ===========================
 MIS CASOS
=========================== --}}


<section>


<h2 class="text-sm uppercase tracking-widest font-semibold mb-4">
Mis casos activos
</h2>



<div class="grid md:grid-cols-3 gap-5">



@foreach([
'INC-00120',
'INC-00121',
'INC-00122'

] as $caso)


<div class="bg-card border border-border rounded-2xl p-5">


<div class="flex items-center gap-3">


<i data-lucide="ticket"
class="w-5 h-5 text-primary"></i>


<div>

<p class="text-sm font-semibold">
{{$caso}}
</p>


<p class="text-xs text-muted-foreground">
En proceso
</p>


</div>


</div>


</div>


@endforeach


</div>


</section>









{{-- ===========================
 EQUIPO TI
=========================== --}}


<section>


<h2 class="text-sm uppercase tracking-widest font-semibold mb-4">
Equipo TI activo
</h2>



<div class="grid md:grid-cols-3 gap-5">


@foreach([
'Roberto Castillo',
'Laura Pérez',
'Carlos Mejía'

] as $persona)


<div class="bg-card border border-border rounded-2xl p-5">


<div class="flex gap-3 items-center">


<div class="w-10 h-10 rounded-full bg-muted flex items-center justify-center">

<i data-lucide="user"
class="w-5 h-5"></i>

</div>


<div>

<p class="text-sm font-semibold">
{{$persona}}
</p>


<p class="text-xs text-emerald-600">
Disponible
</p>


</div>


</div>


</div>


@endforeach


</div>


</section>








{{-- ===========================
 AVISOS
=========================== --}}


<section>


<h2 class="text-sm uppercase tracking-widest font-semibold mb-4">
Avisos TI
</h2>


<div class="bg-card border border-border rounded-2xl p-5">


<div class="flex gap-3">


<i data-lucide="triangle-alert"
class="w-5 h-5 text-amber-500"></i>


<div>


<p class="text-sm font-semibold">
Degradación VPN corporativa
</p>


<p class="text-xs text-muted-foreground">
Infraestructura continúa trabajando.
</p>


</div>


</div>


</div>


</section>



</main>


</div>



<script>
lucide.createIcons();
</script>


@endsection