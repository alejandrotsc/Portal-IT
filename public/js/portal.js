function portalApp() {
    return {
        profileOpen: false,

        init() {
            this.renderIcons();
        },

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