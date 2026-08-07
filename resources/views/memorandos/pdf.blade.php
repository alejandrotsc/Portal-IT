<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <style>

    @page {
        margin: 40px 50px 48px;
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN GENERAL
    |--------------------------------------------------------------------------
    */

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;

        font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;

        color: #202020;

        font-size: 10.5px;
        line-height: 1.48;
    }

    .documento {
        width: 100%;
    }


    /*
    |--------------------------------------------------------------------------
    | ENCABEZADO
    |--------------------------------------------------------------------------
    */

    .encabezado {
        position: relative;

        margin-bottom: 18px;
        padding: 8px 0 9px;

        border-top: 1px solid #8a8a8a;
        border-bottom: 1px solid #8a8a8a;
    }

    .encabezado-tabla {
        width: 100%;

        border-collapse: collapse;
    }

    .encabezado-logo {
        width: 22%;

        vertical-align: middle;
    }

    .encabezado-titulo {
        width: 78%;

        padding-right: 22%;

        text-align: center;
        vertical-align: middle;
    }

    .logo {
        display: block;

        width: 82px;
    }

    .titulo-principal {
        margin: 0;

        color: #252525;

        font-size: 16px;
        font-weight: bold;

        letter-spacing: 0.7px;
    }

    .subtitulo {
        margin-top: 3px;

        color: #666666;

        font-size: 7.5px;

        letter-spacing: 0.45px;
        text-transform: uppercase;
    }

    .codigo-documento {
        color: #2d2d2d;

        font-size: 8px;
        font-weight: bold;
    }

    .codigo-documento span {
        display: block;

        margin-top: 2px;

        color: #777777;

        font-size: 7.5px;
        font-weight: normal;
    }


    /*
    |--------------------------------------------------------------------------
    | BLOQUE DE DATOS
    |--------------------------------------------------------------------------
    */

    .datos {
        margin-bottom: 22px;

        border: 0;
    }

    .tabla-datos {
        width: 100%;

        border-collapse: collapse;
    }

    .tabla-datos td {
        padding: 3px 0;

        border: 0;

        vertical-align: top;
    }

    .tabla-datos .etiqueta {
    width: 100px;

    padding-right: 14px;

    background: transparent;

    font-family: "Helvetica", "DejaVu Sans", Arial, sans-serif;

    color: #222222;

    font-size: 9.5px;
    font-weight: bold;

    letter-spacing: 0;
}

    .tabla-datos .valor {
    padding-left: 4px;

    font-family: "Helvetica", "DejaVu Sans", Arial, sans-serif;

    color: #222222;

    font-size: 10px;
    font-weight: normal;

    line-height: 1.25;
}

    .tabla-datos tr:last-child td {
        padding-bottom: 8px;
    }

    .datos::after {
        display: block;

        width: 100%;

        margin-top: 5px;

        border-bottom: 1px solid #687f97;

        content: "";
    }


    /*
    |--------------------------------------------------------------------------
    | CUERPO DEL DOCUMENTO
    |--------------------------------------------------------------------------
    */

    .cuerpo {
        margin-top: 3px;

        color: #272727;

        text-align: justify;
    }

    .cuerpo p {
        margin: 0 0 12px;
    }

    .cuerpo strong {
        color: #222222;

        font-weight: bold;
    }


    /*
    |--------------------------------------------------------------------------
    | BLOQUE DESTACADO
    |--------------------------------------------------------------------------
    */

    .bloque-destacado {
        margin: 8px 0 13px;

        padding: 0;

        border: 0;

        background: transparent;
    }

    .bloque-destacado p {
        margin: 0;
    }

    .bloque-destacado strong {
        font-weight: bold;
    }


    /*
    |--------------------------------------------------------------------------
    | TÍTULOS DE SECCIÓN
    |--------------------------------------------------------------------------
    */

    .seccion-titulo {
        margin: 18px 0 7px;

        padding: 0 0 4px;

        border: 0;
        border-bottom: 1px solid #a3a3a3;

        color: #292929;

        font-size: 9px;
        font-weight: bold;

        letter-spacing: 0.25px;
        text-transform: uppercase;
    }


    /*
    |--------------------------------------------------------------------------
    | MOTIVO
    |--------------------------------------------------------------------------
    */

    .motivo {
        min-height: 0;

        margin-top: 5px;

        padding: 4px 0 4px 10px;

        border: 0;
        border-left: 2px solid #8c9dac;

        background: transparent;

        color: #303030;

        text-align: justify;
    }


    /*
    |--------------------------------------------------------------------------
    | TABLA DE EQUIPOS
    |--------------------------------------------------------------------------
    */

    .tabla-equipos {
        width: 100%;

        margin-top: 9px;

        border-collapse: collapse;
        table-layout: fixed;
    }

    .tabla-equipos th {
        padding: 6px 5px;

        border: 1px solid #5e5e5e;

        background: #ededeb;

        color: #222222;

        font-size: 8.5px;
        font-weight: bold;

        text-align: center;
        text-transform: uppercase;
    }

    .tabla-equipos td {
        padding: 7px 6px;

        border: 1px solid #666666;

        background: #ffffff;

        color: #272727;

        font-size: 8.5px;

        text-align: center;
        vertical-align: middle;

        word-wrap: break-word;
    }

    .tabla-equipos tbody tr:nth-child(even) td {
        background: #ffffff;
    }

    .sin-registros {
        padding: 13px 8px !important;

        color: #707070 !important;

        font-style: italic;
    }


    /*
    |--------------------------------------------------------------------------
    | CIERRE
    |--------------------------------------------------------------------------
    */

    .cierre {
        margin-top: 25px;

        color: #292929;

        text-align: justify;
    }

    .cierre p {
        margin: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | FIRMA
    |--------------------------------------------------------------------------
    */

    .firma {
        width: 270px;

        margin: 62px auto 0;

        text-align: center;
    }

    .firma-linea {
        margin-bottom: 6px;

        border-top: 1px solid #444444;
    }

    .firma-nombre {
        margin: 0;

        color: #272727;

        font-size: 9px;
        font-weight: bold;
    }

    .firma-cargo {
        margin: 2px 0 0;

        color: #6b6b6b;

        font-size: 8px;
    }


    /*
    |--------------------------------------------------------------------------
    | PIE DE PÁGINA
    |--------------------------------------------------------------------------
    */

    .pie {
        position: fixed;

        right: 0;
        bottom: -25px;
        left: 0;

        padding-top: 5px;

        border-top: 1px solid #bdbdbd;

        color: #7c7c7c;

        font-size: 7px;

        text-align: center;
    }

</style>

</head>

<body>

@php

    $fechaDocumento = null;

    if ($memorando->fecha_documento) {
        $fechaDocumento = \Carbon\Carbon::parse(
            $memorando->fecha_documento
        )
            ->locale('es')
            ->isoFormat(
                'D [de] MMMM [de] YYYY'
            );
    }

@endphp


<div class="documento">


    {{-- Encabezado --}}

    <div class="encabezado">

        <table class="encabezado-tabla">

            <tr>

                <td class="encabezado-logo">

                    <img
                        src="{{ public_path('img/tvc2.png') }}"
                        class="logo"
                        alt="Logo institucional"
                    >

                </td>

                <td class="encabezado-titulo">

                    <h1 class="titulo-principal">
                        MEMORÁNDUM
                    </h1>

                    <div class="subtitulo">
                        Dirección de Información y Tecnología
                    </div>

                </td>

            </tr>

        </table>

    </div>


    {{-- Datos del memorando --}}

    <div class="datos">

        <table class="tabla-datos">

            <tr>

                <td class="etiqueta">
                    PARA:
                </td>

                <td class="valor">
                    {{ $memorando->para_nombre ?? '-' }}
                </td>

            </tr>

            <tr>

                <td class="etiqueta">
                    CC:
                </td>

                <td class="valor">
                    {{ $memorando->cc_nombre ?? '-' }}
                </td>

            </tr>

            <tr>

                <td class="etiqueta">
                    DE:
                </td>

                <td class="valor">
                    {{ $memorando->de_nombre ?? '-' }}
                </td>

            </tr>

            <tr>

                <td class="etiqueta">
                    FECHA:
                </td>

                <td class="valor">
                    {{ $fechaDocumento ?? '-' }}
                </td>

            </tr>

            <tr>

                <td class="etiqueta">
                    ASUNTO:
                </td>

                <td class="valor">
                    {{ $memorando->asunto ?? '-' }}
                </td>

            </tr>

        </table>

    </div>


    {{-- Cuerpo --}}

    <div class="cuerpo">

        <p>
            Por este medio solicito la autorización correspondiente para el
            ingreso de equipo tecnológico que será utilizado por:
        </p>

        <div class="bloque-destacado">

            <p>

                <strong>
                    {{ data_get($memorando->datos_extra, 'colaborador', '-') }}
                </strong>

                quien estará desempeñando actividades dentro de la compañía.

            </p>

        </div>

        <p>

            <strong>
                Área o cargo:
            </strong>

            {{ data_get($memorando->datos_extra, 'cargo_area', '-') }}

        </p>

        <p>

            <strong>
                Fecha prevista para el ingreso:
            </strong>

            {{ $fechaDocumento ?? '-' }}

        </p>


        <div class="seccion-titulo">
            Motivo de autorización
        </div>

        <div class="motivo">

            {{ data_get(
                $memorando->datos_extra,
                'motivo_autorizacion',
                '-'
            ) }}

        </div>


        <div class="seccion-titulo">
            Detalle del equipo
        </div>

        <p>
            A continuación se detallan las características del equipo autorizado:
        </p>

    </div>


    {{-- Tabla de equipos --}}

    <table class="tabla-equipos">

        <thead>

            <tr>

                <th style="width: 24%;">
                    Equipo
                </th>

                <th style="width: 18%;">
                    Marca
                </th>

                <th style="width: 20%;">
                    Modelo
                </th>

                <th style="width: 22%;">
                    Serie
                </th>

                <th style="width: 16%;">
                    Color
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse(
                data_get(
                    $memorando->datos_extra,
                    'equipos',
                    []
                ) as $equipo
            )

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

            @empty

                <tr>

                    <td
                        colspan="5"
                        class="sin-registros"
                    >
                        Sin equipos registrados
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- Cierre --}}

    <div class="cierre">

        <p>
            Agradezco de antemano la atención y colaboración brindada
            para la autorización correspondiente.
        </p>

    </div>


    {{-- Firma --}}

    <div class="firma">

        <div class="firma-linea"></div>

        <p class="firma-nombre">
            Vo. Bo. Jefe Inmediato
        </p>

        <p class="firma-cargo">
            Firma y sello
        </p>

    </div>


</div>


</body>

</html>