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

    // 3D Tilt Effect for Login Container
    const loginContainer = document.querySelector('.login-container');
    if (loginContainer) {
        document.addEventListener('mousemove', (e) => {
            const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
            const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
            loginContainer.style.transform = `perspective(1000px) rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });

        // Reset transform when mouse leaves window
        document.addEventListener('mouseleave', () => {
            loginContainer.style.transform = `perspective(1000px) rotateY(0deg) rotateX(0deg)`;
            loginContainer.style.transition = 'transform 0.5s ease';
        });

        document.addEventListener('mouseenter', () => {
            loginContainer.style.transition = 'transform 0.1s ease-out, box-shadow 0.1s ease-out';
        });
    }
});
