document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('incidenciaForm');

    if (!form) return;


    /*
    |--------------------------------------------------------------------------
    | EVITAR INICIALIZACIÓN DUPLICADA
    |--------------------------------------------------------------------------
    |
    | Impide registrar dos veces los eventos si este archivo JavaScript
    | se carga accidentalmente más de una vez.
    |
    */

    if (window.__incidenciaFormInicializado) {
        return;
    }

    window.__incidenciaFormInicializado = true;


    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS DEL FORMULARIO
    |--------------------------------------------------------------------------
    */

    const dropzone = document.getElementById('dropzone');
    const input = document.getElementById('archivos');
    const preview = document.getElementById('preview');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnEnviar = document.getElementById('btnEnviar');

    const modal = document.getElementById('modalIncidencia');
    const modalIcono = document.getElementById('modalIcono');
    const modalTitulo = document.getElementById('modalTitulo');
    const modalMensaje = document.getElementById('modalMensaje');
    const codigoIncidencia = document.getElementById('codigoIncidencia');

    const cerrarModalIncidencia = document.getElementById(
        'cerrarModalIncidencia'
    );

    const estadoCorreo = document.getElementById(
        'estadoCorreoIncidencia'
    );

    const estadoCorreoIcono = document.getElementById(
        'estadoCorreoIncidenciaIcono'
    );

    const estadoCorreoTitulo = document.getElementById(
        'estadoCorreoIncidenciaTitulo'
    );

    const estadoCorreoMensaje = document.getElementById(
        'estadoCorreoIncidenciaMensaje'
    );

    const smtpEstado = document.getElementById(
        'smtpEstadoIncidencia'
    );

    const btnReportarModal = document.getElementById(
        'btnReportarSmtpIncidencia'
    );

    const btnReportarPersistente = document.getElementById(
        'btnReportarSmtpIncidenciaPersistente'
    );


    let archivosSeleccionados = [];
    let enviando = false;
    let abriendoOutlook = false;
    let seguimientoCorreoActual = 0;


    if (
        modal
        && modal.parentElement !== document.body
    ) {
        document.body.appendChild(
            modal
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ETIQUETAS LEGIBLES
    |--------------------------------------------------------------------------
    */

    const etiquetasAfectacion = {
        solo: 'Solo a mí',
        varios: 'A varias personas',
        todos: 'A toda el área',
    };


    const etiquetasTiempo = {
        hoy: 'Desde hoy',
        ayer: 'Desde ayer',
        varios_dias: 'Desde hace varios días',
    };


    function obtenerEtiqueta(mapa, valor) {
        if (!valor) {
            return 'No especificado';
        }

        if (mapa[valor]) {
            return mapa[valor];
        }

        const texto = String(valor)
            .replaceAll('_', ' ')
            .replaceAll('-', ' ')
            .trim();

        return texto
            ? texto.charAt(0).toUpperCase() + texto.slice(1)
            : 'No especificado';
    }


    /*
    |--------------------------------------------------------------------------
    | DRAG & DROP
    |--------------------------------------------------------------------------
    */

    dropzone?.addEventListener('click', () => {
        input?.click();
    });

    dropzone?.addEventListener('keydown', event => {
        if (
            event.key === 'Enter'
            || event.key === ' '
        ) {
            event.preventDefault();
            input?.click();
        }
    });


    input?.addEventListener('change', event => {
        agregarArchivos(event.target.files);
    });


    dropzone?.addEventListener('dragover', event => {
        event.preventDefault();

        dropzone.classList.add(
            'border-primary',
            'bg-primary/[0.08]',
            'shadow-md'
        );
    });


    dropzone?.addEventListener('dragleave', () => {
        dropzone.classList.remove(
            'border-primary',
            'bg-primary/[0.08]',
            'shadow-md'
        );
    });


    dropzone?.addEventListener('drop', event => {
        event.preventDefault();

        dropzone.classList.remove(
            'border-primary',
            'bg-primary/[0.08]',
            'shadow-md'
        );

        agregarArchivos(event.dataTransfer.files);
    });


    function agregarArchivos(files) {
        Array.from(files).forEach(file => {

            const esImagen =
                file.type.startsWith('image/');

            const tamanoPermitido =
                file.size <= 10 * 1024 * 1024;

            const repetido =
                archivosSeleccionados.some(actual =>
                    actual.name === file.name
                    && actual.size === file.size
                    && actual.lastModified === file.lastModified
                );

            if (
                esImagen
                && tamanoPermitido
                && !repetido
            ) {
                archivosSeleccionados.push(file);
            }

        });

        renderPreview();
    }


    function renderPreview() {
        if (!preview || !input) return;

        preview.innerHTML = '';

        const dataTransfer = new DataTransfer();

        archivosSeleccionados.forEach(file => {

            dataTransfer.items.add(file);

            const card = document.createElement('div');

            card.className =
                'group/preview relative overflow-hidden rounded-xl '
                + 'border border-border bg-white shadow-sm '
                + 'transition-all duration-300 hover:border-primary/20 '
                + 'hover:shadow-lg hover:shadow-primary/10 '
                + 'dark:border-slate-700/70 dark:bg-slate-900 '
                + 'dark:hover:border-blue-700/60 dark:hover:shadow-black/20 '
                + 'motion-safe:hover:-translate-y-1';


            const imagen = document.createElement('img');

            imagen.className =
                'h-28 w-full object-cover transition-transform '
                + 'duration-500 motion-safe:group-hover/preview:scale-105';

            imagen.alt = file.name;


            const boton = document.createElement('button');

            boton.type = 'button';

            boton.className =
                'absolute right-2 top-2 flex h-8 w-8 items-center '
                + 'justify-center rounded-lg border border-white/20 '
                + 'bg-slate-950/70 text-white shadow-sm backdrop-blur-sm '
                + 'transition-all duration-200 hover:bg-red-600 '
                + 'focus:outline-none focus:ring-2 focus:ring-white/60 '
                + 'motion-safe:hover:scale-110';

            boton.setAttribute(
                'aria-label',
                `Eliminar ${file.name}`
            );

            boton.innerHTML = `
                <i
                    data-lucide="x"
                    stroke-width="2"
                    class="h-4 w-4">
                </i>
            `;


            boton.addEventListener('click', () => {

                archivosSeleccionados =
                    archivosSeleccionados.filter(
                        actual => actual !== file
                    );

                renderPreview();

            });


            const reader = new FileReader();

            reader.addEventListener('load', event => {
                imagen.src = event.target.result;
            });

            reader.readAsDataURL(file);


            card.appendChild(imagen);
            card.appendChild(boton);
            preview.appendChild(card);

        });

        input.files = dataTransfer.files;

        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | LIMPIAR FORMULARIO
    |--------------------------------------------------------------------------
    */

    function limpiarFormulario() {
        form.reset();

        archivosSeleccionados = [];

        if (input) {
            input.value = '';
        }

        renderPreview();
    }


    btnCancelar?.addEventListener('click', () => {
        limpiarFormulario();
    });


    /*
    |--------------------------------------------------------------------------
    | OUTLOOK 365
    |--------------------------------------------------------------------------
    */

    function abrirOutlook(boton, event) {
        event?.preventDefault();
        event?.stopPropagation();
        event?.stopImmediatePropagation();

        const url = boton?.dataset.outlookUrl;

        if (!url || abriendoOutlook) {
            return;
        }

        abriendoOutlook = true;

        /*
        | Outlook se abre únicamente en una pestaña nueva.
        | No se utiliza window.location.href porque eso reemplazaría
        | la página actual del Portal TI.
        */

        const nuevaVentana = window.open(
            url,
            '_blank'
        );

        if (nuevaVentana) {
            nuevaVentana.opener = null;
        }

        /*
        | Evita aperturas duplicadas por doble clic.
        */

        window.setTimeout(() => {
            abriendoOutlook = false;
        }, 1000);
    }


    btnReportarModal?.addEventListener('click', event => {
        abrirOutlook(
            btnReportarModal,
            event
        );
    });


    btnReportarPersistente?.addEventListener('click', event => {
        abrirOutlook(
            btnReportarPersistente,
            event
        );
    });


    /*
    |--------------------------------------------------------------------------
    | ENVIAR INCIDENCIA
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', async event => {

        event.preventDefault();

        if (enviando || !form.reportValidity()) {
            return;
        }

        enviando = true;

        const datosFormulario =
            obtenerDatosFormulario();

        activarCarga();

        try {

            const response = await fetch(form.action, {

                method: 'POST',

                headers: {

                    'X-CSRF-TOKEN':
                        form.querySelector('[name="_token"]')
                            ?.value ?? '',

                    'X-Requested-With':
                        'XMLHttpRequest',

                    'Accept':
                        'application/json',

                },

                body: new FormData(form),

            });


            const texto =
                await response.text();

            let data;


            try {

                data = JSON.parse(texto);

            } catch (error) {

                console.error(
                    'Respuesta no válida del servidor:',
                    texto
                );

                throw new Error(
                    'No pudimos completar el envío en este momento. '
                    + 'Por favor, intenta nuevamente.'
                );

            }


            if (!response.ok || !data.success) {

                throw new Error(
                    obtenerMensajeError(data)
                );

            }


            configurarResultadoInicial(
                data,
                datosFormulario
            );


            /*
            | La incidencia quedó registrada.
            */

            limpiarFormulario();
            abrirModal();


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
                    data,
                    datosFormulario
                );

            }

        } catch (error) {

            console.error(
                'Error al enviar la incidencia:',
                error
            );

            mostrarErrorTotal(
                error,
                datosFormulario
            );

            /*
            | Si el registro falló, se conserva la información
            | ingresada en el formulario.
            */

            abrirModal();

        } finally {

            enviando = false;
            restaurarBoton();

        }

    });


    function obtenerMensajeError(data) {
        const primerGrupo = data?.errors
            ? Object.values(data.errors)[0]
            : null;

        if (
            Array.isArray(primerGrupo)
            && primerGrupo[0]
        ) {
            return primerGrupo[0];
        }

        return data?.error
            ?? data?.message
            ?? 'No pudimos registrar la incidencia. '
            + 'Revisa la información e intenta nuevamente.';
    }


    function obtenerDatosFormulario() {
        const afectacion =
            form.querySelector('[name="afectacion"]')
                ?.value ?? '';

        const tiempo =
            form.querySelector('[name="tiempo_problema"]')
                ?.value ?? '';

        return {

            titulo:
                form.querySelector('[name="titulo"]')
                    ?.value.trim()
                || 'No especificado',

            descripcion:
                form.querySelector('[name="descripcion"]')
                    ?.value.trim()
                || 'No especificada',

            tiempo: obtenerEtiqueta(
                etiquetasTiempo,
                tiempo
            ),

            afectacion: obtenerEtiqueta(
                etiquetasAfectacion,
                afectacion
            ),

            equipo:
                form.querySelector('[name="equipo"]')
                    ?.value.trim()
                || 'No especificado',

            ubicacion:
                form.querySelector('[name="ubicacion"]')
                    ?.value.trim()
                || 'No especificada',

            cantidadArchivos:
                archivosSeleccionados.length,

        };
    }


    /*
    |--------------------------------------------------------------------------
    | SEGUIMIENTO DEL ESTADO DEL CORREO
    |--------------------------------------------------------------------------
    */

    async function vigilarEstadoCorreo(
        deliveryId,
        data,
        datosFormulario
    ) {
        if (
            !deliveryId
            || !window.emailDeliveryStatusUrl
        ) {
            return;
        }

        const seguimientoId =
            ++seguimientoCorreoActual;

        const maxConsultas =
            20;

        const esperaMs =
            1500;

        for (
            let consulta = 1;
            consulta <= maxConsultas;
            consulta++
        ) {
            await esperar(
                esperaMs
            );

            if (
                seguimientoId
                !== seguimientoCorreoActual
            ) {
                return;
            }

            try {
                const url =
                    window.emailDeliveryStatusUrl.replace(
                        '__DELIVERY_ID__',
                        encodeURIComponent(
                            deliveryId
                        )
                    );

                const response =
                    await fetch(
                        url,
                        {
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
                    mostrarExito(
                        data
                    );

                    return;
                }

                if (
                    resultado.email?.failed === true
                    || estado === 'fallido'
                ) {
                    mostrarAdvertenciaCorreo(
                        {
                            ...data,

                            email: {
                                ...data.email,
                                ...resultado.email,
                            },
                        },
                        datosFormulario
                    );

                    return;
                }

                mostrarCorreoEnCola(
                    data,
                    estado,
                    resultado.email?.attempts
                );

            } catch (error) {
                console.warn(
                    'No se pudo consultar el estado del correo:',
                    error
                );
            }
        }

        if (
            seguimientoId
            === seguimientoCorreoActual
        ) {
            actualizarIndicador(
                'queued',
                'El correo continúa pendiente en la cola'
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
    | ÉXITO: REGISTRADA Y NOTIFICADA
    |--------------------------------------------------------------------------
    */

    function configurarResultadoInicial(
        data,
        datosFormulario
    ) {
        const email =
            data.email
            ?? {};

        const estado =
            String(
                email.status
                ?? ''
            ).toLowerCase();

        if (
            email.sent === true
            || estado === 'enviado'
        ) {
            mostrarExito(
                data
            );

            return;
        }

        if (
            email.failed === true
            || estado === 'fallido'
        ) {
            mostrarAdvertenciaCorreo(
                data,
                datosFormulario
            );

            return;
        }

        mostrarCorreoEnCola(
            data,
            estado,
            email.attempts
        );
    }


    function mostrarCorreoEnCola(
        data,
        estado = 'pendiente',
        attempts = 0
    ) {
        ocultarBotonesReporte();

        configurarCabecera(
            'queued',
            'Incidencia registrada',
            data.message
                ?? 'Tu incidencia fue registrada correctamente. '
                + 'La notificación por correo se está procesando.'
        );

        configurarEstadoCorreo(
            'queued',
            estado === 'enviando'
                ? 'Enviando notificación'
                : 'Correo en procesamiento',
            estado === 'enviando'
                ? (
                    Number(attempts) > 0
                        ? `El servidor está procesando el correo. Intento ${attempts}.`
                        : 'El servidor está procesando el correo.'
                )
                : 'La notificación fue agregada a la cola y será enviada en segundo plano.'
        );

        mostrarCodigo(
            data.codigo
        );

        actualizarIndicador(
            'queued',
            'Correo pendiente en la cola'
        );

        refrescarIconos();
    }


    function mostrarExito(data) {
        ocultarBotonesReporte();

        configurarCabecera(
            'success',
            'Incidencia enviada',
            data.message
                ?? 'Tu incidencia fue registrada correctamente. '
                + 'El equipo de soporte TI ya fue notificado.'
        );

        configurarEstadoCorreo(
            'success',
            'El equipo de soporte fue notificado',
            'Tu reporte fue enviado correctamente y '
            + 'ya puede ser revisado por el equipo de TI.'
        );

        mostrarCodigo(data.codigo);

        actualizarIndicador(
            'success',
            'Notificación enviada correctamente'
        );

        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | ADVERTENCIA: REGISTRADA, PERO NO NOTIFICADA
    |--------------------------------------------------------------------------
    */

    function mostrarAdvertenciaCorreo(
        data,
        datosFormulario
    ) {
        configurarCabecera(
            'warning',
            'Incidencia registrada',
            'Tu incidencia fue guardada correctamente, '
            + 'pero no pudimos avisar automáticamente '
            + 'al equipo de soporte.'
        );

        configurarEstadoCorreo(
            'warning',
            'La notificación no pudo enviarse',
            'Tu reporte no se perdió. Puedes utilizar '
            + 'el botón de Outlook para informar al '
            + 'equipo de soporte.'
        );

        mostrarCodigo(data.codigo);

        const outlookUrl = construirOutlook(
            data,
            datosFormulario,
            false
        );

        configurarBotonesReporte(
            outlookUrl,
            'warning'
        );

        actualizarIndicador(
            'warning',
            'La incidencia se guardó, pero el aviso no pudo enviarse'
        );

        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | ERROR: NO SE REGISTRÓ
    |--------------------------------------------------------------------------
    */

    function mostrarErrorTotal(
        error,
        datosFormulario
    ) {
        configurarCabecera(
            'error',
            'No pudimos enviar tu incidencia',
            error?.message
                ?? 'Ocurrió un inconveniente al procesar '
                + 'tu reporte. Intenta nuevamente.'
        );

        configurarEstadoCorreo(
            'error',
            'El reporte no fue enviado',
            'Tu información permanece en el formulario. '
            + 'También puedes utilizar Outlook para '
            + 'informar al equipo de soporte.'
        );

        mostrarCodigo(null);

        const outlookUrl = construirOutlook(
            null,
            datosFormulario,
            true
        );

        configurarBotonesReporte(
            outlookUrl,
            'error'
        );

        actualizarIndicador(
            'error',
            'No fue posible enviar el reporte'
        );

        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN DEL MODAL
    |--------------------------------------------------------------------------
    */

    function configurarCabecera(
        tipo,
        titulo,
        mensaje
    ) {
        const estilos = {

            queued: [
                'bg-blue-50 dark:bg-blue-950/45',
                'border-blue-200 dark:border-blue-800',
                'text-blue-600 dark:text-blue-400',
                'clock-3',
            ],

            success: [
                'bg-emerald-50 dark:bg-emerald-950/45',
                'border-emerald-200 dark:border-emerald-800',
                'text-emerald-600 dark:text-emerald-400',
                'circle-check-big',
            ],

            warning: [
                'bg-amber-50 dark:bg-amber-950/45',
                'border-amber-200 dark:border-amber-800',
                'text-amber-600 dark:text-amber-400',
                'mail-warning',
            ],

            error: [
                'bg-red-50 dark:bg-red-950/45',
                'border-red-200 dark:border-red-800',
                'text-red-600 dark:text-red-400',
                'circle-x',
            ],

        };

        const estilo =
            estilos[tipo] ?? estilos.error;


        if (modalIcono) {

            modalIcono.className =
                `mx-auto flex h-16 w-16 items-center `
                + `justify-center rounded-2xl border ${estilo[1]} `
                + `${estilo[0]} shadow-sm`;

            modalIcono.innerHTML =
                `<i data-lucide="${estilo[3]}" stroke-width="1.8" `
                + `class="h-8 w-8 ${estilo[2]}"></i>`;

        }


        if (modalTitulo) {
            modalTitulo.textContent = titulo;
        }


        if (modalMensaje) {
            modalMensaje.textContent = mensaje;
        }
    }


    function configurarEstadoCorreo(
        tipo,
        titulo,
        mensaje
    ) {
        const estilos = {

            queued: [
                'border-blue-200 dark:border-blue-800',
                'bg-gradient-to-br from-blue-50/80 via-white to-sky-50/50 dark:from-blue-950/45 dark:via-slate-900 dark:to-sky-950/30',
                'text-blue-800 dark:text-blue-300',
                'text-blue-700 dark:text-blue-400',
                'mail',
                'text-blue-600 dark:text-blue-400',
            ],

            success: [
                'border-emerald-200 dark:border-emerald-800',
                'bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 dark:from-emerald-950/45 dark:via-slate-900 dark:to-teal-950/30',
                'text-emerald-800 dark:text-emerald-300',
                'text-emerald-700 dark:text-emerald-400',
                'mail-check',
                'text-emerald-600 dark:text-emerald-400',
            ],

            warning: [
                'border-amber-200 dark:border-amber-800',
                'bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50 dark:from-amber-950/45 dark:via-slate-900 dark:to-orange-950/30',
                'text-amber-800 dark:text-amber-300',
                'text-amber-700 dark:text-amber-400',
                'mail-warning',
                'text-amber-600 dark:text-amber-400',
            ],

            error: [
                'border-red-200 dark:border-red-800',
                'bg-gradient-to-br from-red-50/80 via-white to-rose-50/50 dark:from-red-950/45 dark:via-slate-900 dark:to-rose-950/30',
                'text-red-800 dark:text-red-300',
                'text-red-700 dark:text-red-400',
                'triangle-alert',
                'text-red-600 dark:text-red-400',
            ],

        };

        const estilo =
            estilos[tipo] ?? estilos.error;


        if (estadoCorreo) {

            estadoCorreo.className =
                `rounded-2xl border ${estilo[0]} `
                + `${estilo[1]} p-5 text-left shadow-sm`;

        }


        if (estadoCorreoIcono) {

            estadoCorreoIcono.className =
                `flex h-10 w-10 shrink-0 items-center justify-center `
                + `rounded-xl border ${estilo[0]} bg-white dark:bg-slate-900 `
                + `${estilo[5]} shadow-sm`;

            estadoCorreoIcono.innerHTML =
                `<i data-lucide="${estilo[4]}" stroke-width="1.8" `
                + 'class="h-5 w-5"></i>';

        }


        if (estadoCorreoTitulo) {

            estadoCorreoTitulo.className =
                `text-sm font-semibold ${estilo[2]}`;

            estadoCorreoTitulo.textContent =
                titulo;

        }


        if (estadoCorreoMensaje) {

            estadoCorreoMensaje.className =
                `mt-1.5 text-xs leading-relaxed ${estilo[3]}`;

            estadoCorreoMensaje.textContent =
                mensaje;

        }
    }


    function mostrarCodigo(codigo) {
        if (!codigoIncidencia) return;

        if (codigo) {

            codigoIncidencia.textContent =
                codigo;

            codigoIncidencia.classList.remove(
                'hidden'
            );

            codigoIncidencia.classList.add(
                'inline-flex'
            );

        } else {

            codigoIncidencia.textContent = '';

            codigoIncidencia.classList.add(
                'hidden'
            );

            codigoIncidencia.classList.remove(
                'inline-flex'
            );

        }
    }


    /*
    |--------------------------------------------------------------------------
    | MENSAJE DE RESPALDO PARA OUTLOOK
    |--------------------------------------------------------------------------
    */

    function construirOutlook(
        data,
        datos,
        errorTotal = false
    ) {
        const botonDatos =
            btnReportarModal
            ?? btnReportarPersistente;

        const recipient =
            botonDatos?.dataset.recipient
            || 'helpdesk@televicentro.hn';

        const userName =
            botonDatos?.dataset.userName
            || 'No especificado';

        const userEmail =
            botonDatos?.dataset.userEmail
            || 'No especificado';

        const codigo =
            data?.codigo
            ?? 'No generado';


        const subject = errorTotal
            ? '[Portal TI] Apoyo con reporte de incidencia'
            : `[Portal TI] Seguimiento de incidencia ${codigo}`;


        const mensajePrincipal = errorTotal
            ? 'Intenté registrar una incidencia en el Portal TI, '
                + 'pero el proceso no pudo completarse.'
            : 'La incidencia fue registrada en el Portal TI, '
                + 'pero el equipo de soporte no recibió '
                + 'la notificación automática.';


        const mensajeFinal = errorTotal
            ? 'Por favor, ayúdenme a revisar y registrar esta incidencia.'
            : 'Por favor, ayúdenme a dar seguimiento a esta incidencia.';


        const cantidadEvidencias =
            datos.cantidadArchivos === 1
                ? '1 imagen'
                : `${datos.cantidadArchivos} imágenes`;


        const body = [

            'Hola, equipo de Helpdesk:',
            '',

            mensajePrincipal,
            '',

            'Datos del usuario',
            `Nombre: ${userName}`,
            `Correo: ${userEmail}`,
            '',

            'Información de la incidencia',
            `Código: ${codigo}`,
            `Título: ${datos.titulo}`,
            `Descripción: ${datos.descripcion}`,
            `¿Desde cuándo ocurre?: ${datos.tiempo}`,
            `Equipo: ${datos.equipo}`,
            `Ubicación: ${datos.ubicacion}`,
            `Personas afectadas: ${datos.afectacion}`,
            `Evidencias seleccionadas: ${cantidadEvidencias}`,
            '',

            `Fecha del reporte: ${
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

        ].join('\r\n');


        return 'https://outlook.office.com/mail/deeplink/compose'
            + `?to=${encodeURIComponent(recipient)}`
            + `&subject=${encodeURIComponent(subject)}`
            + `&body=${encodeURIComponent(body)}`;
    }


    /*
    |--------------------------------------------------------------------------
    | BOTONES DE OUTLOOK
    |--------------------------------------------------------------------------
    */

    function configurarBotonesReporte(
        url,
        tipo
    ) {
        [
            btnReportarModal,
            btnReportarPersistente,
        ].forEach(boton => {

            if (!boton) return;

            boton.dataset.outlookUrl = url;

            boton.classList.remove('hidden');
            boton.classList.add('inline-flex');


            if (tipo === 'error') {

                boton.classList.remove(
                    'border-amber-300',
                    'bg-amber-50',
                    'text-amber-800',
                    'hover:bg-amber-100',
                    'hover:border-amber-400',
                    'dark:border-amber-800',
                    'dark:bg-amber-950/30',
                    'dark:text-amber-300',
                    'dark:hover:border-amber-700',
                    'dark:hover:bg-amber-900/45'
                );

                boton.classList.add(
                    'border-red-300',
                    'bg-red-50',
                    'text-red-800',
                    'hover:bg-red-100',
                    'hover:border-red-400',
                    'dark:border-red-800',
                    'dark:bg-red-950/30',
                    'dark:text-red-300',
                    'dark:hover:border-red-700',
                    'dark:hover:bg-red-900/45'
                );

            } else {

                boton.classList.remove(
                    'border-red-300',
                    'bg-red-50',
                    'text-red-800',
                    'hover:bg-red-100',
                    'hover:border-red-400',
                    'dark:border-red-800',
                    'dark:bg-red-950/30',
                    'dark:text-red-300',
                    'dark:hover:border-red-700',
                    'dark:hover:bg-red-900/45'
                );

                boton.classList.add(
                    'border-amber-300',
                    'bg-amber-50',
                    'text-amber-800',
                    'hover:bg-amber-100',
                    'hover:border-amber-400',
                    'dark:border-amber-800',
                    'dark:bg-amber-950/30',
                    'dark:text-amber-300',
                    'dark:hover:border-amber-700',
                    'dark:hover:bg-amber-900/45'
                );

            }

        });
    }


    function ocultarBotonesReporte() {
        [
            btnReportarModal,
            btnReportarPersistente,
        ].forEach(boton => {

            if (!boton) return;

            boton.classList.add('hidden');
            boton.classList.remove('inline-flex');

            delete boton.dataset.outlookUrl;

        });
    }


    function actualizarIndicador(
        tipo,
        texto
    ) {
        if (!smtpEstado) return;

        const estilos = {

            queued: [
                'text-blue-700 dark:text-blue-300',
                'bg-blue-500',
                'border-blue-200 dark:border-blue-800',
                'bg-blue-50 dark:bg-blue-950/35',
            ],

            success: [
                'text-emerald-700 dark:text-emerald-300',
                'bg-emerald-500',
                'border-emerald-200 dark:border-emerald-800',
                'bg-emerald-50 dark:bg-emerald-950/35',
            ],

            warning: [
                'text-amber-700 dark:text-amber-300',
                'bg-amber-500',
                'border-amber-200 dark:border-amber-800',
                'bg-amber-50 dark:bg-amber-950/35',
            ],

            error: [
                'text-red-700 dark:text-red-300',
                'bg-red-500',
                'border-red-200 dark:border-red-800',
                'bg-red-50 dark:bg-red-950/35',
            ],

        };

        const estilo =
            estilos[tipo] ?? estilos.error;

        smtpEstado.className =
            'inline-flex items-center gap-2 rounded-lg border '
            + 'px-3 py-2 text-xs font-medium shadow-sm '
            + `${estilo[0]} ${estilo[2]} ${estilo[3]}`;

        smtpEstado.innerHTML =
            `<span class="h-2.5 w-2.5 shrink-0 rounded-full `
            + `${estilo[1]}"></span>${texto}`;
    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN DE ENVÍO
    |--------------------------------------------------------------------------
    */

    function activarCarga() {
        if (!btnEnviar) return;

        btnEnviar.disabled = true;

        btnEnviar.innerHTML = `
            <span class="spinner-envio"></span>
            <span>Registrando reporte...</span>
        `;
    }


    function restaurarBoton() {
        if (!btnEnviar) return;

        btnEnviar.disabled = false;

        btnEnviar.innerHTML = `
            <i
                id="btnEnviarIcono"
                data-lucide="send"
                stroke-width="1.8"
                class="h-4 w-4 transition-transform duration-200 motion-safe:group-hover/send:translate-x-0.5 motion-safe:group-hover/send:-translate-y-0.5"
            ></i>

            <span id="btnEnviarTexto">
                Enviar reporte
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
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        refrescarIconos();
    }


    function ocultarModal() {
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }


    cerrarModalIncidencia?.addEventListener(
        'click',
        ocultarModal
    );


    modal?.addEventListener('click', event => {
        if (event.target === modal) {
            ocultarModal();
        }
    });


    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            ocultarModal();
        }
    });


    window.cerrarModal = ocultarModal;


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


    refrescarIconos();

});