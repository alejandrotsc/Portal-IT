/*
|--------------------------------------------------------------------------
| Laravel Echo y Reverb
|--------------------------------------------------------------------------
|
| Configura la conexión en tiempo real del Portal TI mediante Laravel Echo,
| Reverb y Pusher, incluyendo autenticación, control de tiempo de espera y
| eventos de conexión.
|
*/

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;


/*
|--------------------------------------------------------------------------
| Configuración general
|--------------------------------------------------------------------------
|
| Define el tiempo máximo de espera y obtiene los valores necesarios para
| establecer la conexión con Reverb desde las variables de entorno.
|
*/

const REVERB_TIMEOUT_MS = 8000;

const csrfToken =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content')
    ?? '';

const scheme =
    import.meta.env.VITE_REVERB_SCHEME
    ?? (
        window.location.protocol === 'https:'
            ? 'https'
            : 'http'
    );

const host =
    import.meta.env.VITE_REVERB_HOST
    ?? window.location.hostname;

const port = Number(
    import.meta.env.VITE_REVERB_PORT
    ?? (
        scheme === 'https'
            ? 443
            : 8080
    )
);

const appKey =
    import.meta.env.VITE_REVERB_APP_KEY;


/*
|--------------------------------------------------------------------------
| Validación de configuración
|--------------------------------------------------------------------------
|
| Verifica que exista la clave de aplicación requerida antes de intentar
| iniciar la conexión en tiempo real.
|
*/

if (!appKey) {
    console.warn(
        '[Reverb] No se configuró VITE_REVERB_APP_KEY.'
    );
} else {
    iniciarEcho();
}


/*
|--------------------------------------------------------------------------
| Inicializar Echo
|--------------------------------------------------------------------------
|
| Crea la instancia de Laravel Echo, configura el transporte WebSocket,
| autentica canales privados y registra los eventos principales de conexión.
|
*/

function iniciarEcho() {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',

            key: appKey,

            wsHost: host,
            wsPort: port,
            wssPort: port,

            forceTLS:
                scheme === 'https',

            enabledTransports: [
                'ws',
                'wss',
            ],

            authEndpoint:
                '/broadcasting/auth',

            auth: {
                headers: {
                    'X-CSRF-TOKEN':
                        csrfToken,

                    'X-Requested-With':
                        'XMLHttpRequest',
                },
            },
        });

        const connection =
            window.Echo
                ?.connector
                ?.pusher
                ?.connection;

        if (!connection) {
            throw new Error(
                'No se pudo obtener la conexión de Pusher.'
            );
        }

        let conectado = false;


        /*
        |--------------------------------------------------------------------------
        | Límite de espera
        |--------------------------------------------------------------------------
        |
        | Desactiva temporalmente la conexión si Reverb no responde dentro del
        | tiempo máximo definido, evitando que el portal permanezca esperando.
        |
        */

        const timeoutId = window.setTimeout(
            () => {
                if (conectado) {
                    return;
                }

                console.warn(
                    `[Reverb] No respondió en ${
                        REVERB_TIMEOUT_MS / 1000
                    } segundos. Se desactivaron temporalmente las notificaciones en tiempo real.`
                );

                desconectarEcho();
            },
            REVERB_TIMEOUT_MS
        );


        /*
        |--------------------------------------------------------------------------
        | Eventos de conexión
        |--------------------------------------------------------------------------
        |
        | Escucha los cambios de estado de la conexión y los expone mediante
        | eventos personalizados para que otros módulos puedan reaccionar.
        |
        */

        connection.bind(
            'connected',
            () => {
                conectado = true;

                window.clearTimeout(
                    timeoutId
                );

                console.info(
                    '[Reverb] Conectado correctamente.'
                );

                window.dispatchEvent(
                    new CustomEvent(
                        'echo:connected'
                    )
                );
            }
        );

        connection.bind(
            'error',
            error => {
                console.warn(
                    '[Reverb] Error de conexión:',
                    error
                );

                window.dispatchEvent(
                    new CustomEvent(
                        'echo:error',
                        {
                            detail: error,
                        }
                    )
                );
            }
        );

        connection.bind(
            'disconnected',
            () => {
                conectado = false;

                console.warn(
                    '[Reverb] Conexión cerrada.'
                );

                window.dispatchEvent(
                    new CustomEvent(
                        'echo:disconnected'
                    )
                );
            }
        );

    } catch (error) {
        console.warn(
            '[Reverb] No fue posible inicializar Echo:',
            error
        );

        desconectarEcho();
    }
}


/*
|--------------------------------------------------------------------------
| Desconexión segura
|--------------------------------------------------------------------------
|
| Cierra la instancia de Echo sin propagar errores y limpia la referencia
| global para evitar reutilizar una conexión inválida.
|
*/

function desconectarEcho() {
    try {
        window.Echo?.disconnect();
    } catch (error) {
        console.warn(
            '[Reverb] No fue posible cerrar la conexión:',
            error
        );
    } finally {
        window.Echo = null;
    }
}
