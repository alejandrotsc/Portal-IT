/*
|--------------------------------------------------------------------------
| Notificaciones en tiempo real
|--------------------------------------------------------------------------
|
| Gestiona la recepción de notificaciones mediante Laravel Echo y mantiene
| sincronizados los principales indicadores visuales del Portal TI.
|
| Actualiza:
| - Contador de la campana.
| - Resumen de notificaciones sin leer.
| - Lista desplegable.
| - Título de la pestaña.
| - Favicon con indicador numérico.
|
*/


/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
|
| Prepara el módulo, valida la existencia del widget y del usuario autenticado y establece el estado inicial de las notificaciones.
|
*/

/*
|--------------------------------------------------------------------------
| Inicializar módulo de notificaciones
|--------------------------------------------------------------------------
|
| Valida los elementos requeridos, carga el estado inicial y configura la
| suscripción privada del usuario cuando Echo está disponible.
|
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


    /*
    |--------------------------------------------------------------------------
    | Elementos de la interfaz
    |--------------------------------------------------------------------------
    |
    | Obtiene las referencias utilizadas para actualizar visualmente contador, resumen, listado, estado vacío, acciones y campana.
    |
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
    |
    | Lee desde el dataset la cantidad de notificaciones no leídas entregada inicialmente por Laravel.
    |
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
    |
    | Conserva el título base de la pestaña para poder añadir o retirar el contador de notificaciones sin duplicarlo.
    |
    */

    const tituloOriginal =
        document.title.replace(
            /^\(\d+\+?\)\s*/,
            ''
        );


    actualizarInterfaz();




    /*
    |--------------------------------------------------------------------------
    | Disponibilidad del servicio en tiempo real
    |--------------------------------------------------------------------------
    |
    | La interfaz se inicializa siempre con los datos entregados por Laravel.
    | Si Echo no está disponible, únicamente se omite la suscripción en vivo.
    |
    */

    if (!window.Echo) {

        console.warn(
            '[Notificaciones] Tiempo real no disponible. Se mantienen los datos cargados desde el servidor.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Canal privado
    |--------------------------------------------------------------------------
    |
    | Construye y abre el canal privado correspondiente al usuario autenticado para recibir notificaciones en tiempo real.
    |
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


    let canal;


    try {

        canal =
            window.Echo.private(
                nombreCanal
            );

    } catch (error) {

        console.warn(
            '[Notificaciones] No fue posible suscribirse al canal privado:',
            error
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Canal autorizado
    |--------------------------------------------------------------------------
    |
    | Confirma que la suscripción al canal privado fue aceptada correctamente por el backend.
    |
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
    |
    | Registra errores producidos durante la autenticación o suscripción al canal privado del usuario.
    |
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
    |
    | Procesa cada notificación entrante, incrementa el contador, la agrega al dropdown y actualiza los indicadores visuales.
    |
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
            animarContador();
            animarCampana();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Actualizar interfaz
    |--------------------------------------------------------------------------
    |
    | Sincroniza contador, resumen, acción de marcar todas, título de pestaña y favicon con la cantidad actual de pendientes.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Sincronizar interfaz
    |--------------------------------------------------------------------------
    |
    | Actualiza todos los indicadores visibles a partir de la cantidad actual
    | de notificaciones pendientes.
    |
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
    |
    | Combina propiedades directas y datos internos para producir una estructura consistente de notificación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Normalizar notificación
    |--------------------------------------------------------------------------
    |
    | Combina diferentes estructuras posibles y aplica valores por defecto para
    | garantizar una forma uniforme antes de renderizarla.
    |
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
    |
    | Construye dinámicamente el elemento visual de una nueva notificación y lo inserta al inicio del listado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Insertar notificación
    |--------------------------------------------------------------------------
    |
    | Construye dinámicamente el elemento del dropdown, lo inserta al inicio y
    | aplica animación, límite visual e inicialización de iconos.
    |
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
        |
        | Crea el bloque visual destinado al icono representativo de la notificación.
        |
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
        |
        | Construye título, mensaje, indicador de estado y fecha mostrados dentro de cada elemento del dropdown.
        |
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


        animarNuevaNotificacion(
            enlace
        );


        limitarNotificaciones();
        recrearIconos(
            icono
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener URL para abrir y marcar como leída
    |--------------------------------------------------------------------------
    |
    | Genera la URL de apertura utilizando la plantilla del backend cuando existe un identificador de notificación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Resolver URL de notificación
    |--------------------------------------------------------------------------
    |
    | Utiliza la plantilla de apertura configurada por Laravel cuando existe un
    | identificador válido y recurre a la URL original como alternativa.
    |
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
    |
    | Limita el dropdown a las cinco notificaciones más recientes para conservar una interfaz compacta.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Limitar listado visible
    |--------------------------------------------------------------------------
    |
    | Conserva únicamente las cinco notificaciones más recientes dentro del
    | dropdown.
    |
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
    |
    | Vuelve a procesar los iconos creados dinámicamente después de insertar una nueva notificación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Renderizar iconos dinámicos
    |--------------------------------------------------------------------------
    |
    | Solicita a Lucide reprocesar el contenido después de insertar un icono
    | nuevo en el DOM.
    |
    */

    function recrearIconos(
        iconoPendiente
    ) {

        if (
            !iconoPendiente
            ||
            !window.lucide
            ||
            typeof window.lucide.createIcons
                !==
                'function'
        ) {
            return;
        }


        window.lucide.createIcons();
    }


    /*
    |--------------------------------------------------------------------------
    | Animación del contador
    |--------------------------------------------------------------------------
    |
    | Aplica una animación breve al badge cuando aumenta la cantidad de notificaciones no leídas.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Animar contador
    |--------------------------------------------------------------------------
    |
    | Reinicia y ejecuta la animación del badge para resaltar un incremento en
    | las notificaciones no leídas.
    |
    */

    function animarContador() {

        if (
            !contador
            ||
            cantidadNoLeidas <= 0
        ) {
            return;
        }


        contador
            .getAnimations()
            .forEach(
                animacion => {
                    animacion.cancel();
                }
            );


        contador.animate(
            [
                {
                    transform: 'scale(0.55)',
                    opacity: 0.25,
                },
                {
                    transform: 'scale(1.4)',
                    opacity: 1,
                    offset: 0.45,
                },
                {
                    transform: 'scale(0.9)',
                    opacity: 1,
                    offset: 0.72,
                },
                {
                    transform: 'scale(1.08)',
                    opacity: 1,
                    offset: 0.88,
                },
                {
                    transform: 'scale(1)',
                    opacity: 1,
                },
            ],
            {
                duration: 650,
                easing: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Animación de la campana
    |--------------------------------------------------------------------------
    |
    | Simula un balanceo real de campana sin usar desvanecidos.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Animar campana
    |--------------------------------------------------------------------------
    |
    | Aplica un movimiento de balanceo para destacar visualmente la llegada de
    | una nueva notificación.
    |
    */

    function animarCampana() {

        if (!botonCampana) {
            return;
        }


        botonCampana
            .getAnimations()
            .forEach(
                animacion => {
                    animacion.cancel();
                }
            );


        botonCampana.animate(
            [
                { transform: 'rotate(0deg) scale(1)' },
                { transform: 'rotate(-18deg) scale(1.08)', offset: 0.16 },
                { transform: 'rotate(16deg) scale(1.08)', offset: 0.32 },
                { transform: 'rotate(-12deg) scale(1.06)', offset: 0.48 },
                { transform: 'rotate(9deg) scale(1.04)', offset: 0.64 },
                { transform: 'rotate(-5deg) scale(1.02)', offset: 0.78 },
                { transform: 'rotate(3deg) scale(1.01)', offset: 0.9 },
                { transform: 'rotate(0deg) scale(1)' },
            ],
            {
                duration: 850,
                easing: 'ease-in-out',
                transformOrigin: '50% 15%',
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Animación de una nueva notificación
    |--------------------------------------------------------------------------
    |
    | Anima la entrada del elemento recién agregado al listado desplegable.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Animar elemento nuevo
    |--------------------------------------------------------------------------
    |
    | Aplica una transición de entrada al elemento recién insertado dentro del
    | dropdown.
    |
    */

    function animarNuevaNotificacion(
        elemento
    ) {

        if (!elemento) {
            return;
        }


        elemento.animate(
            [
                {
                    opacity: 0,
                    transform: 'translateY(-12px) scale(0.98)',
                },
                {
                    opacity: 1,
                    transform: 'translateY(3px) scale(1.01)',
                    offset: 0.72,
                },
                {
                    opacity: 1,
                    transform: 'translateY(0) scale(1)',
                },
            ],
            {
                duration: 460,
                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            }
        );
    }


    /*
|--------------------------------------------------------------------------
| Favicon con indicador de notificación
|--------------------------------------------------------------------------
|
| Genera dinámicamente un favicon con badge numérico cuando existen notificaciones pendientes.
|
*/

/*
|--------------------------------------------------------------------------
| Actualizar favicon
|--------------------------------------------------------------------------
|
| Recrea el favicon con un badge numérico cuando existen notificaciones y
| restaura el original cuando el contador vuelve a cero.
|
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
    |
    | Recupera el favicon original cuando ya no existen notificaciones sin leer.
    |
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
|
| Calcula y dibuja la burbuja roja que sobresale del borde superior derecho del favicon.
|
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
        |
        | Añade un contorno blanco alrededor del indicador para mejorar su contraste.
        |
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
        |
        | Dibuja dentro del badge la cantidad de notificaciones o el límite visual 9+.
        |
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
    |
    | Consulta el estado actual de la conexión utilizada por Echo y Pusher para fines de diagnóstico.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Obtener estado de conexión
    |--------------------------------------------------------------------------
    |
    | Devuelve el estado actual de la conexión administrada por Echo/Pusher para
    | fines de diagnóstico.
    |
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
|
| Inicializa el módulo inmediatamente si el DOM ya está listo o espera a DOMContentLoaded cuando todavía está cargando.
|
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