<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Escolar</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .navbar-brand { font-weight: bold; }
        .contenedor-principal {
            margin-top: 30px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        /* --- PREMIUM UI/UX: Single Page App (SPA) Transitions --- */
        body {
            animation: fadeInPage 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0; 
        }
        @keyframes fadeInPage {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        body.fade-out {
            animation: fadeOutPage 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeOutPage {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-10px); }
        }

        /* --- PREMIUM UI/UX: Glassmorphism Navbar --- */
        .glass-navbar {
            background: rgba(13, 110, 253, 0.85) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 1050;
        }
        [data-bs-theme="dark"] .glass-navbar {
            background: rgba(33, 37, 41, 0.85) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* --- PREMIUM UI/UX: Micro-interactions & Glows --- */
        .card, .btn {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4) !important;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(25, 135, 84, 0.4) !important;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4) !important;
        }
        .card:hover {
            box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
        }
        [data-bs-theme="dark"] .card:hover {
            box-shadow: 0 12px 24px rgba(0,0,0,0.4) !important;
        }

        [data-bs-theme="light"] body { background-color: #f8f9fa; }
        [data-bs-theme="light"] .contenedor-principal { background-color: white; }
        [data-bs-theme="dark"] body { background-color: #121212; }
        [data-bs-theme="dark"] .contenedor-principal { background-color: #1e1e1e; color: #fff; }
    </style>
    <script>
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
                link.addEventListener('click', function(e) {
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
    </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark glass-navbar shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="<?php echo isset($_SESSION['id_docente']) ? '../docentes/mis_alumnos.php' : '../calificaciones/dashboard.php'; ?>">🎓 Sistema Escolar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'Director'): ?>
            <li class="nav-item"><a class="nav-link" href="../calificaciones/dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Panel</a></li>
            <li class="nav-item"><a class="nav-link" href="../calificaciones/calificaciones.php"><i class="bi bi-pencil-square me-1"></i> Capturar Notas</a></li>
            <li class="nav-item"><a class="nav-link" href="../calificaciones/ver_calificaciones.php"><i class="bi bi-clock-history me-1"></i> Historial</a></li>
            <li class="nav-item"><a class="nav-link" href="../alumnos/alumnos.php"><i class="bi bi-people-fill me-1"></i> Alumnos</a></li>
            <li class="nav-item"><a class="nav-link" href="../materias/materias.php"><i class="bi bi-book-half me-1"></i> Materias</a></li>
            <li class="nav-item"><a class="nav-link text-warning fw-bold" href="../usuarios/usuarios.php"><i class="bi bi-person-badge-fill me-1"></i> Usuarios</a></li>
            
            <li class="nav-item ms-lg-3 d-flex align-items-center">
                <span class="text-light me-3">👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</span>
                <a class="btn btn-outline-light btn-sm me-2" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i class="bi bi-person-gear"></i></a>
                <button class="btn btn-outline-light btn-sm me-2" id="btnThemeToggle" title="Cambiar Tema">
                    <span id="themeIcon">🌙</span>
                </button>
                <a class="btn btn-danger btn-sm" href="../auth/cerrar_sesion.php">Salir</a>
            </li>

        <?php
elseif (isset($_SESSION['id_docente'])): ?>
            <li class="nav-item"><a class="nav-link" href="../calificaciones/calificaciones.php"><i class="bi bi-pencil-square me-1"></i> Capturar Notas</a></li>
            <li class="nav-item"><a class="nav-link" href="../docentes/mis_alumnos.php"><i class="bi bi-person-lines-fill me-1"></i> Mis Alumnos</a></li>
            <li class="nav-item"><a class="nav-link" href="../docentes/mi_calendario.php"><i class="bi bi-calendar-week me-1"></i> Calendario</a></li>
            <li class="nav-item"><a class="nav-link" href="../docentes/pasar_lista.php"><i class="bi bi-clipboard-check me-1"></i> Pasar Lista</a></li>
            <li class="nav-item"><a class="nav-link" href="../docentes/reporte_asistencia.php"><i class="bi bi-file-earmark-bar-graph me-1"></i> Reporte Asist.</a></li>

            <li class="nav-item ms-lg-3 d-flex align-items-center">
                <span class="text-light me-3">👨‍🏫 <?php echo htmlspecialchars($_SESSION['nombre_docente']); ?> (Docente)</span>
                <a class="btn btn-outline-light btn-sm me-2" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i class="bi bi-person-gear"></i></a>
                <button class="btn btn-outline-light btn-sm me-2" id="btnThemeToggle" title="Cambiar Tema">
                    <span id="themeIcon">🌙</span>
                </button>
                <a class="btn btn-danger btn-sm" href="../auth/cerrar_sesion.php">Salir</a>
            </li>
            
        <?php
else: ?>
            <li class="nav-item ms-lg-3 d-flex align-items-center">
                <button class="btn btn-outline-light btn-sm" id="btnThemeToggle" title="Cambiar Tema">
                    <span id="themeIcon">🌙</span>
                </button>
            </li>
        <?php
endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container contenedor-principal">

<!-- PREMIUM UI/UX: Global Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
  <div id="globalToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body fw-bold" id="toastMessage"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script>
    // --- PREMIUM UI/UX: Global Toast Function ---
    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('globalToast');
        const toastBody = document.getElementById('toastMessage');
        
        // Reset classes
        toastEl.className = 'toast align-items-center text-white border-0 bg-' + type;
        
        let icon = type === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' : 
                  (type === 'danger' ? '<i class="bi bi-exclamation-triangle-fill me-2"></i>' : '<i class="bi bi-info-circle-fill me-2"></i>');
                  
        toastBody.innerHTML = icon + message;
        
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
            window.history.replaceState({path:newUrl}, '', newUrl);
        }
    });
</script>