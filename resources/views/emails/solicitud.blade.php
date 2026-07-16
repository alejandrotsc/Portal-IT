<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Nueva solicitud de servicio</title>

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
alt="TVC">

<h1>
Nueva solicitud de servicio
</h1>

<p>
El Portal TI recibió una nueva solicitud.
</p>

</div>




{{-- IDENTIFICADOR --}}

<div class="ticket-card">

<span>
Código de solicitud
</span>

<strong>
{{ $solicitud->folio }}
</strong>

</div>





{{-- INFORMACIÓN GENERAL --}}

<div class="email-section">

<h2>
Información general
</h2>


<table>


<tr>

<td>
Usuario
</td>

<td>
{{ $solicitud->usuario->nombre ?? 'N/A' }}
</td>

</tr>


<tr>

<td>
Correo
</td>

<td>
{{ $solicitud->usuario->correo ?? 'N/A' }}
</td>

</tr>


<tr>

<td>
Fecha de solicitud
</td>

<td>
{{ $solicitud->created_at->format('d/m/Y H:i') }}
</td>

</tr>



<tr>

<td>
Categoría
</td>

<td>
@php
$categorias=[
'computadora'=>'Computadora o accesorios',
'programa'=>'Instalar un programa',
'acceso'=>'Solicitar un acceso',
'vpn'=>'VPN / Acceso remoto',
'impresora'=>'Impresoras',
'cuenta'=>'Cuenta o contraseña',
'cambio'=>'Cambio o configuración de equipo',
'otra'=>'Otra solicitud'
];
@endphp

{{ $categorias[$solicitud->categoria] ?? $solicitud->categoria }}

</td>

</tr>



<tr>

<td>
Asunto
</td>

<td>
{{ $solicitud->asunto }}
</td>

</tr>


</table>


</div>








{{-- DESCRIPCIÓN --}}

<div class="email-section">


<h2>
Descripción de la solicitud
</h2>


<div class="description-box">

{{ $solicitud->descripcion }}

</div>


</div>








{{-- INFORMACIÓN ADICIONAL --}}

@if($solicitud->datos_extra)


<div class="email-section">


<h2>
Información adicional
</h2>


<table>


@foreach($solicitud->datos_extra as $campo=>$valor)


<tr>

<td>
{{ ucfirst(str_replace('_',' ',$campo)) }}
</td>


<td>
{{ $valor }}
</td>


</tr>


@endforeach


</table>


</div>


@endif

<div class="email-footer">

<p>
Portal TI · Mesa de ayuda tecnológica
</p>

</div>



</div>


</body>

</html>