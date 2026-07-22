<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Nueva solicitud de servicio
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
Nueva solicitud de servicio
</h1>

<p>
El Portal TI recibió una nueva gestión que requiere revisión.
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


{{-- ESTADO --}}

<div class="email-section">

<h2>
Estado de la gestión
</h2>

<div class="description-box status-box">

<strong>
Pendiente de revisión
</strong>

<br><br>

La solicitud fue registrada correctamente. Revisar
la información proporcionada y continuar con el proceso correspondiente.

</div>

</div>


{{-- INFORMACIÓN GENERAL --}}

<div class="email-section">

<h2>
Información general
</h2>

@php

    $categorias = [

        'computadora' =>
            'Computadora o accesorios',

        'programa' =>
            'Instalar un programa',

        'acceso' =>
            'Solicitar un acceso',

        'vpn' =>
            'VPN / Acceso remoto',

        'impresora' =>
            'Impresoras',

        'cuenta' =>
            'Cuenta o contraseña',

        'cambio' =>
            'Cambio o configuración de equipo',

        'otra' =>
            'Otra solicitud',

    ];

@endphp

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
{{ $solicitud->created_at
    ? $solicitud->created_at
        ->timezone(config('app.timezone'))
        ->format('d/m/Y H:i')
    : now()->format('d/m/Y H:i') }}
</td>

</tr>


<tr>

<td>
Categoría
</td>

<td>
{{ $categorias[$solicitud->categoria]
    ?? $solicitud->categoria
    ?? 'N/A' }}
</td>

</tr>


<tr>

<td>
Asunto
</td>

<td>
{{ $solicitud->asunto ?? 'N/A' }}
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

{{ $solicitud->descripcion ?? 'N/A' }}

</div>

</div>


{{-- INFORMACIÓN ADICIONAL --}}

@if(
    !empty($solicitud->datos_extra)
    && is_array($solicitud->datos_extra)
)

<div class="email-section">

<h2>
Información adicional
</h2>

<table>

@foreach($solicitud->datos_extra as $campo => $valor)

<tr>

<td>
{{ ucfirst(
    str_replace(
        '_',
        ' ',
        $campo
    )
) }}
</td>

<td>

@if(is_array($valor))

    {{ implode(
        ', ',
        array_map(
            fn ($item) => is_scalar($item)
                ? (string) $item
                : json_encode(
                    $item,
                    JSON_UNESCAPED_UNICODE
                ),
            $valor
        )
    ) }}

@elseif(is_bool($valor))

    {{ $valor ? 'Sí' : 'No' }}

@else

    {{ $valor ?: 'N/A' }}

@endif

</td>

</tr>

@endforeach

</table>

</div>

@endif


{{-- ATENCIÓN PARA HELPDESK --}}

<div class="email-section">

<h2>
Atención requerida por Helpdesk
</h2>

<div class="description-box helpdesk-box">

Revise la categoría, descripción e información adicional
proporcionada por el solicitante.

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