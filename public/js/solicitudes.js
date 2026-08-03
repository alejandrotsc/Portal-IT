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
let enviandoSolicitud=false;

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


const modalSolicitud = document.getElementById('modalSolicitud');
const cerrarModalSolicitud = document.getElementById('cerrarModalSolicitud');
const modalSolicitudIcono = document.getElementById('modalSolicitudIcono');
const modalSolicitudTitulo = document.getElementById('modalSolicitudTitulo');
const modalSolicitudMensaje = document.getElementById('modalSolicitudMensaje');
const modalSolicitudFolio = document.getElementById('modalSolicitudFolio');
const estadoCorreoSolicitud = document.getElementById('estadoCorreoSolicitud');
const estadoCorreoSolicitudIcono = document.getElementById('estadoCorreoSolicitudIcono');
const estadoCorreoSolicitudTitulo = document.getElementById('estadoCorreoSolicitudTitulo');
const estadoCorreoSolicitudMensaje = document.getElementById('estadoCorreoSolicitudMensaje');
const btnReportarSmtpSolicitud = document.getElementById('btnReportarSmtpSolicitud');
const estadoPersistenteSolicitud = document.getElementById('estadoPersistenteSolicitud');
const smtpEstadoSolicitud = document.getElementById('smtpEstadoSolicitud');
const btnReportarSmtpSolicitudPersistente =
    document.getElementById('btnReportarSmtpSolicitudPersistente');

if (modalSolicitud && modalSolicitud.parentElement !== document.body) {
    document.body.appendChild(modalSolicitud);
}

let seguimientoCorreoActual = 0;


// Elementos opcionales para archivos
const archivosSolicitud =
    document.getElementById('archivosSolicitud');

const filesContainer =
    document.getElementById('filesContainer');

const fileInput =
    document.getElementById('fileInput');


cards.forEach(card=>{

card.setAttribute('role','button');
card.setAttribute('tabindex','0');
card.setAttribute('aria-pressed','false');

card.addEventListener('click',()=>{
seleccionarCategoria(card,true);
});

card.addEventListener('keydown',(event)=>{

if(event.key==='Enter'||event.key===' '){
event.preventDefault();
seleccionarCategoria(card,true);
}

});

});


function seleccionarCategoria(card,debeDesplazar=false){

const category=card?.dataset.id;

if(
!category
||!Object.prototype.hasOwnProperty.call(FIELDS,category)
||!PLACEHOLDERS[category]
){
return;
}

selectedCategory=category;

if(categoriaInput)
categoriaInput.value=category;

actualizarTarjetas();
mostrarFormulario(category);
mostrarBadge(card);
actualizarPlaceholders(category);

formularioSolicitud?.classList.remove('hidden');
accionesSolicitud?.classList.remove('hidden');
cambiarCategoria?.classList.remove('hidden');

if(debeDesplazar){
setTimeout(()=>{
formularioSolicitud?.scrollIntoView({
behavior:'smooth',
block:'start'
});
},100);
}

}


/*
|--------------------------------------------------------------------------
| RESTAURAR CATEGORÍA Y PRELLENADO DEL CHATBOT
|--------------------------------------------------------------------------
*/

function aplicarPrellenadoChatbot() {

    const categoriaFormulario = String(
        solicitudForm?.dataset.prefillCategoria
        || categoriaInput?.value
        || ''
    ).trim();

    const asuntoFormulario = String(
        solicitudForm?.dataset.prefillAsunto
        || ''
    ).trim();

    const descripcionFormulario = String(
        solicitudForm?.dataset.prefillDescripcion
        || ''
    ).trim();

    if (
        asunto
        && !asunto.value.trim()
        && asuntoFormulario
    ) {
        asunto.value = asuntoFormulario;
    }

    if (
        descripcion
        && !descripcion.value.trim()
        && descripcionFormulario
    ) {
        descripcion.value =
            descripcionFormulario;
    }

    if (
        !categoriaFormulario
        || !PLACEHOLDERS[categoriaFormulario]
    ) {
        return;
    }

    const tarjetaCategoria =
        Array.from(cards).find(
            card =>
                card.dataset.id ===
                categoriaFormulario
        );

    if (!tarjetaCategoria) {
        return;
    }

    seleccionarCategoria(
        tarjetaCategoria,
        true
    );
}

aplicarPrellenadoChatbot();


function actualizarPlaceholders(category){

if(!PLACEHOLDERS[category])return;

if(asunto)
asunto.placeholder=PLACEHOLDERS[category].asunto;

if(descripcion)
descripcion.placeholder=PLACEHOLDERS[category].descripcion;

}

function actualizarTarjetas(){

const modoOscuro=
document.documentElement.classList.contains('dark');

cards.forEach(card=>{

const seleccionado=card.dataset.id===selectedCategory;
const color=card.dataset.color;
const bg=card.dataset.bg;

const iconBox=card.querySelector('.icon-container');
const icon=card.querySelector('.icon-container svg,.icon-container i');
const check=card.querySelector('.check-categoria');

card.setAttribute(
'aria-pressed',
seleccionado?'true':'false'
);

card.classList.toggle(
'categoria-card-seleccionada',
seleccionado
);

if(seleccionado){

card.style.borderColor=color;
card.style.boxShadow=`0 8px 20px ${color}30`;

}else{

card.style.borderColor='';
card.style.boxShadow='';

}

card.style.backgroundColor=seleccionado
    ? (modoOscuro ? `${color}26` : bg)
    : (modoOscuro ? '#0f172a' : 'white');

if(iconBox){
    iconBox.style.setProperty(
        'background-color',
        seleccionado
            ? color
            : (modoOscuro ? '#1e293b' : bg),
        'important'
    );
}

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
const modoOscuro=
document.documentElement.classList.contains('dark');

categoriaSeleccionada.textContent=titulo;
categoriaSeleccionada.style.color=modoOscuro
    ? card.dataset.color
    : card.dataset.color;
categoriaSeleccionada.style.backgroundColor=modoOscuro
    ? `${card.dataset.color}26`
    : card.dataset.bg;
categoriaSeleccionada.style.borderColor=card.dataset.color;

categoriaSeleccionada.classList.remove('hidden');

}


const observadorTema=new MutationObserver(()=>{
    actualizarTarjetas();

    const tarjetaSeleccionada=Array.from(cards).find(
        card=>card.dataset.id===selectedCategory
    );

    if(tarjetaSeleccionada){
        mostrarBadge(tarjetaSeleccionada);
    }
});

observadorTema.observe(
    document.documentElement,
    {
        attributes:true,
        attributeFilter:['class'],
    }
);



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
input.required=field.required===true;
input.id=`campo-dinamico-${field.id}`;

label.htmlFor=input.id;


const inputContainer=
    document.createElement('div');

inputContainer.className=
    'group/field flex min-h-11 w-full items-center gap-2.5 '
    + 'rounded-lg border border-border bg-white px-3.5 '
    + 'transition-all duration-200 focus-within:border-primary '
    + 'focus-within:ring-2 focus-within:ring-primary/10 '
    + 'dark:border-slate-700/70 dark:bg-slate-900/80 '
    + 'dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/15';


const icono=
    document.createElement('i');

icono.dataset.lucide=
    field.type==='select'
        ? 'list-filter'
        : field.type==='textarea'
            ? 'align-left'
            : 'text';

icono.setAttribute(
    'stroke-width',
    '1.8'
);

icono.className=
    'h-4 w-4 shrink-0 text-muted-foreground '
    + 'transition-all duration-200 '
    + 'group-focus-within/field:text-blue-600 '
    + 'dark:text-slate-400 dark:group-focus-within/field:text-blue-400 '
    + 'motion-safe:group-focus-within/field:scale-110';

if(field.type==='textarea'){
    inputContainer.classList.remove(
        'items-center'
    );

    inputContainer.classList.add(
        'items-start'
    );

    icono.classList.add(
        'mt-3'
    );
}


input.className=
    'w-full border-0 bg-transparent py-2.5 text-sm '
    + 'text-foreground placeholder:text-muted-foreground '
    + 'focus:outline-none focus:ring-0 dark:text-slate-200 '
    + 'dark:placeholder:text-slate-500 '
    + (
        field.type==='select'
            ? 'appearance-none'
            : ''
    );


input.addEventListener(
    'input',
    () => {
        inputContainer.classList.remove(
            'border-red-300',
            'ring-2',
            'ring-red-500/10'
        );
    }
);


inputContainer.appendChild(icono);
inputContainer.appendChild(input);


if(field.type==='select'){
    const chevron=
        document.createElement('i');

    chevron.dataset.lucide=
        'chevron-down';

    chevron.setAttribute(
        'stroke-width',
        '1.8'
    );

    chevron.className=
        'h-4 w-4 shrink-0 text-muted-foreground '
        + 'transition-transform duration-200 '
        + 'group-focus-within/field:rotate-180 '
        + 'group-focus-within/field:text-blue-600 '
        + 'dark:text-slate-400 dark:group-focus-within/field:text-blue-400';

    inputContainer.appendChild(
        chevron
    );
}


wrapper.appendChild(inputContainer);
camposDinamicos.appendChild(wrapper);


});

if(window.lucide){
    lucide.createIcons();
}

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

div.className=
    'group flex items-center gap-3 rounded-xl border '
    + 'border-border bg-white px-3 py-2.5 shadow-sm '
    + 'transition-all duration-200 hover:border-primary/20 '
    + 'hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900/80 '
    + 'dark:hover:border-blue-700/70 dark:hover:shadow-black/20';


div.innerHTML=`

<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
${iconoArchivo(file.name)}
</div>

<div class="flex-1 min-w-0">
<p class="truncate text-xs font-medium text-foreground">${escaparHtml(file.name)}</p>
<p class="text-[10px] text-muted-foreground">${formatearPeso(file.size)}</p>
</div>

<button type="button" class="eliminar-archivo inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground opacity-0 transition-all duration-200 hover:bg-red-50 hover:text-red-600 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 group-hover:opacity-100 dark:text-slate-400 dark:hover:bg-red-950/45 dark:hover:text-red-400" data-id="${item.id}" aria-label="Eliminar archivo">
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

const contenedor=
    input.closest('.group\\/field');

contenedor?.classList.remove(
    'border-border'
);

contenedor?.classList.add(
    'border-red-300',
    'ring-2',
    'ring-red-500/10'
);

return false;

}

}


return true;

}



if (solicitudForm) {
    solicitudForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (enviandoSolicitud || !validarFormulario()) {
            return;
        }

        enviandoSolicitud = true;
        bloquearBotonEnvio();

        try {
            const response = await fetch(solicitudForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(solicitudForm),
            });

            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch {
                throw new Error('El servidor devolvió una respuesta inválida.');
            }

            if (!response.ok || data.success !== true) {
                throw new Error(
                    data.message
                    ?? data.error
                    ?? 'No fue posible registrar la solicitud.'
                );
            }

            configurarResultadoSolicitud(data);
            abrirModalSolicitud();

            const estado = String(data.email?.status ?? '').toLowerCase();

            if (
                data.email?.queued === true
                || estado === 'pendiente'
                || estado === 'enviando'
            ) {
                vigilarEstadoCorreo(data.email?.delivery_id, data);
            }

        } catch (error) {
            mostrarErrorSolicitud(error);

        } finally {
            enviandoSolicitud = false;
            restaurarBotonEnvio();
        }
    });
}


function bloquearBotonEnvio() {
    if (!btnEnviar) return;

    btnEnviar.disabled = true;
    btnEnviarIcono?.classList.add('hidden');

    if (!document.getElementById('spinnerSolicitud')) {
        const spinner = document.createElement('span');
        spinner.id = 'spinnerSolicitud';
        spinner.className = 'spinner-envio';
        btnEnviar.insertBefore(spinner, btnEnviarTexto || null);
    }

    if (btnEnviarTexto) {
        btnEnviarTexto.textContent = 'Registrando...';
    }
}


function restaurarBotonEnvio() {
    if (!btnEnviar) return;

    btnEnviar.disabled = false;
    document.getElementById('spinnerSolicitud')?.remove();
    btnEnviarIcono?.classList.remove('hidden');

    if (btnEnviarTexto) {
        btnEnviarTexto.textContent = 'Enviar solicitud';
    }

    if (window.lucide) lucide.createIcons();
}


function configurarResultadoSolicitud(data) {
    const email = data.email ?? {};
    const estado = String(email.status ?? '').toLowerCase();

    if (modalSolicitudMensaje) {
        modalSolicitudMensaje.textContent =
            data.message ?? 'La solicitud fue registrada correctamente.';
    }

    const folio = data.folio ?? data.codigo ?? null;

    if (modalSolicitudFolio) {
        modalSolicitudFolio.textContent = folio ?? '';
        modalSolicitudFolio.classList.toggle('hidden', !folio);
        modalSolicitudFolio.classList.toggle('inline-flex', Boolean(folio));
    }

    if (email.sent === true || estado === 'enviado') {
        configurarCorreoExitoso();
    } else if (email.failed === true || estado === 'fallido') {
        configurarCorreoFallido(data);
    } else {
        configurarCorreoEnCola(estado);
    }
}


function configurarCorreoEnCola(estado = 'pendiente', attempts = 0) {
    ocultarBotonesOutlook();

    if (modalSolicitudIcono) {
        modalSolicitudIcono.className =
            'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 shadow-sm dark:border-blue-800 dark:bg-blue-950/45';
        modalSolicitudIcono.innerHTML =
            '<i data-lucide="clock-3" stroke-width="1.8" class="h-8 w-8 text-blue-600 dark:text-blue-400"></i>';
    }

    if (modalSolicitudTitulo) {
        modalSolicitudTitulo.textContent = 'Solicitud registrada';
    }

    if (estadoCorreoSolicitud) {
        estadoCorreoSolicitud.className =
            'rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/50 p-5 text-left shadow-sm dark:border-blue-800 dark:from-blue-950/45 dark:via-slate-900 dark:to-sky-950/30';
    }

    if (estadoCorreoSolicitudIcono) {
        estadoCorreoSolicitudIcono.className =
            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-200 bg-white text-blue-600 shadow-sm dark:border-blue-800 dark:bg-slate-900 dark:text-blue-400';
        estadoCorreoSolicitudIcono.innerHTML =
            '<i data-lucide="mail" stroke-width="1.8" class="h-5 w-5"></i>';
    }

    if (estadoCorreoSolicitudTitulo) {
        estadoCorreoSolicitudTitulo.className =
            'text-sm font-semibold text-blue-800 dark:text-blue-300';
        estadoCorreoSolicitudTitulo.textContent =
            estado === 'enviando' ? 'Enviando correo' : 'Correo en procesamiento';
    }

    if (estadoCorreoSolicitudMensaje) {
        estadoCorreoSolicitudMensaje.className =
            'mt-1.5 text-xs leading-relaxed text-blue-700 dark:text-blue-400';

        estadoCorreoSolicitudMensaje.textContent =
            estado === 'enviando' && Number(attempts) > 0
                ? `El servidor está procesando el correo. Intento ${attempts}.`
                : 'La notificación fue agregada a la cola y será enviada en segundo plano.';
    }

    actualizarEstadoPersistente('queued');

    if (window.lucide) lucide.createIcons();
}


function configurarCorreoExitoso() {
    ocultarBotonesOutlook();

    if (modalSolicitudIcono) {
        modalSolicitudIcono.className =
            'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/45';
        modalSolicitudIcono.innerHTML =
            '<i data-lucide="circle-check-big" stroke-width="1.8" class="h-8 w-8 text-emerald-600 dark:text-emerald-400"></i>';
    }

    if (modalSolicitudTitulo) {
        modalSolicitudTitulo.textContent = 'Solicitud enviada';
    }

    if (modalSolicitudMensaje) {
        modalSolicitudMensaje.textContent =
            'La solicitud fue registrada correctamente y la notificación por correo fue enviada.';
    }

    if (estadoCorreoSolicitud) {
        estadoCorreoSolicitud.className =
            'rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 p-5 text-left shadow-sm dark:border-emerald-800 dark:from-emerald-950/45 dark:via-slate-900 dark:to-teal-950/30';
    }

    if (estadoCorreoSolicitudIcono) {
        estadoCorreoSolicitudIcono.className =
            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-white text-emerald-600 shadow-sm dark:border-emerald-800 dark:bg-slate-900 dark:text-emerald-400';
        estadoCorreoSolicitudIcono.innerHTML =
            '<i data-lucide="mail-check" stroke-width="1.8" class="h-5 w-5"></i>';
    }

    if (estadoCorreoSolicitudTitulo) {
        estadoCorreoSolicitudTitulo.className =
            'text-sm font-semibold text-emerald-800 dark:text-emerald-300';
        estadoCorreoSolicitudTitulo.textContent =
            'Correo enviado correctamente';
    }

    if (estadoCorreoSolicitudMensaje) {
        estadoCorreoSolicitudMensaje.className =
            'mt-1.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400';
        estadoCorreoSolicitudMensaje.textContent =
            'El servidor SMTP aceptó la notificación para el equipo de soporte TI.';
    }

    actualizarEstadoPersistente('success');

    if (window.lucide) lucide.createIcons();
}


function configurarCorreoFallido(data) {
    const outlookUrl = construirUrlOutlook(data);
    mostrarBotonesOutlook(outlookUrl);

    if (modalSolicitudIcono) {
        modalSolicitudIcono.className =
            'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 shadow-sm dark:border-amber-800 dark:bg-amber-950/45';
        modalSolicitudIcono.innerHTML =
            '<i data-lucide="mail-warning" stroke-width="1.8" class="h-8 w-8 text-amber-600 dark:text-amber-400"></i>';
    }

    if (modalSolicitudTitulo) {
        modalSolicitudTitulo.textContent =
            'Solicitud registrada con advertencia';
    }

    if (modalSolicitudMensaje) {
        modalSolicitudMensaje.textContent =
            'La solicitud quedó registrada, pero no fue posible enviar la notificación automática.';
    }

    if (estadoCorreoSolicitud) {
        estadoCorreoSolicitud.className =
            'rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50 p-5 text-left shadow-sm dark:border-amber-800 dark:from-amber-950/45 dark:via-slate-900 dark:to-orange-950/30';
    }

    if (estadoCorreoSolicitudIcono) {
        estadoCorreoSolicitudIcono.className =
            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-200 bg-white text-amber-600 shadow-sm dark:border-amber-800 dark:bg-slate-900 dark:text-amber-400';
        estadoCorreoSolicitudIcono.innerHTML =
            '<i data-lucide="mail-warning" stroke-width="1.8" class="h-5 w-5"></i>';
    }

    if (estadoCorreoSolicitudTitulo) {
        estadoCorreoSolicitudTitulo.className =
            'text-sm font-semibold text-amber-800 dark:text-amber-300';
        estadoCorreoSolicitudTitulo.textContent =
            'No se pudo enviar el correo';
    }

    if (estadoCorreoSolicitudMensaje) {
        estadoCorreoSolicitudMensaje.className =
            'mt-1.5 text-xs leading-relaxed text-amber-700 dark:text-amber-400';
        estadoCorreoSolicitudMensaje.textContent =
            'La solicitud quedó registrada. Puedes informar la falla mediante Outlook 365.';
    }

    actualizarEstadoPersistente('warning');

    if (window.lucide) lucide.createIcons();
}


async function vigilarEstadoCorreo(deliveryId, datosRegistro) {
    if (!deliveryId || !window.emailDeliveryStatusUrl) return;

    const seguimientoId = ++seguimientoCorreoActual;
    const maxConsultas = 20;

    for (let consulta = 1; consulta <= maxConsultas; consulta++) {
        await esperar(1500);

        if (seguimientoId !== seguimientoCorreoActual) return;

        try {
            const url = window.emailDeliveryStatusUrl.replace(
                '__DELIVERY_ID__',
                encodeURIComponent(deliveryId)
            );

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            });

            if (!response.ok) continue;

            const resultado = await response.json();
            const estado = String(resultado.email?.status ?? '').toLowerCase();

            if (resultado.email?.sent === true || estado === 'enviado') {
                configurarCorreoExitoso();
                return;
            }

            if (resultado.email?.failed === true || estado === 'fallido') {
                configurarCorreoFallido({
                    ...datosRegistro,
                    email: {
                        ...datosRegistro?.email,
                        ...resultado.email,
                    },
                });
                return;
            }

            configurarCorreoEnCola(estado, resultado.email?.attempts);

        } catch (error) {
            console.warn('Error consultando el estado del correo:', error);
        }
    }

    if (
        seguimientoId === seguimientoCorreoActual
        && estadoCorreoSolicitudMensaje
    ) {
        estadoCorreoSolicitudMensaje.textContent =
            'El correo continúa en cola. El proceso seguirá ejecutándose en segundo plano.';
    }
}


function construirUrlOutlook(data) {
    const boton =
        btnReportarSmtpSolicitudPersistente ?? btnReportarSmtpSolicitud;

    const recipient =
        boton?.dataset.recipient || 'helpdesk@televicentro.hn';

    const userName =
        boton?.dataset.userName || 'No especificado';

    const userEmail =
        boton?.dataset.userEmail || 'No especificado';

    const categoria =
        categoriaSeleccionada?.textContent?.trim()
        || selectedCategory
        || 'No especificada';

    const asuntoSolicitud =
        asunto?.value?.trim() || 'No especificado';

    const folio =
        data.folio ?? data.codigo ?? 'Sin folio';

    const subject =
        `[Portal TI] Seguimiento de solicitud ${folio}`;

    const body = [
        'Hola, equipo de Helpdesk:',
        '',
        'La solicitud quedó registrada en el Portal TI, pero la notificación automática por correo no pudo enviarse.',
        '',
        'Datos del usuario',
        `Nombre: ${userName}`,
        `Correo: ${userEmail}`,
        '',
        'Información de la solicitud',
        `Folio: ${folio}`,
        `Categoría: ${categoria}`,
        `Asunto: ${asuntoSolicitud}`,
        `Fecha de la solicitud: ${new Date().toLocaleString('es-HN', {
            dateStyle: 'long',
            timeStyle: 'short',
        })}`,
        '',
        'La solicitud quedó registrada correctamente en el Portal TI.',
        '',
        'Por favor, ayúdenme a darle seguimiento.',
    ].join('\r\n');

    return 'https://outlook.office.com/mail/deeplink/compose'
        + `?to=${encodeURIComponent(recipient)}`
        + `&subject=${encodeURIComponent(subject)}`
        + `&body=${encodeURIComponent(body)}`;
}


function mostrarBotonesOutlook(outlookUrl) {
    [btnReportarSmtpSolicitud, btnReportarSmtpSolicitudPersistente]
        .forEach((boton) => {
            if (!boton) return;

            boton.dataset.outlookUrl = outlookUrl;
            boton.classList.remove('hidden');
            boton.classList.add('inline-flex');
        });
}


function ocultarBotonesOutlook() {
    [btnReportarSmtpSolicitud, btnReportarSmtpSolicitudPersistente]
        .forEach((boton) => {
            if (!boton) return;

            boton.classList.add('hidden');
            boton.classList.remove('inline-flex');
            delete boton.dataset.outlookUrl;
        });
}


function actualizarEstadoPersistente(estado) {
    if (!estadoPersistenteSolicitud || !smtpEstadoSolicitud) return;

    const opciones = {
        queued: [
            'border-blue-200 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/50 dark:border-blue-800 dark:from-blue-950/45 dark:via-slate-900 dark:to-sky-950/30',
            'text-blue-700 dark:text-blue-300',
            'bg-blue-500',
            'Correo pendiente en la cola',
        ],
        success: [
            'border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 dark:border-emerald-800 dark:from-emerald-950/45 dark:via-slate-900 dark:to-teal-950/30',
            'text-emerald-700 dark:text-emerald-300',
            'bg-emerald-500',
            'Último envío de correo SMTP correcto',
        ],
        warning: [
            'border-amber-200 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50 dark:border-amber-800 dark:from-amber-950/45 dark:via-slate-900 dark:to-orange-950/30',
            'text-amber-700 dark:text-amber-300',
            'bg-amber-500',
            'Último envío de correo SMTP fallido',
        ],
    };

    const opcion = opciones[estado] ?? opciones.queued;

    estadoPersistenteSolicitud.className =
        `flex flex-col gap-3 overflow-hidden rounded-xl border p-4 shadow-sm sm:flex-row sm:items-center ${opcion[0]}`;

    smtpEstadoSolicitud.className =
        `inline-flex items-center gap-2 text-xs font-medium ${opcion[1]}`;

    smtpEstadoSolicitud.innerHTML =
        `<span class="h-2.5 w-2.5 shrink-0 rounded-full ${opcion[2]}"></span>${opcion[3]}`;
}


function abrirModalSolicitud() {
    if (!modalSolicitud) return;

    modalSolicitud.classList.remove('hidden');
    modalSolicitud.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}


function cerrarModalRespuesta() {
    if (!modalSolicitud) return;

    modalSolicitud.classList.add('hidden');
    modalSolicitud.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}


function mostrarErrorSolicitud(error) {
    seguimientoCorreoActual++;

    if (modalSolicitudIcono) {
        modalSolicitudIcono.className =
            'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-red-200 bg-red-50 shadow-sm dark:border-red-800 dark:bg-red-950/45';
        modalSolicitudIcono.innerHTML =
            '<i data-lucide="circle-x" stroke-width="1.8" class="h-8 w-8 text-red-600 dark:text-red-400"></i>';
    }

    if (modalSolicitudTitulo) {
        modalSolicitudTitulo.textContent =
            'No se pudo registrar la solicitud';
    }

    if (modalSolicitudMensaje) {
        modalSolicitudMensaje.textContent =
            error?.message ?? 'Ocurrió un error inesperado.';
    }

    if (estadoCorreoSolicitud) {
        estadoCorreoSolicitud.className =
            'rounded-2xl border border-red-200 bg-gradient-to-br from-red-50/80 via-white to-rose-50/50 p-5 text-left shadow-sm dark:border-red-800 dark:from-red-950/45 dark:via-slate-900 dark:to-rose-950/30';
    }

    if (estadoCorreoSolicitudIcono) {
        estadoCorreoSolicitudIcono.className =
            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-red-200 bg-white text-red-600 shadow-sm dark:border-red-800 dark:bg-slate-900 dark:text-red-400';
        estadoCorreoSolicitudIcono.innerHTML =
            '<i data-lucide="triangle-alert" stroke-width="1.8" class="h-5 w-5"></i>';
    }

    if (estadoCorreoSolicitudTitulo) {
        estadoCorreoSolicitudTitulo.className =
            'text-sm font-semibold text-red-800 dark:text-red-300';
        estadoCorreoSolicitudTitulo.textContent =
            'La gestión no fue registrada';
    }

    if (estadoCorreoSolicitudMensaje) {
        estadoCorreoSolicitudMensaje.className =
            'mt-1.5 text-xs leading-relaxed text-red-700 dark:text-red-400';
        estadoCorreoSolicitudMensaje.textContent =
            'Revisa la información e intenta nuevamente.';
    }

    ocultarBotonesOutlook();
    abrirModalSolicitud();

    if (window.lucide) lucide.createIcons();
}


function abrirReporteOutlook(boton, event) {
    event?.preventDefault();

    const outlookUrl = boton?.dataset.outlookUrl;
    if (!outlookUrl) return;

    const ventana = window.open(outlookUrl, '_blank');

    if (ventana) {
        ventana.opener = null;
    } else {
        window.location.href = outlookUrl;
    }
}


[btnReportarSmtpSolicitud, btnReportarSmtpSolicitudPersistente]
    .forEach((boton) => {
        boton?.addEventListener('click', (event) => {
            abrirReporteOutlook(boton, event);
        });
    });


function esperar(milisegundos) {
    return new Promise((resolve) => {
        window.setTimeout(resolve, milisegundos);
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

cerrarModalSolicitud?.addEventListener(
    'click',
    cerrarModalRespuesta
);

modalSolicitud?.addEventListener('click', (event) => {
    if (event.target === modalSolicitud) {
        cerrarModalRespuesta();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        cerrarModalRespuesta();
    }
});


if(window.lucide){

    lucide.createIcons();

}


function escaparHtml(valor){

    const elemento=
        document.createElement('div');

    elemento.textContent=
        String(valor??'');

    return elemento.innerHTML;

}


});