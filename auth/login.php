<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
include_once '../includes/csrf.php';
$csrf_token = generar_token_csrf();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema Escolar</title>
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>">
    <link rel="manifest" href="/calificaciones/manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <script src="../assets/js/auth.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/calificaciones/sw.js');
            });
        }
    </script>
</head>

<body class="auth-admin">

    <div class="login-container animate-fade-in">
        <button class="theme-toggle-btn shadow-sm" id="btnThemeToggle" title="Cambiar Tema">
            <span id="themeIcon"></span>
        </button>

        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3"
                style="width: 80px; height: 80px;">
                <i class="bi bi-building fs-1"></i>
            </div>
            <h3 class="text-primary fw-bold">Portal Administrativo</h3>
            <p class="text-muted small">Acceso para Directivos, Coordinadores y Administrativos.</p>
        </div>

        <form action="procesar_login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="mb-4">
                <label for="correo" class="form-label fw-bold text-secondary ps-1">Correo Electrónico</label>
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" class="form-control border-start-0 ps-0" id="correo" name="correo"
                        placeholder="usuario@escuela.edu" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-bold text-secondary ps-1">Contraseña</label>
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password"
                        placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg mt-2 mb-4 rounded-pill shadow">
                Ingresar <i class="bi bi-box-arrow-in-right ms-2"></i>
            </button>
        </form>

        <div class="text-center pt-3 border-top">
            <a href="login_docente.php"
                class="text-decoration-none text-info fw-medium d-inline-block mb-3 px-3 py-2 rounded-pill bg-info bg-opacity-10 transition-all hover-scale">
                <i class="bi bi-person-workspace me-1"></i> Soy Docente
            </a>
            <a href="login_padre.php"
                class="text-decoration-none text-warning fw-medium d-inline-block mb-3 px-3 py-2 ms-2 rounded-pill bg-warning bg-opacity-10 transition-all hover-scale">
                <i class="bi bi-people-fill me-1"></i> Portal Padres
            </a>
            <br>
            <a href="login_alumno.php"
                class="text-decoration-none text-success fw-medium d-inline-block px-3 py-2 rounded-pill bg-success bg-opacity-10">
                <i class="bi bi-mortarboard me-1"></i> Consultar Calificaciones
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>