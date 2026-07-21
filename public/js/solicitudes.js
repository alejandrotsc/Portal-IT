document.addEventListener('DOMContentLoaded',()=>{

const PLACEHOLDERS={
computadora:{
asunto:'Ej: Solicitud de laptop para nuevo colaborador',
descripcion:'Indica qué equipo necesitas, para quién será utilizado, área y motivo de la solicitud...'
},
programa:{
asunto:'Ej: Instalación de Adobe Acrobat Pro',
descripcion:'Indica el programa requerido, para qué actividad lo utilizarás y si existe alguna fecha límite...'
},
acceso:{
asunto:'Ej: Acceso a carpeta compartida de Finanzas',
descripcion:'Describe el sistema o recurso requerido, usuario afectado y motivo del acceso...'
},
vpn:{
asunto:'Ej: Configuración de VPN para trabajo remoto',
descripcion:'Indica desde dónde te conectarás, equipo utilizado y motivo del acceso remoto...'
},
impresora:{
asunto:'Ej: Configuración de impresora en nueva área',
descripcion:'Indica la ubicación de la impresora, problema presentado y equipo donde necesitas configurarla...'
},
cuenta:{
asunto:'Ej: Restablecimiento de contraseña corporativa',
descripcion:'Describe el sistema afectado, usuario y problema presentado al iniciar sesión...'
},
cambio:{
asunto:'Ej: Reemplazo de equipo por falla',
descripcion:'Indica qué equipo tienes actualmente, qué cambio necesitas y motivo de la solicitud...'
},
otra:{
asunto:'Ej: Solicitud de soporte TI',
descripcion:'Describe detalladamente la necesidad o problema que presentas...'
}
};

const FIELDS={

computadora:[
{id:'tipo',label:'Tipo de solicitud',type:'select',required:true,options:[
'Equipo nuevo',
'Reemplazo',
'Adicional'
]}
],

programa:[
],

acceso:[
{id:'nivel',label:'Tipo de acceso requerido',type:'select',required:true,options:[
'Solo lectura',
'Lectura y escritura',
'Acceso completo',
'No lo sé, TI lo decide'
]}
],

vpn:[
],

impresora:[
],

cuenta:[
],

cambio:[
{id:'solicitud',label:'Cambio requerido',type:'select',required:true,options:[
'Reemplazar equipo dañado',
'Migrar a equipo nuevo',
'Reinstalar sistema operativo',
'Cambiar configuración de red',
'Cambio de área o usuario',
'Otro'
]}
],

otra:[]
};


let selectedCategory=null;
let attachedFiles=[];

const cards=document.querySelectorAll('.categoria-card');

cards.forEach(card=>{

card.addEventListener('mouseenter',()=>{

if(card.dataset.id!==selectedCategory){

const color=card.dataset.color;

card.style.borderColor=color;
card.style.boxShadow=`0 8px 20px ${color}20`;

}

});


card.addEventListener('mouseleave',()=>{

if(card.dataset.id!==selectedCategory){

card.style.borderColor='';
card.style.boxShadow='';

}

});

});

const formularioSolicitud=document.getElementById('formularioSolicitud');
const accionesSolicitud=document.getElementById('accionesSolicitud');
const camposDinamicos=document.getElementById('camposDinamicos');
const categoriaSeleccionada=document.getElementById('categoriaSeleccionada');
const cambiarCategoria=document.getElementById('cambiarCategoria');
const categoriaInput=document.getElementById('categoria');

const asunto=document.querySelector('[name="asunto"]');
const descripcion=document.querySelector('[name="descripcion"]');
const btnEnviar = document.getElementById('btnEnviar');
const btnCancelar = document.getElementById('btnCancelar');

const solicitudForm =
    document.getElementById('solicitudForm')
    || btnEnviar?.closest('form');

const btnEnviarTexto =
    document.getElementById('btnEnviarTexto');

const btnEnviarIcono =
    document.getElementById('btnEnviarIcono');


// Elementos opcionales para archivos
const archivosSolicitud =
    document.getElementById('archivosSolicitud');

const filesContainer =
    document.getElementById('filesContainer');

const fileInput =
    document.getElementById('fileInput');


cards.forEach(card=>{
card.addEventListener('click',()=>{
selectedCategory=card.dataset.id;

if(categoriaInput)
categoriaInput.value=selectedCategory;

actualizarTarjetas();
mostrarFormulario(selectedCategory);
mostrarBadge(card);
actualizarPlaceholders(selectedCategory);

formularioSolicitud.classList.remove('hidden');

if(accionesSolicitud)
    accionesSolicitud.classList.remove('hidden');

if(cambiarCategoria)
    cambiarCategoria.classList.remove('hidden');

setTimeout(()=>{
formularioSolicitud.scrollIntoView({behavior:'smooth',block:'start'});
},100);

});
});


function actualizarPlaceholders(category){

if(!PLACEHOLDERS[category])return;

if(asunto)
asunto.placeholder=PLACEHOLDERS[category].asunto;

if(descripcion)
descripcion.placeholder=PLACEHOLDERS[category].descripcion;

}

function actualizarTarjetas(){

cards.forEach(card=>{

const seleccionado=card.dataset.id===selectedCategory;
const color=card.dataset.color;
const bg=card.dataset.bg;

const iconBox=card.querySelector('.icon-container');
const icon=card.querySelector('.icon-container svg,.icon-container i');
const check=card.querySelector('.check-categoria');

if(seleccionado){

card.style.borderColor=color;
card.style.boxShadow=`0 8px 20px ${color}30`;

}else{

card.style.borderColor='';
card.style.boxShadow='';

}

card.style.backgroundColor=seleccionado?bg:'white';

if(iconBox)
iconBox.style.backgroundColor=seleccionado?color:bg;

if(icon)
icon.style.color=seleccionado?'#ffffff':color;

if(check){

check.classList.toggle('hidden',!seleccionado);
check.classList.toggle('flex',seleccionado);

}

});

}


function mostrarBadge(card){

if(!categoriaSeleccionada)return;

const titulo=card.querySelector('p.text-xs')?.textContent.trim()??'';

categoriaSeleccionada.textContent=titulo;
categoriaSeleccionada.style.color=card.dataset.color;
categoriaSeleccionada.style.backgroundColor=card.dataset.bg;
categoriaSeleccionada.style.borderColor=card.dataset.color;

categoriaSeleccionada.classList.remove('hidden');

}



function mostrarFormulario(category){

camposDinamicos.innerHTML='';

const fields=FIELDS[category]||[];

fields.forEach(field=>{

const wrapper=document.createElement('div');
wrapper.className='space-y-2';

const label=document.createElement('label');
label.className='block text-xs font-semibold text-muted-foreground uppercase tracking-widest';

label.innerHTML=`${field.label}${field.required?' <span class="text-primary">*</span>':''}`;

wrapper.appendChild(label);


let input;


if(field.type==='text'){

input=document.createElement('input');
input.type='text';
input.placeholder=field.placeholder||'';

}


if(field.type==='textarea'){

input=document.createElement('textarea');
input.rows=4;
input.placeholder=field.placeholder||'';

}


if(field.type==='select'){

input=document.createElement('select');

const option=document.createElement('option');
option.value='';
option.textContent='Selecciona una opción...';

input.appendChild(option);

field.options.forEach(item=>{

const opt=document.createElement('option');

opt.value=item;
opt.textContent=item;

input.appendChild(opt);

});

}


input.name=field.id;
input.dataset.required=field.required?'true':'false';

input.className='w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all';


wrapper.appendChild(input);
camposDinamicos.appendChild(wrapper);


});


}



if(cambiarCategoria){

cambiarCategoria.addEventListener('click',resetFormulario);

}



function agregarArchivos(files){

Array.from(files).forEach(file=>{

attachedFiles.push({
id:Math.random().toString(36).substring(2),
file
});

});


pintarArchivos();

}



function pintarArchivos(){

if(!filesContainer)return;

filesContainer.innerHTML='';


attachedFiles.forEach(item=>{

const file=item.file;


const div=document.createElement('div');

div.className='flex items-center gap-3 px-3 py-2.5 rounded-lg border border-border bg-white group';


div.innerHTML=`

<div class="w-7 h-7 rounded-lg bg-muted flex items-center justify-center">
${iconoArchivo(file.name)}
</div>

<div class="flex-1 min-w-0">
<p class="text-xs font-medium text-foreground truncate">${file.name}</p>
<p class="text-[10px] text-muted-foreground">${formatearPeso(file.size)}</p>
</div>

<button type="button" class="eliminar-archivo opacity-0 group-hover:opacity-100 p-1 rounded-md text-muted-foreground hover:text-red-500 hover:bg-red-50 transition-all" data-id="${item.id}">
<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M18 6L6 18M6 6l12 12"/>
</svg>
</button>

`;


filesContainer.appendChild(div);


});


filesContainer.querySelectorAll('.eliminar-archivo').forEach(btn=>{

btn.addEventListener('click',()=>{

attachedFiles=attachedFiles.filter(f=>f.id!==btn.dataset.id);

pintarArchivos();

});

});


}



function iconoArchivo(name){

const ext=name.split('.').pop().toLowerCase();


if(['jpg','jpeg','png','gif','webp'].includes(ext)){

return `
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
<rect x="3" y="3" width="18" height="18" rx="2"/>
<circle cx="8.5" cy="8.5" r="1.5"/>
<polyline points="21 15 16 10 5 21"/>
</svg>`;

}


if(ext==='pdf'){

return `
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
</svg>`;

}


return `
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M6 2h9l3 3v17H6z"/>
</svg>`;

}



function formatearPeso(bytes){

if(bytes<1024*1024)
return Math.round(bytes/1024)+' KB';

return(bytes/1024/1024).toFixed(1)+' MB';

}



function validarFormulario(){

if(!selectedCategory){

alert('Selecciona una categoría');

return false;

}


const inputs=formularioSolicitud.querySelectorAll('[data-required="true"]');


for(const input of inputs){

if(input.value.trim()===''){

input.focus();

input.classList.add('border-red-500');

return false;

}

}


return true;

}



if (solicitudForm) {

    solicitudForm.addEventListener('submit', (e) => {

        /*
        |--------------------------------------------------------------------------
        | Validar formulario
        |--------------------------------------------------------------------------
        */

        if (!validarFormulario()) {

            e.preventDefault();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Bloquear botón
        |--------------------------------------------------------------------------
        */

        if (!btnEnviar) {
            return;
        }

        btnEnviar.disabled = true;


        /*
        |--------------------------------------------------------------------------
        | Ocultar icono
        |--------------------------------------------------------------------------
        */

        if (btnEnviarIcono) {

            btnEnviarIcono.classList.add('hidden');

        }


        /*
        |--------------------------------------------------------------------------
        | Mostrar spinner
        |--------------------------------------------------------------------------
        */

        if (!document.getElementById('spinnerSolicitud')) {

            const spinner =
                document.createElement('span');

            spinner.id = 'spinnerSolicitud';

            spinner.className = 'spinner-envio';

            btnEnviar.insertBefore(
                spinner,
                btnEnviarTexto || null
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Cambiar texto
        |--------------------------------------------------------------------------
        */

        if (btnEnviarTexto) {

            btnEnviarTexto.textContent =
                'Enviando...';

        }

    });

}



if(btnCancelar){

btnCancelar.addEventListener('click',resetFormulario);

}



function resetFormulario(){

selectedCategory=null;

attachedFiles=[];


actualizarTarjetas();


if (formularioSolicitud) {

    formularioSolicitud.classList.add('hidden');

}

if (archivosSolicitud) {

    archivosSolicitud.classList.add('hidden');

}

if (accionesSolicitud) {

    accionesSolicitud.classList.add('hidden');

}

if(cambiarCategoria)
cambiarCategoria.classList.add('hidden');


if(categoriaSeleccionada)
categoriaSeleccionada.classList.add('hidden');


camposDinamicos.innerHTML='';


if(filesContainer)
filesContainer.innerHTML='';


if(categoriaInput)
categoriaInput.value='';


if(asunto)
asunto.value='';


if(descripcion)
descripcion.value='';


if(fileInput)
fileInput.value='';


}

const cerrarModalSolicitud =
document.getElementById('cerrarModalSolicitud');


if(cerrarModalSolicitud){

    cerrarModalSolicitud.addEventListener(
        'click',
        ()=>{

            const modal =
            document.getElementById('modalSolicitud');


            if(modal){

                modal.remove();

            }

        }
    );

}


});