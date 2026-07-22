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
Verifica tu correo
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
        border: 0;
        display: block;
    }

    @media only screen and (max-width: 620px) {

        .email-wrapper {
            width: 100% !important;
        }

        .email-card {
            border-radius: 0 !important;
        }

        .email-padding {
            padding-left: 24px !important;
            padding-right: 24px !important;
        }

        .email-title {
            font-size: 25px !important;
            line-height: 32px !important;
        }

        .verification-code {
            font-size: 30px !important;
            letter-spacing: 8px !important;
        }

        .account-column {
            display: block !important;
            width: 100% !important;
        }

        .expiration-column {
            display: block !important;
            width: 100% !important;
            padding-top: 0 !important;
            text-align: left !important;
        }

    }

</style>

</head>


<body style="
    margin: 0;
    padding: 0;
    background-color: #f1f5f9;
    color: #0f172a;
    font-family: Arial, Helvetica, sans-serif;
">


{{-- Texto previo de la bandeja de entrada --}}

<div style="
    display: none;
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    color: transparent;
">
Tu código para verificar la cuenta del Portal de Gestiones TI.
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


{{-- Marca --}}

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


{{-- Contenido principal --}}

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
Verificación de cuenta
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
Confirma tu correo
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
Recibimos una solicitud para crear tu cuenta en el Portal de Gestiones TI.
Utiliza el código que aparece a continuación para confirmar tu correo.
</p>

</td>

</tr>

</table>

</td>

</tr>


{{-- Información de la cuenta --}}

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
    style="padding: 17px 18px;"
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
Correo que se verificará
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
{{ $minutosExpiracion ?? 10 }} minutos
</p>

</td>

</tr>

</table>

</td>

</tr>


{{-- Código de verificación --}}

<tr>

<td
    class="email-padding"
    style="
        padding: 30px 42px 14px;
    "
>

<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 14px;
    "
>

<tr>

<td
    align="center"
    style="
        padding: 25px 18px 10px;
    "
>

<p style="
    margin: 0;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.8px;
    line-height: 18px;
    text-transform: uppercase;
">
Tu código de verificación
</p>

</td>

</tr>


<tr>

<td
    align="center"
    style="
        padding: 0 18px 26px;
    "
>

<p
    class="verification-code"
    style="
        margin: 0;
        color: #0f172a;
        font-family: Consolas, Monaco, 'Courier New', monospace;
        font-size: 36px;
        font-weight: 700;
        letter-spacing: 12px;
        line-height: 48px;
        text-align: center;
    "
>
{{ $codigo }}
</p>

</td>

</tr>

</table>

</td>

</tr>


{{-- Instrucción --}}

<tr>

<td
    align="center"
    class="email-padding"
    style="
        padding: 2px 42px 28px;
    "
>

<p style="
    margin: 0;
    color: #64748b;
    font-size: 12px;
    line-height: 19px;
">
Regresa al Portal TI e introduce este código para completar tu registro.
</p>

</td>

</tr>


{{-- Información de seguridad --}}

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
        border: 0;
        outline: 0;
        background-color: transparent;
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
Protege este código
</p>

<p style="
    margin: 5px 0 0;
    color: #a16207;
    font-size: 12px;
    line-height: 19px;
">
No lo compartas con otras personas. El código puede utilizarse una sola vez
y vencerá automáticamente después de
5 minutos.
</p>

</td>

</tr>

</table>

</td>

</tr>


{{-- Registro no reconocido --}}

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
¿No realizaste este registro?
</p>

<p style="
    margin: 5px 0 0;
    color: #64748b;
    font-size: 12px;
    line-height: 19px;
">
Puedes ignorar este mensaje. La cuenta permanecerá sin verificar
y el código vencerá automáticamente.
</p>

</td>

</tr>


{{-- Footer --}}

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
No compartas códigos de verificación.
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


</table>

</td>

</tr>

</table>

</body>

</html>