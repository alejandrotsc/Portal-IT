document.addEventListener('DOMContentLoaded',()=>{


const filas =
document.getElementById('equipoFilas');


const salida =
document.getElementById('equipoSalida');


const boton =
document.getElementById('agregarFila');



if(!filas)
return;




function actualizar(){


let equipos=[];



filas.querySelectorAll('.fila-equipo')
.forEach(fila=>{


let datos =
[...fila.querySelectorAll('input')]
.map(i=>i.value);



if(datos.some(x=>x.trim())){

equipos.push(datos);

}


});





if(!salida)
return;



if(equipos.length===0){


salida.innerHTML=
`
<tr>
<td colspan="5">
Sin equipos registrados
</td>
</tr>
`;


return;


}



salida.innerHTML =
equipos.map(e=>`

<tr>

<td>${e[0]}</td>
<td>${e[1]}</td>
<td>${e[2]}</td>
<td>${e[3]}</td>
<td>${e[4]}</td>

</tr>

`).join('');



}






document.addEventListener(
'input',
actualizar
);



if(boton){


boton.addEventListener(
'click',
()=>{


let tr =
document.createElement('tr');


tr.className =
'fila-equipo';



tr.innerHTML=`

<td><input name="equipo[]"></td>
<td><input name="marca[]"></td>
<td><input name="modelo[]"></td>
<td><input name="serie[]"></td>
<td><input name="color[]"></td>

`;



filas.appendChild(tr);



});


}



actualizar();



});