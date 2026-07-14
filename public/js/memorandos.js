document.addEventListener('DOMContentLoaded', () => {

/*
|--------------------------------------------------------------------------
| REFERENCIAS GENERALES
|--------------------------------------------------------------------------
*/
const form = document.getElementById('documentForm');
const tipoSelect = document.getElementById('tipo_id');
const tipoDocumento = document.getElementById('tipo_documento');
const fecha = document.getElementById('fecha');
const btnText = document.getElementById('btnSubmitText');
const codigoDocumento = document.getElementById('codigoDocumento');
const headerMemorando = document.getElementById('header-memorando');
const previewPase = document.getElementById('preview-pase-temporal');
const informacionDocumento = document.getElementById('informacion-documento-general');
const fechaPase = document.getElementById('fecha_pase');
const numeroPase = document.getElementById('numero_pase');
const departamentoPase = document.getElementById('departamento_pase');
const tecnicoPase = document.getElementById('tecnico_pase');
const solicitantePase = document.getElementById('solicitante_pase');
const responsablePase = document.getElementById('responsable_pase');
const tipoPase = document.getElementById('tipo_pase');
const empresaPase = document.getElementById('empresa_pase');
const observacionesPase = document.getElementById('observaciones_pase');
const horaSalidaPase = document.getElementById('hora_salida_pase');
const guardiaSalidaPase = document.getElementById('guardia_salida_pase');
const horaEntradaPase = document.getElementById('hora_entrada_pase');
const guardiaEntradaPase = document.getElementById('guardia_entrada_pase');
const cerradaPase = document.getElementById('cerrada_pase');

/*
|--------------------------------------------------------------------------
| CONTENEDORES PRINCIPALES
|--------------------------------------------------------------------------
*/
const placeholder = document.getElementById('preview-placeholder-container');
const previewDocumento = document.getElementById('preview-documento-real');
const previewCompra = document.getElementById('preview-solicitud-compra');
const previews = document.querySelectorAll('.preview-dinamico');
const formularios = document.querySelectorAll('.formulario-dinamico');

/*
|--------------------------------------------------------------------------
| FECHAS
|--------------------------------------------------------------------------
*/
const meses = [
    'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
];

function formatoFechaCorta(date) {
    let mes = date.getMonth() + 1;
    let dia = date.getDate();
    if (mes < 10) mes = '0' + mes;
    if (dia < 10) dia = '0' + dia;
    return `${date.getFullYear()}-${mes}-${dia}`;
}

function formatoFechaLarga(valor) {
    if (!valor) return '-';
    const partes = valor.split('-');
    return `${partes[2]} de ${meses[Number(partes[1]) - 1]} del ${partes[0]}`;
}

if (fecha && !fecha.value) {
    fecha.value = formatoFechaCorta(new Date());
}

if (fechaPase && !fechaPase.value) {
    fechaPase.value = formatoFechaCorta(new Date());
}

/*
|--------------------------------------------------------------------------
| CONTROL DE PREVIEWS
|--------------------------------------------------------------------------
*/
function ocultarTodo() {
    if (placeholder) placeholder.style.display = 'none';
    if (previewDocumento) previewDocumento.style.display = 'none';
    if (previewCompra) previewCompra.style.display = 'none';
    if (previewPase) previewPase.style.display = 'none';
    previews.forEach(p => p.style.display = 'none');
}

function mostrarPlaceholder() {
    ocultarTodo();
    if (placeholder) placeholder.style.display = 'block';
}

function mostrarDocumento(tipo) {
    ocultarTodo();

    if (tipo === 'pase_temporal') {
        if (previewPase) previewPase.style.display = 'block';

        previews.forEach(p => {
            p.style.display = p.dataset.preview === tipo ? 'block' : 'none';
        });

        return;
    }

    if (previewDocumento) previewDocumento.style.display = 'block';

    previews.forEach(p => {
        p.style.display = p.dataset.preview === tipo ? 'block' : 'none';
    });
}

function mostrarCompra() {
    ocultarTodo();
    if (previewCompra) {
        previewCompra.style.display = 'block';
        const interno = previewCompra.querySelector('.preview-dinamico');
        if (interno) interno.style.display = 'block';
    }
}

/*
|--------------------------------------------------------------------------
| DATOS GENERALES
|--------------------------------------------------------------------------
*/
const outPara = document.getElementById('out_para');
const outCc = document.getElementById('out_cc');
const outDe = document.getElementById('out_de');
const outAsunto = document.getElementById('out_asunto');
const outFecha = document.getElementById('out_fecha');

const outParaCompra = document.getElementById('out_para_compra');
const outCcCompra = document.getElementById('out_cc_compra');
const outDeCompra = document.getElementById('out_de_compra');
const outAsuntoCompra = document.getElementById('out_asunto_compra');
const outFechaCompra = document.getElementById('out_fecha_compra');

function obtenerCC() {
    if (!cc) return '-';

    return [...cc.selectedOptions]
        .map(option => option.text)
        .join(', ') || '-';
}

function actualizarDatosGenerales() {
    const valorCC = obtenerCC();
    const valorFecha = formatoFechaLarga(fecha?.value);

    if (outPara) outPara.textContent = para?.value || '-';
    if (outCc) outCc.textContent = valorCC;
    if (outDe) outDe.textContent = de?.value || '-';
    if (outAsunto) outAsunto.textContent = asunto?.value || '-';
    if (outFecha) outFecha.textContent = valorFecha;

    if (outParaCompra) outParaCompra.textContent = para?.value || '-';
    if (outCcCompra) outCcCompra.textContent = valorCC;
    if (outDeCompra) outDeCompra.textContent = de?.value || '-';
    if (outAsuntoCompra) outAsuntoCompra.textContent = asunto?.value || '-';
    if (outFechaCompra) outFechaCompra.textContent = valorFecha;
}

/*
|--------------------------------------------------------------------------
| AUTORIZACION
|--------------------------------------------------------------------------
*/
const colaborador = document.getElementById('colaborador');
const cargoArea = document.getElementById('cargo_area');
const fechaIngreso = document.getElementById('fecha_ingreso');
const motivoAutorizacion = document.getElementById('motivo_autorizacion');

const outColaborador = document.getElementById('out_colaborador');
const outCargoArea = document.getElementById('out_cargo_area');
const outFechaIngreso = document.getElementById('out_fecha_ingreso');
const outMotivo = document.getElementById('out_motivo_autorizacion');

function actualizarAutorizacion() {
    if (outColaborador) outColaborador.textContent = colaborador?.value || '-';
    if (outCargoArea) outCargoArea.textContent = cargoArea?.value || '-';
    if (outFechaIngreso) outFechaIngreso.textContent = fechaIngreso?.value || '-';
    if (outMotivo) outMotivo.textContent = motivoAutorizacion?.value || '-';
}

/*
|--------------------------------------------------------------------------
| OBSERVACIONES
|--------------------------------------------------------------------------
*/
const observaciones = document.getElementById('observaciones');
const outObservaciones = document.getElementById('out_observaciones');

function actualizarObservaciones() {
    if (outObservaciones) outObservaciones.textContent = observaciones?.value || 'Sin observaciones.';
}

/*
|--------------------------------------------------------------------------
| PASE TEMPORAL
|--------------------------------------------------------------------------
*/
const outFechaPase = document.querySelector('#preview-pase-temporal [data-field="fecha"]');

function actualizarPaseTemporal() {
    const preview = document.querySelector('#preview-pase-temporal');
    if (!preview) return;

    const campos = {
        departamento: departamentoPase,
        tecnico: tecnicoPase,
        solicitante: solicitantePase,
        responsable: responsablePase,
        empresa: empresaPase
    };

    Object.entries(campos).forEach(([nombre, campo]) => {
        const salida = preview.querySelector(`[data-field="${nombre}"]`);
        if (salida) salida.textContent = campo?.value || '-';
    });

    const salidaFecha = preview.querySelector('[data-field="fecha"]');
    if (salidaFecha) salidaFecha.textContent = formatoFechaLarga(fechaPase?.value);

    const salidaObservaciones = preview.querySelector('[data-field="observaciones"]');
    if (salidaObservaciones) salidaObservaciones.textContent = observacionesPase?.value || 'Sin observaciones.';

    const salidaHoraSalida = preview.querySelector('[data-field="hora_salida"]');
    if (salidaHoraSalida) salidaHoraSalida.textContent = horaSalidaPase?.value || '';

    const salidaGuardiaSalida = preview.querySelector('[data-field="guardia_salida"]');
    if (salidaGuardiaSalida) salidaGuardiaSalida.textContent = guardiaSalidaPase?.value || '';

    const salidaHoraEntrada = preview.querySelector('[data-field="hora_entrada"]');
    if (salidaHoraEntrada) salidaHoraEntrada.textContent = horaEntradaPase?.value || '';

    const salidaGuardiaEntrada = preview.querySelector('[data-field="guardia_entrada"]');
    if (salidaGuardiaEntrada) salidaGuardiaEntrada.textContent = guardiaEntradaPase?.value || '';

    const salidaCerrada = preview.querySelector('[data-field="cerrada"]');
    if (salidaCerrada) salidaCerrada.textContent = cerradaPase?.checked ? 'X' : '';

    actualizarTipoSolicitud();
    actualizarEquiposPase();
}

/*
|--------------------------------------------------------------------------
| EQUIPOS (PASE TEMPORAL)
|--------------------------------------------------------------------------
*/
const equiposPaseBody = document.getElementById('equipos-body');
const previewEquiposPase = document.getElementById('previewEquipos');
const btnAgregarEquipoPase = document.getElementById('btnAgregarEquipo');
let contadorEquipoPase = 1;

function crearFilaEquipoPase() {
    const fila = document.createElement('tr');
    fila.className = 'equipo-row';
    fila.innerHTML = `
        <td><input type="number" name="cantidad[]"></td>
        <td><input type="text" name="marca[]"></td>
        <td><input type="text" name="serie[]"></td>
        <td><input type="text" name="detalle[]"></td>
        <td><input type="text" name="observacion[]"></td>
        <td><button type="button" class="btn-eliminar-equipo">X</button></td>
    `;
    contadorEquipoPase++;
    activarFilaEquipoPase(fila);
    return fila;
}

function activarFilaEquipoPase(fila) {
    fila.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', actualizarEquiposPase);
        input.addEventListener('change', actualizarEquiposPase);
    });

    const boton = fila.querySelector('.btn-eliminar-equipo');
    if (boton) {
        boton.addEventListener('click', () => {
            const cantidad = equiposPaseBody?.querySelectorAll('.equipo-row').length;
            if (cantidad > 1) {
                fila.remove();
                actualizarEquiposPase();
            }
        });
    }
}

function actualizarEquiposPase() {
    if (!equiposPaseBody || !previewEquiposPase) return;

    let html = '';
    equiposPaseBody.querySelectorAll('.equipo-row').forEach(fila => {
        const valores = [...fila.querySelectorAll('input')].map(input => input.value.trim());
        if (valores.some(valor => valor)) {
            html += `
            <tr>
                <td>${valores[0] || '-'}</td>
                <td>${valores[1] || '-'}</td>
                <td>${valores[2] || '-'}</td>
                <td>${valores[3] || '-'}</td>
                <td>${valores[4] || '-'}</td>
            </tr>
            `;
        }
    });

    previewEquiposPase.innerHTML = html || `
        <tr><td colspan="5">Sin equipos registrados</td></tr>
    `;
}

if (equiposPaseBody) {
    equiposPaseBody.querySelectorAll('.equipo-row').forEach(activarFilaEquipoPase);
}

if (btnAgregarEquipoPase) {
    btnAgregarEquipoPase.addEventListener('click', () => {
        equiposPaseBody.appendChild(crearFilaEquipoPase());
        actualizarEquiposPase();
    });
}

/*
|--------------------------------------------------------------------------
| ACTUALIZACION GLOBAL
|--------------------------------------------------------------------------
*/
function actualizarPreview() {
    const tipoActual = tipoSelect?.selectedOptions[0]?.dataset?.formulario;

    if (tipoActual === 'pase_temporal') {
        actualizarPaseTemporal();
    } else {
        actualizarDatosGenerales();
        actualizarAutorizacion();
        actualizarObservaciones();
        actualizarFechaCompra();
    }
}

[para, cc, de, asunto, fecha, colaborador, cargoArea, fechaIngreso, motivoAutorizacion, observaciones]
.forEach(campo => {
    if (!campo) return;
    campo.addEventListener('input', actualizarPreview);
    campo.addEventListener('change', actualizarPreview);
});

[departamentoPase, tecnicoPase, solicitantePase, responsablePase, empresaPase, tipoPase]
.forEach(campo => {
    if (!campo) return;
    campo.addEventListener('input', actualizarPaseTemporal);
    campo.addEventListener('change', actualizarPaseTemporal);
});

if (fechaPase) {
    fechaPase.addEventListener('input', actualizarPreview);
    fechaPase.addEventListener('change', actualizarPreview);
}

/*
|--------------------------------------------------------------------------
| EQUIPOS (AUTORIZACION)
|--------------------------------------------------------------------------
*/
const equipoFilas = document.getElementById('equipoFilas');
const equipoSalida = document.getElementById('equipoSalida');
const agregarFilaEquipo = document.getElementById('agregarFila');
let contadorEquipo = 1;

function crearFilaEquipo() {
    const fila = document.createElement('tr');
    fila.className = 'fila-equipo';
    fila.innerHTML = `
        <td><input type="text" name="equipos[${contadorEquipo}][descripcion]" placeholder="Laptop"></td>
        <td><input type="text" name="equipos[${contadorEquipo}][marca]" placeholder="Dell"></td>
        <td><input type="text" name="equipos[${contadorEquipo}][modelo]" placeholder="Modelo"></td>
        <td><input type="text" name="equipos[${contadorEquipo}][codigo]" placeholder="Serie"></td>
        <td><input type="text" name="equipos[${contadorEquipo}][color]" placeholder="Color"></td>
        <td><button type="button" class="btn-remove-fila">✕</button></td>
    `;
    contadorEquipo++;
    activarFilaEquipo(fila);
    return fila;
}

function activarFilaEquipo(fila) {
    fila.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', actualizarEquipos);
    });

    const boton = fila.querySelector('.btn-remove-fila');
    if (boton) {
        boton.addEventListener('click', () => {
            const cantidad = equipoFilas?.querySelectorAll('.fila-equipo').length;
            if (cantidad > 1) {
                fila.remove();
                actualizarEquipos();
            }
        });
    }
}

function actualizarEquipos() {
    if (!equipoFilas || !equipoSalida) return;

    let html = '';
    equipoFilas.querySelectorAll('.fila-equipo').forEach(fila => {
        const valores = [...fila.querySelectorAll('input')].map(input => input.value.trim());
        if (valores.some(valor => valor)) {
            html += `
            <tr>
                <td>${valores[0] || '-'}</td>
                <td>${valores[1] || '-'}</td>
                <td>${valores[2] || '-'}</td>
                <td>${valores[3] || '-'}</td>
                <td>${valores[4] || '-'}</td>
            </tr>
            `;
        }
    });

    equipoSalida.innerHTML = html || `
        <tr><td colspan="5">Sin equipos registrados</td></tr>
    `;
}

if (equipoFilas) {
    equipoFilas.querySelectorAll('.fila-equipo').forEach(activarFilaEquipo);
}

if (agregarFilaEquipo) {
    agregarFilaEquipo.addEventListener('click', () => {
        equipoFilas.appendChild(crearFilaEquipo());
        actualizarEquipos();
    });
}

function actualizarTipoSolicitud() {
    document.querySelectorAll('#preview-pase-temporal [data-check]').forEach(check => {
        check.textContent = check.dataset.check === tipoPase?.value ? '✓' : '';
    });
}

function generarNumeroPase() {
    if (numeroPase) {
        const numero = localStorage.getItem('numero_pase_temporal') || 0;
        const siguiente = Number(numero) + 1;
        localStorage.setItem('numero_pase_temporal', siguiente);
        numeroPase.value = siguiente.toString().padStart(5, '0');
    }
}

const paseCampos = document.querySelectorAll(
    '[data-formulario="pase_temporal"] input, [data-formulario="pase_temporal"] textarea, [data-formulario="pase_temporal"] select'
);

paseCampos.forEach(campo => {
    campo.addEventListener('input', actualizarPaseTemporal);
    campo.addEventListener('change', actualizarPaseTemporal);
});

/*
|--------------------------------------------------------------------------
| SOLICITUD DE COMPRA
|--------------------------------------------------------------------------
*/
const empresa = document.getElementById('empresa');
const tipoMercado = document.getElementById('tipo_mercado');
const inventario = document.getElementById('inventario');
const mantenimiento = document.getElementById('mantenimiento');
const proyecto = document.getElementById('proyecto');
const otros = document.getElementById('otros');
const proveedor = document.getElementById('proveedor');
const razonProveedor = document.getElementById('razon_proveedor');

const outInventario = document.getElementById('out_inventario');
const outMantenimiento = document.getElementById('out_mantenimiento');
const outProyecto = document.getElementById('out_proyecto');
const outOtros = document.getElementById('out_otros');
const outProveedor = document.getElementById('out_proveedor');
const outRazonProveedor = document.getElementById('out_razon_proveedor');
const outProveedorDocumento = document.getElementById('out_proveedor_documento');

function actualizarCompra() {
    if (outInventario) outInventario.textContent = inventario?.value || '-';
    if (outMantenimiento) outMantenimiento.textContent = mantenimiento?.value || '';
    if (outProyecto) outProyecto.textContent = proyecto?.value || '';
    if (outOtros) outOtros.textContent = otros?.value || '';
    if (outProveedor) outProveedor.textContent = proveedor?.value || '';
    if (outRazonProveedor) outRazonProveedor.textContent = razonProveedor?.value || '';
    if (outProveedorDocumento) outProveedorDocumento.textContent = proveedor?.value || 'proveedor';

    actualizarFechaCompra();
    actualizarArticulos();
}

function actualizarFechaCompra() {
    if (!fecha || !fecha.value) return;

    const partes = fecha.value.split('-');
    const dia = document.getElementById('out_dia');
    const mes = document.getElementById('out_mes');
    const anio = document.getElementById('out_anio');

    if (dia) dia.textContent = partes[2];
    if (mes) mes.textContent = partes[1];
    if (anio) anio.textContent = partes[0];
}

/*
|--------------------------------------------------------------------------
| ARTICULOS DE COMPRA
|--------------------------------------------------------------------------
*/
const articulosBody = document.getElementById('articulosBody');
const btnAgregarArticulo = document.getElementById('btnAgregarArticulo');
const tablaArticulosPreview = document.getElementById('tablaArticulosPreview');
const tablaDetalleMemo = document.getElementById('tablaDetalleMemo');

let contadorArticulo = 1;

function obtenerArticulos() {
    if (!articulosBody) return [];

    return [...articulosBody.querySelectorAll('.fila-articulo')].map(fila => {
        const campos = fila.querySelectorAll('input,select');
        return {
            codigo: campos[0]?.value || '',
            descripcion: campos[1]?.value || '',
            unidad: campos[2]?.value || '',
            cantidad: campos[3]?.value || '1'
        };
    });
}

/*
|--------------------------------------------------------------------------
| GENERAR TABLA ARTICULOS PREVIEW
|--------------------------------------------------------------------------
*/
function actualizarArticulos() {
    const articulos = obtenerArticulos();

    if (tablaArticulosPreview) {
        let html = '';
        articulos.forEach(item => {
            if (item.codigo || item.descripcion) {
                html += `
                <tr>
                    <td>${item.codigo}</td>
                    <td>${item.descripcion}</td>
                    <td>${item.unidad}</td>
                    <td>${item.cantidad}</td>
                </tr>
                `;
            }
        });

        tablaArticulosPreview.innerHTML = html || `
            <tr><td colspan="4">Sin artículos registrados</td></tr>
        `;
    }

    if (tablaDetalleMemo) {
        let htmlMemo = '';
        articulos.forEach(item => {
            if (item.codigo || item.descripcion) {
                htmlMemo += `
                <tr>
                    <td>${item.codigo}</td>
                    <td>${item.descripcion}</td>
                    <td>${item.cantidad}</td>
                </tr>
                `;
            }
        });

        tablaDetalleMemo.innerHTML = htmlMemo || `
            <tr><td colspan="3">Sin artículos registrados</td></tr>
        `;
    }
}

/*
|--------------------------------------------------------------------------
| AGREGAR ARTICULOS
|--------------------------------------------------------------------------
*/
function crearArticulo() {
    const fila = document.createElement('tr');
    fila.className = 'fila-articulo';
    fila.innerHTML = `
        <td><input type="text" name="articulos[${contadorArticulo}][codigo]"></td>
        <td><input type="text" name="articulos[${contadorArticulo}][descripcion]"></td>
        <td>
            <select name="articulos[${contadorArticulo}][unidad]">
                <option value="Unidad">Unidad</option>
                <option value="Servicio">Servicio</option>
            </select>
        </td>
        <td><input type="number" min="1" value="1" name="articulos[${contadorArticulo}][cantidad]"></td>
        <td><button type="button" class="btnEliminarArticulo">✕</button></td>
    `;
    contadorArticulo++;
    activarArticulo(fila);
    return fila;
}

function activarArticulo(fila) {
    fila.querySelectorAll('input,select').forEach(campo => {
        campo.addEventListener('input', actualizarArticulos);
        campo.addEventListener('change', actualizarArticulos);
    });

    const boton = fila.querySelector('.btnEliminarArticulo');
    if (boton) {
        boton.addEventListener('click', () => {
            fila.remove();
            actualizarArticulos();
        });
    }
}

if (articulosBody) {
    articulosBody.querySelectorAll('.fila-articulo').forEach(activarArticulo);
}

if (btnAgregarArticulo) {
    btnAgregarArticulo.addEventListener('click', () => {
        articulosBody.appendChild(crearArticulo());
        actualizarArticulos();
    });
}

/*
|--------------------------------------------------------------------------
| CHECKBOX EMPRESA / MERCADO
|--------------------------------------------------------------------------
*/
function actualizarCheckboxes() {
    const empresaChecks = document.querySelectorAll('.checkbox[data-empresa]');
    empresaChecks.forEach(check => {
        check.textContent = check.dataset.empresa === empresa?.value ? '✓' : '';
    });

    const local = document.getElementById('checkLocal');
    const exterior = document.getElementById('checkExterior');

    if (local) local.textContent = tipoMercado?.value === 'Compra local' ? '✓' : '';
    if (exterior) exterior.textContent = tipoMercado?.value === 'Compra exterior' ? '✓' : '';
}

[empresa, tipoMercado, inventario, mantenimiento, proyecto, otros, proveedor, razonProveedor]
.forEach(campo => {
    if (!campo) return;
    campo.addEventListener('input', () => {
        actualizarCompra();
        actualizarCheckboxes();
    });
    campo.addEventListener('change', () => {
        actualizarCompra();
        actualizarCheckboxes();
    });
});

/*
|--------------------------------------------------------------------------
| TABS MEMORANDO / COMPRA
|--------------------------------------------------------------------------
*/
document.querySelectorAll('.document-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.document-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.document-page').forEach(page => page.classList.remove('active'));

        tab.classList.add('active');

        const pagina = document.getElementById(`page-${tab.dataset.page}`);
        if (pagina) pagina.classList.add('active');
    });
});

/*
|--------------------------------------------------------------------------
| CAMBIO DE TIPO DE DOCUMENTO
|--------------------------------------------------------------------------
*/
function textoBoton(tipo) {
    switch (tipo) {
        case 'solicitud_compra':
            return 'Generar Solicitud de Compra';
        case 'autorizacion':
            return 'Generar autorización';
        case 'equipos':
            return 'Generar documento';
        default:
            return 'Generar documento';
    }
}

function mostrarFormulario(tipo) {
    formularios.forEach(formulario => {
        formulario.style.display = formulario.dataset.formulario === tipo ? 'block' : 'none';
    });
}

function aplicarTipo() {
    const opcion = tipoSelect?.selectedOptions[0];

    if (!opcion || !opcion.value) {
        mostrarFormulario(null);
        mostrarPlaceholder();
        if (btnText) btnText.textContent = 'Generar documento';
        if (tipoDocumento) tipoDocumento.value = '';
        return;
    }

    const tipo = opcion.dataset.formulario;

    if (informacionDocumento) {
        informacionDocumento.style.display = tipo === 'pase_temporal' ? 'none' : 'block';
    }

    actualizarDestinatarios(tipo);

    if (tipoDocumento) tipoDocumento.value = tipo;

    mostrarFormulario(tipo);

    if (tipo === 'solicitud_compra') {
        mostrarCompra();
    } else {
        mostrarDocumento(tipo);
    }

    if (btnText) btnText.textContent = textoBoton(tipo);

    actualizarPreview();
}

if (tipoSelect) {
    tipoSelect.addEventListener('change', aplicarTipo);
}

/*
|--------------------------------------------------------------------------
| DESTINATARIOS SEGÚN GESTIÓN
|--------------------------------------------------------------------------
*/
function actualizarDestinatarios(tipo) {
    if (!para || !cc) return;

    cc.innerHTML = '';

    if (tipo === 'solicitud_compra') {
        para.value = 'Ing. Osman Madrid - Director Sr Operaciones';
        cc.multiple = false;
        cc.innerHTML = `
            <option>
            Lic. Juan Carlos Dique - Director Finanzas y Administración
            </option>
        `;
    }

    if (tipo === 'autorizacion') {
        para.value = 'Lic. Byron Castro - Director de Seguridad';
        cc.multiple = false;
        cc.innerHTML = `
            <option>
            Ing. Wesly López - Director Senior de Información y Tecnología
            </option>

            <option>
            Lic. Fernando Figueroa - Coordinador de Infraestructura IT
            </option>
        `;
    }

    if (tipo === 'pase_temporal') {
        para.value = '';
        cc.multiple = false;
        cc.innerHTML = '';
    }

    actualizarPreview();
}

/*
|--------------------------------------------------------------------------
| ESTADO INICIAL
|--------------------------------------------------------------------------
*/
aplicarTipo();

if (tipoSelect?.selectedOptions[0]?.dataset?.formulario === 'pase_temporal') {
    generarNumeroPase();
    actualizarPreview();
    actualizarEquipos();
    actualizarCompra();
    actualizarArticulos();
    actualizarCheckboxes();
}

/*
|--------------------------------------------------------------------------
| ENVÍO DEL FORMULARIO
|--------------------------------------------------------------------------
*/
if (form) {
    form.addEventListener('submit', async e => {
        e.preventDefault();

        if (!tipoSelect || !tipoSelect.value) {
            alert('Seleccione el tipo de gestión');
            return;
        }

        const datos = new FormData(form);

        try {
            if (btnText) btnText.textContent = 'Generando...';

            const response = await fetch(form.action, {
                method: 'POST',
                body: datos,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                if (result.codigo) {
                    if (codigoDocumento) codigoDocumento.textContent = result.codigo;
                }
                alert('Documento generado correctamente');
            } else {
                alert(result.error || 'Ocurrió un error generando el documento');
            }
        } catch (error) {
            console.error(error);
            alert('Error comunicándose con el servidor');
        } finally {
            const tipoActual = tipoSelect?.selectedOptions[0]?.dataset?.formulario;
            if (btnText) btnText.textContent = textoBoton(tipoActual);
        }
    });
}

});