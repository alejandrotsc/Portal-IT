import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

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