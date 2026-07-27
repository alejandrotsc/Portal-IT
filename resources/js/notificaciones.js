/*
|--------------------------------------------------------------------------
| Notificaciones en tiempo real
|--------------------------------------------------------------------------
|
| Escucha el canal privado del usuario mediante Laravel Echo y actualiza:
|
| - Contador de la campana.
| - Resumen de notificaciones sin leer.
| - Lista desplegable.
| - Título de la pestaña.
|
*/


/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
*/

function iniciarNotificaciones() {

    let faviconOriginal = null;

    const widget =
        document.getElementById(
            'notificaciones-widget'
        );


    if (!widget) {

        console.warn(
            '[Notificaciones] No se encontró el widget.'
        );

        return;
    }


    const usuarioId =
        widget.dataset.usuarioId;


    if (!usuarioId) {

        console.warn(
            '[Notificaciones] No se encontró el ID del usuario.'
        );

        return;
    }


    if (!window.Echo) {

        console.error(
            '[Notificaciones] Laravel Echo no está disponible.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Elementos de la interfaz
    |--------------------------------------------------------------------------
    */

    const contador =
        document.getElementById(
            'notificaciones-contador'
        );


    const resumen =
        document.getElementById(
            'notificaciones-resumen'
        );


    const lista =
        document.getElementById(
            'notificaciones-lista'
        );


    const estadoVacio =
        document.getElementById(
            'notificaciones-vacio'
        );


    const formularioMarcarTodas =
        document.getElementById(
            'notificaciones-marcar-todas'
        );


    const botonCampana =
        document.getElementById(
            'notificaciones-boton'
        );


    /*
    |--------------------------------------------------------------------------
    | Cantidad inicial
    |--------------------------------------------------------------------------
    */

    let cantidadNoLeidas =
        Number.parseInt(
            widget.dataset.noLeidas ?? '0',
            10
        );


    if (
        Number.isNaN(
            cantidadNoLeidas
        )
    ) {
        cantidadNoLeidas = 0;
    }


    /*
    |--------------------------------------------------------------------------
    | Título original
    |--------------------------------------------------------------------------
    */

    const tituloOriginal =
        document.title.replace(
            /^\(\d+\+?\)\s*/,
            ''
        );


    actualizarInterfaz();




    /*
    |--------------------------------------------------------------------------
    | Canal privado
    |--------------------------------------------------------------------------
    */

    const nombreCanal =
        `usuarios.${usuarioId}`;


    console.log(
        '[Notificaciones] Intentando escuchar:',
        nombreCanal
    );


    console.log(
        '[Notificaciones] Estado de Reverb:',
        obtenerEstadoConexion()
    );


    const canal =
        window.Echo.private(
            nombreCanal
        );


    /*
    |--------------------------------------------------------------------------
    | Canal autorizado
    |--------------------------------------------------------------------------
    */

    canal.subscribed(() => {

        console.log(
            '[Notificaciones] Canal autorizado:',
            nombreCanal
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Error de autorización
    |--------------------------------------------------------------------------
    */

    canal.error(error => {

        console.error(
            '[Notificaciones] Error en el canal privado:',
            error
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Recibir notificación
    |--------------------------------------------------------------------------
    */

    canal.notification(
        notificacionRecibida => {

            console.log(
                '[Notificaciones] Notificación recibida:',
                notificacionRecibida
            );


            /*
            |--------------------------------------------------------------------------
            | Normalizar datos
            |--------------------------------------------------------------------------
            |
            | Algunas versiones pueden entregar los datos directamente y otras
            | pueden incluirlos dentro de la propiedad "data".
            |
            */

            const notificacion =
                normalizarNotificacion(
                    notificacionRecibida
                );


            cantidadNoLeidas++;


            widget.dataset.noLeidas =
                String(
                    cantidadNoLeidas
                );


            agregarNotificacion(
                notificacion
            );


            actualizarInterfaz();
            animarCampana();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Actualizar interfaz
    |--------------------------------------------------------------------------
    */

    function actualizarInterfaz() {

        if (contador) {

            contador.textContent =
                cantidadNoLeidas > 9
                    ? '9+'
                    : String(
                        cantidadNoLeidas
                    );


            contador.classList.toggle(
                'hidden',
                cantidadNoLeidas === 0
            );
        }


        if (resumen) {

            resumen.textContent =
                cantidadNoLeidas === 1
                    ? '1 notificación sin leer'
                    : `${cantidadNoLeidas} notificaciones sin leer`;
        }


        if (formularioMarcarTodas) {

            formularioMarcarTodas
                .classList
                .toggle(
                    'hidden',
                    cantidadNoLeidas === 0
                );
        }


        document.title =
    cantidadNoLeidas > 0
        ? `(${cantidadNoLeidas}) ${tituloOriginal}`
        : tituloOriginal;


actualizarFavicon(
    cantidadNoLeidas
);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar la notificación recibida
    |--------------------------------------------------------------------------
    */

    function normalizarNotificacion(
        notificacion
    ) {

        const datosInternos =
            notificacion?.data
            &&
            typeof notificacion.data === 'object'
                ? notificacion.data
                : {};


        return {
            ...datosInternos,
            ...notificacion,

            id:
                notificacion?.id
                ?? datosInternos?.id
                ?? null,

            titulo:
                notificacion?.titulo
                ?? datosInternos?.titulo
                ?? 'Nueva notificación',

            mensaje:
                notificacion?.mensaje
                ?? datosInternos?.mensaje
                ?? 'Tienes una nueva actualización.',

            icono:
                notificacion?.icono
                ?? datosInternos?.icono
                ?? 'bell',

            url:
                notificacion?.url
                ?? datosInternos?.url
                ?? '#',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Agregar notificación al dropdown
    |--------------------------------------------------------------------------
    */

    function agregarNotificacion(
        notificacion
    ) {

        if (!lista) {

            console.warn(
                '[Notificaciones] No se encontró la lista.'
            );

            return;
        }


        estadoVacio
            ?.classList
            .add(
                'hidden'
            );


        const enlace =
            document.createElement(
                'a'
            );


        enlace.href =
            obtenerUrl(
                notificacion
            );


        enlace.dataset.notificacionItem =
            'true';


        enlace.className = [
            'flex',
            'items-start',
            'gap-3',
            'px-4',
            'py-3',
            'border-b',
            'border-border',
            'last:border-b-0',
            'transition-colors',
            'duration-200',
            'hover:bg-muted/60',
            'bg-primary/[0.035]',
        ].join(' ');


        /*
        |--------------------------------------------------------------------------
        | Contenedor del icono
        |--------------------------------------------------------------------------
        */

        const contenedorIcono =
            document.createElement(
                'div'
            );


        contenedorIcono.className = [
            'mt-0.5',
            'flex',
            'items-center',
            'justify-center',
            'w-9',
            'h-9',
            'shrink-0',
            'rounded-lg',
            'bg-primary/10',
            'text-primary',
        ].join(' ');


        const icono =
            document.createElement(
                'i'
            );


        icono.setAttribute(
            'data-lucide',
            notificacion.icono
                ?? 'bell'
        );


        icono.setAttribute(
            'stroke-width',
            '1.8'
        );


        icono.className =
            'w-[17px] h-[17px]';


        contenedorIcono.appendChild(
            icono
        );


        /*
        |--------------------------------------------------------------------------
        | Contenido
        |--------------------------------------------------------------------------
        */

        const contenido =
            document.createElement(
                'div'
            );


        contenido.className =
            'min-w-0 flex-1';


        const encabezado =
            document.createElement(
                'div'
            );


        encabezado.className =
            'flex items-start gap-2';


        const titulo =
            document.createElement(
                'p'
            );


        titulo.className =
            'min-w-0 flex-1 text-sm font-medium text-foreground leading-snug';


        titulo.textContent =
            notificacion.titulo
            ?? 'Nueva notificación';


        const indicador =
            document.createElement(
                'span'
            );


        indicador.className =
            'mt-1.5 w-2 h-2 shrink-0 rounded-full bg-primary';


        encabezado.append(
            titulo,
            indicador
        );


        const mensaje =
            document.createElement(
                'p'
            );


        mensaje.className =
            'mt-1 text-xs leading-relaxed text-muted-foreground';


        mensaje.textContent =
            notificacion.mensaje
            ?? 'Tienes una nueva actualización.';


        const fecha =
            document.createElement(
                'p'
            );


        fecha.className =
            'mt-1.5 text-[11px] text-muted-foreground';


        fecha.textContent =
            'Ahora';


        contenido.append(
            encabezado,
            mensaje,
            fecha
        );


        enlace.append(
            contenedorIcono,
            contenido
        );


        lista.prepend(
            enlace
        );


        limitarNotificaciones();
        recrearIconos();
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener URL para abrir y marcar como leída
    |--------------------------------------------------------------------------
    */

    function obtenerUrl(
        notificacion
    ) {

        const plantilla =
            widget.dataset.urlAbrir;


        if (
            plantilla
            &&
            notificacion.id
        ) {
            return plantilla.replace(
                '__NOTIFICATION_ID__',
                encodeURIComponent(
                    notificacion.id
                )
            );
        }


        return notificacion.url
            ?? '#';
    }


    /*
    |--------------------------------------------------------------------------
    | Mantener solamente las últimas cinco
    |--------------------------------------------------------------------------
    */

    function limitarNotificaciones() {

        if (!lista) {
            return;
        }


        const elementos =
            lista.querySelectorAll(
                '[data-notificacion-item]'
            );


        elementos.forEach(
            (
                elemento,
                indice
            ) => {

                if (indice >= 5) {
                    elemento.remove();
                }

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Recrear iconos Lucide
    |--------------------------------------------------------------------------
    */

    function recrearIconos() {

        if (
            window.lucide
            &&
            typeof window.lucide.createIcons
                ===
                'function'
        ) {
            window.lucide.createIcons();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Animación de la campana
    |--------------------------------------------------------------------------
    */

    function animarCampana() {

        if (!botonCampana) {
            return;
        }


        botonCampana.classList.add(
            'animate-pulse'
        );


        window.setTimeout(
            () => {

                botonCampana.classList.remove(
                    'animate-pulse'
                );

            },
            1500
        );
    }


    /*
|--------------------------------------------------------------------------
| Favicon con indicador de notificación
|--------------------------------------------------------------------------
*/

function actualizarFavicon(
    cantidad
) {

    const favicon =
        document.querySelector(
            'link[rel~="icon"]'
        );


    if (!favicon) {

        console.warn(
            '[Notificaciones] No se encontró el favicon.'
        );

        return;
    }


    if (!faviconOriginal) {

        faviconOriginal =
            favicon.href;
    }


    /*
    |--------------------------------------------------------------------------
    | Restaurar favicon original
    |--------------------------------------------------------------------------
    */

    if (cantidad <= 0) {

        favicon.href =
            faviconOriginal;

        return;
    }


    const imagen =
        new Image();


    imagen.onload = () => {

        const tamaño =
            64;


        const canvas =
            document.createElement(
                'canvas'
            );


        canvas.width =
            tamaño;

        canvas.height =
            tamaño;


        const contexto =
            canvas.getContext(
                '2d'
            );


        if (!contexto) {
            return;
        }


        contexto.clearRect(
            0,
            0,
            tamaño,
            tamaño
        );


        /*
|--------------------------------------------------------------------------
| Dibujar el logo reducido
|--------------------------------------------------------------------------
|
| Se deja espacio arriba y a la derecha para que la burbuja parezca
| sobresalir del favicon.
|
*/

const margenLogo =
    7;

const tamañoLogo =
    tamaño - 14;


contexto.drawImage(
    imagen,
    0,
    margenLogo,
    tamañoLogo,
    tamañoLogo
);


        /*
        |--------------------------------------------------------------------------
        | Círculo rojo
        |--------------------------------------------------------------------------
        */

        /*
|--------------------------------------------------------------------------
| Burbuja parcialmente fuera del logo
|--------------------------------------------------------------------------
*/

const radio =
    14;

const centroX =
    tamaño - 11;

const centroY =
    11;


        contexto.beginPath();


        contexto.arc(
            centroX,
            centroY,
            radio,
            0,
            Math.PI * 2
        );


        contexto.fillStyle =
            '#dc2626';


        contexto.fill();


        /*
        |--------------------------------------------------------------------------
        | Borde blanco
        |--------------------------------------------------------------------------
        */

        contexto.lineWidth =
            4;


        contexto.strokeStyle =
            '#ffffff';


        contexto.stroke();


        /*
        |--------------------------------------------------------------------------
        | Número
        |--------------------------------------------------------------------------
        */

        contexto.fillStyle =
            '#ffffff';


        contexto.font =
            cantidad > 9
                ? 'bold 14px Arial'
                : 'bold 18px Arial';


        contexto.textAlign =
            'center';


        contexto.textBaseline =
            'middle';


        contexto.fillText(
            cantidad > 9
                ? '9+'
                : String(
                    cantidad
                ),
            centroX,
            centroY + 1
        );


        favicon.type =
            'image/png';


        favicon.href =
            canvas.toDataURL(
                'image/png'
            );
    };


    imagen.onerror = error => {

        console.error(
            '[Notificaciones] No se pudo cargar el favicon:',
            error
        );

    };


    imagen.src =
        faviconOriginal;
}

    /*
    |--------------------------------------------------------------------------
    | Estado de conexión
    |--------------------------------------------------------------------------
    */

    function obtenerEstadoConexion() {

        return window.Echo
            ?.connector
            ?.pusher
            ?.connection
            ?.state
            ?? 'desconocido';
    }

}


/*
|--------------------------------------------------------------------------
| Ejecutar cuando el documento esté preparado
|--------------------------------------------------------------------------
*/

if (
    document.readyState === 'loading'
) {

    document.addEventListener(
        'DOMContentLoaded',
        iniciarNotificaciones,
        {
            once: true,
        }
    );

} else {

    

    iniciarNotificaciones();

    

}