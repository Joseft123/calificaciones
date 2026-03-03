const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
document.documentElement.setAttribute('data-bs-theme', theme);

document.addEventListener('DOMContentLoaded', () => {
    const btnToggle = document.getElementById('btnThemeToggle');
    const themeIcon = document.getElementById('themeIcon');

    // Determine icon color based on page body class
    let iconClass = 'text-primary';
    if (document.body.classList.contains('auth-docente')) iconClass = 'text-info';
    if (document.body.classList.contains('auth-alumno')) iconClass = 'text-success';

    const getIconHTML = (current) => {
        return current === 'dark'
            ? '<i class="bi bi-sun-fill text-warning"></i>'
            : `<i class="bi bi-moon-stars-fill ${iconClass}"></i>`;
    };

    if (btnToggle) {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        themeIcon.innerHTML = getIconHTML(currentTheme);

        btnToggle.addEventListener('click', () => {
            const html = document.documentElement;
            const newTheme = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeIcon.innerHTML = getIconHTML(newTheme);
        });
    }
});
