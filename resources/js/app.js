/*
|--------------------------------------------------------------------------
| Módulos principales
|--------------------------------------------------------------------------
*/

import './switch';

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

        if (!window.Echo) {
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

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        iniciarTiempoReal,
        { once: true }
    );
} else {
    void iniciarTiempoReal();
}