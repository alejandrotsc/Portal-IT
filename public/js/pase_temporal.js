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

        /*
|--------------------------------------------------------------------------
| Mover modal directamente al body
|--------------------------------------------------------------------------
|
| Evita que un contenedor padre con transform, overflow o z-index limite
| el fondo del modal y deje el header fuera del desenfoque.
|
*/

if (
    modal
    && modal.parentElement !== document.body
) {
    document.body.appendChild(
        modal
    );
}

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
    | Control del seguimiento del correo
    |--------------------------------------------------------------------------
    */

    let seguimientoCorreoActual = 0;


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

                const estadoCorreoActual =
                    String(
                        data.email?.status
                        ?? ''
                    ).toLowerCase();

                const correoEnviado =
                    data.email?.sent === true
                    || estadoCorreoActual === 'enviado';

                const correoEnCola =
                    data.email?.queued === true
                    || estadoCorreoActual === 'pendiente'
                    || estadoCorreoActual === 'enviando';

                const correoFallido =
                    estadoCorreoActual === 'fallido';

                if (correoEnviado) {
                    mostrarExito(
                        data
                    );

                } else if (correoEnCola) {
                    mostrarCorreoEnCola(
                        data
                    );

                    vigilarEstadoCorreo(
                        data.email?.delivery_id,
                        datosFormulario,
                        data
                    );

                } else if (correoFallido) {
                    mostrarAdvertenciaSmtp(
                        data,
                        datosFormulario
                    );

                } else {
                    mostrarCorreoEnCola(
                        data
                    );

                    vigilarEstadoCorreo(
                        data.email?.delivery_id,
                        datosFormulario,
                        data
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
    | SEGUIMIENTO DEL ESTADO DEL CORREO
    |--------------------------------------------------------------------------
    |
    | Consulta periódicamente el estado del EmailDelivery. El formulario no
    | espera al servidor SMTP, pero el modal se actualiza cuando el worker
    | confirma que el correo fue enviado o que terminó fallando.
    |
    */

    async function vigilarEstadoCorreo(
        deliveryId,
        datosFormulario,
        datosRegistro
    ) {
        if (!deliveryId) {
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

            /*
             * Si comenzó otro envío, detener el seguimiento anterior.
             */
            if (
                seguimientoId !== seguimientoCorreoActual
            ) {
                return;
            }

            try {
                const response =
                    await fetch(
                        `/email-deliveries/${encodeURIComponent(deliveryId)}/status`,
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


                /*
                |--------------------------------------------------------------------------
                | Correo enviado
                |--------------------------------------------------------------------------
                */

                if (
                    resultado.email?.sent === true
                    || estado === 'enviado'
                ) {
                    mostrarExito({
                        ...datosRegistro,

                        message:
                            'La solicitud fue registrada correctamente y la notificación por correo fue enviada.',

                        email: {
                            ...datosRegistro?.email,
                            ...resultado.email,
                        },
                    });

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Correo fallido
                |--------------------------------------------------------------------------
                */

                if (
                    resultado.email?.failed === true
                    || estado === 'fallido'
                ) {
                    mostrarAdvertenciaSmtp(
                        {
                            ...datosRegistro,

                            message:
                                'La solicitud fue registrada correctamente, pero no fue posible enviar la notificación por correo.',

                            email: {
                                ...datosRegistro?.email,
                                ...resultado.email,
                            },
                        },
                        datosFormulario
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Continúa pendiente o enviándose
                |--------------------------------------------------------------------------
                */

                if (
                    estado === 'pendiente'
                    || estado === 'enviando'
                    || resultado.email?.queued === true
                ) {
                    actualizarEstadoCorreoEnProceso(
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


        /*
        |--------------------------------------------------------------------------
        | Tiempo máximo de seguimiento alcanzado
        |--------------------------------------------------------------------------
        |
        | No se presenta como fallo porque el worker puede continuar procesando
        | el correo después de cerrar o dejar abierta esta página.
        |
        */

        if (
            seguimientoId === seguimientoCorreoActual
        ) {
            actualizarIndicadorPrincipal(
                'queued',
                'El correo continúa en procesamiento'
            );

            if (estadoCorreoMensaje) {
                estadoCorreoMensaje.textContent =
                    'El correo continúa en cola. El proceso seguirá ejecutándose en segundo plano.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar detalle mientras el worker procesa
    |--------------------------------------------------------------------------
    */

    function actualizarEstadoCorreoEnProceso(
        estado,
        attempts = 0
    ) {
        const intentos =
            Number(attempts) || 0;

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.textContent =
                estado === 'enviando'
                    ? 'Enviando correo'
                    : 'Correo en procesamiento';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.textContent =
                estado === 'enviando'
                    ? (
                        intentos > 0
                            ? `El servidor está procesando el correo. Intento ${intentos}.`
                            : 'El servidor está procesando el correo.'
                    )
                    : 'La notificación continúa en cola y será procesada por el servidor.';
        }

        actualizarIndicadorPrincipal(
            'queued',
            estado === 'enviando'
                ? 'Correo SMTP en proceso'
                : 'Correo pendiente en la cola'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Espera asíncrona
    |--------------------------------------------------------------------------
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
                + 'border-emerald-200 bg-emerald-50 shadow-sm '
                + 'dark:border-emerald-800 dark:bg-emerald-950/45';

            modalIcono.innerHTML = `
                <i
                    data-lucide="circle-check-big"
                    stroke-width="1.8"
                    class="h-8 w-8 text-emerald-600 dark:text-emerald-400"
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
                + 'to-teal-50/50 p-5 text-left shadow-sm '
                + 'dark:border-emerald-800 dark:from-emerald-950/45 '
                + 'dark:via-slate-900 dark:to-teal-950/30';
        }


        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center '
                + 'justify-center rounded-xl border '
                + 'border-emerald-200 bg-white text-emerald-600 shadow-sm '
                + 'dark:border-emerald-800 dark:bg-slate-900 '
                + 'dark:text-emerald-400';

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
                'text-sm font-semibold text-emerald-800 dark:text-emerald-300';

            estadoCorreoTitulo.textContent =
                'Correo enviado correctamente';
        }


        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400';

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
    | ESTADO AZUL — CORREO EN COLA
    |--------------------------------------------------------------------------
    |
    | La gestión quedó registrada y el correo será procesado por el worker.
    |
    */

    function mostrarCorreoEnCola(data) {

        ocultarBotonesReporte();

        if (modalIcono) {
            modalIcono.className =
                'mx-auto flex h-16 w-16 items-center '
                + 'justify-center rounded-2xl border '
                + 'border-blue-200 bg-blue-50 shadow-sm '
                + 'dark:border-blue-800 dark:bg-blue-950/45';

            modalIcono.innerHTML = `
                <i
                    data-lucide="clock-3"
                    stroke-width="1.8"
                    class="h-8 w-8 text-blue-600 dark:text-blue-400"
                ></i>
            `;
        }

        if (modalTitulo) {
            modalTitulo.textContent =
                'Solicitud registrada';
        }

        if (modalMensaje) {
            modalMensaje.textContent =
                data.message
                ?? 'El pase temporal fue registrado correctamente. La notificación por correo se está procesando.';
        }

        if (estadoCorreo) {
            estadoCorreo.className =
                'rounded-2xl border border-blue-200 '
                + 'bg-gradient-to-br from-blue-50/80 via-white '
                + 'to-sky-50/50 p-5 text-left shadow-sm '
                + 'dark:border-blue-800 dark:from-blue-950/45 '
                + 'dark:via-slate-900 dark:to-sky-950/30';
        }

        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center '
                + 'justify-center rounded-xl border '
                + 'border-blue-200 bg-white text-blue-600 shadow-sm '
                + 'dark:border-blue-800 dark:bg-slate-900 dark:text-blue-400';

            estadoCorreoIcono.innerHTML = `
                <i
                    data-lucide="mail"
                    stroke-width="1.8"
                    class="h-5 w-5"
                ></i>
            `;
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className =
                'text-sm font-semibold text-blue-800 dark:text-blue-300';

            estadoCorreoTitulo.textContent =
                'Correo en procesamiento';
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-blue-700 dark:text-blue-400';

            estadoCorreoMensaje.textContent =
                'La notificación fue agregada a la cola y será enviada en segundo plano.';
        }

        actualizarIndicadorPrincipal(
            'queued',
            'Último correo agregado a la cola'
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
                + 'border-amber-200 bg-amber-50 shadow-sm '
                + 'dark:border-amber-800 dark:bg-amber-950/45';

            modalIcono.innerHTML = `
                <i
                    data-lucide="mail-warning"
                    stroke-width="1.8"
                    class="h-8 w-8 text-amber-600 dark:text-amber-400"
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
                + 'to-orange-50/50 p-5 text-left shadow-sm '
                + 'dark:border-amber-800 dark:from-amber-950/45 '
                + 'dark:via-slate-900 dark:to-orange-950/30';
        }


        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center '
                + 'justify-center rounded-xl border '
                + 'border-amber-200 bg-white text-amber-600 shadow-sm '
                + 'dark:border-amber-800 dark:bg-slate-900 dark:text-amber-400';

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
                'text-sm font-semibold text-amber-800 dark:text-amber-300';

            estadoCorreoTitulo.textContent =
                'No se pudo enviar el correo';
        }


        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-amber-700 dark:text-amber-400';

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
                + 'border-red-200 bg-red-50 shadow-sm '
                + 'dark:border-red-800 dark:bg-red-950/45';

            modalIcono.innerHTML = `
                <i
                    data-lucide="circle-x"
                    stroke-width="1.8"
                    class="h-8 w-8 text-red-600 dark:text-red-400"
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
                + 'to-rose-50/50 p-5 text-left shadow-sm '
                + 'dark:border-red-800 dark:from-red-950/45 '
                + 'dark:via-slate-900 dark:to-rose-950/30';
        }


        if (estadoCorreoIcono) {
            estadoCorreoIcono.className =
                'flex h-10 w-10 shrink-0 items-center '
                + 'justify-center rounded-xl border '
                + 'border-red-200 bg-white text-red-600 shadow-sm '
                + 'dark:border-red-800 dark:bg-slate-900 dark:text-red-400';

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
                'text-sm font-semibold text-red-800 dark:text-red-300';

            estadoCorreoTitulo.textContent =
                'La gestión no pudo completarse';
        }


        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className =
                'mt-1.5 text-xs leading-relaxed text-red-700 dark:text-red-400';

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
                    'hover:border-amber-400',
                    'dark:border-amber-800',
                    'dark:bg-slate-900',
                    'dark:text-amber-300',
                    'dark:hover:border-amber-700',
                    'dark:hover:bg-amber-900/55'
                );

                boton.classList.add(
                    'border-red-300',
                    'bg-red-50',
                    'text-red-800',
                    'hover:bg-red-100',
                    'hover:border-red-400',
                    'dark:border-red-800',
                    'dark:bg-slate-900',
                    'dark:text-red-300',
                    'dark:hover:border-red-700',
                    'dark:hover:bg-red-900/55'
                );

            } else {
                boton.classList.remove(
                    'border-red-300',
                    'bg-red-50',
                    'text-red-800',
                    'hover:bg-red-100',
                    'hover:border-red-400',
                    'dark:border-red-800',
                    'dark:text-red-300',
                    'dark:hover:border-red-700',
                    'dark:hover:bg-red-900/55'
                );

                boton.classList.add(
                    'border-amber-300',
                    'bg-amber-50',
                    'text-amber-800',
                    'hover:bg-amber-100',
                    'hover:border-amber-400',
                    'dark:border-amber-800',
                    'dark:bg-slate-900',
                    'dark:text-amber-300',
                    'dark:hover:border-amber-700',
                    'dark:hover:bg-amber-900/55'
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
                    'text-emerald-700 dark:text-emerald-300',

                dot:
                    'bg-emerald-500',

                border:
                    'border-emerald-200 dark:border-emerald-800',

                background:
                    'bg-emerald-50 dark:bg-emerald-950/45',
            },

            queued: {
                text:
                    'text-blue-700 dark:text-blue-300',

                dot:
                    'bg-blue-500',

                border:
                    'border-blue-200 dark:border-blue-800',

                background:
                    'bg-blue-50 dark:bg-blue-950/45',
            },

            warning: {
                text:
                    'text-amber-700 dark:text-amber-300',

                dot:
                    'bg-amber-500',

                border:
                    'border-amber-200 dark:border-amber-800',

                background:
                    'bg-amber-50 dark:bg-amber-950/45',
            },

            error: {
                text:
                    'text-red-700 dark:text-red-300',

                dot:
                    'bg-red-500',

                border:
                    'border-red-200 dark:border-red-800',

                background:
                    'bg-red-50 dark:bg-red-950/45',
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
                Registrando...
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