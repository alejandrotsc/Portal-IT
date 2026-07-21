<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>
Solicitud de pase menor a 24 horas
</title>

<style>

{!! file_get_contents(public_path('css/incidencias.css')) !!}

</style>

</head>


<body>

<div class="email-container">


{{-- HEADER --}}

<div class="email-header">

<img 
src="https://upload.wikimedia.org/wikipedia/commons/4/4e/Televicentro_HN_logo_2020.png"
class="email-logo"
alt="TVC"
>


<h1>
Solicitud de pase menor a 24 horas
</h1>


<p>
El Portal TI recibió una nueva solicitud de autorización.
</p>


</div>




{{-- SOLICITANTE --}}

<div class="email-section">

<h2>
Información del solicitante
</h2>


<table>

<tr>
<td>
Usuario
</td>

<td>
{{ $remitenteName ?? 'N/A' }}
</td>
</tr>


<tr>
<td>
Correo
</td>

<td>
{{ $remitenteEmail ?? 'N/A' }}
</td>
</tr>


<tr>
<td>
Fecha solicitud
</td>

<td>
{{ now()->format('d/m/Y H:i') }}
</td>
</tr>


</table>

</div>







{{-- INFORMACIÓN DEL DOCUMENTO --}}

<div class="email-section">

<h2>
Información del documento
</h2>


<table>


<tr>
<td>
Para
</td>

<td>
{{ $datos['para_nombre'] ?? 'N/A' }}
</td>
</tr>



<tr>
<td>
CC
</td>

<td>
{{ $datos['cc_nombre'] ?? 'N/A' }}
</td>
</tr>



<tr>
<td>
De
</td>

<td>
{{ $datos['de_nombre'] ?? 'N/A' }}
</td>
</tr>



<tr>
<td>
Asunto
</td>

<td>
{{ $datos['asunto'] ?? 'N/A' }}
</td>
</tr>



<tr>
<td>
Fecha documento
</td>

<td>
{{ isset($datos['fecha_documento']) 
    ? \Carbon\Carbon::parse($datos['fecha_documento'])->format('d/m/Y') 
    : 'N/A' }}
</td>
</tr>



</table>

</div>







{{-- INFORMACIÓN DEL COLABORADOR --}}

<div class="email-section">


<h2>
Información del responsable del equipo
</h2>


<table>


<tr>

<td>
Responsable del equipo
</td>

<td>
{{ $datos['colaborador'] ?? 'N/A' }}
</td>

</tr>




<tr>

<td>
Cargo / Área
</td>

<td>
{{ $datos['cargo_area'] ?? 'N/A' }}
</td>

</tr>



</table>


</div>








{{-- MOTIVO --}}

<div class="email-section">


<h2>
Motivo de autorización
</h2>


<div class="description-box">

{{ $datos['motivo_autorizacion'] ?? 'N/A' }}

</div>


</div>








{{-- EQUIPOS --}}

@if(!empty($datos['equipos']))


<div class="email-section">


<h2>
Equipo(s) registrado(s)
</h2>



<table>


<tr>

<td>
Descripción
</td>

<td>
Marca
</td>

<td>
Modelo
</td>

<td>
Serie
</td>

<td>
Color
</td>

</tr>




@foreach($datos['equipos'] as $equipo)


<tr>


<td>
{{ $equipo['descripcion'] ?? '-' }}
</td>


<td>
{{ $equipo['marca'] ?? '-' }}
</td>


<td>
{{ $equipo['modelo'] ?? '-' }}
</td>


<td>
{{ $equipo['codigo'] ?? '-' }}
</td>


<td>
{{ $equipo['color'] ?? '-' }}
</td>


</tr>



@endforeach



</table>


</div>


@endif







{{-- OBSERVACIONES --}}

@if(!empty($datos['observaciones']))


<div class="email-section">


<h2>
Observaciones
</h2>


<div class="description-box">

{{ $datos['observaciones'] }}

</div>


</div>


@endif







{{-- FOOTER --}}

<div class="email-footer">


<p>
Portal de Gestiones de Tecnología e Infomación
</p>


</div>



</div>


</body>

</html>