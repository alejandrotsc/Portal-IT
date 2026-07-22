<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Nueva incidencia registrada
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
    alt="Televicentro"
>

<h1>
Nueva incidencia registrada
</h1>

<p>
El Portal TI recibió un nuevo reporte que requiere revisión.
</p>

</div>


{{-- IDENTIFICADOR --}}

<div class="ticket-card">

<span>
Código de incidencia
</span>

<strong>
{{ $incidencia->codigo }}
</strong>

</div>


{{-- ESTADO --}}

<div class="email-section">

<h2>
Estado de la incidencia
</h2>

<div class="description-box status-box">

<strong>
Pendiente de revisión
</strong>

<br><br>

La incidencia fue registrada correctamente. Revisar
la información proporcionada y continuar con la atención correspondiente.

</div>

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
{{ $incidencia->usuario->nombre ?? 'N/A' }}
</td>

</tr>


<tr>

<td>
Correo
</td>

<td>
{{ $incidencia->usuario->correo ?? 'N/A' }}
</td>

</tr>


<tr>

<td>
Fecha del reporte
</td>

<td>
{{ $incidencia->created_at
    ? $incidencia->created_at
        ->timezone(config('app.timezone'))
        ->format('d/m/Y H:i')
    : now()->format('d/m/Y H:i') }}
</td>

</tr>


<tr>

<td>
Título
</td>

<td>
{{ $incidencia->titulo ?? 'N/A' }}
</td>

</tr>

</table>

</div>


{{-- DESCRIPCIÓN --}}

<div class="email-section">

<h2>
Descripción del problema
</h2>

<div class="description-box">

{{ $incidencia->descripcion ?? 'N/A' }}

</div>

</div>


{{-- INFORMACIÓN ADICIONAL --}}

<div class="email-section">

<h2>
Información adicional
</h2>

@php

    $tiemposProblema = [

        'hoy' =>
            'Hoy',

        'ayer' =>
            'Ayer',

        'varios_dias' =>
            'Hace varios días',

    ];


    $afectaciones = [

        'solo' =>
            'Solo a mí',

        'varios' =>
            'A varias personas',

        'todos' =>
            'A toda el área',

    ];

@endphp

<table>

<tr>

<td>
Equipo
</td>

<td>
{{ $incidencia->equipo ?? 'No especificado' }}
</td>

</tr>


<tr>

<td>
Ubicación
</td>

<td>
{{ $incidencia->ubicacion ?? 'No especificada' }}
</td>

</tr>


<tr>

<td>
Tiempo del problema
</td>

<td>
{{ $tiemposProblema[
    $incidencia->tiempo_problema
] ?? 'No indicado' }}
</td>

</tr>


<tr>

<td>
Afectación
</td>

<td>
{{ $afectaciones[
    $incidencia->afectacion
] ?? 'No indicada' }}
</td>

</tr>

</table>

</div>


{{-- EVIDENCIAS --}}

@if(
    $incidencia->relationLoaded('archivos')
    && $incidencia->archivos->isNotEmpty()
)

<div class="email-section">

<h2>
Evidencias adjuntas
</h2>

@foreach($incidencia->archivos as $archivo)

<div class="attachment-box">

<p>

<strong>
Archivo:
</strong>

{{ $archivo->nombre_original ?? 'Archivo adjunto' }}

</p>


@if(!empty($archivo->texto_ocr))

<div class="ocr-box">

<strong>
Texto identificado automáticamente
</strong>

<p>
{{ $archivo->texto_ocr }}
</p>

</div>

@endif

</div>

@endforeach

</div>

@endif


{{-- ATENCIÓN PARA HELPDESK --}}

<div class="email-section">

<h2>
Atención requerida por Helpdesk
</h2>

<div class="description-box helpdesk-box">

Revise la descripción, el equipo, la ubicación, el nivel de afectación
y las evidencias proporcionadas por el usuario.

<br><br>

Si necesita más información, utilice el correo del usuario
indicado en esta notificación.

</div>

</div>


{{-- FOOTER --}}

<div class="email-footer">

<p>
Portal de Gestiones de Tecnología e Información
</p>

<p>
Notificación interna enviada exclusivamente a Helpdesk.
</p>

<p>
© {{ date('Y') }} Televicentro
</p>

</div>


</div>

</body>

</html>