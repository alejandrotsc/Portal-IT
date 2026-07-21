document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('incidenciaForm');
    const dropzone = document.getElementById('dropzone');
    const input = document.getElementById('archivos');
    const preview = document.getElementById('preview');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnEnviar = document.getElementById('btnEnviar');
    const btnEnviarTexto = document.getElementById('btnEnviarTexto');

    const modal = document.getElementById('modalIncidencia');
    const modalIcono = document.getElementById('modalIcono');
    const modalTitulo = document.getElementById('modalTitulo');
    const modalMensaje = document.getElementById('modalMensaje');
    const codigoIncidencia = document.getElementById('codigoIncidencia');
    const cerrarModalIncidencia = document.getElementById('cerrarModalIncidencia');

    const estadoCorreo = document.getElementById('estadoCorreoIncidencia');
    const estadoCorreoIcono = document.getElementById('estadoCorreoIncidenciaIcono');
    const estadoCorreoTitulo = document.getElementById('estadoCorreoIncidenciaTitulo');
    const estadoCorreoMensaje = document.getElementById('estadoCorreoIncidenciaMensaje');
    const smtpEstado = document.getElementById('smtpEstadoIncidencia');

    const btnReportarModal = document.getElementById('btnReportarSmtpIncidencia');
    const btnReportarPersistente = document.getElementById('btnReportarSmtpIncidenciaPersistente');

    if (!form) return;

    let archivosSeleccionados = [];
    let enviando = false;


    /*
    |--------------------------------------------------------------------------
    | DRAG & DROP
    |--------------------------------------------------------------------------
    */

    dropzone?.addEventListener('click', () => input?.click());

    input?.addEventListener('change', event => {
        agregarArchivos(event.target.files);
    });

    dropzone?.addEventListener('dragover', event => {
        event.preventDefault();
        dropzone.classList.add('border-primary', 'bg-primary/5');
    });

    dropzone?.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-primary', 'bg-primary/5');
    });

    dropzone?.addEventListener('drop', event => {
        event.preventDefault();
        dropzone.classList.remove('border-primary', 'bg-primary/5');
        agregarArchivos(event.dataTransfer.files);
    });


    function agregarArchivos(files) {
        Array.from(files).forEach(file => {
            const esImagen = file.type.startsWith('image/');
            const tamanoPermitido = file.size <= 10 * 1024 * 1024;

            const repetido = archivosSeleccionados.some(actual =>
                actual.name === file.name
                && actual.size === file.size
                && actual.lastModified === file.lastModified
            );

            if (esImagen && tamanoPermitido && !repetido) {
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
            card.className = 'relative rounded-xl overflow-hidden border border-border bg-white';

            const imagen = document.createElement('img');
            imagen.className = 'w-full h-28 object-cover';
            imagen.alt = file.name;

            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'absolute top-2 right-2 bg-black/60 text-white rounded-full w-7 h-7 flex items-center justify-center hover:bg-red-600 transition';
            boton.setAttribute('aria-label', `Eliminar ${file.name}`);
            boton.textContent = '×';

            boton.addEventListener('click', () => {
                archivosSeleccionados = archivosSeleccionados.filter(actual => actual !== file);
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
    | CANCELAR
    |--------------------------------------------------------------------------
    */

    btnCancelar?.addEventListener('click', () => {
        form.reset();
        archivosSeleccionados = [];

        if (input) input.value = '';

        renderPreview();
    });


    /*
    |--------------------------------------------------------------------------
    | OUTLOOK 365
    |--------------------------------------------------------------------------
    */

    function abrirOutlook(boton, event) {
        event?.preventDefault();

        const url = boton?.dataset.outlookUrl;
        if (!url) return;

        const ventana = window.open(url, '_blank');

        if (ventana) {
            ventana.opener = null;
        } else {
            window.location.href = url;
        }
    }

    btnReportarModal?.addEventListener('click', event => {
        abrirOutlook(btnReportarModal, event);
    });

    btnReportarPersistente?.addEventListener('click', event => {
        abrirOutlook(btnReportarPersistente, event);
    });


    /*
    |--------------------------------------------------------------------------
    | SUBMIT AJAX
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', async event => {
        event.preventDefault();

        if (enviando || !form.reportValidity()) return;

        enviando = true;

        const datosFormulario = obtenerDatosFormulario();
        activarCarga();

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });

            const texto = await response.text();
            let data;

            try {
                data = JSON.parse(texto);
            } catch (error) {
                console.error('Respuesta no JSON:', texto);
                throw new Error('Laravel devolvió una respuesta inválida.');
            }

            if (!response.ok || !data.success) {
                throw new Error(obtenerMensajeError(data));
            }

            if (data.email?.sent === true) {
                mostrarExito(data);
            } else {
                mostrarAdvertenciaSmtp(data, datosFormulario);
            }

            form.reset();
            archivosSeleccionados = [];
            renderPreview();
            abrirModal();

        } catch (error) {
            console.error('Error enviando incidencia:', error);
            mostrarErrorTotal(error, datosFormulario);
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

        if (Array.isArray(primerGrupo) && primerGrupo[0]) {
            return primerGrupo[0];
        }

        return data?.error
            ?? data?.message
            ?? 'No fue posible registrar la incidencia.';
    }


    function obtenerDatosFormulario() {
        return {
            titulo: form.querySelector('[name="titulo"]')?.value ?? 'N/A',
            descripcion: form.querySelector('[name="descripcion"]')?.value ?? 'N/A',
            tiempo: form.querySelector('[name="tiempo_problema"]')?.value ?? 'N/A',
            afectacion: form.querySelector('[name="afectacion"]')?.value ?? 'N/A',
            equipo: form.querySelector('[name="equipo"]')?.value ?? 'N/A',
            ubicacion: form.querySelector('[name="ubicacion"]')?.value ?? 'N/A',
            cantidadArchivos: archivosSeleccionados.length,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | VERDE: REGISTRADA Y NOTIFICADA
    |--------------------------------------------------------------------------
    */

    function mostrarExito(data) {
        ocultarBotonesReporte();

        configurarCabecera(
            'success',
            'Incidencia registrada',
            data.message ?? 'La incidencia fue registrada y el equipo TI fue notificado.'
        );

        configurarEstadoCorreo(
            'success',
            'Correo enviado correctamente',
            'El servidor SMTP aceptó la notificación para el equipo de soporte TI.'
        );

        mostrarCodigo(data.codigo);
        actualizarIndicador('success', 'Último envío SMTP correcto');
        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | NARANJA: REGISTRADA, SMTP FALLIDO
    |--------------------------------------------------------------------------
    */

    function mostrarAdvertenciaSmtp(data, datosFormulario) {
        configurarCabecera(
            'warning',
            'Incidencia registrada con advertencia',
            data.message ?? 'La incidencia fue registrada, pero el correo no pudo enviarse.'
        );

        configurarEstadoCorreo(
            'warning',
            'No se pudo enviar el correo',
            'La incidencia quedó registrada. Puedes informar la falla mediante Outlook 365.'
        );

        mostrarCodigo(data.codigo);

        const outlookUrl = construirOutlook(data, datosFormulario, false);
        configurarBotonesReporte(outlookUrl, 'warning');
        actualizarIndicador('warning', 'Último envío SMTP fallido');
        refrescarIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | ROJO: NO SE REGISTRÓ
    |--------------------------------------------------------------------------
    */

    function mostrarErrorTotal(error, datosFormulario) {
        configurarCabecera(
            'error',
            'No se pudo registrar la incidencia',
            error?.message ?? 'Ocurrió un error procesando el reporte.'
        );

        configurarEstadoCorreo(
            'error',
            'La gestión no pudo completarse',
            'Puedes informar el problema al equipo de Helpdesk mediante Outlook 365.'
        );

        mostrarCodigo(null);

        const outlookUrl = construirOutlook(null, datosFormulario, true, error);
        configurarBotonesReporte(outlookUrl, 'error');
        actualizarIndicador('error', 'No se pudo registrar ni notificar la incidencia');
        refrescarIconos();
    }


    function configurarCabecera(tipo, titulo, mensaje) {
        const estilos = {
            success: ['bg-green-50', 'border-green-200', 'text-green-600', 'check-circle'],
            warning: ['bg-amber-50', 'border-amber-200', 'text-amber-600', 'mail-warning'],
            error: ['bg-red-50', 'border-red-200', 'text-red-600', 'x-circle'],
        };

        const estilo = estilos[tipo] ?? estilos.error;

        if (modalIcono) {
            modalIcono.className = `w-16 h-16 rounded-2xl ${estilo[0]} border ${estilo[1]} flex items-center justify-center mx-auto`;
            modalIcono.innerHTML = `<i data-lucide="${estilo[3]}" class="w-8 h-8 ${estilo[2]}"></i>`;
        }

        if (modalTitulo) modalTitulo.textContent = titulo;
        if (modalMensaje) modalMensaje.textContent = mensaje;
    }


    function configurarEstadoCorreo(tipo, titulo, mensaje) {
        const estilos = {
            success: ['border-green-200', 'bg-green-50/70', 'text-green-800', 'text-green-700', 'mail-check', 'text-green-600'],
            warning: ['border-amber-200', 'bg-amber-50/70', 'text-amber-800', 'text-amber-700', 'mail-warning', 'text-amber-600'],
            error: ['border-red-200', 'bg-red-50/70', 'text-red-800', 'text-red-700', 'triangle-alert', 'text-red-600'],
        };

        const estilo = estilos[tipo] ?? estilos.error;

        if (estadoCorreo) {
            estadoCorreo.className = `rounded-2xl border ${estilo[0]} ${estilo[1]} p-5 text-left`;
        }

        if (estadoCorreoIcono) {
            estadoCorreoIcono.className = `w-10 h-10 rounded-xl bg-white border ${estilo[0]} flex items-center justify-center`;
            estadoCorreoIcono.innerHTML = `<i data-lucide="${estilo[4]}" class="w-5 h-5 ${estilo[5]}"></i>`;
        }

        if (estadoCorreoTitulo) {
            estadoCorreoTitulo.className = `text-sm font-semibold ${estilo[2]}`;
            estadoCorreoTitulo.textContent = titulo;
        }

        if (estadoCorreoMensaje) {
            estadoCorreoMensaje.className = `text-xs ${estilo[3]} leading-relaxed mt-1.5`;
            estadoCorreoMensaje.textContent = mensaje;
        }
    }


    function mostrarCodigo(codigo) {
        if (!codigoIncidencia) return;

        if (codigo) {
            codigoIncidencia.textContent = codigo;
            codigoIncidencia.classList.remove('hidden');
            codigoIncidencia.classList.add('inline-flex');
        } else {
            codigoIncidencia.textContent = '';
            codigoIncidencia.classList.add('hidden');
            codigoIncidencia.classList.remove('inline-flex');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CORREO DE RESPALDO OUTLOOK 365
    |--------------------------------------------------------------------------
    */

    function construirOutlook(data, datos, errorTotal = false, error = null) {
        const botonDatos = btnReportarModal ?? btnReportarPersistente;

        const recipient = botonDatos?.dataset.recipient || 'helpdesk@televicentro.hn';
        const userName = botonDatos?.dataset.userName || 'N/A';
        const userEmail = botonDatos?.dataset.userEmail || 'N/A';
        const codigo = data?.codigo ?? 'N/A';
        const deliveryId = data?.email?.delivery_id ?? 'N/A';

        const subject = errorTotal
            ? '[Portal TI] Error al registrar incidencia'
            : `[Portal TI] Falla SMTP - Incidencia ${codigo}`;

        const body = [
            'Hola, equipo de Helpdesk:',
            '',
            errorTotal
                ? 'El Portal TI no pudo registrar una incidencia.'
                : 'El Portal TI registró una incidencia, pero no pudo enviar la notificación mediante SMTP.',
            '',
            `Usuario: ${userName}`,
            `Correo del usuario: ${userEmail}`,
            'Gestión: Reporte de incidencia',
            `Código: ${codigo}`,
            `Título: ${datos.titulo}`,
            `Descripción: ${datos.descripcion}`,
            `Equipo: ${datos.equipo}`,
            `Ubicación: ${datos.ubicacion}`,
            `Afectación: ${datos.afectacion}`,
            `Cantidad de evidencias: ${datos.cantidadArchivos}`,
            `Referencia del envío: ${deliveryId}`,
            `Estado: ${data?.email?.status ?? (errorTotal ? 'error al registrar' : 'fallido')}`,
            errorTotal ? `Mensaje mostrado: ${error?.message ?? 'Error no especificado'}` : null,
            `Fecha del reporte: ${new Date().toLocaleString('es-HN')}`,
            `Página del Portal TI: ${window.location.href}`,
            '',
            errorTotal
                ? 'Por favor, revisen la disponibilidad del Portal TI.'
                : 'La incidencia quedó registrada. Por favor, revisen el servicio SMTP.',
        ].filter(linea => linea !== null).join('\r\n');

        return 'https://outlook.office.com/mail/deeplink/compose'
            + `?to=${encodeURIComponent(recipient)}`
            + `&subject=${encodeURIComponent(subject)}`
            + `&body=${encodeURIComponent(body)}`;
    }


    function configurarBotonesReporte(url, tipo) {
        [btnReportarModal, btnReportarPersistente].forEach(boton => {
            if (!boton) return;

            boton.dataset.outlookUrl = url;
            boton.classList.remove('hidden');
            boton.classList.add('inline-flex');

            if (tipo === 'error') {
                boton.classList.remove('border-amber-300', 'bg-amber-50', 'text-amber-800', 'hover:bg-amber-100');
                boton.classList.add('border-red-300', 'bg-red-50', 'text-red-800', 'hover:bg-red-100');
            } else {
                boton.classList.remove('border-red-300', 'bg-red-50', 'text-red-800', 'hover:bg-red-100');
                boton.classList.add('border-amber-300', 'bg-amber-50', 'text-amber-800', 'hover:bg-amber-100');
            }
        });
    }


    function ocultarBotonesReporte() {
        [btnReportarModal, btnReportarPersistente].forEach(boton => {
            if (!boton) return;

            boton.classList.add('hidden');
            boton.classList.remove('inline-flex');
            delete boton.dataset.outlookUrl;
        });
    }


    function actualizarIndicador(tipo, texto) {
        if (!smtpEstado) return;

        const estilos = {
            success: ['text-green-700', 'bg-green-500'],
            warning: ['text-amber-700', 'bg-amber-500'],
            error: ['text-red-700', 'bg-red-500'],
        };

        const estilo = estilos[tipo] ?? estilos.error;

        smtpEstado.className = `inline-flex items-center gap-2 text-xs ${estilo[0]}`;
        smtpEstado.innerHTML = `<span class="w-2.5 h-2.5 rounded-full ${estilo[1]}"></span>${texto}`;
    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN Y MODAL
    |--------------------------------------------------------------------------
    */

    function activarCarga() {
        if (!btnEnviar) return;

        btnEnviar.disabled = true;
        btnEnviar.innerHTML = `
            <span class="spinner-envio"></span>
            <span>Enviando...</span>
        `;
    }


    function restaurarBoton() {
        if (!btnEnviar) return;

        btnEnviar.disabled = false;
        btnEnviar.innerHTML = `
            <i id="btnEnviarIcono" data-lucide="send" class="w-4 h-4"></i>
            <span id="btnEnviarTexto">Enviar reporte</span>
        `;

        refrescarIconos();
    }


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


    cerrarModalIncidencia?.addEventListener('click', ocultarModal);

    modal?.addEventListener('click', event => {
        if (event.target === modal) ocultarModal();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') ocultarModal();
    });

    window.cerrarModal = ocultarModal;


    function refrescarIconos() {
        if (window.lucide) lucide.createIcons();
    }

    refrescarIconos();
});