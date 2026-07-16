<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Nueva incidencia registrada</title>


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
            Nueva incidencia registrada
        </h1>


        <p>
            El Portal TI recibió una nueva solicitud de soporte.
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
                    Título
                </td>

                <td>
                    {{ $incidencia->titulo }}
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

            {{ $incidencia->descripcion }}

        </div>


    </div>









    {{-- INFORMACIÓN ADICIONAL --}}

    <div class="email-section">


        <h2>
            Información adicional
        </h2>



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




            @php
    $tiemposProblema = [
        'hoy' => 'Hoy',
        'ayer' => 'Ayer',
        'varios_dias' => 'Hace varios días',
    ];

    $afectaciones = [
        'solo' => 'Solo a mí',
        'varios' => 'A varias personas',
        'todos' => 'A toda el área',
    ];
@endphp

<tr>
    <td>
        Tiempo del problema
    </td>

    <td>
        {{ $tiemposProblema[$incidencia->tiempo_problema] ?? 'No indicado' }}
    </td>
</tr>

<tr>
    <td>
        Afectación
    </td>

    <td>
        {{ $afectaciones[$incidencia->afectacion] ?? 'No indicada' }}
    </td>
</tr>



        </table>


    </div>









@if($incidencia->archivos->count())


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

{{ $archivo->nombre_original }}

</p>



@if($archivo->texto_ocr)


<div class="ocr-box">


<strong>
Texto identificado automáticamente:
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







<div class="email-footer">


<p>

Portal TI · Mesa de ayuda tecnológica

</p>


</div>





</div>


</body>

</html>