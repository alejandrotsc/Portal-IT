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

    const modalError              = document.getElementById('modalErrorAutorizacion');
    const textoError              = document.getElementById('textoErrorAutorizacion');
    const btnCerrarError          = document.getElementById('btnCerrarErrorAutorizacion');


    if (!form) return;


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

        // Estado de carga
        if (btnGenerar) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = `
                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Generando...
            `;
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


            const data = await response.json();


            if (data.success && data.download) {

                // Asignar URL de descarga al link del modal
                linkDescarga.href = data.download;

                abrirModal(modalDescarga);

            } else {

                textoError.textContent = data.error ?? 'Error desconocido al generar el documento.';
                abrirModal(modalError);

            }

        } catch (err) {

            textoError.textContent = 'Error de red. Por favor intente nuevamente.';
            abrirModal(modalError);

        } finally {

            if (btnGenerar) {
                btnGenerar.disabled = false;
                btnGenerar.innerHTML = `
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Generar documento
                `;
                if (window.lucide) lucide.createIcons();
            }

        }

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