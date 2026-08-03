<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <style>

        @page {
            margin: 42px 48px 48px;
        }


        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN GENERAL
        |--------------------------------------------------------------------------
        */

        body {
            margin: 0;
            font-family: "DejaVu Serif", "Times New Roman", serif;
            color: #111827;
            font-size: 11.5px;
            line-height: 1.55;
        }

        * {
            box-sizing: border-box;
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
            border-bottom: 1.5px solid #1f2937;
            padding-bottom: 14px;
            margin-bottom: 22px;
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
            width: 104px;
            display: block;
        }

        .titulo-principal {
            margin: 0;
            color: #111827;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .subtitulo {
            margin-top: 4px;
            color: #4b5563;
            font-size: 8.5px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .codigo-documento {
            color: #111827;
            font-size: 9px;
            font-weight: bold;
        }

        .codigo-documento span {
            display: block;
            margin-top: 3px;
            color: #6b7280;
            font-size: 8px;
            font-weight: normal;
        }


        /*
        |--------------------------------------------------------------------------
        | BLOQUE DE DATOS
        |--------------------------------------------------------------------------
        */

        .datos {
            margin-bottom: 24px;
            border: 1px solid #d1d5db;
        }

        .tabla-datos {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-datos td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .tabla-datos tr:last-child td {
            border-bottom: 0;
        }

        .tabla-datos .etiqueta {
            width: 92px;
            background: #f3f4f6;
            color: #1f2937;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.4px;
        }

        .tabla-datos .valor {
            color: #111827;
            font-size: 10.5px;
        }


        /*
        |--------------------------------------------------------------------------
        | CUERPO DEL DOCUMENTO
        |--------------------------------------------------------------------------
        */

        .cuerpo {
            margin-top: 4px;
            text-align: justify;
        }

        .cuerpo p {
            margin: 0 0 13px;
        }

        .cuerpo strong {
            color: #111827;
        }

        .bloque-destacado {
            margin: 10px 0 16px;
            padding: 11px 13px;
            border-left: 3px solid #374151;
            background: #f9fafb;
        }

        .bloque-destacado p {
            margin: 0;
        }

        .seccion-titulo {
            margin: 20px 0 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #9ca3af;
            color: #111827;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .motivo {
            min-height: 48px;
            margin-top: 6px;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            text-align: justify;
        }


        /*
        |--------------------------------------------------------------------------
        | TABLA DE EQUIPOS
        |--------------------------------------------------------------------------
        */

        .tabla-equipos {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .tabla-equipos th {
            padding: 7px 5px;
            border: 1px solid #9ca3af;
            background: #e5e7eb;
            color: #111827;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .tabla-equipos td {
            padding: 7px 5px;
            border: 1px solid #cbd5e1;
            color: #1f2937;
            font-size: 8.5px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .tabla-equipos tbody tr:nth-child(even) td {
            background: #f9fafb;
        }

        .sin-registros {
            padding: 14px 8px !important;
            color: #6b7280 !important;
            font-style: italic;
        }


        /*
        |--------------------------------------------------------------------------
        | CIERRE Y FIRMA
        |--------------------------------------------------------------------------
        */

        .cierre {
            margin-top: 26px;
            text-align: justify;
        }

        .firma {
            width: 270px;
            margin: 68px auto 0;
            text-align: center;
        }

        .firma-linea {
            border-top: 1px solid #111827;
            margin-bottom: 7px;
        }

        .firma-nombre {
            margin: 0;
            color: #111827;
            font-size: 10px;
            font-weight: bold;
        }

        .firma-cargo {
            margin: 2px 0 0;
            color: #6b7280;
            font-size: 8.5px;
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
            border-top: 1px solid #d1d5db;
            padding-top: 6px;
            color: #6b7280;
            font-size: 8px;
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