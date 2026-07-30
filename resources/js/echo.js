import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const csrfToken =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

console.log('[Reverb] Configuración efectiva:', {
    pageProtocol: window.location.protocol,
    scheme: import.meta.env.VITE_REVERB_SCHEME,
    host: import.meta.env.VITE_REVERB_HOST,
    port: import.meta.env.VITE_REVERB_PORT,
    forceTLS:
        (
            import.meta.env.VITE_REVERB_SCHEME
            ?? 'http'
        ) === 'https',
});

window.Echo = new Echo({
    broadcaster: 'reverb',

    key:
        import.meta.env.VITE_REVERB_APP_KEY,

    wsHost:
        import.meta.env.VITE_REVERB_HOST
        ?? window.location.hostname,

    wsPort:
        Number(
            import.meta.env.VITE_REVERB_PORT
            ?? 8080
        ),

    wssPort:
        Number(
            import.meta.env.VITE_REVERB_PORT
            ?? 443
        ),

    forceTLS:
        (
            import.meta.env.VITE_REVERB_SCHEME
            ?? 'http'
        ) === 'https',

    enabledTransports: [
        'ws',
        'wss',
    ],

    authEndpoint:
        '/broadcasting/auth',

    auth: {
        headers: {
            'X-CSRF-TOKEN':
                csrfToken ?? '',

            'X-Requested-With':
                'XMLHttpRequest',
        },
    },
});

window.Echo.connector.pusher.connection.bind(
    'connected',
    () => {
        console.log(
            'Reverb conectado correctamente.'
        );
    }
);

window.Echo.connector.pusher.connection.bind(
    'error',
    error => {
        console.error(
            'Error de conexión con Reverb:',
            error
        );
    }
);