<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="color-scheme"
    content="light only"
>

<title>
Acceso al Portal TI
</title>

<style>

    body {
        margin: 0 !important;
        padding: 0 !important;
        background-color: #f1f5f9;
    }

    table {
        border-collapse: collapse;
        border-spacing: 0;
    }

    img {
        display: block;
        border: 0;
    }

    a {
        text-decoration: none;
    }

    @media only screen and (max-width: 620px) {

        .email-wrapper {
            width: 100% !important;
        }

        .email-card {
            border-radius: 0 !important;
        }

        .email-padding {
            padding-right: 24px !important;
            padding-left: 24px !important;
        }

        .email-title {
            font-size: 25px !important;
            line-height: 32px !important;
        }

        .email-button {
            display: block !important;
            width: auto !important;
        }

        .account-column,
        .expiration-column {
            display: block !important;
            width: auto !important;
            text-align: left !important;
        }

        .expiration-column {
            padding-top: 0 !important;
        }

    }

</style>

</head>


<body style="
    margin: 0;
    padding: 0;
    color: #0f172a;
    background-color: #f1f5f9;
    font-family: Arial, Helvetica, sans-serif;
">


{{-- Texto previo mostrado en la bandeja de entrada --}}

<div style="
    display: none;
    max-height: 0;
    overflow: hidden;
    color: transparent;
    opacity: 0;
">
Tu enlace seguro para acceder al Portal de Gestiones TI.
</div>


<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="background-color: #f1f5f9;"
>

<tr>

<td
    align="center"
    style="padding: 32px 16px;"
>


{{-- CONTENEDOR PRINCIPAL --}}

<table
    role="presentation"
    width="600"
    cellpadding="0"
    cellspacing="0"
    border="0"
    class="email-wrapper email-card"
    style="
        width: 100%;
        max-width: 600px;
        overflow: hidden;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.08);
    "
>


{{-- Línea superior --}}

<tr>

<td
    style="
        height: 5px;
        background-color: #2563eb;
        font-size: 0;
        line-height: 0;
    "
>
&nbsp;
</td>

</tr>


{{-- MARCA --}}

<tr>

<td
    class="email-padding"
    style="
        padding: 25px 42px;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    "
>

<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
>

<tr>

<td
    valign="middle"
    style="width: 90px;"
>

<img
    src="https://upload.wikimedia.org/wikipedia/commons/4/4e/Televicentro_HN_logo_2020.png"
    width="76"
    alt="Televicentro"
    style="
        width: 76px;
        height: auto;
    "
>

</td>


<td
    valign="middle"
    style="
        padding-left: 16px;
        border-left: 1px solid #dbe3ee;
    "
>

<p style="
    margin: 0;
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
    line-height: 20px;
">
Portal de Gestiones TI
</p>

<p style="
    margin: 3px 0 0;
    color: #64748b;
    font-size: 12px;
    line-height: 18px;
">
Tecnologías e Información
</p>

</td>

</tr>

</table>

</td>

</tr>


{{-- CONTENIDO PRINCIPAL --}}

<tr>

<td
    class="email-padding"
    style="
        padding: 42px 42px 20px;
    "
>

<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
>

<tr>

<td>

<p style="
    margin: 24px 0 0;
    color: #2563eb;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.4px;
    line-height: 18px;
    text-transform: uppercase;
">
Solicitud de acceso
</p>


<h1
    class="email-title"
    style="
        margin: 8px 0 0;
        color: #0f172a;
        font-size: 30px;
        font-weight: 700;
        letter-spacing: -0.7px;
        line-height: 38px;
    "
>
Inicia sesión en el Portal TI
</h1>


<p style="
    margin: 20px 0 0;
    color: #334155;
    font-size: 15px;
    line-height: 25px;
">
Hola, <strong>{{ $nombre }}</strong>.
</p>


<p style="
    margin: 10px 0 0;
    color: #475569;
    font-size: 14px;
    line-height: 24px;
">
Recibimos una solicitud para iniciar sesión en el Portal de Gestiones TI.
Para continuar, utiliza el botón que aparece a continuación.
</p>

</td>

</tr>

</table>

</td>

</tr>


{{-- INFORMACIÓN DE LA CUENTA --}}

<tr>

<td
    class="email-padding"
    style="
        padding: 10px 42px 0;
    "
>

<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    "
>

<tr>

<td
    class="account-column"
    style="
        padding: 17px 18px;
    "
>

<p style="
    margin: 0;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.7px;
    line-height: 17px;
    text-transform: uppercase;
">
Cuenta solicitada
</p>

<p style="
    margin: 5px 0 0;
    color: #0f172a;
    font-size: 14px;
    font-weight: 600;
    line-height: 21px;
    word-break: break-all;
">
{{ $correo }}
</p>

</td>


<td
    align="right"
    valign="middle"
    class="expiration-column"
    style="
        padding: 17px 18px;
        white-space: nowrap;
    "
>

<p style="
    margin: 0;
    color: #64748b;
    font-size: 11px;
    line-height: 17px;
">
Válido por
</p>

<p style="
    margin: 3px 0 0;
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
    line-height: 20px;
">
5 minutos
</p>

</td>

</tr>

</table>

</td>

</tr>


{{-- BOTÓN PRINCIPAL --}}

<tr>

<td
    align="center"
    class="email-padding"
    style="
        padding: 30px 42px 12px;
    "
>

<a
    href="{{ $url }}"
    class="email-button"
    style="
        display: inline-block;
        min-width: 210px;
        padding: 15px 28px;
        color: #ffffff;
        background-color: #2563eb;
        border: 1px solid #2563eb;
        border-radius: 10px;
        box-shadow: 0 7px 16px rgba(37, 99, 235, 0.22);
        font-size: 14px;
        font-weight: 700;
        line-height: 20px;
        text-align: center;
        text-decoration: none;
    "
>
Acceder al Portal TI
</a>

</td>

</tr>


<tr>

<td
    align="center"
    class="email-padding"
    style="
        padding: 4px 42px 28px;
    "
>

<p style="
    margin: 0;
    color: #64748b;
    font-size: 12px;
    line-height: 19px;
">
El enlace puede utilizarse una sola vez.
</p>

</td>

</tr>


{{-- ENLACE ALTERNATIVO --}}

<tr>

<td
    class="email-padding"
    style="
        padding: 0 42px 28px;
    "
>

<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    "
>

<tr>

<td style="padding: 17px 18px;">

<p style="
    margin: 0;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    line-height: 18px;
">
¿El botón no funciona?
</p>

<p style="
    margin: 6px 0 0;
    color: #64748b;
    font-size: 12px;
    line-height: 19px;
">
Copia y pega el siguiente enlace en tu navegador:
</p>


<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        margin-top: 10px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    "
>

<tr>

<td
    style="
        padding: 10px 12px;
        font-family: Consolas, Monaco, monospace;
        font-size: 11px;
        line-height: 18px;
        word-break: break-all;
    "
>

<a
    href="{{ $url }}"
    style="
        color: #2563eb;
        text-decoration: none;
        word-break: break-all;
    "
>
{{ $url }}
</a>

</td>

</tr>

</table>

</td>

</tr>

</table>

</td>

</tr>


{{-- AVISO DE SEGURIDAD --}}

<tr>

<td
    class="email-padding"
    style="
        padding: 0 42px 38px;
    "
>

<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background-color: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
    "
>

<tr>

<td
    valign="top"
    style="
        width: 24px;
        padding: 18px 0 17px 18px;
        background-color: transparent;
        border: 0;
        font-size: 0;
        line-height: 0;
    "
>

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="20"
    height="20"
    viewBox="0 0 24 24"
    fill="none"
    stroke="#b45309"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    style="
        display: block;
        margin: 0;
        padding: 0;
        background-color: transparent;
        border: 0;
        outline: 0;
    "
>
    <path
        d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"
    ></path>

    <path d="M12 9v4"></path>

    <path d="M12 17h.01"></path>
</svg>

</td>


<td
    valign="top"
    style="
        padding: 17px 18px 17px 10px;
    "
>

<p style="
    margin: 0;
    color: #92400e;
    font-size: 12px;
    font-weight: 700;
    line-height: 18px;
">
Protege este enlace
</p>

<p style="
    margin: 5px 0 0;
    color: #a16207;
    font-size: 12px;
    line-height: 19px;
">
No lo compartas con otras personas. Quien tenga acceso al enlace
podría iniciar sesión en tu cuenta mientras continúe vigente.
</p>

</td>

</tr>

</table>

</td>

</tr>


{{-- SOLICITUD NO RECONOCIDA --}}

<tr>

<td
    class="email-padding"
    style="
        padding: 24px 42px;
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
    "
>

<p style="
    margin: 0;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    line-height: 19px;
">
¿No solicitaste este acceso?
</p>

<p style="
    margin: 5px 0 0;
    color: #64748b;
    font-size: 12px;
    line-height: 19px;
">
Puedes ignorar este mensaje. El enlace vencerá automáticamente y no
se iniciará ninguna sesión mientras nadie lo utilice.
</p>

</td>

</tr>


{{-- FOOTER --}}

<tr>

<td
    align="center"
    class="email-padding"
    style="
        padding: 25px 42px;
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
    "
>

<p style="
    margin: 0;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    line-height: 18px;
">
Portal de Gestiones de Tecnología e Información
</p>

<p style="
    margin: 6px 0 0;
    color: #64748b;
    font-size: 11px;
    line-height: 17px;
">
Mensaje automático generado por la plataforma.
No compartas enlaces de acceso.
</p>

<p style="
    margin: 14px 0 0;
    color: #94a3b8;
    font-size: 10px;
    line-height: 16px;
">
© {{ date('Y') }} Televicentro
</p>

</td>

</tr>


{{-- CIERRE DEL CONTENEDOR PRINCIPAL --}}

</table>

</td>

</tr>

</table>

</body>

</html>