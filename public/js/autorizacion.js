/*
|--------------------------------------------------------------------------
| MODAL AYUDA SERIE — funciones globales
|--------------------------------------------------------------------------
| Se declaran en el scope global (fuera de DOMContentLoaded) porque el
| botón en la tabla las llama con onclick="abrirAyudaSerie()" inline,
| y el HTML no tiene acceso a funciones declaradas dentro de un closure.
*/

function abrirAyudaSerie() {

    const modal = document.getElementById('modalSerie');

    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

}


function cerrarAyudaSerie() {

    const modal = document.getElementById('modalSerie');

    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');

}


document.addEventListener('DOMContentLoaded', () => {


    /*
    |--------------------------------------------------------------------------
    | TABLA DE EQUIPOS — Agregar / Eliminar filas
    |--------------------------------------------------------------------------
    */

    const tabla        = document.getElementById('equipoFilas');
    const botonAgregar = document.getElementById('agregarFila');
    const template      = document.getElementById('templateEquipo');


    if (tabla && botonAgregar && template) {

        let contador = tabla.querySelectorAll('.fila-equipo').length;


        botonAgregar.addEventListener('click', () => {

            let contenido = template.innerHTML;

            contenido = contenido.replaceAll('INDEX', contador);

            tabla.insertAdjacentHTML('beforeend', contenido);

            contador++;

            if (window.lucide) lucide.createIcons();

            const nuevaFila = tabla.lastElementChild;

            nuevaFila
                ?.querySelector('input')
                ?.focus();

        });


        document.addEventListener('click', (e) => {

            const boton = e.target.closest('.btn-remove-fila');

            if (!boton) return;

            const fila         = boton.closest('.fila-equipo');
            const filasActuales = tabla.querySelectorAll('.fila-equipo');

            if (!fila || filasActuales.length <= 1) return;

            fila.classList.add(
                'opacity-0',
                'scale-[0.98]'
            );

            window.setTimeout(
                () => fila.remove(),
                150
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | MODAL AYUDA SERIE — cierre por click-fuera y tecla Escape
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', (e) => {

        const modal = document.getElementById('modalSerie');

        if (modal && e.target === modal) {
            cerrarAyudaSerie();
        }

    });


    document.addEventListener('keydown', (e) => {

        if (e.key === 'Escape') {
            cerrarAyudaSerie();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS DE MODALES
    |--------------------------------------------------------------------------
    */

    const form                    = document.getElementById('documentForm');
    const btnPreview              = document.getElementById('btnPreview');
    const btnGenerar              = document.getElementById('btnGenerar');

    const modalPreview            = document.getElementById('modalPreview');
    const contenidoPreview        = document.getElementById('contenidoPreview');
    const btnCerrarPreview        = document.getElementById('btnCerrarPreview');
    const btnCerrarPreview2       = document.getElementById('btnCerrarPreview2');
    const btnGenerarDesdePreview  = document.getElementById('btnGenerarDesdePreview');

    const modalDescarga           = document.getElementById('modalDescarga');
    const linkDescarga            = document.getElementById('linkDescarga');
    const btnCerrarDescarga       = document.getElementById('btnCerrarDescarga');

    const smtpEstadoPrevio        = document.getElementById('smtpEstadoPrevio');
    const modalResultadoIcono     = document.getElementById('modalResultadoIcono');
    const modalResultadoTitulo    = document.getElementById('modalResultadoTitulo');
    const modalResultadoMensaje   = document.getElementById('modalResultadoMensaje');
    const estadoCorreo            = document.getElementById('estadoCorreoAutorizacion');
    const estadoCorreoIcono       = document.getElementById('estadoCorreoIconoContenedor');
    const estadoCorreoTitulo      = document.getElementById('estadoCorreoTitulo');
    const estadoCorreoMensaje     = document.getElementById('estadoCorreoMensaje');
    const btnReportarSmtp         = document.getElementById('btnReportarSmtp');
    const btnReportarPersistente  = document.getElementById('btnReportarSmtpPersistente');

    const modalError              = document.getElementById('modalErrorAutorizacion');
    const textoError              = document.getElementById('textoErrorAutorizacion');
    const btnCerrarError          = document.getElementById('btnCerrarErrorAutorizacion');


    /*
    |--------------------------------------------------------------------------
    | Mover modales directamente al body
    |--------------------------------------------------------------------------
    |
    | Evita que contenedores con transform, overflow o z-index limiten el
    | fondo oscuro y el desenfoque sobre el header.
    |
    */

    [
        modalPreview,
        modalDescarga,
        modalError,
    ].forEach((modal) => {
        if (
            modal
            && modal.parentElement !== document.body
        ) {
            document.body.appendChild(
                modal
            );
        }
    });


    if (!form) return;

    let enviando = false;
    let seguimientoCorreoActual = 0;


    /*
    |--------------------------------------------------------------------------
    | ABRIR REPORTE EN OUTLOOK 365
    |--------------------------------------------------------------------------
    |
    | Funciona tanto si btnReportarSmtp es un <button> como si es un <a>.
    | La URL se construye solamente cuando el SMTP reporta un fallo.
    |
    */

    function abrirReporteOutlook(button, event) {

        event?.preventDefault();

        const outlookUrl =
            button?.dataset.outlookUrl;

        if (!outlookUrl) {
            console.warn(
                'No se encontró la URL para abrir Outlook 365.'
            );
            return;
        }

        const outlookWindow = window.open(
            outlookUrl,
            '_blank'
        );

        if (outlookWindow) {
            outlookWindow.opener = null;
        } else {
            window.location.href = outlookUrl;
        }

    }

    btnReportarSmtp?.addEventListener(
        'click',
        (event) => abrirReporteOutlook(btnReportarSmtp, event)
    );

    btnReportarPersistente?.addEventListener(
        'click',
        (event) => abrirReporteOutlook(btnReportarPersistente, event)
    );


    /*
    |--------------------------------------------------------------------------
    | VER PREVIEW — Abre el modal con el documento renderizado
    |--------------------------------------------------------------------------
    */

    btnPreview?.addEventListener('click', async () => {

        abrirModal(modalPreview);

        // Mostrar spinner
        contenidoPreview.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="flex flex-col items-center gap-3 text-muted-foreground">
                    <svg class="h-6 w-6 animate-spin text-primary" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-sm">Cargando preview...</span>
                </div>
            </div>
        `;

        try {

            // Cargar el partial de preview
            const url      = window.autorizacionPreviewUrl ?? '/memorandos/preview/autorizacion';
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('No se pudo cargar el preview.');

            const html = await response.text();

            contenidoPreview.innerHTML = html;

            // Rellenar los campos del preview con los valores actuales del form
            rellenarPreview();

            if (window.lucide) lucide.createIcons();

        } catch (err) {

            contenidoPreview.innerHTML = `
                <div class="mx-auto max-w-md rounded-2xl border border-red-200 bg-red-50 px-5 py-6 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <i data-lucide="circle-alert" class="h-5 w-5"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-red-800">
                        No se pudo cargar la vista previa
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-red-700">
                        ${escaparHtml(err.message)}
                    </p>
                </div>
            `;

            if (window.lucide) lucide.createIcons();

        }

    });


    /*
    |--------------------------------------------------------------------------
    | RELLENAR PREVIEW con los datos del formulario
    |--------------------------------------------------------------------------
    */

    function rellenarPreview() {

        // Campos de texto simples
        const mapaCampos = {

            // Información del documento
            'out_para'                : 'para_nombre',
            'out_cc'                  : 'cc_nombre',
            'out_de'                  : 'de_nombre',
            'out_fecha_documento'     : 'fecha_documento',
            'out_asunto'              : 'asunto',


            // Información autorización
            'out_colaborador'         : 'colaborador',
            'out_cargo_area'          : 'cargo_area',
            'out_fecha_ingreso'       : 'fecha_documento',
            'out_motivo_autorizacion' : 'motivo_autorizacion',

        };

        Object.entries(mapaCampos).forEach(([idSalida, nameInput]) => {

            const el    = document.getElementById(idSalida);
            const input = form.querySelector(`[name="${nameInput}"]`);

            if (el && input) {
                el.textContent = input.value || '-';
            }

        });


        // Tabla de equipos
        const tbodySalida = document.getElementById('equipoSalida');

        if (tbodySalida && tabla) {

            const filas = tabla.querySelectorAll('.fila-equipo');

            tbodySalida.innerHTML = '';

            if (filas.length === 0) {

                tbodySalida.innerHTML = `
                    <tr>
                        <td colspan="5" class="fila-vacia">Sin equipos registrados</td>
                    </tr>
                `;

            } else {

                filas.forEach(fila => {

                    const inputs = fila.querySelectorAll('input[type="text"]');

                    const vals = Array.from(inputs).map(i => i.value || '-');

                    tbodySalida.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${escaparHtml(vals[0] ?? '-')}</td>
                            <td>${escaparHtml(vals[1] ?? '-')}</td>
                            <td>${escaparHtml(vals[2] ?? '-')}</td>
                            <td>${escaparHtml(vals[3] ?? '-')}</td>
                            <td>${escaparHtml(vals[4] ?? '-')}</td>
                        </tr>
                    `);

                });

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR MODAL PREVIEW
    |--------------------------------------------------------------------------
    */

    btnCerrarPreview?.addEventListener('click',  () => cerrarModal(modalPreview));
    btnCerrarPreview2?.addEventListener('click', () => cerrarModal(modalPreview));

    modalPreview?.addEventListener('click', (e) => {
        if (e.target === modalPreview) cerrarModal(modalPreview);
    });


    /*
    |--------------------------------------------------------------------------
    | GENERAR DESDE PREVIEW → dispara el submit del form principal
    |--------------------------------------------------------------------------
    */

    btnGenerarDesdePreview?.addEventListener('click', () => {
        cerrarModal(modalPreview);
        generarDocumento();
    });


    /*
    |--------------------------------------------------------------------------
    | SUBMIT PRINCIPAL → Generar PDF
    |--------------------------------------------------------------------------
    */

    if (btnGenerar) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            generarDocumento();
        });
    }


    async function generarDocumento() {

        if (enviando) return;

        if (!form.reportValidity()) return;

        enviando = true;

        if (btnGenerar) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = `
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Generando documento...
            `;
        }

        if (btnGenerarDesdePreview) {
            btnGenerarDesdePreview.disabled = true;
        }

        try {

            const formData = new FormData(form);

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });


            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (error) {
                throw new Error('El servidor devolvió una respuesta inválida.');
            }

            if (!response.ok || !data.success) {
                throw new Error(
                    data.error
                    ?? data.message
                    ?? 'No fue posible generar el documento.'
                );
            }

            if (data.download) {

                linkDescarga.href = data.download;

                configurarResultadoCorreo(data);

                abrirModal(modalDescarga);

                const estadoEmail =
                    String(
                        data.email?.status
                        ?? ''
                    ).toLowerCase();

                const correoEnCola =
                    data.email?.queued === true
                    || estadoEmail === 'pendiente'
                    || estadoEmail === 'enviando';

                if (correoEnCola) {
                    vigilarEstadoCorreo(
                        data.email?.delivery_id,
                        data
                    );
                }

            } else {
                throw new Error('El documento se registró, pero no se recibió la ruta de descarga.');
            }

        } catch (err) {

            textoError.textContent =
                err.message
                ?? 'Error de red. Por favor intenta nuevamente.';

            configurarEstadoErrorTotal(err);

            abrirModal(modalError);

        } finally {

            enviando = false;

            if (btnGenerar) {
                btnGenerar.disabled = false;
                btnGenerar.innerHTML = `
                    <i
                        id="btnGenerarIcono"
                        data-lucide="send"
                        stroke-width="1.8"
                        class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/send:translate-x-0.5 motion-safe:group-hover/send:-translate-y-0.5">
                    </i>
                    <span id="btnGenerarTexto">Generar y enviar</span>
                `;
            }

            if (btnGenerarDesdePreview) {
                btnGenerarDesdePreview.disabled = false;
            }

            if (window.lucide) lucide.createIcons();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SEGUIMIENTO DEL ESTADO DEL CORREO
    |--------------------------------------------------------------------------
    */

    async function vigilarEstadoCorreo(
        deliveryId,
        datosRegistro
    ) {
        if (!deliveryId) {
            return;
        }

        const urlBase =
            window.emailDeliveryStatusUrl;

        if (!urlBase) {
            console.warn(
                'No se configuró la URL para consultar el estado del correo.'
            );

            return;
        }

        const seguimientoId =
            ++seguimientoCorreoActual;

        const maxConsultas = 20;
        const tiempoEspera = 1500;

        for (
            let consulta = 1;
            consulta <= maxConsultas;
            consulta++
        ) {
            await esperar(
                tiempoEspera
            );

            if (
                seguimientoId !== seguimientoCorreoActual
            ) {
                return;
            }

            try {
                const url =
                    urlBase.replace(
                        '__DELIVERY_ID__',
                        encodeURIComponent(
                            deliveryId
                        )
                    );

                const response =
                    await fetch(
                        url,
                        {
                            method:
                                'GET',

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },

                            cache:
                                'no-store',
                        }
                    );

                if (!response.ok) {
                    console.warn(
                        'No se pudo consultar el estado del correo.',
                        response.status
                    );

                    continue;
                }

                const resultado =
                    await response.json();

                const estado =
                    String(
                        resultado.email?.status
                        ?? ''
                    ).toLowerCase();

                if (
                    resultado.email?.sent === true
                    || estado === 'enviado'
                ) {
                    if (modalResultadoMensaje) {
                        modalResultadoMensaje.textContent =
                            'El documento fue generado correctamente y la notificación por correo fue enviada.';
                    }

                    configurarEstadoCorreoExitoso();

                    return;
                }

                if (
                    resultado.email?.failed === true
                    || estado === 'fallido'
                ) {
                    configurarEstadoCorreoFallido({
                        ...datosRegistro,

                        message:
                            'El documento fue generado correctamente, pero no fue posible enviar la notificación por correo.',

                        email: {
                            ...datosRegistro?.email,
                            ...resultado.email,
                        },
                    });

                    if (modalResultadoMensaje) {
                        modalResultadoMensaje.textContent =
                            'El documento fue generado correctamente, pero no fue posible enviar la notificación por correo.';
                    }

                    return;
                }

                if (
                    resultado.email?.queued === true
                    || estado === 'pendiente'
                    || estado === 'enviando'
                ) {
                    configurarEstadoCorreoEnCola(
                        estado,
                        resultado.email?.attempts
                    );
                }

            } catch (error) {
                console.warn(
                    'Error consultando el estado del correo:',
                    error
                );
            }
        }

        if (
            seguimientoId === seguimientoCorreoActual
        ) {
            actualizarIndicadorSmtp(
                'queued'
            );

            if (estadoCorreoMensaje) {
                estadoCorreoMensaje.textContent =
                    'El correo continúa en cola. El proceso seguirá ejecutándose en segundo plano.';
            }
        }
    }


    function esperar(
        milisegundos
    ) {
        return new Promise(
            resolve =>
                window.setTimeout(
                    resolve,
                    milisegundos
                )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MOSTRAR RESULTADO DEL SMTP
    |--------------------------------------------------------------------------
    */

    function configurarResultadoCorreo(data) {

        const email =
            data.email
            ?? null;

        const estado =
            String(
                email?.status
                ?? ''
            ).toLowerCase();

        const correoEnviado =
            email?.sent === true
            || estado === 'enviado';

        const correoEnCola =
            email?.queued === true
            || estado === 'pendiente'
            || estado === 'enviando';

        const correoFallido =
            email?.failed === true
            || estado === 'fallido';

        if (modalResultadoMensaje) {
            modalResultadoMensaje.textContent =
                data.message
                ?? 'El documento fue generado correctamente.';
        }

        if (!email) {
            configurarEstadoSinComprobacion();
            return;
        }

        if (correoEnviado) {
            configurarEstadoCorreoExitoso();

        } else if (correoEnCola) {
            configurarEstadoCorreoEnCola(
                estado
            );

        } else if (correoFallido) {
            configurarEstadoCorreoFallido(
                data
            );

        } else {
            configurarEstadoCorreoEnCola(
                'pendiente'
            );
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    }


    function ocultarBotonesReporte() {
        [
            btnReportarSmtp,
            btnReportarPersistente,
        ].forEach((boton) => {
            if (!boton) {
                return;
            }

            boton.classList.add(
                'hidden'
            );

            boton.classList.remove(
                'inline-flex'
            );

            delete boton.dataset.outlookUrl;
        });
    }


    function configurarEstadoCorreoEnCola(
        estado = 'pendiente',
        attempts = 0
    ) {
        ocultarBotonesReporte();

        if (modalResultadoIcono) {
            modalResultadoIcono.className =
                'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 shadow-sm';

            modalResultadoIcono.innerHTML =
                '<i data-lucide="clock-3" stroke-width="1.8" class="h-8 w-8 text-blue-600"></i>';
        }

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado';
        }

        if (estadoCorreo) {
            estadoCorreo.classList.remove(
                'hidden'
            );

            estadoCorreo.className =
                'rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/50 p-5 text-left shadow-sm';
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-semibold text-blue-800';

            estadoCorreoTitulo.textContent =
                estado === 'enviando'
                    ? 'Enviando correo'
                    : 'Correo en procesamiento';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-blue-700';

            const numeroIntentos =
                Number(attempts) || 0;

            estadoCorreoMensaje.textContent =
                estado === 'enviando'
                    ? (
                        numeroIntentos > 0
                            ? `El servidor está procesando el correo. Intento ${numeroIntentos}.`
                            : 'El servidor está procesando el correo.'
                    )
                    : 'La notificación fue agregada a la cola y será enviada en segundo plano.';
        }

        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-200 bg-white text-blue-600 shadow-sm';

            estadoCorreoIcono.innerHTML =
                '<i data-lucide="mail" stroke-width="1.8" class="h-5 w-5"></i>';
        }

        actualizarIndicadorSmtp(
            'queued'
        );

        if (window.lucide) {
            lucide.createIcons();
        }
    }


    function configurarEstadoCorreoExitoso() {

        ocultarBotonesReporte();

        if (modalResultadoIcono) {
            modalResultadoIcono.className =
                'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 shadow-sm';

            modalResultadoIcono.innerHTML =
                '<i data-lucide="circle-check-big" stroke-width="1.8" class="h-8 w-8 text-emerald-600"></i>';
        }

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado y enviado';
        }

        if (estadoCorreo) {
            estadoCorreo.className =
                'rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 p-5 text-left shadow-sm';
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-semibold text-emerald-800';
            estadoCorreoTitulo.textContent =
                'Correo enviado correctamente';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-emerald-700';
            estadoCorreoMensaje.textContent =
                'El servidor SMTP aceptó la notificación.';
        }

        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-white text-emerald-600 shadow-sm';

            estadoCorreoIcono.innerHTML =
                '<i data-lucide="mail-check" stroke-width="1.8" class="h-5 w-5"></i>';
        }

        actualizarIndicadorSmtp('success');
    }


    function configurarEstadoCorreoFallido(data) {

        if (btnReportarSmtp) {
            const recipient =
    btnReportarPersistente?.dataset.recipient
    || 'helpdesk@televicentro.hn';


const userName =
    btnReportarPersistente?.dataset.userName
    || 'No especificado';


const userEmail =
    btnReportarPersistente?.dataset.userEmail
    || 'No especificado';


const solicitante =
    form.querySelector('[name="de_nombre"]')
        ?.value.trim()
    || 'No especificado';


const colaborador =
    form.querySelector('[name="colaborador"]')
        ?.value.trim()
    || 'No especificado';


const cargoArea =
    form.querySelector('[name="cargo_area"]')
        ?.value.trim()
    || 'No especificado';


const asuntoGestion =
    form.querySelector('[name="asunto"]')
        ?.value.trim()
    || 'No especificado';


const motivo =
    form.querySelector('[name="motivo_autorizacion"]')
        ?.value.trim()
    || 'No especificado';


const fechaDocumento =
    form.querySelector('[name="fecha_documento"]')
        ?.value
    || 'No especificada';


const subject =
    '[Portal TI] Apoyo con solicitud de pase mayor a 24 horas';


const body = [

    'Hola, equipo de Helpdesk:',

    '',

    'La solicitud de pase mayor a 24 horas quedó registrada '
    + 'en el Portal TI, pero la notificación automática por correo no pudo enviarse.',

    '',

    'Datos del usuario',

    `Nombre: ${userName}`,

    `Correo: ${userEmail}`,

    '',

    'Información del pase',

    `Solicitante: ${solicitante}`,

    `Responsable del equipo: ${colaborador}`,

    `Cargo o área: ${cargoArea}`,

    `Asunto: ${asuntoGestion}`,

    `Fecha del documento: ${fechaDocumento}`,

    '',

    `Fecha de la solicitud: ${
        new Date().toLocaleString(
            'es-HN',
            {
                dateStyle: 'long',
                timeStyle: 'short',
            }
        )
    }`,

    '',

    'Por favor, ayúdenme a dar seguimiento a esta solicitud.',

].join('\r\n');

            /*
             * No usar URLSearchParams aquí: convierte los espacios en "+"
             * y Outlook 365 puede mostrarlos literalmente en el mensaje.
             */
            const outlookComposeUrl =
                'https://outlook.office.com/mail/deeplink/compose'
                + `?to=${encodeURIComponent(recipient)}`
                + `&subject=${encodeURIComponent(subject)}`
                + `&body=${encodeURIComponent(body)}`;

            btnReportarSmtp.dataset.outlookUrl =
                outlookComposeUrl;

            btnReportarSmtp.classList.remove('hidden');
            btnReportarSmtp.classList.add('inline-flex');

            if (btnReportarPersistente) {
                btnReportarPersistente.dataset.outlookUrl =
                    outlookComposeUrl;

                btnReportarPersistente.className =
                    'group/report inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md active:scale-[0.98]';
            }
        }

        if (modalResultadoIcono) {
            modalResultadoIcono.className =
                'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 shadow-sm';

            modalResultadoIcono.innerHTML =
                '<i data-lucide="mail-warning" stroke-width="1.8" class="h-8 w-8 text-amber-600"></i>';
        }

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado con advertencia';
        }

        if (estadoCorreo) {
            estadoCorreo.className =
                'rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50 p-5 text-left shadow-sm';
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-semibold text-amber-800';
            estadoCorreoTitulo.textContent =
                'No se pudo enviar el correo';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-amber-700';
            estadoCorreoMensaje.textContent =
                'El documento quedó registrado y puede descargarse. El fallo SMTP fue guardado para revisión.';
        }

        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-200 bg-white text-amber-600 shadow-sm';

            estadoCorreoIcono.innerHTML =
                '<i data-lucide="mail-warning" stroke-width="1.8" class="h-5 w-5"></i>';
        }

        actualizarIndicadorSmtp('warning');
    }


    function configurarEstadoSinComprobacion() {

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado';
        }

        if (estadoCorreo) {
            estadoCorreo.classList.add('hidden');
        }
    }


    function actualizarIndicadorSmtp(
        estado
    ) {
        if (!smtpEstadoPrevio) {
            return;
        }

        const configuraciones = {
            success: {
                clase:
                    'inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 shadow-sm',

                punto:
                    'bg-emerald-500',

                texto:
                    'Último envío SMTP correcto',
            },

            queued: {
                clase:
                    'inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700 shadow-sm',

                punto:
                    'bg-blue-500',

                texto:
                    'Correo pendiente en la cola',
            },

            warning: {
                clase:
                    'inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 shadow-sm',

                punto:
                    'bg-amber-500',

                texto:
                    'Último envío SMTP fallido',
            },
        };

        const configuracion =
            configuraciones[estado]
            ?? configuraciones.warning;

        smtpEstadoPrevio.className =
            configuracion.clase;

        smtpEstadoPrevio.innerHTML =
            `<span class="h-2.5 w-2.5 shrink-0 rounded-full ${configuracion.punto}"></span> ${configuracion.texto}`;
    }


    function configurarEstadoErrorTotal(error) {

        if (smtpEstadoPrevio) {
            smtpEstadoPrevio.className =
                'inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 shadow-sm';

            smtpEstadoPrevio.innerHTML =
                '<span class="h-2.5 w-2.5 shrink-0 rounded-full bg-red-500"></span> No se pudo generar ni notificar la gestión';
        }

        if (!btnReportarPersistente) return;

        const recipient =
    btnReportarPersistente?.dataset.recipient
    || 'helpdesk@televicentro.hn';


const userName =
    btnReportarPersistente?.dataset.userName
    || 'No especificado';


const userEmail =
    btnReportarPersistente?.dataset.userEmail
    || 'No especificado';


const solicitante =
    form.querySelector('[name="de_nombre"]')
        ?.value.trim()
    || 'No especificado';


const colaborador =
    form.querySelector('[name="colaborador"]')
        ?.value.trim()
    || 'No especificado';


const cargoArea =
    form.querySelector('[name="cargo_area"]')
        ?.value.trim()
    || 'No especificado';


const asuntoGestion =
    form.querySelector('[name="asunto"]')
        ?.value.trim()
    || 'No especificado';


const motivo =
    form.querySelector('[name="motivo_autorizacion"]')
        ?.value.trim()
    || 'No especificado';


const fechaDocumento =
    form.querySelector('[name="fecha_documento"]')
        ?.value
    || 'No especificada';


const subject =
    '[Portal TI] Apoyo con solicitud de pase mayor a 24 horas';


const body = [

    'Hola, equipo de Helpdesk:',

    '',

    'Intenté registrar una solicitud de pase mayor a 24 horas '
    + 'en el Portal TI, pero el proceso no pudo completarse.',

    '',

    'Datos del usuario',

    `Nombre: ${userName}`,

    `Correo: ${userEmail}`,

    '',

    'Información del pase',

    `Solicitante: ${solicitante}`,

    `Responsable del equipo: ${colaborador}`,

    `Cargo o área: ${cargoArea}`,

    `Asunto: ${asuntoGestion}`,

    `Fecha del documento: ${fechaDocumento}`,

    '',

    `Fecha de la solicitud: ${
        new Date().toLocaleString(
            'es-HN',
            {
                dateStyle: 'long',
                timeStyle: 'short',
            }
        )
    }`,

    '',

    'Por favor, ayúdenme a revisar y registrar esta solicitud.',

].join('\r\n');

        const outlookUrl =
            'https://outlook.office.com/mail/deeplink/compose'
            + `?to=${encodeURIComponent(recipient)}`
            + `&subject=${encodeURIComponent(subject)}`
            + `&body=${encodeURIComponent(body)}`;

        btnReportarPersistente.dataset.outlookUrl =
            outlookUrl;

        btnReportarPersistente.className =
            'group/report inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-medium text-red-800 shadow-sm transition-all duration-200 hover:border-red-400 hover:bg-red-100 hover:shadow-md active:scale-[0.98]';

        if (window.lucide) lucide.createIcons();
    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR MODAL DESCARGA
    |--------------------------------------------------------------------------
    */

    btnCerrarDescarga?.addEventListener('click', () => cerrarModal(modalDescarga));

    modalDescarga?.addEventListener('click', (e) => {
        if (e.target === modalDescarga) cerrarModal(modalDescarga);
    });


    /*
    |--------------------------------------------------------------------------
    | CERRAR MODAL ERROR
    |--------------------------------------------------------------------------
    */

    btnCerrarError?.addEventListener('click', () => cerrarModal(modalError));

    modalError?.addEventListener('click', (e) => {
        if (e.target === modalError) cerrarModal(modalError);
    });


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function abrirModal(modal) {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function cerrarModal(modal) {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function escaparHtml(valor) {
        const elemento = document.createElement('div');

        elemento.textContent = String(valor ?? '');

        return elemento.innerHTML;
    }

});