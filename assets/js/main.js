// Establecer el tema desde un inicio para evitar destellos
const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
document.documentElement.setAttribute('data-bs-theme', theme);

document.addEventListener('DOMContentLoaded', () => {
    const btnToggle = document.getElementById('btnThemeToggle');
    const themeIcon = document.getElementById('themeIcon');

    if (btnToggle) {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        themeIcon.textContent = currentTheme === 'dark' ? '☀️' : '🌙';

        btnToggle.addEventListener('click', () => {
            const html = document.documentElement;
            const newTheme = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeIcon.textContent = newTheme === 'dark' ? '☀️' : '🌙';
        });
    }

    // --- PREMIUM UI/UX: SPA Transition Interceptor ---
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            const target = this.getAttribute('target');

            // Solo interceptar enlaces internos y que no abran en nueva pestaña
            if (href && !href.startsWith('#') && !href.startsWith('javascript:') && target !== '_blank') {
                e.preventDefault();
                document.body.classList.add('fade-out');
                setTimeout(() => {
                    window.location.href = href;
                }, 250); // Tiempo que coincide con la animación CSS fadeOutPage
            }
        });
    });
});

// --- PREMIUM UI/UX: Global Toast Function ---
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('globalToast');
    const toastBody = document.getElementById('toastMessage');

    if (!toastEl) return;

    // Reset classes
    toastEl.className = 'toast align-items-center text-white border-0 bg-' + type;

    let icon = type === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' :
        (type === 'danger' ? '<i class="bi bi-exclamation-triangle-fill me-2"></i>' : '<i class="bi bi-info-circle-fill me-2"></i>');

    toastBody.innerHTML = icon + message;

    // eslint-disable-next-line no-undef
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
}

// Interceptar variables $_GET en JS para lanzar Toasts
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('msg')) {
        const msgType = urlParams.get('msg');
        if (msgType === 'success' || msgType === 'creado' || msgType === 'editado' || msgType === 'eliminado') {
            showToast('Operación completada exitosamente.', 'success');
        } else if (msgType === 'error') {
            showToast('Ocurrió un error en la operación.', 'danger');
        } else if (msgType === 'duplicado') {
            showToast('El registro ya existe.', 'warning');
        }

        // Limpiar URL sin recargar
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: newUrl }, '', newUrl);
    }
});
