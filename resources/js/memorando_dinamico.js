document.addEventListener('DOMContentLoaded',()=>{


const tipo =
document.getElementById('tipo_id');


const formulario =
document.getElementById('formulario-dinamico');


const preview =
document.getElementById('preview-dinamico');



if(!tipo)
return;



tipo.addEventListener(
'change',
()=>{


const opcion =
tipo.selectedOptions[0];



if(!opcion)
return;



const nombre =
opcion.dataset.formulario;



if(!nombre)
return;



cargarFormulario(nombre);



}

);





async function cargarFormulario(nombre){



try{


const formResponse =
await fetch(
`/memorandos/formulario/${nombre}`
);



formulario.innerHTML =
await formResponse.text();




const previewResponse =
await fetch(
`/memorandos/preview/${nombre}`
);



preview.innerHTML =
await previewResponse.text();



}catch(error){


console.error(
'Error cargando formulario dinámico',
error
);


}



}



});