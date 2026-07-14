document.addEventListener('DOMContentLoaded',()=>{


/*
====================================================
REFERENCIAS GENERALES
====================================================
*/


const form =
document.getElementById('documentForm');


const btnText =
document.getElementById('btnSubmitText');


const codigoDocumento =
document.getElementById('codigoDocumento');





/*
====================================================
CAMPOS FORMULARIO
====================================================
*/


const deInput =
document.getElementById('de');


const asuntoInput =
document.getElementById('asunto');


const fechaInput =
document.getElementById('fecha');


const empresaInput =
document.getElementById('empresa');


const mercadoInput =
document.getElementById('tipo_mercado');



const inventarioInput =
document.getElementById('inventario');


const mantenimientoInput =
document.getElementById('mantenimiento');


const proyectoInput =
document.getElementById('proyecto');


const otrosInput =
document.getElementById('otros');


const proveedorInput =
document.getElementById('proveedor');


const razonProveedorInput =
document.getElementById('razon_proveedor');







/*
====================================================
OUTPUT MEMORANDO
====================================================
*/


const outDe =
document.getElementById('out_de');


const outFecha =
document.getElementById('out_fecha');


const outAsunto =
document.getElementById('out_asunto');


const previewTexto =
document.getElementById('previewTexto');


const tablaDetalleMemo =
document.getElementById('tablaDetalleMemo');







/*
====================================================
OUTPUT SOLICITUD COMPRA
====================================================
*/


const tablaArticulosPreview =
document.getElementById('tablaArticulosPreview');


const outDia =
document.getElementById('out_dia');


const outMes =
document.getElementById('out_mes');


const outAnio =
document.getElementById('out_anio');



const outInventario =
document.getElementById('out_inventario');


const outMantenimiento =
document.getElementById('out_mantenimiento');


const outProyecto =
document.getElementById('out_proyecto');


const outOtros =
document.getElementById('out_otros');


const outProveedor =
document.getElementById('out_proveedor');


const outRazonProveedor =
document.getElementById('out_razon_proveedor');








/*
====================================================
FECHA
====================================================
*/


function fechaActual(){


    const hoy = new Date();


    return hoy.toISOString()
    .split('T')[0];

}





function formatoFecha(fecha){


    if(!fecha)
        return '-';


    const meses=[

        'enero',
        'febrero',
        'marzo',
        'abril',
        'mayo',
        'junio',
        'julio',
        'agosto',
        'septiembre',
        'octubre',
        'noviembre',
        'diciembre'

    ];



    const partes =
    fecha.split('-');



    return `${partes[2]} de ${meses[Number(partes[1])-1]} del ${partes[0]}`;

}




if(
    fechaInput &&
    !fechaInput.value
){

    fechaInput.value =
    fechaActual();

}









/*
====================================================
TABS
====================================================
*/


document
.querySelectorAll('.document-tab')
.forEach(tab=>{


tab.addEventListener('click',()=>{


    document
    .querySelectorAll('.document-tab')
    .forEach(t=>{

        t.classList.remove('active');

    });



    document
    .querySelectorAll('.document-page')
    .forEach(p=>{

        p.classList.remove('active');

    });



    tab.classList.add('active');



    const page =
    document.getElementById(
        `page-${tab.dataset.page}`
    );



    if(page)
        page.classList.add('active');


});


});










/*
====================================================
ARTICULOS
====================================================
*/


const cuerpoArticulos =
document.getElementById('articulosBody');


const btnAgregar =
document.getElementById('btnAgregarArticulo');


let contadorArticulo = 1;





if(btnAgregar){


btnAgregar.addEventListener('click',()=>{


    const fila =
    document.createElement('tr');



    fila.innerHTML = `


<td>

<input
type="text"
name="articulos[${contadorArticulo}][codigo]">

</td>



<td>

<input
type="text"
name="articulos[${contadorArticulo}][descripcion]">

</td>



<td>

<select
name="articulos[${contadorArticulo}][unidad]">


<option value="Unidad">
Unidad
</option>


<option value="Servicio">
Servicio
</option>


</select>

</td>




<td>

<input

type="number"

value="1"

min="1"

name="articulos[${contadorArticulo}][cantidad]">

</td>




<td>


<button

type="button"

class="btnEliminarArticulo">


✕

</button>


</td>


`;



    cuerpoArticulos.appendChild(fila);



    contadorArticulo++;



    actualizarPreview();


});


}








document.addEventListener(
'click',
e=>{


if(
e.target.classList.contains(
'btnEliminarArticulo'
)
){


    e.target
    .closest('tr')
    .remove();


    actualizarPreview();


}



});











/*
====================================================
OBTENER ARTICULOS
====================================================
*/


function obtenerArticulos(){



return [

...document.querySelectorAll(
'#articulosBody tr'
)

]

.map(fila=>{



const campos =
fila.querySelectorAll(
'input,select'
);



return {


codigo:
campos[0]?.value.trim() || '',



descripcion:
campos[1]?.value.trim() || '',



unidad:
campos[2]?.value || '',



cantidad:
campos[3]?.value || 1



};



});



}









/*
====================================================
TABLA SOLICITUD COMPRA
====================================================
*/


function pintarTablaSolicitud(){


if(!tablaArticulosPreview)
return;



let html = '';



const articulos =
obtenerArticulos();



articulos.forEach(a=>{


if(
a.codigo ||
a.descripcion
){



html += `


<tr>


<td>
${a.codigo}
</td>


<td>
${a.descripcion}
</td>


<td>
${a.unidad}
</td>


<td>
${a.cantidad}
</td>


</tr>


`;



}



});





if(!html){


html = `


<tr>

<td colspan="4">

Sin artículos registrados

</td>

</tr>


`;



}



tablaArticulosPreview.innerHTML =
html;



}









/*
====================================================
TABLA MEMORANDO
====================================================
*/


function pintarTablaMemorando(){


if(!tablaDetalleMemo)
return;



let html='';



const articulos =
obtenerArticulos();



articulos.forEach(a=>{


if(
a.codigo ||
a.descripcion
){


html += `


<tr>


<td>
${a.codigo}
</td>


<td>
${a.descripcion}
</td>


<td>
${a.cantidad}
</td>


</tr>


`;



}



});




if(!html){


html = `


<tr>


<td colspan="3">

Sin artículos registrados

</td>


</tr>


`;



}



tablaDetalleMemo.innerHTML =
html;



}

 
 
/*
====================================================
CHECKS EMPRESA / MERCADO
====================================================
*/


function actualizarChecks(){



document
.querySelectorAll(
    '.checkbox[data-empresa]'
)
.forEach(check=>{



    check.classList.remove(
        'checked'
    );



    if(
        empresaInput &&
        empresaInput.value ===
        check.dataset.empresa
    ){


        check.classList.add(
            'checked'
        );


    }



});






const local =
document.getElementById(
    'checkLocal'
);



const exterior =
document.getElementById(
    'checkExterior'
);





if(local){


local.classList.toggle(

    'checked',

    mercadoInput?.value ===
    'Compra local'

);


}





if(exterior){


exterior.classList.toggle(

    'checked',

    mercadoInput?.value ===
    'Compra exterior'

);


}




}









/*
====================================================
GENERAR DETALLE ARTICULOS TEXTO
====================================================
*/


function obtenerTextoArticulos(){



const articulos =
obtenerArticulos()
.filter(a=>
    a.descripcion
);



if(!articulos.length)
    return 'Pendiente';




return articulos
.map(a=>{


return `${a.cantidad} ${a.descripcion}`;


})
.join('<br>');



}











/*
====================================================
PREVIEW MEMORANDO
====================================================
*/


function actualizarMemorando(){



if(outDe){


outDe.textContent =
deInput?.value || '-';


}



if(outFecha){


outFecha.textContent =
formatoFecha(
    fechaInput?.value
);


}




if(outAsunto){


outAsunto.textContent =
asuntoInput?.value ||
'Solicitud de Compra';


}







if(previewTexto){



const detalle =
obtenerTextoArticulos();




const motivo =
otrosInput?.value ||
mantenimientoInput?.value ||
proyectoInput?.value ||
'';



let texto = `



Por medio de la presente se solicita
la generación de la orden de compra
correspondiente para la compra de los
siguientes equipos:



<br><br>



<strong>

${detalle}

</strong>



`;





if(motivo){


texto += `


<br><br>


Equipos a asignar / motivo:

<br>


<strong>
${motivo}
</strong>


`;


}





texto += `


<br><br>


Se adjunta la siguiente documentación:


<br>


1. Solicitud de orden de compra.


<br>


2. Cotización por parte de 
<strong>
${proveedorInput?.value || 'proveedor'}
</strong>.


`;





previewTexto.innerHTML =
texto;



}





}









/*
====================================================
PREVIEW SOLICITUD COMPRA
====================================================
*/


function actualizarSolicitudCompra(){





if(fechaInput?.value){



const partes =
fechaInput.value.split('-');



if(outDia)
outDia.textContent =
partes[2];



if(outMes)
outMes.textContent =
partes[1];



if(outAnio)
outAnio.textContent =
partes[0];


}








if(outInventario){


outInventario.textContent =
inventarioInput?.value ||
'Pendiente';


}





if(outMantenimiento){


outMantenimiento.textContent =
mantenimientoInput?.value ||
'Pendiente';


}





if(outProyecto){


outProyecto.textContent =
proyectoInput?.value ||
'Pendiente';


}





if(outOtros){


outOtros.textContent =
otrosInput?.value ||
'Pendiente';


}







if(outProveedor){


outProveedor.textContent =
proveedorInput?.value ||
'Pendiente';


}







if(outRazonProveedor){


outRazonProveedor.textContent =
razonProveedorInput?.value ||
'Pendiente';


}






}









/*
====================================================
ACTUALIZAR TODO
====================================================
*/


function actualizarPreview(){



actualizarMemorando();



actualizarSolicitudCompra();



pintarTablaSolicitud();



pintarTablaMemorando();



actualizarChecks();



}









/*
====================================================
EVENTOS INPUT
====================================================
*/


[
deInput,
asuntoInput,
fechaInput,
empresaInput,
mercadoInput,
inventarioInput,
mantenimientoInput,
proyectoInput,
otrosInput,
proveedorInput,
razonProveedorInput

]
.forEach(campo=>{


if(campo){



campo.addEventListener(
'input',
actualizarPreview
);



campo.addEventListener(
'change',
actualizarPreview
);



}


});









/*
====================================================
EVENTOS TABLA ARTICULOS
====================================================
*/


document.addEventListener(
'input',
e=>{


if(
e.target.closest(
    '#articulosBody'
)
){


actualizarPreview();


}



});









/*
====================================================
CARGA INICIAL
====================================================
*/


actualizarPreview();









/*
====================================================
ENVIO FORMULARIO
====================================================
*/


if(form){



form.addEventListener(
'submit',
async e=>{



e.preventDefault();



const datos =
new FormData(form);





try{



if(btnText){

btnText.textContent =
'Generando...';

}





const response =
await fetch(

form.action,

{


method:'POST',

body:datos,


headers:{


'X-Requested-With':
'XMLHttpRequest'


}


}

);






const result =
await response.json();






if(result.success){



if(
result.codigo &&
codigoDocumento
){


codigoDocumento.textContent =
result.codigo;


}





alert(
'Solicitud generada correctamente'
);



}else{


alert(

result.error ||
'Error generando documento'

);



}






}catch(error){



console.error(error);



alert(
'Error comunicándose con el servidor'
);



}

finally{



if(btnText){


btnText.textContent =
'Generar Solicitud de Compra';


}



}



});



}



});