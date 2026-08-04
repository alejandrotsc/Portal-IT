function initializeThemeToggle() {
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
    | El layout ya aplica el tema guardado antes de pintar la página.
    | Se utiliza esa clase como fuente inicial para evitar inconsistencias.
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