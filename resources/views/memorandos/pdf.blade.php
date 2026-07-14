<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">


<style>


@page {
    margin: 45px 55px;
}


/*
|--------------------------------------------------------------------------
| GENERAL
|--------------------------------------------------------------------------
*/

body {

    font-family:"Times New Roman", serif;

    color:#000;

    font-size:14px;

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

    border-bottom:2px solid #111;

    padding-bottom:18px;

    margin-bottom:25px;

}



.logo-documento {

    width:110px;

    display:block;

    margin:0 auto 12px;

}



.document-header h1 {

    margin:0;

    font-size:22px;

    letter-spacing:2px;

    font-weight:bold;

}




/*
|--------------------------------------------------------------------------
| DATOS MEMORANDO
|--------------------------------------------------------------------------
*/


.datos-documento {

    margin-bottom:25px;

}



.tabla-datos {

    width:100%;

    border-collapse:collapse;

}



.tabla-datos td {

    padding:5px 0;

    vertical-align:top;

}



.tabla-datos .label {

    width:85px;

    font-weight:bold;

}




/*
|--------------------------------------------------------------------------
| CUERPO
|--------------------------------------------------------------------------
*/


.memo-body-preview {

    margin:25px 0;

    text-align:justify;

    font-size:14px;

    line-height:1.7;

}



.memo-body-preview p {

    margin-bottom:10px;

}



.campo-texto {

    min-height:40px;

    border-bottom:1px solid #000;

    padding-bottom:5px;

}




/*
|--------------------------------------------------------------------------
| TABLA EQUIPOS
|--------------------------------------------------------------------------
*/


.equipo-output-table {


    width:100%;

    border-collapse:collapse;

    margin-top:15px;


}



.equipo-output-table th,
.equipo-output-table td {


    border:1px solid #000;

    padding:5px 6px;

    font-size:10px;

    text-align:center;


}



.equipo-output-table th {


    background:#f1f5f9;

    font-size:10px;

    font-weight:bold;


}



.fila-vacia {


    text-align:center;

}




/*
|--------------------------------------------------------------------------
| AGRADECIMIENTO
|--------------------------------------------------------------------------
*/


.agradecimiento {

    margin-top:30px;

    font-size:14px;

}




/*
|--------------------------------------------------------------------------
| FIRMA
|--------------------------------------------------------------------------
*/


.firma {


    margin-top:65px;

    width:230px;

    margin-left:auto;

    margin-right:auto;

    text-align:center;


}



.firma div {


    border-top:1px solid #000;

    margin-bottom:8px;


}



.firma p {


    margin:0;

    font-weight:bold;

    font-size:13px;


}



.firma small {


    font-size:10px;

    color:#555;


}


</style>


</head>



<body>


<div class="documento-final">



{{-- HEADER --}}


<div class="document-header">


    <img
        src="{{ public_path('img/tvc2.png') }}"
        class="logo-documento">


    <h1>
        MEMORÁNDUM
    </h1>


</div>






{{-- DATOS DEL MEMORANDO --}}


<div class="datos-documento">


<table class="tabla-datos">


<tr>

<td class="label">
PARA:
</td>

<td>
{{ $memorando->para_nombre ?? '-' }}
</td>

</tr>



<tr>

<td class="label">
CC:
</td>

<td>
{{ $memorando->cc_nombre ?? '-' }}
</td>

</tr>



<tr>

<td class="label">
DE:
</td>

<td>
{{ $memorando->de_nombre ?? '-' }}
</td>

</tr>



<tr>

<td class="label">
FECHA:
</td>

<td>

{{ optional($memorando->fecha_documento)->format('d/m/Y') ?? '-' }}

</td>

</tr>



<tr>

<td class="label">
ASUNTO:
</td>

<td>

{{ $memorando->asunto ?? '-' }}

</td>

</tr>


</table>


</div>







{{-- CUERPO --}}


<div class="memo-body-preview">



<p>

Por este medio solicito la autorización correspondiente para
el ingreso de equipo tecnológico que será utilizado por:


</p>




<p>


<strong>

{{ data_get($memorando->datos_extra,'colaborador','-') }}

</strong>


quien estará desempeñando actividades dentro de la compañía.


</p>




<p>


Área o cargo:


<strong>

{{ data_get($memorando->datos_extra,'cargo_area','-') }}

</strong>


</p>





<p>


La fecha prevista para el ingreso del equipo será:


<strong>

{{ optional($memorando->fecha_documento)->format('d/m/Y') ?? '-' }}

</strong>


</p>





<p>

Motivo de autorización:

</p>




<p class="campo-texto">


{{ data_get($memorando->datos_extra,'motivo_autorizacion','-') }}


</p>




<p>

A continuación se detallan las características del equipo:

</p>



</div>








{{-- TABLA EQUIPOS --}}



<table class="equipo-output-table">


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



@forelse(data_get($memorando->datos_extra,'equipos',[]) as $equipo)


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

<td colspan="5" class="fila-vacia">

Sin equipos registrados

</td>

</tr>


@endforelse



</tbody>



</table>









<p class="agradecimiento">

Gracias por su colaboración.

</p>







<div class="firma">


<div></div>


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