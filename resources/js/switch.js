function initializeThemeToggle() {
    const button = document.getElementById('theme-toggle');
    const track = document.getElementById('theme-switch-track');
    const thumb = document.getElementById('theme-switch-thumb');
    const sunIcon = document.getElementById('theme-sun-icon');
    const moonIcon = document.getElementById('theme-moon-icon');

    if (!button || !track || !thumb || !sunIcon || !moonIcon) {
        return;
    }

    function applyTheme(isDark) {
        document.documentElement.classList.toggle('dark', isDark);

        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute(
            'aria-label',
            isDark
                ? 'Desactivar modo oscuro'
                : 'Activar modo oscuro'
        );

        track.style.backgroundColor = isDark
            ? '#2563eb'
            : '#cbd5e1';

        thumb.style.transform = isDark
            ? 'translateX(20px)'
            : 'translateX(0)';

        sunIcon.style.display = isDark
            ? 'none'
            : 'inline-flex';

        moonIcon.style.display = isDark
            ? 'inline-flex'
            : 'none';
    }

    const savedTheme = localStorage.getItem('theme');
    let isDark = savedTheme === 'dark';

    applyTheme(isDark);

    button.addEventListener('click', () => {
        isDark = !isDark;

        localStorage.setItem(
            'theme',
            isDark ? 'dark' : 'light'
        );

        applyTheme(isDark);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeThemeToggle
    );
} else {
    initializeThemeToggle();
}