function portalApp() {
    return {
        profileOpen: false,

        /*
        |--------------------------------------------------------------------------
        | Inicializar aplicación del portal
        |--------------------------------------------------------------------------
        |
        | Ejecuta las tareas necesarias al iniciar el componente principal,
        | incluyendo la renderización de los iconos disponibles en la interfaz.
        |
        */

        init() {
            this.renderIcons();
        },

        /*
        |--------------------------------------------------------------------------
        | Renderizar iconos de Lucide
        |--------------------------------------------------------------------------
        |
        | Inicializa los iconos únicamente cuando la librería se encuentra
        | disponible y evita que un fallo externo interrumpa el funcionamiento
        | general del portal.
        |
        */

        renderIcons() {
            /*
            |--------------------------------------------------------------------------
            | Inicialización segura de Lucide
            |--------------------------------------------------------------------------
            |
            | Si Lucide está disponible, genera los iconos normalmente.
            | Si la CDN falla, portalApp continúa funcionando sin lanzar errores.
            |
            */

            if (
                !window.lucide
                ||
                typeof window.lucide.createIcons
                    !==
                    'function'
            ) {
                console.warn(
                    '[Portal] Lucide no está disponible. El portal continuará sin inicializar los iconos.'
                );

                return;
            }

            try {
                window.lucide.createIcons();
            } catch (error) {
                console.warn(
                    '[Portal] No fue posible inicializar los iconos.',
                    error
                );
            }
        },
    };
}