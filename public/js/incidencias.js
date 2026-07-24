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


    input?.addEventListener('change', event => {
        agregarArchivos(event.target.files);
    });


    dropzone?.addEventListener('dragover', event => {
        event.preventDefault();

        dropzone.classList.add(
            'border-primary',
            'bg-primary/5'
        );
    });


    dropzone?.addEventListener('dragleave', () => {
        dropzone.classList.remove(
            'border-primary',
            'bg-primary/5'
        );
    });


    dropzone?.addEventListener('drop', event => {
        event.preventDefault();

        dropzone.classList.remove(
            'border-primary',
            'bg-primary/5'
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
                'relative rounded-xl overflow-hidden '
                + 'border border-border bg-white';


            const imagen = document.createElement('img');

            imagen.className =
                'w-full h-28 object-cover';

            imagen.alt = file.name;


            const boton = document.createElement('button');

            boton.type = 'button';

            boton.className =
                'absolute top-2 right-2 bg-black/60 '
                + 'text-white rounded-full w-7 h-7 '
                + 'flex items-center justify-center '
                + 'hover:bg-red-600 transition';

            boton.setAttribute(
                'aria-label',
                `Eliminar ${file.name}`
            );

            boton.textContent = '×';


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


            if (data.email?.sent === true) {

                mostrarExito(data);

            } else {

                mostrarAdvertenciaCorreo(
                    data,
                    datosFormulario
                );

            }


            /*
            | La incidencia quedó registrada.
            */

            limpiarFormulario();
            abrirModal();

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
    | ÉXITO: REGISTRADA Y NOTIFICADA
    |--------------------------------------------------------------------------
    */

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

            success: [
                'bg-green-50',
                'border-green-200',
                'text-green-600',
                'check-circle',
            ],

            warning: [
                'bg-amber-50',
                'border-amber-200',
                'text-amber-600',
                'mail-warning',
            ],

            error: [
                'bg-red-50',
                'border-red-200',
                'text-red-600',
                'x-circle',
            ],

        };

        const estilo =
            estilos[tipo] ?? estilos.error;


        if (modalIcono) {

            modalIcono.className =
                `w-16 h-16 rounded-2xl ${estilo[0]} `
                + `border ${estilo[1]} flex items-center `
                + 'justify-center mx-auto';

            modalIcono.innerHTML =
                `<i data-lucide="${estilo[3]}" `
                + `class="w-8 h-8 ${estilo[2]}"></i>`;

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

            success: [
                'border-green-200',
                'bg-green-50/70',
                'text-green-800',
                'text-green-700',
                'mail-check',
                'text-green-600',
            ],

            warning: [
                'border-amber-200',
                'bg-amber-50/70',
                'text-amber-800',
                'text-amber-700',
                'mail-warning',
                'text-amber-600',
            ],

            error: [
                'border-red-200',
                'bg-red-50/70',
                'text-red-800',
                'text-red-700',
                'triangle-alert',
                'text-red-600',
            ],

        };

        const estilo =
            estilos[tipo] ?? estilos.error;


        if (estadoCorreo) {

            estadoCorreo.className =
                `rounded-2xl border ${estilo[0]} `
                + `${estilo[1]} p-5 text-left`;

        }


        if (estadoCorreoIcono) {

            estadoCorreoIcono.className =
                `w-10 h-10 rounded-xl bg-white border `
                + `${estilo[0]} flex items-center justify-center`;

            estadoCorreoIcono.innerHTML =
                `<i data-lucide="${estilo[4]}" `
                + `class="w-5 h-5 ${estilo[5]}"></i>`;

        }


        if (estadoCorreoTitulo) {

            estadoCorreoTitulo.className =
                `text-sm font-semibold ${estilo[2]}`;

            estadoCorreoTitulo.textContent =
                titulo;

        }


        if (estadoCorreoMensaje) {

            estadoCorreoMensaje.className =
                `text-xs ${estilo[3]} `
                + 'leading-relaxed mt-1.5';

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
                    'hover:bg-amber-100'
                );

                boton.classList.add(
                    'border-red-300',
                    'bg-red-50',
                    'text-red-800',
                    'hover:bg-red-100'
                );

            } else {

                boton.classList.remove(
                    'border-red-300',
                    'bg-red-50',
                    'text-red-800',
                    'hover:bg-red-100'
                );

                boton.classList.add(
                    'border-amber-300',
                    'bg-amber-50',
                    'text-amber-800',
                    'hover:bg-amber-100'
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

            success: [
                'text-green-700',
                'bg-green-500',
            ],

            warning: [
                'text-amber-700',
                'bg-amber-500',
            ],

            error: [
                'text-red-700',
                'bg-red-500',
            ],

        };

        const estilo =
            estilos[tipo] ?? estilos.error;

        smtpEstado.className =
            `inline-flex items-center gap-2 `
            + `text-xs ${estilo[0]}`;

        smtpEstado.innerHTML =
            `<span class="w-2.5 h-2.5 rounded-full `
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
            <span>Enviando reporte...</span>
        `;
    }


    function restaurarBoton() {
        if (!btnEnviar) return;

        btnEnviar.disabled = false;

        btnEnviar.innerHTML = `
            <i
                id="btnEnviarIcono"
                data-lucide="send"
                class="w-4 h-4"
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

        refrescarIconos();
    }


    function ocultarModal() {
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
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