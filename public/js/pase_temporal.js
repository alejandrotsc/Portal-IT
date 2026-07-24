/*
|--------------------------------------------------------------------------
| MODAL DE AYUDA PARA LA SERIE
|--------------------------------------------------------------------------
|
| Estas funciones deben ser globales porque los botones del Blade pueden
| utilizarlas mediante onclick.
|
*/

function abrirAyudaSerie() {
    const modal =
        document.getElementById(
            'modalSerie'
        );

    if (!modal) {
        return;
    }

    modal.classList.remove(
        'hidden'
    );

    modal.classList.add(
        'flex'
    );

    document.body.classList.add(
        'overflow-hidden'
    );
}


function cerrarAyudaSerie() {
    const modal =
        document.getElementById(
            'modalSerie'
        );

    if (!modal) {
        return;
    }

    modal.classList.add(
        'hidden'
    );

    modal.classList.remove(
        'flex'
    );

    document.body.classList.remove(
        'overflow-hidden'
    );
}

document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS PRINCIPALES
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById(
            'documentForm'
        );

    if (!form) {
        return;
    }

    /*
|--------------------------------------------------------------------------
| TABLA DINÁMICA DE EQUIPOS
|--------------------------------------------------------------------------
*/

const tablaEquipos =
    document.getElementById(
        'equipoFilas'
    );

const btnAgregarEquipo =
    document.getElementById(
        'agregarFila'
    );

const templateEquipo =
    document.getElementById(
        'templateEquipo'
    );


if (
    tablaEquipos
    && btnAgregarEquipo
    && templateEquipo
) {
    let contadorEquipos =
        tablaEquipos
            .querySelectorAll(
                '.fila-equipo'
            )
            .length;


    /*
    |--------------------------------------------------------------------------
    | Agregar equipo
    |--------------------------------------------------------------------------
    */

    btnAgregarEquipo.addEventListener(
        'click',
        () => {
            let contenido =
                templateEquipo.innerHTML;

            contenido =
                contenido.replaceAll(
                    'INDEX',
                    String(contadorEquipos)
                );

            tablaEquipos.insertAdjacentHTML(
                'beforeend',
                contenido
            );

            contadorEquipos++;

            if (window.lucide) {
                lucide.createIcons();
            }

            tablaEquipos
                .lastElementChild
                ?.querySelector('input')
                ?.focus();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Eliminar equipo
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        (event) => {
            const botonEliminar =
                event.target.closest(
                    '.btn-remove-fila'
                );

            if (!botonEliminar) {
                return;
            }

            const fila =
                botonEliminar.closest(
                    '.fila-equipo'
                );

            if (!fila) {
                return;
            }

            const filasActuales =
                tablaEquipos.querySelectorAll(
                    '.fila-equipo'
                );

            /*
             * Mantener al menos una fila.
             */
            if (filasActuales.length <= 1) {
                /*
                 * En lugar de eliminar la última fila,
                 * solamente limpiar sus campos.
                 */
                fila.querySelectorAll(
                    'input, select, textarea'
                ).forEach((campo) => {
                    campo.value = '';
                });

                return;
            }

            fila.classList.add(
                'opacity-0',
                'scale-[0.98]'
            );

            window.setTimeout(
                () => fila.remove(),
                150
            );
        }
    );
}

/*
|--------------------------------------------------------------------------
| CERRAR AYUDA DE SERIE
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    (event) => {
        const modalSerie =
            document.getElementById(
                'modalSerie'
            );

        if (
            modalSerie
            && event.target === modalSerie
        ) {
            cerrarAyudaSerie();
        }
    }
);


    const btnEnviar =
        document.getElementById(
            'btnEnviar'
        );

    const modal =
        document.getElementById(
            'modalResultado'
        );

    const modalIcono =
        document.getElementById(
            'modalIcono'
        );

    const modalTitulo =
        document.getElementById(
            'modalTitulo'
        );

    const modalMensaje =
        document.getElementById(
            'modalMensaje'
        );

    const cerrarModal =
        document.getElementById(
            'cerrarModal'
        );


    /*
    |--------------------------------------------------------------------------
    | ESTADO DEL CORREO
    |--------------------------------------------------------------------------
    */

    const estadoCorreo =
        document.getElementById(
            'estadoCorreoPase'
        );

    const estadoCorreoIcono =
        document.getElementById(
            'estadoCorreoPaseIconoContenedor'
        );

    const estadoCorreoTitulo =
        document.getElementById(
            'estadoCorreoPaseTitulo'
        );

    const estadoCorreoMensaje =
        document.getElementById(
            'estadoCorreoPaseMensaje'
        );

    const smtpEstadoPase =
        document.getElementById(
            'smtpEstadoPase'
        );


    /*
    |--------------------------------------------------------------------------
    | BOTONES DE OUTLOOK
    |--------------------------------------------------------------------------
    */

    const btnReportarModal =
        document.getElementById(
            'btnReportarSmtpPase'
        );

    const btnReportarPersistente =
        document.getElementById(
            'btnReportarSmtpPasePersistente'
        );


    let enviando = false;


    /*
    |--------------------------------------------------------------------------
    | ABRIR OUTLOOK 365
    |--------------------------------------------------------------------------
    */

    function abrirOutlook(
        boton,
        event
    ) {
        event?.preventDefault();

        const outlookUrl =
            boton?.dataset.outlookUrl;

        if (!outlookUrl) {
            console.warn(
                'No se encontró el enlace de Outlook 365.'
            );

            return;
        }

        const ventana =
            window.open(
                outlookUrl,
                '_blank'
            );

        if (ventana) {
            ventana.opener = null;
        } else {
            /*
             * Si el navegador bloquea la pestaña nueva,
             * abrir Outlook en la pestaña actual.
             */
            window.location.href =
                outlookUrl;
        }
    }


    btnReportarModal?.addEventListener(
        'click',
        (event) => {
            abrirOutlook(
                btnReportarModal,
                event
            );
        }
    );


    btnReportarPersistente?.addEventListener(
        'click',
        (event) => {
            abrirOutlook(
                btnReportarPersistente,
                event
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | ENVÍO DEL PASE TEMPORAL
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        async (event) => {
            event.preventDefault();

            if (enviando) {
                return;
            }

            if (!form.reportValidity()) {
                return;
            }

            enviando = true;

            activarCarga();

            /*
             * Capturamos los datos antes de resetear el formulario.
             */
            const datosFormulario =
                obtenerDatosFormulario();

            try {
                const response =
                    await fetch(
                        form.action,
                        {
                            method:
                                'POST',

                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'Accept':
                                    'application/json',
                            },

                            body:
                                new FormData(form),
                        }
                    );

                const textoRespuesta =
                    await response.text();

                let data;

                try {
                    data =
                        JSON.parse(
                            textoRespuesta
                        );

                } catch (error) {
                    console.error(
                        'Respuesta no JSON:',
                        textoRespuesta
                    );

                    throw new Error(
                        'Laravel devolvió una respuesta inválida.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | ERROR TOTAL
                |--------------------------------------------------------------------------
                */

                if (
                    !response.ok
                    || !data.success
                ) {
                    throw new Error(
                        data.error
                        ?? data.message
                        ?? 'No fue posible registrar el pase temporal.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | GESTIÓN REGISTRADA
                |--------------------------------------------------------------------------
                */

                const correoEnviado =
                    data.email?.sent === true;

                if (correoEnviado) {
                    mostrarExito(
                        data
                    );

                } else {
                    mostrarAdvertenciaSmtp(
                        data,
                        datosFormulario
                    );
                }

                /*
                 * La gestión sí quedó registrada, incluso
                 * cuando el correo falló.
                 */
                form.reset();

                abrirModal();

            } catch (error) {
                console.error(
                    'Error procesando pase temporal:',
                    error
                );

                mostrarErrorTotal(
                    error,
                    datosFormulario
                );

                abrirModal();

            } finally {
                enviando = false;

                restaurarBoton();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | DATOS DEL FORMULARIO
    |--------------------------------------------------------------------------
    */

    function obtenerDatosFormulario() {
        return {
            deNombre:
                form.querySelector(
                    '[name="de_nombre"]'
                )?.value
                ?? 'N/A',

            colaborador:
                form.querySelector(
                    '[name="colaborador"]'
                )?.value
                ?? 'N/A',

            cargoArea:
                form.querySelector(
                    '[name="cargo_area"]'
                )?.value
                ?? 'N/A',

            asunto:
                form.querySelector(
                    '[name="asunto"]'
                )?.value
                ?? 'N/A',

            motivo:
                form.querySelector(
                    '[name="motivo_autorizacion"]'
                )?.value
                ?? 'N/A',

            fechaDocumento:
                form.querySelector(
                    '[name="fecha_documento"]'
                )?.value
                ?? 'N/A',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | ESTADO VERDE
    |--------------------------------------------------------------------------
    */

    function mostrarExito(data) {

        ocultarBotonesReporte();

        if (modalIcono) {
            modalIcono.className =
                'mx-auto flex h-16 w-16 items-center '
                + 'justify-center rounded-2xl border '
                + 'border-emerald-200 bg-emerald-50 shadow-sm';

            modalIcono.innerHTML = `
                <i
                    data-lucide="circle-check-big"
                    stroke-width="1.8"
                    class="h-8 w-8 text-emerald-600"
                ></i>
            `;
        }


        if (modalTitulo) {
            modalTitulo.textContent =
                'Solicitud enviada';
        }


        if (modalMensaje) {
            modalMensaje.textContent =
                data.message
                ?? 'El pase temporal fue registrado y enviado correctamente.';
        }


        if (estadoCorreo) {
            estadoCorreo.className =
                'rounded-2xl border border-emerald-200 '
                + 'bg-gradient-to-br from-emerald-50/80 via-white '
                + 'to-teal-50/50 p-5 text-left shadow-sm';
        }


        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center '
                + 'justify-center rounded-xl border '
                + 'border-emerald-200 bg-white text-emerald-600 shadow-sm';

            estadoCorreoIcono.innerHTML = `
                <i
                    data-lucide="mail-check"
                    stroke-width="1.8"
                    class="h-5 w-5"
                ></i>
            `;
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
                'El servidor SMTP aceptó la notificación para Helpdesk.';
        }


        actualizarIndicadorPrincipal(
            'success',
            'Último envío SMTP correcto'
        );

        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | ESTADO NARANJA
    |--------------------------------------------------------------------------
    |
    | La gestión quedó guardada, pero el correo SMTP falló.
    |
    */

    function mostrarAdvertenciaSmtp(
        data,
        datosFormulario
    ) {
        if (modalIcono) {
            modalIcono.className =
                'mx-auto flex h-16 w-16 items-center '
                + 'justify-center rounded-2xl border '
                + 'border-amber-200 bg-amber-50 shadow-sm';

            modalIcono.innerHTML = `
                <i
                    data-lucide="mail-warning"
                    stroke-width="1.8"
                    class="h-8 w-8 text-amber-600"
                ></i>
            `;
        }


        if (modalTitulo) {
            modalTitulo.textContent =
                'Solicitud registrada con advertencia';
        }


        if (modalMensaje) {
            modalMensaje.textContent =
                data.message
                ?? 'La solicitud fue registrada, pero el correo no pudo enviarse.';
        }


        if (estadoCorreo) {
            estadoCorreo.className =
                'rounded-2xl border border-amber-200 '
                + 'bg-gradient-to-br from-amber-50/80 via-white '
                + 'to-orange-50/50 p-5 text-left shadow-sm';
        }


        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center '
                + 'justify-center rounded-xl border '
                + 'border-amber-200 bg-white text-amber-600 shadow-sm';

            estadoCorreoIcono.innerHTML = `
                <i
                    data-lucide="mail-warning"
                    stroke-width="1.8"
                    class="h-5 w-5"
                ></i>
            `;
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
                'La gestión sí quedó registrada. Puedes reportar la falla mediante Outlook 365.';
        }


        const outlookUrl =
            construirReporteOutlook(
                data,
                datosFormulario,
                false
            );

        configurarBotonesReporte(
            outlookUrl,
            'warning'
        );


        actualizarIndicadorPrincipal(
            'warning',
            'Último envío SMTP fallido'
        );

        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | ESTADO ROJO
    |--------------------------------------------------------------------------
    |
    | No se pudo completar el registro de la gestión.
    |
    */

    function mostrarErrorTotal(
        error,
        datosFormulario
    ) {
        if (modalIcono) {
            modalIcono.className =
                'mx-auto flex h-16 w-16 items-center '
                + 'justify-center rounded-2xl border '
                + 'border-red-200 bg-red-50 shadow-sm';

            modalIcono.innerHTML = `
                <i
                    data-lucide="circle-x"
                    stroke-width="1.8"
                    class="h-8 w-8 text-red-600"
                ></i>
            `;
        }


        if (modalTitulo) {
            modalTitulo.textContent =
                'No se pudo completar la solicitud';
        }


        if (modalMensaje) {
            modalMensaje.textContent =
                error?.message
                ?? 'No fue posible registrar el pase temporal.';
        }


        if (estadoCorreo) {
            estadoCorreo.className =
                'rounded-2xl border border-red-200 '
                + 'bg-gradient-to-br from-red-50/80 via-white '
                + 'to-rose-50/50 p-5 text-left shadow-sm';
        }


        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center '
                + 'justify-center rounded-xl border '
                + 'border-red-200 bg-white text-red-600 shadow-sm';

            estadoCorreoIcono.innerHTML = `
                <i
                    data-lucide="triangle-alert"
                    stroke-width="1.8"
                    class="h-5 w-5"
                ></i>
            `;
        }


        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-semibold text-red-800';

            estadoCorreoTitulo.textContent =
                'La gestión no pudo completarse';
        }


        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-red-700';

            estadoCorreoMensaje.textContent =
                'Puedes informar el problema al equipo de Helpdesk mediante Outlook 365.';
        }


        const outlookUrl =
            construirReporteOutlook(
                null,
                datosFormulario,
                true,
                error
            );

        configurarBotonesReporte(
            outlookUrl,
            'error'
        );


        actualizarIndicadorPrincipal(
            'error',
            'No se pudo registrar ni notificar la gestión'
        );

        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | CONSTRUIR CORREO PARA OUTLOOK 365
    |--------------------------------------------------------------------------
    |
    | Se utiliza encodeURIComponent directamente.
    | No usamos URLSearchParams porque puede convertir espacios en "+".
    |
    */

    function construirReporteOutlook(
        data,
        datosFormulario,
        errorTotal = false,
        error = null
    ) {
        const botonDatos =
            btnReportarModal
            ?? btnReportarPersistente;

        const recipient =
            botonDatos?.dataset.recipient
            || 'helpdesk@televicentro.hn';

        const userName =
            botonDatos?.dataset.userName
            || 'N/A';

        const userEmail =
            botonDatos?.dataset.userEmail
            || 'N/A';

        const identificador =
            data?.codigo
            ?? data?.id
            ?? 'N/A';

        const deliveryId =
            data?.email?.delivery_id
            ?? 'N/A';

        const status =
            data?.email?.status
            ?? (
                errorTotal
                    ? 'Error al registrar'
                    : 'Fallido'
            );


       const subject = errorTotal
    ? '[Portal TI] Apoyo con solicitud de pase menor a 24 horas'
    : `[Portal TI] Seguimiento de pase menor a 24 horas - ${identificador}`;


const mensajePrincipal = errorTotal
    ? 'Intenté registrar una solicitud de pase menor a 24 horas '
        + 'en el Portal TI, pero el proceso no pudo completarse.'
    : 'La solicitud de pase quedó registrada en el Portal TI, '
        + 'pero el equipo de soporte no recibió la notificación automática.';


const mensajeFinal = errorTotal
    ? 'Por favor, ayúdenme a revisar y registrar esta solicitud.'
    : 'Por favor, ayúdenme a dar seguimiento a esta solicitud.';


const body = [

    'Hola, equipo de Helpdesk:',

    '',

    mensajePrincipal,

    '',

    'Datos del usuario',

    `Nombre: ${userName || 'No especificado'}`,

    `Correo: ${userEmail || 'No especificado'}`,

    '',

    'Información del pase',

    `Solicitante: ${
        datosFormulario.deNombre
        || 'No especificado'
    }`,

    `Responsable del equipo: ${
        datosFormulario.colaborador
        || 'No especificado'
    }`,

    `Cargo o área: ${
        datosFormulario.cargoArea
        || 'No especificado'
    }`,

    `Asunto: ${
        datosFormulario.asunto
        || 'No especificado'
    }`,

    `Motivo: ${
        datosFormulario.motivo
        || 'No especificado'
    }`,

    `Fecha del documento: ${
        datosFormulario.fechaDocumento
        || 'No especificada'
    }`,

    `Código: ${
        identificador
        || 'No generado'
    }`,

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

    mensajeFinal,

]
    .filter(linea => linea !== null)
    .join('\r\n');


return (
    'https://outlook.office.com/mail/deeplink/compose'
    + `?to=${encodeURIComponent(recipient)}`
    + `&subject=${encodeURIComponent(subject)}`
    + `&body=${encodeURIComponent(body)}`
);
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIGURAR BOTONES DE REPORTE
    |--------------------------------------------------------------------------
    */

    function configurarBotonesReporte(
        outlookUrl,
        tipo
    ) {
        const botones = [
            btnReportarModal,
            btnReportarPersistente,
        ];


        botones.forEach((boton) => {
            if (!boton) {
                return;
            }

            boton.dataset.outlookUrl =
                outlookUrl;

            boton.classList.remove(
                'hidden'
            );

            boton.classList.add(
                'inline-flex'
            );


            if (tipo === 'error') {
                boton.classList.remove(
                    'border-amber-300',
                    'bg-amber-50',
                    'text-amber-800',
                    'hover:bg-amber-100',
                    'hover:border-amber-400'
                );

                boton.classList.add(
                    'border-red-300',
                    'bg-red-50',
                    'text-red-800',
                    'hover:bg-red-100',
                    'hover:border-red-400'
                );

            } else {
                boton.classList.remove(
                    'border-red-300',
                    'bg-red-50',
                    'text-red-800',
                    'hover:bg-red-100',
                    'hover:border-red-400'
                );

                boton.classList.add(
                    'border-amber-300',
                    'bg-amber-50',
                    'text-amber-800',
                    'hover:bg-amber-100',
                    'hover:border-amber-400'
                );
            }
        });
    }


    function ocultarBotonesReporte() {
        [
            btnReportarModal,
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
    | INDICADOR PERSISTENTE
    |--------------------------------------------------------------------------
    */

    function actualizarIndicadorPrincipal(
        tipo,
        texto
    ) {
        if (!smtpEstadoPase) {
            return;
        }

        const colores = {
            success: {
                text:
                    'text-emerald-700',

                dot:
                    'bg-emerald-500',

                border:
                    'border-emerald-200',

                background:
                    'bg-emerald-50',
            },

            warning: {
                text:
                    'text-amber-700',

                dot:
                    'bg-amber-500',

                border:
                    'border-amber-200',

                background:
                    'bg-amber-50',
            },

            error: {
                text:
                    'text-red-700',

                dot:
                    'bg-red-500',

                border:
                    'border-red-200',

                background:
                    'bg-red-50',
            },
        };

        const color =
            colores[tipo]
            ?? colores.error;

        smtpEstadoPase.className =
            'inline-flex items-center gap-2 rounded-lg border '
            + 'px-3 py-2 text-xs font-medium shadow-sm '
            + `${color.text} ${color.border} ${color.background}`;

        smtpEstadoPase.innerHTML = `
            <span
                class="h-2.5 w-2.5 shrink-0 rounded-full ${color.dot}"
            ></span>

            ${texto}
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN DE CARGA
    |--------------------------------------------------------------------------
    */

    function activarCarga() {
        if (!btnEnviar) {
            return;
        }

        btnEnviar.disabled =
            true;

        btnEnviar.innerHTML = `
            <i
                data-lucide="loader-circle"
                stroke-width="1.8"
                class="h-4 w-4 animate-spin"
            ></i>

            <span>
                Enviando...
            </span>
        `;

        refrescarIconos();
    }


    function restaurarBoton() {
        if (!btnEnviar) {
            return;
        }

        btnEnviar.disabled =
            false;

        btnEnviar.innerHTML = `
            <i
                id="btnEnviarIcono"
                data-lucide="send"
                stroke-width="1.8"
                class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/send:translate-x-0.5 motion-safe:group-hover/send:-translate-y-0.5"
            ></i>

            <span id="btnEnviarTexto">
                Enviar solicitud
            </span>
        `;

        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | MODAL
    |--------------------------------------------------------------------------
    */

    function abrirModal() {
        if (!modal) {
            return;
        }

        modal.classList.remove(
            'hidden'
        );

        modal.classList.add(
            'flex'
        );

        document.body.classList.add(
            'overflow-hidden'
        );
    }


    function ocultarModal() {
        if (!modal) {
            return;
        }

        modal.classList.add(
            'hidden'
        );

        modal.classList.remove(
            'flex'
        );

        document.body.classList.remove(
            'overflow-hidden'
        );
    }


    cerrarModal?.addEventListener(
        'click',
        ocultarModal
    );


    modal?.addEventListener(
        'click',
        (event) => {
            if (event.target === modal) {
                ocultarModal();
            }
        }
    );


    document.addEventListener(
    'keydown',
    (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        cerrarAyudaSerie();
        ocultarModal();
    }
);


    /*
    |--------------------------------------------------------------------------
    | ICONOS
    |--------------------------------------------------------------------------
    */

    function refrescarIconos() {
        if (window.lucide) {
            lucide.createIcons();
        }
    }

});
