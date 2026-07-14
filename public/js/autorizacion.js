document.addEventListener('DOMContentLoaded', () => {

    const tabla = document.getElementById('equipoFilas');
    const botonAgregar = document.getElementById('agregarFila');
    const template = document.getElementById('templateEquipo');


    if (!tabla || !botonAgregar || !template) {
        console.error('No se encontraron elementos del formulario de autorización');
        return;
    }


    let contador = tabla.querySelectorAll('.fila-equipo').length;



    /*
    |--------------------------------------------------------------------------
    | AGREGAR EQUIPO
    |--------------------------------------------------------------------------
    */

    botonAgregar.addEventListener('click', () => {


        let contenido = template.innerHTML;


        contenido = contenido.replaceAll(
            'INDEX',
            contador
        );


        tabla.insertAdjacentHTML(
            'beforeend',
            contenido
        );


        contador++;


        // Renderizar iconos lucide nuevos
        if (window.lucide) {
            lucide.createIcons();
        }


    });





    /*
    |--------------------------------------------------------------------------
    | ELIMINAR EQUIPO
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', (e) => {


        const boton = e.target.closest('.btn-remove-fila');


        if (!boton) {
            return;
        }


        const fila = boton.closest('.fila-equipo');


        if (!fila) {
            return;
        }



        const filasActuales = tabla.querySelectorAll('.fila-equipo');


        // Evitar borrar la última fila
        if (filasActuales.length <= 1) {

            return;

        }



        fila.remove();



    });





    /*
    |--------------------------------------------------------------------------
    | TOOLTIP SERIE
    |--------------------------------------------------------------------------
    */

    const ayudaSerie = document.querySelector('[data-lucide="circle-help"]');


    if (ayudaSerie) {


        ayudaSerie.addEventListener('mouseenter', () => {

            ayudaSerie.setAttribute(
                'title',
                'Número de serie: revisa la etiqueta del equipo, generalmente ubicada en la parte inferior de la laptop o en la parte trasera del monitor.'
            );

        });


    }


});