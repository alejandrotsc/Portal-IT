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

}


function cerrarAyudaSerie() {

    const modal = document.getElementById('modalSerie');

    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');

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

        });


        document.addEventListener('click', (e) => {

            const boton = e.target.closest('.btn-remove-fila');

            if (!boton) return;

            const fila         = boton.closest('.fila-equipo');
            const filasActuales = tabla.querySelectorAll('.fila-equipo');

            if (!fila || filasActuales.length <= 1) return;

            fila.remove();

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
    const estadoCorreoTitulo      = document.getElementById('estadoCorreoTitulo');
    const estadoCorreoMensaje     = document.getElementById('estadoCorreoMensaje');
    const btnReportarSmtp         = document.getElementById('btnReportarSmtp');
    const btnReportarPersistente  = document.getElementById('btnReportarSmtpPersistente');

    const modalError              = document.getElementById('modalErrorAutorizacion');
    const textoError              = document.getElementById('textoErrorAutorizacion');
    const btnCerrarError          = document.getElementById('btnCerrarErrorAutorizacion');


    if (!form) return;

    let enviando = false;


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
                    <svg class="animate-spin w-6 h-6" viewBox="0 0 24 24" fill="none">
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
                <p class="text-center text-sm text-red-500 py-8">
                    Error al cargar el preview: ${err.message}
                </p>
            `;

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
                            <td>${vals[0] ?? '-'}</td>
                            <td>${vals[1] ?? '-'}</td>
                            <td>${vals[2] ?? '-'}</td>
                            <td>${vals[3] ?? '-'}</td>
                            <td>${vals[4] ?? '-'}</td>
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
                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Generando y enviando...
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
                    <i data-lucide="send" class="w-4 h-4"></i>
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
    | MOSTRAR RESULTADO DEL SMTP
    |--------------------------------------------------------------------------
    */

    function configurarResultadoCorreo(data) {

        const email = data.email ?? null;
        const correoEnviado = email?.sent === true;

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
        } else {
            configurarEstadoCorreoFallido(data);
        }

        if (window.lucide) lucide.createIcons();
    }


    function configurarEstadoCorreoExitoso() {

        if (btnReportarSmtp) {
            btnReportarSmtp.classList.add('hidden');
            btnReportarSmtp.classList.remove('inline-flex');
            delete btnReportarSmtp.dataset.outlookUrl;
        }

        if (btnReportarPersistente) {
            btnReportarPersistente.classList.add('hidden');
            btnReportarPersistente.classList.remove('inline-flex');
            delete btnReportarPersistente.dataset.outlookUrl;
        }

        if (modalResultadoIcono) {
            modalResultadoIcono.className =
                'w-16 h-16 rounded-full bg-green-50 border-2 border-green-200 flex items-center justify-center mx-auto mb-5';

            modalResultadoIcono.innerHTML =
                '<i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>';
        }

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado y enviado';
        }

        if (estadoCorreo) {
            estadoCorreo.className =
                'mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-left';
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-medium text-green-800';
            estadoCorreoTitulo.textContent =
                'Correo enviado correctamente';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'text-xs text-green-700 mt-1';
            estadoCorreoMensaje.textContent =
                'El servidor SMTP aceptó la notificación.';
        }

        if (estadoCorreo) {
            const icono = estadoCorreo.querySelector('svg, i');
            icono?.remove();
            estadoCorreo.querySelector('.flex')?.insertAdjacentHTML(
                'afterbegin',
                '<i data-lucide="mail-check" class="w-5 h-5 text-green-600 shrink-0 mt-0.5"></i>'
            );
        }

        actualizarIndicadorSmtp(true);
    }


    function configurarEstadoCorreoFallido(data) {

        if (btnReportarSmtp) {
            const recipient =
                btnReportarSmtp.dataset.recipient
                || 'helpdesk@televicentro.hn';

            const userName =
                btnReportarSmtp.dataset.userName
                ?? 'N/A';

            const userEmail =
                btnReportarSmtp.dataset.userEmail
                ?? 'N/A';

            const solicitanteDocumento =
                form.querySelector('[name="de_nombre"]')?.value
                ?? 'N/A';

            const asuntoDocumento =
                form.querySelector('[name="asunto"]')?.value
                ?? 'N/A';

            const subject =
                `[Portal TI] Falla SMTP - Pase mayor a 24 horas - ${data.codigo ?? data.id ?? 'N/A'}`;

            const body = [
                'Hola, equipo de soporte TI:',
                '',
                'Deseo reportar que el Portal TI no pudo enviar la notificación por correo de la siguiente gestión:',
                '',
                `Usuario: ${userName}`,
                `Correo del usuario: ${userEmail}`,
                `Solicitante del documento: ${solicitanteDocumento}`,
                'Gestión: Pase mayor a 24 horas',
                `Asunto de la gestión: ${asuntoDocumento}`,
                `Código o identificador: ${data.codigo ?? data.id ?? 'N/A'}`,
                `Referencia del envío: ${data.email?.delivery_id ?? 'N/A'}`,
                `Estado registrado: ${data.email?.status ?? 'fallido'}`,
                `Fecha del reporte: ${new Date().toLocaleString('es-HN')}`,
                `Página del Portal TI: ${window.location.href}`,
                '',
                'El documento sí fue generado y permanece disponible en el Portal TI.',
                '',
                'Por favor, revisen la configuración o disponibilidad del servicio SMTP.',
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
                    'inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-800 hover:bg-amber-100 transition';
            }
        }

        if (modalResultadoIcono) {
            modalResultadoIcono.className =
                'w-16 h-16 rounded-full bg-amber-50 border-2 border-amber-200 flex items-center justify-center mx-auto mb-5';

            modalResultadoIcono.innerHTML =
                '<i data-lucide="mail-warning" class="w-8 h-8 text-amber-600"></i>';
        }

        if (modalResultadoTitulo) {
            modalResultadoTitulo.textContent =
                'Documento generado con advertencia';
        }

        if (estadoCorreo) {
            estadoCorreo.className =
                'mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-left';
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-medium text-amber-800';
            estadoCorreoTitulo.textContent =
                'No se pudo enviar el correo';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'text-xs text-amber-700 mt-1';
            estadoCorreoMensaje.textContent =
                'El documento quedó registrado y puede descargarse. El fallo SMTP fue guardado para revisión.';
        }

        if (estadoCorreo) {
            const icono = estadoCorreo.querySelector('svg, i');
            icono?.remove();
            estadoCorreo.querySelector('.flex')?.insertAdjacentHTML(
                'afterbegin',
                '<i data-lucide="mail-warning" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>'
            );
        }

        actualizarIndicadorSmtp(false);
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


    function actualizarIndicadorSmtp(exitoso) {

        if (!smtpEstadoPrevio) return;

        smtpEstadoPrevio.className = exitoso
            ? 'inline-flex items-center gap-2 text-xs text-green-700'
            : 'inline-flex items-center gap-2 text-xs text-amber-700';

        smtpEstadoPrevio.innerHTML = exitoso
            ? '<span class="h-2.5 w-2.5 rounded-full bg-green-500"></span> Último envío SMTP correcto'
            : '<span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Último envío SMTP fallido';
    }


    function configurarEstadoErrorTotal(error) {

        if (smtpEstadoPrevio) {
            smtpEstadoPrevio.className =
                'inline-flex items-center gap-2 text-xs text-red-700';

            smtpEstadoPrevio.innerHTML =
                '<span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> No se pudo generar ni notificar la gestión';
        }

        if (!btnReportarPersistente) return;

        const recipient =
            btnReportarPersistente.dataset.recipient
            || 'helpdesk@televicentro.hn';

        const userName =
            btnReportarPersistente.dataset.userName
            || 'N/A';

        const userEmail =
            btnReportarPersistente.dataset.userEmail
            || 'N/A';

        const solicitante =
            form.querySelector('[name="de_nombre"]')?.value
            || 'N/A';

        const asuntoGestion =
            form.querySelector('[name="asunto"]')?.value
            || 'N/A';

        const subject =
            '[Portal TI] Error al generar pase mayor a 24 horas';

        const body = [
            'Hola, equipo de soporte TI:',
            '',
            'Deseo reportar que el Portal TI no pudo completar una gestión.',
            '',
            `Usuario: ${userName}`,
            `Correo del usuario: ${userEmail}`,
            `Solicitante del documento: ${solicitante}`,
            'Gestión: Pase mayor a 24 horas',
            `Asunto de la gestión: ${asuntoGestion}`,
            `Mensaje mostrado: ${error?.message ?? 'Error no especificado'}`,
            `Fecha del reporte: ${new Date().toLocaleString('es-HN')}`,
            `Página del Portal TI: ${window.location.href}`,
            '',
            'Por favor, revisen la disponibilidad del Portal TI y del servicio de notificación.',
        ].join('\r\n');

        const outlookUrl =
            'https://outlook.office.com/mail/deeplink/compose'
            + `?to=${encodeURIComponent(recipient)}`
            + `&subject=${encodeURIComponent(subject)}`
            + `&body=${encodeURIComponent(body)}`;

        btnReportarPersistente.dataset.outlookUrl =
            outlookUrl;

        btnReportarPersistente.className =
            'inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-800 hover:bg-red-100 transition';

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
    }

    function cerrarModal(modal) {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

});
