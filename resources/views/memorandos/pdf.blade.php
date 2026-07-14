<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">


<style>

@page {
    margin: 45px 50px;
}


body {

    font-family: Arial, Helvetica, sans-serif;

    color:#0f172a;

    font-size:12px;

    line-height:1.6;

}



.documento-final {

    width:100%;

}



/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/


.document-header {

    text-align:center;

    padding-bottom:22px;

    margin-bottom:30px;

    border-bottom:2px solid #042b72;

}



.logo-documento {

    width:150px;

    height:auto;

    display:block;

    margin:0 auto 18px auto;

}



.document-header h1 {

    margin:0;

    font-size:25px;

    letter-spacing:4px;

    color:#042b72;

    font-weight:bold;

}



/*
|--------------------------------------------------------------------------
| DATOS DEL MEMORANDO
|--------------------------------------------------------------------------
*/


.datos-documento {

    margin-bottom:30px;

    border:1px solid #dbe3ef;

    padding:18px;

    background:#f8fafc;

}



.dato-fila {

    margin-bottom:10px;

    font-size:13px;

}



.dato-fila:last-child {

    margin-bottom:0;

}



.dato-fila b {

    display:inline-block;

    width:90px;

    color:#042b72;

    font-weight:bold;

}



/*
|--------------------------------------------------------------------------
| CUERPO
|--------------------------------------------------------------------------
*/


.memo-body {

    margin-top:25px;

    margin-bottom:25px;

    text-align:justify;

    font-size:13px;

}



.memo-body p {

    margin-bottom:15px;

}



/*
|--------------------------------------------------------------------------
| TABLA EQUIPOS
|--------------------------------------------------------------------------
*/


.equipo-table {

    width:100%;

    border-collapse:collapse;

    margin-top:25px;

}



.equipo-table th {

    background:#042b72;

    color:white;

    padding:10px;

    font-size:11px;

    font-weight:bold;

    border:1px solid #042b72;

}



.equipo-table td {

    padding:9px;

    font-size:11px;

    border:1px solid #cbd5e1;

    text-align:center;

}



.equipo-table tr:nth-child(even) td {

    background:#f8fafc;

}



/*
|--------------------------------------------------------------------------
| OBSERVACIONES
|--------------------------------------------------------------------------
*/


.observaciones {

    margin-top:25px;

    padding:15px;

    border-left:4px solid #042b72;

    background:#f1f5f9;

    font-size:12px;

}



/*
|--------------------------------------------------------------------------
| AGRADECIMIENTO
|--------------------------------------------------------------------------
*/


.agradecimiento {

    margin-top:35px;

    font-size:13px;

}



/*
|--------------------------------------------------------------------------
| FIRMA
|--------------------------------------------------------------------------
*/


.firma {

    margin-top:90px;

    width:260px;

    margin-left:auto;

    margin-right:auto;

    text-align:center;

}



.firma-linea {

    border-top:1px solid #000;

    margin-bottom:12px;

}



.firma p {

    margin:0;

    font-size:13px;

    font-weight:bold;

}



.firma small {

    color:#64748b;

    font-size:11px;

}



</style>


</head>



<body>


<div class="documento-final">



    <div class="document-header">


        <img 
            src="{{ public_path('img/tvc2.png') }}"
            class="logo-documento"
            alt="TVC">


        <h1>
            MEMORÁNDUM
        </h1>


    </div>





    <div class="datos-documento">


        <div class="dato-fila">

            <b>PARA:</b>

            {{ $documento['datos']['para'] ?? '-' }}

        </div>



        <div class="dato-fila">

            <b>DE:</b>

            {{ $documento['datos']['de'] ?? '-' }}

        </div>



        <div class="dato-fila">

            <b>CC:</b>

            {{ $documento['datos']['cc'] ?? '-' }}

        </div>



        <div class="dato-fila">

            <b>ASUNTO:</b>

            {{ $documento['datos']['asunto'] ?? '-' }}

        </div>



        <div class="dato-fila">

            <b>FECHA:</b>

            {{ $documento['datos']['fecha'] ?? '-' }}

        </div>


    </div>







    <div class="memo-body">


        <p>
            Hola estimados,
        </p>



        <p>

            Por este medio solicito la autorización para el ingreso de una
            computadora personal que será utilizada por

            <strong>
                {{ $documento['datos']['colaborador'] ?? '-' }}
            </strong>

            quien estará realizando sus funciones en

            <strong>
                {{ $documento['datos']['ubicacion'] ?? '-' }}
            </strong>.

            A continuación se detallan las especificaciones del equipo correspondiente.

        </p>


    </div>







    <table class="equipo-table">


        <thead>

            <tr>

                <th>
                    EQUIPO
                </th>

                <th>
                    MARCA
                </th>

                <th>
                    MODELO
                </th>

                <th>
                    SERIE
                </th>

                <th>
                    COLOR
                </th>

            </tr>


        </thead>



        <tbody>


        @forelse($documento['datos']['equipos'] ?? [] as $equipo)


            <tr>

                <td>
                    {{ $equipo['equipo'] }}
                </td>


                <td>
                    {{ $equipo['marca'] }}
                </td>


                <td>
                    {{ $equipo['modelo'] }}
                </td>


                <td>
                    {{ $equipo['serie'] }}
                </td>


                <td>
                    {{ $equipo['color'] }}
                </td>


            </tr>


        @empty


            <tr>

                <td colspan="5">
                    Sin equipos registrados
                </td>

            </tr>


        @endforelse


        </tbody>


    </table>







    @if(!empty($documento['datos']['observaciones']))


    <div class="observaciones">


        <strong>
            Observaciones:
        </strong>


        <br>


        {{ $documento['datos']['observaciones'] }}


    </div>


    @endif







    <p class="agradecimiento">

        Gracias por su colaboración.

    </p>








    <div class="firma">


        <div class="firma-linea"></div>


        <p>
            Vo.Bo. Jefe Inmediato
        </p>


        <small>
            Firma y sello
        </small>


    </div>





</div>



</body>

</html>