<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Nueva incidencia</title>

</head>


<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:30px;">


<table width="100%" cellpadding="0" cellspacing="0">

<tr>

<td align="center">


<table 
width="600"
style="
background:white;
border-radius:12px;
padding:30px;
border:1px solid #e5e5e5;
">


<tr>

<td>


<h2 style="
margin:0;
color:#11406A;
font-size:22px;
">
Nueva incidencia registrada
</h2>


<p style="
color:#666;
margin-top:8px;
">
El Portal TI recibió una nueva solicitud de soporte.
</p>



<hr style="border:none;border-top:1px solid #eee;">





<h3 style="color:#333;">
Información general
</h3>


<table width="100%" cellpadding="6">


<tr>

<td>
<strong>Código:</strong>
</td>

<td>
{{ $incidencia->codigo }}
</td>

</tr>



<tr>

<td>
<strong>Usuario:</strong>
</td>

<td>
{{ $incidencia->usuario->nombre ?? 'N/A' }}
</td>

</tr>



<tr>

<td>
<strong>Título:</strong>
</td>

<td>
{{ $incidencia->titulo }}
</td>

</tr>



<tr>

<td>
<strong>Prioridad:</strong>
</td>

<td>
{{ $incidencia->prioridad }}
</td>

</tr>



<tr>

<td>
<strong>Estado:</strong>
</td>

<td>
{{ $incidencia->estado }}
</td>

</tr>



</table>






<h3 style="color:#333;margin-top:25px;">
Descripción del problema
</h3>


<div style="
background:#f8f9fa;
padding:15px;
border-radius:8px;
">

{{ $incidencia->descripcion }}

</div>







<h3 style="color:#333;margin-top:25px;">
Información adicional
</h3>


<table width="100%" cellpadding="6">


<tr>

<td>
<strong>Equipo:</strong>
</td>

<td>
{{ $incidencia->equipo ?? 'No especificado' }}
</td>

</tr>


<tr>

<td>
<strong>Ubicación:</strong>
</td>

<td>
{{ $incidencia->ubicacion ?? 'No especificado' }}
</td>

</tr>



<tr>

<td>
<strong>Tiempo del problema:</strong>
</td>

<td>
{{ $incidencia->tiempo_problema ?? 'No especificado' }}
</td>

</tr>



<tr>

<td>
<strong>Afectación:</strong>
</td>

<td>
{{ $incidencia->afectacion ?? 'No especificado' }}
</td>

</tr>


</table>








@if($incidencia->archivos->count())


<h3 style="color:#333;margin-top:25px;">
Evidencias analizadas
</h3>



@foreach($incidencia->archivos as $archivo)


<div style="
background:#f8f9fa;
padding:15px;
border-radius:8px;
margin-bottom:15px;
">


<p style="margin:0 0 8px 0;">

<strong>
Archivo:
</strong>

{{ $archivo->nombre_original }}

</p>





@if($archivo->texto_ocr)


<p style="
margin-top:10px;
color:#555;
">

<strong>
Texto detectado por OCR:
</strong>

</p>



<div style="
background:white;
border:1px solid #ddd;
padding:12px;
border-radius:6px;
white-space:pre-line;
">


{{ $archivo->texto_ocr }}


</div>



@else


<p style="
color:#999;
font-size:13px;
">

No se detectó texto en esta imagen.

</p>



@endif



</div>



@endforeach



@endif








<p style="
margin-top:30px;
font-size:13px;
color:#777;
">


Este correo fue generado automáticamente por el Portal TI.


</p>



</td>

</tr>


</table>


</td>

</tr>


</table>


</body>

</html>