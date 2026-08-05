/*
|--------------------------------------------------------------------------
| Alpine.js
|--------------------------------------------------------------------------
|
| Alpine se carga localmente mediante Vite, por lo que los componentes
| interactivos no dependen de internet.
|
*/

import Alpine from 'alpinejs';


/*
|--------------------------------------------------------------------------
| Lucide Icons
|--------------------------------------------------------------------------
*/

import {
    createIcons,
    icons,
} from 'lucide';


/*
|--------------------------------------------------------------------------
| Módulos principales
|--------------------------------------------------------------------------
*/

import './switch';


/*
|--------------------------------------------------------------------------
| Inicialización de iconos Lucide
|--------------------------------------------------------------------------
*/

function iniciarLucide() {
    try {
        createIcons({
            icons,

            attrs: {
                'aria-hidden': 'true',
            },
        });
    } catch (error) {
        console.warn(
            '[Lucide] No fue posible renderizar los iconos.',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Compatibilidad global
|--------------------------------------------------------------------------
|
| Algunos componentes usan:
|
| window.lucide.createIcons()
|
| Se conserva esa llamada para no modificar los Blade existentes.
|
*/

window.lucide = {
    createIcons: iniciarLucide,
};

window.Alpine = Alpine;


/*
|--------------------------------------------------------------------------
| Inicialización independiente de servicios en tiempo real
|--------------------------------------------------------------------------
|
| La interfaz principal no depende de Echo/Reverb. La conexión se intenta
| cuando el DOM ya está disponible y cualquier error queda aislado.
|
*/

async function iniciarTiempoReal() {
    try {
        await import('./echo');

        if (! window.Echo) {
            console.warn(
                '[Echo] El servicio en tiempo real no está disponible.'
            );

            return;
        }

        await import('./notificaciones');
    } catch (error) {
        console.warn(
            '[Tiempo real] No fue posible iniciar las notificaciones.',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Inicialización general
|--------------------------------------------------------------------------
*/

function iniciarInterfaz() {
    /*
    |--------------------------------------------------------------------------
    | Alpine
    |--------------------------------------------------------------------------
    |
    | Alpine debe iniciarse una sola vez.
    |
    */

    if (! window.__alpineIniciado) {
        Alpine.start();

        window.__alpineIniciado = true;
    }


    /*
    |--------------------------------------------------------------------------
    | Lucide
    |--------------------------------------------------------------------------
    */

    iniciarLucide();


    /*
    |--------------------------------------------------------------------------
    | Tiempo real
    |--------------------------------------------------------------------------
    */

    void iniciarTiempoReal();
}


/*
|--------------------------------------------------------------------------
| Inicio de la interfaz
|--------------------------------------------------------------------------
*/

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        iniciarInterfaz,
        {
            once: true,
        }
    );
} else {
    iniciarInterfaz();
}


/*
|--------------------------------------------------------------------------
| Actualizar iconos dinámicos
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'lucide:refresh',
    iniciarLucide
);

document.addEventListener(
    'livewire:navigated',
    iniciarLucide
);