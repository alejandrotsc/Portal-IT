/*
|--------------------------------------------------------------------------
| Alternancia de tema
|--------------------------------------------------------------------------
|
| Inicializa y controla el switch de tema del Portal TI, sincronizando la
| clase dark, el estado accesible del botón, los estilos visuales y la
| preferencia almacenada localmente.
|
*/

function initializeThemeToggle() {
    /*
    |--------------------------------------------------------------------------
    | Elementos del switch
    |--------------------------------------------------------------------------
    |
    | Obtiene las referencias necesarias para controlar el botón, la pista,
    | el indicador móvil y los iconos de sol y luna.
    |
    */

    const button =
        document.getElementById(
            'theme-toggle'
        );

    const track =
        document.getElementById(
            'theme-switch-track'
        );

    const thumb =
        document.getElementById(
            'theme-switch-thumb'
        );

    const sunIcon =
        document.getElementById(
            'theme-sun-icon'
        );

    const moonIcon =
        document.getElementById(
            'theme-moon-icon'
        );


    /*
    |--------------------------------------------------------------------------
    | Validar elementos requeridos
    |--------------------------------------------------------------------------
    |
    | Evita ejecutar la lógica cuando alguno de los componentes necesarios
    | del switch no se encuentra disponible en la vista actual.
    |
    */

    if (
        !button
        ||
        !track
        ||
        !thumb
        ||
        !sunIcon
        ||
        !moonIcon
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Evitar inicialización duplicada
    |--------------------------------------------------------------------------
    |
    | Impide registrar múltiples veces el mismo evento cuando el archivo se
    | carga o inicializa más de una vez.
    |
    */

    if (
        button.dataset.themeInitialized
        ===
        'true'
    ) {
        return;
    }

    button.dataset.themeInitialized =
        'true';


    /*
    |--------------------------------------------------------------------------
    | Aplicar tema
    |--------------------------------------------------------------------------
    |
    | Sincroniza la clase dark del documento, atributos accesibles, posición
    | del switch, color de la pista e icono correspondiente al tema activo.
    |
    */

    function applyTheme(
        isDark
    ) {
        document.documentElement
            .classList
            .toggle(
                'dark',
                isDark
            );

        button.setAttribute(
            'aria-pressed',
            String(
                isDark
            )
        );

        button.setAttribute(
            'aria-label',
            isDark
                ? 'Desactivar modo oscuro'
                : 'Activar modo oscuro'
        );

        track.style.backgroundColor =
            isDark
                ? '#2563eb'
                : '#cbd5e1';

        thumb.style.transform =
            isDark
                ? 'translateX(20px)'
                : 'translateX(0)';

        sunIcon.style.display =
            isDark
                ? 'none'
                : 'inline-flex';

        moonIcon.style.display =
            isDark
                ? 'inline-flex'
                : 'none';
    }


    /*
    |--------------------------------------------------------------------------
    | Estado inicial
    |--------------------------------------------------------------------------
    |
    | El layout ya aplica el tema guardado antes de pintar la página. Esta
    | clase se utiliza como fuente inicial para evitar inconsistencias o
    | cambios visuales durante la carga.
    |
    */

    let isDark =
        document.documentElement
            .classList
            .contains(
                'dark'
            );

    applyTheme(
        isDark
    );


    /*
    |--------------------------------------------------------------------------
    | Alternar tema
    |--------------------------------------------------------------------------
    |
    | Cambia entre modo claro y oscuro, actualiza la interfaz y persiste la
    | preferencia del usuario en localStorage cuando el navegador lo permite.
    |
    */

    button.addEventListener(
        'click',
        () => {
            isDark =
                !isDark;

            applyTheme(
                isDark
            );

            try {
                localStorage.setItem(
                    'theme',
                    isDark
                        ? 'dark'
                        : 'light'
                );
            } catch (error) {
                console.warn(
                    '[Tema] No fue posible guardar la preferencia.',
                    error
                );
            }
        }
    );
}


/*
|--------------------------------------------------------------------------
| Inicialización independiente
|--------------------------------------------------------------------------
|
| Inicializa el switch inmediatamente cuando el DOM ya está disponible o
| espera únicamente a DOMContentLoaded cuando la página continúa cargando.
|
*/

if (
    document.readyState === 'loading'
) {
    document.addEventListener(
        'DOMContentLoaded',
        initializeThemeToggle,
        {
            once: true,
        }
    );
} else {
    initializeThemeToggle();
}