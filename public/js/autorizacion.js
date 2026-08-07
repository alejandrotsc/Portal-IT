/*
|--------------------------------------------------------------------------
| MODAL AYUDA SERIE — funciones globales
|--------------------------------------------------------------------------
|
| Define funciones globales para abrir y cerrar el modal de ayuda de la
| serie. Se mantienen fuera de DOMContentLoaded porque el Blade las invoca
| directamente mediante onclick y necesita acceso desde el scope global.
|
*/

/*
|--------------------------------------------------------------------------
| Abrir ayuda de serie
|--------------------------------------------------------------------------
|
| Muestra el modal informativo y bloquea temporalmente el desplazamiento
| del documento mientras permanece visible.
|
*/

function abrirAyudaSerie() {

    const modal = document.getElementById('modalSerie');

    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

}


/*
|--------------------------------------------------------------------------
| Cerrar ayuda de serie
|--------------------------------------------------------------------------
|
| Oculta el modal informativo y restaura el desplazamiento normal de la
| página.
|
*/

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
    |
    | Administra la creación y eliminación dinámica de filas de equipos, conserva la indexación requerida por el formulario y actualiza los iconos cuando se inserta contenido.
    |
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
    |
    | Permite cerrar el modal de ayuda mediante clic sobre el fondo o utilizando la tecla Escape.
    |
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
    |
    | Obtiene las referencias utilizadas por el formulario, preview, descarga, estados de correo, botones de Outlook y modal de error.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Abrir reporte en Outlook
    |--------------------------------------------------------------------------
    |
    | Recupera la URL preparada y abre Outlook 365 en una nueva pestaña,
    | utilizando navegación directa como respaldo cuando sea necesario.
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
    |
    | Carga mediante fetch la vista previa de la autorización, la inserta en el modal y sincroniza sus datos con el formulario actual.
    |
    */

    btnPreview?.addEventListener('click', async () => {

        abrirModal(modalPreview);

        // Mostrar spinner
        contenidoPreview.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="flex flex-col items-center gap-3 text-muted-foreground dark:text-slate-400">
                    <svg class="h-6 w-6 animate-spin text-primary dark:text-blue-400" viewBox="0 0 24 24" fill="none">
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
                <div class="mx-auto max-w-md rounded-2xl border border-red-200 bg-red-50 px-5 py-6 text-center dark:border-red-800 dark:bg-red-950/40">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-950/70 dark:text-red-400">
                        <i data-lucide="circle-alert" class="h-5 w-5"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-red-800 dark:text-red-300">
                        No se pudo cargar la vista previa
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-red-700 dark:text-red-400">
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
    |
    | Copia los valores actuales del formulario hacia los campos y la tabla de equipos presentes en la vista previa renderizada.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Sincronizar datos del preview
    |--------------------------------------------------------------------------
    |
    | Copia los campos generales, información de autorización y equipos
    | registrados hacia el documento cargado dentro del modal.
    |
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
    |
    | Registra los eventos utilizados para cerrar la vista previa mediante sus botones o al hacer clic sobre el fondo.
    |
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
    |
    | Permite iniciar la generación definitiva del documento directamente desde la vista previa.
    |
    */

    btnGenerarDesdePreview?.addEventListener('click', () => {
        cerrarModal(modalPreview);
        generarDocumento();
    });


    /*
    |--------------------------------------------------------------------------
    | SUBMIT PRINCIPAL → Generar PDF
    |--------------------------------------------------------------------------
    |
    | Intercepta el submit del formulario y delega la generación del documento al flujo asíncrono principal.
    |
    */

    if (btnGenerar) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            generarDocumento();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Generar documento
    |--------------------------------------------------------------------------
    |
    | Valida el formulario, envía sus datos mediante fetch, procesa la ruta
    | de descarga y coordina el estado inicial del correo asociado.
    |
    */

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
    |
    | Consulta periódicamente EmailDelivery para actualizar la interfaz cuando el worker confirma envío, fallo o continuidad en cola.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Vigilar estado del correo
    |--------------------------------------------------------------------------
    |
    | Realiza consultas periódicas al endpoint de EmailDelivery y cancela el
    | seguimiento anterior cuando comienza un nuevo proceso.
    |
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


    /*
    |--------------------------------------------------------------------------
    | Esperar intervalo
    |--------------------------------------------------------------------------
    |
    | Devuelve una promesa utilizada para espaciar las consultas periódicas
    | del seguimiento SMTP.
    |
    */

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
    |
    | Interpreta el estado inicial del correo devuelto por el backend y presenta el resultado correspondiente dentro del modal de descarga.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Configurar resultado inicial del correo
    |--------------------------------------------------------------------------
    |
    | Determina si la notificación fue enviada, quedó en cola, falló o no
    | pudo comprobarse y actualiza la interfaz en consecuencia.
    |
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


    /*
    |--------------------------------------------------------------------------
    | Ocultar botones de reporte
    |--------------------------------------------------------------------------
    |
    | Oculta las alternativas de Outlook cuando ya no son necesarias y
    | elimina las URL temporales asociadas.
    |
    */

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


    /*
    |--------------------------------------------------------------------------
    | Mostrar correo en cola
    |--------------------------------------------------------------------------
    |
    | Representa que el documento fue generado mientras la notificación
    | continúa pendiente o siendo procesada por el worker.
    |
    */

    function configurarEstadoCorreoEnCola(
        estado = 'pendiente',
        attempts = 0
    ) {
        ocultarBotonesReporte();

        if (modalResultadoIcono) {
            modalResultadoIcono.className =
                'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 shadow-sm dark:border-blue-800 dark:bg-blue-950/45';

            modalResultadoIcono.innerHTML =
                '<i data-lucide="clock-3" stroke-width="1.8" class="h-8 w-8 text-blue-600 dark:text-blue-400"></i>';
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
                'rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/50 p-5 text-left shadow-sm dark:border-blue-800 dark:from-blue-950/45 dark:via-slate-900 dark:to-sky-950/30';
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-semibold text-blue-800 dark:text-blue-300';

            estadoCorreoTitulo.textContent =
                estado === 'enviando'
                    ? 'Enviando correo'
                    : 'Correo en procesamiento';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-blue-700 dark:text-blue-400';

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
                'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-200 bg-white text-blue-600 shadow-sm dark:border-blue-800 dark:bg-slate-900 dark:text-blue-400';

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


    /*
    |--------------------------------------------------------------------------
    | Mostrar correo enviado
    |--------------------------------------------------------------------------
    |
    | Actualiza modal e indicador persistente cuando el servidor confirma
    | que la notificación fue enviada correctamente.
    |
    */

    function configurarEstadoCorreoExitoso() {

        ocultarBotonesReporte();

        if (modalResultadoIcono) {
            modalResultadoIcono.className =
                'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/45';

            modalResultadoIcono.innerHTML =
                '<i data-lucide="circle-check-big" stroke-width="1.8" class="h-8 w-8 text-emerald-600 dark:text-emerald-400"></i>';
        }

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado y enviado';
        }

        if (estadoCorreo) {
            estadoCorreo.className =
                'rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 p-5 text-left shadow-sm dark:border-emerald-800 dark:from-emerald-950/45 dark:via-slate-900 dark:to-teal-950/30';
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-semibold text-emerald-800 dark:text-emerald-300';
            estadoCorreoTitulo.textContent =
                'Correo enviado correctamente';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400';
            estadoCorreoMensaje.textContent =
                'El servidor SMTP aceptó la notificación.';
        }

        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-white text-emerald-600 shadow-sm dark:border-emerald-800 dark:bg-slate-900 dark:text-emerald-400';

            estadoCorreoIcono.innerHTML =
                '<i data-lucide="mail-check" stroke-width="1.8" class="h-5 w-5"></i>';
        }

        actualizarIndicadorSmtp('success');

        /*
         * Los iconos fueron insertados dinámicamente con innerHTML.
         * Lucide debe procesarlos nuevamente para convertirlos en SVG.
         */
        if (window.lucide) {
            lucide.createIcons();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Mostrar fallo de correo
    |--------------------------------------------------------------------------
    |
    | Informa que el documento fue generado pero la notificación automática
    | falló y prepara una alternativa de seguimiento mediante Outlook 365.
    |
    */

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
                    'group/report inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800 shadow-sm transition-all duration-200 hover:border-amber-400 hover:bg-amber-100 hover:shadow-md dark:border-amber-800 dark:bg-amber-950/45 dark:text-amber-300 dark:hover:border-amber-700 dark:hover:bg-amber-900/55 active:scale-[0.98]';
            }
        }

        if (modalResultadoIcono) {
            modalResultadoIcono.className =
                'mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 shadow-sm dark:border-amber-800 dark:bg-amber-950/45';

            modalResultadoIcono.innerHTML =
                '<i data-lucide="mail-warning" stroke-width="1.8" class="h-8 w-8 text-amber-600 dark:text-amber-400"></i>';
        }

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado con advertencia';
        }

        if (estadoCorreo) {
            estadoCorreo.className =
                'rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50 p-5 text-left shadow-sm dark:border-amber-800 dark:from-amber-950/45 dark:via-slate-900 dark:to-orange-950/30';
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-semibold text-amber-800 dark:text-amber-300';
            estadoCorreoTitulo.textContent =
                'No se pudo enviar el correo';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-amber-700 dark:text-amber-400';
            estadoCorreoMensaje.textContent =
                'El documento quedó registrado y puede descargarse. El fallo SMTP fue guardado para revisión.';
        }

        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-200 bg-white text-amber-600 shadow-sm dark:border-amber-800 dark:bg-slate-900 dark:text-amber-400';

            estadoCorreoIcono.innerHTML =
                '<i data-lucide="mail-warning" stroke-width="1.8" class="h-5 w-5"></i>';
        }

        actualizarIndicadorSmtp('warning');

        /*
         * Renderizar los iconos insertados dinámicamente en el estado
         * de advertencia.
         */
        if (window.lucide) {
            lucide.createIcons();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Mostrar estado sin comprobación
    |--------------------------------------------------------------------------
    |
    | Presenta el documento como generado cuando el backend no devuelve
    | información suficiente para determinar el estado del correo.
    |
    */

    function configurarEstadoSinComprobacion() {

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado';
        }

        if (estadoCorreo) {
            estadoCorreo.classList.add('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar indicador SMTP
    |--------------------------------------------------------------------------
    |
    | Sincroniza el indicador persistente con los estados de éxito, cola o
    | advertencia conocidos para la última notificación.
    |
    */

    function actualizarIndicadorSmtp(
        estado
    ) {
        if (!smtpEstadoPrevio) {
            return;
        }

        const configuraciones = {
            success: {
                clase:
                    'inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300',

                punto:
                    'bg-emerald-500',

                texto:
                    'Último envío SMTP correcto',
            },

            queued: {
                clase:
                    'inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700 shadow-sm dark:border-blue-800 dark:bg-blue-950/45 dark:text-blue-300',

                punto:
                    'bg-blue-500',

                texto:
                    'Correo pendiente en la cola',
            },

            warning: {
                clase:
                    'inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 shadow-sm dark:border-amber-800 dark:bg-amber-950/45 dark:text-amber-300',

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


    /*
    |--------------------------------------------------------------------------
    | Mostrar error total
    |--------------------------------------------------------------------------
    |
    | Representa un fallo en la generación del documento y prepara un reporte
    | de respaldo para solicitar asistencia mediante Outlook.
    |
    */

    function configurarEstadoErrorTotal(error) {

        if (smtpEstadoPrevio) {
            smtpEstadoPrevio.className =
                'inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 shadow-sm dark:border-red-800 dark:bg-red-950/45 dark:text-red-300';

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
            'group/report inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-medium text-red-800 shadow-sm transition-all duration-200 hover:border-red-400 hover:bg-red-100 hover:shadow-md dark:border-red-800 dark:bg-red-950/45 dark:text-red-300 dark:hover:border-red-700 dark:hover:bg-red-900/55 active:scale-[0.98]';

        if (window.lucide) lucide.createIcons();
    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR MODAL DESCARGA
    |--------------------------------------------------------------------------
    |
    | Permite cerrar el modal de descarga mediante el botón dedicado o haciendo clic sobre el fondo.
    |
    */

    btnCerrarDescarga?.addEventListener('click', () => cerrarModal(modalDescarga));

    modalDescarga?.addEventListener('click', (e) => {
        if (e.target === modalDescarga) cerrarModal(modalDescarga);
    });


    /*
    |--------------------------------------------------------------------------
    | CERRAR MODAL ERROR
    |--------------------------------------------------------------------------
    |
    | Permite cerrar el modal de error mediante el botón dedicado o haciendo clic sobre el fondo.
    |
    */

    btnCerrarError?.addEventListener('click', () => cerrarModal(modalError));

    modalError?.addEventListener('click', (e) => {
        if (e.target === modalError) cerrarModal(modalError);
    });


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    |
    | Agrupa utilidades reutilizables para abrir y cerrar modales y escapar contenido antes de insertarlo dinámicamente.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Abrir modal
    |--------------------------------------------------------------------------
    |
    | Muestra el modal recibido y bloquea el desplazamiento del documento.
    |
    */

    function abrirModal(modal) {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    /*
    |--------------------------------------------------------------------------
    | Cerrar modal
    |--------------------------------------------------------------------------
    |
    | Oculta el modal recibido y restaura el desplazamiento normal de la
    | página.
    |
    */

    function cerrarModal(modal) {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    /*
    |--------------------------------------------------------------------------
    | Escapar contenido HTML
    |--------------------------------------------------------------------------
    |
    | Convierte valores dinámicos a texto seguro antes de insertarlos en la
    | vista previa mediante HTML generado.
    |
    */

    function escaparHtml(valor) {
        const elemento = document.createElement('div');

        elemento.textContent = String(valor ?? '');

        return elemento.innerHTML;
    }

});