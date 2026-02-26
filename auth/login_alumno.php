<?php
session_start();
if (isset($_SESSION['id_alumno'])) {
    header("Location: ../calificaciones/mis_calificaciones.php");
    exit();
}
// Si un admin está logueado, igual puede ver esto o podríamos redirigirlo
if (isset($_SESSION['id_usuario'])) {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Alumnos - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0;
            background-color: var(--bs-body-bg);
        }
        .login-container { 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
            position: relative;
        }
        [data-bs-theme="light"] body { background-color: #f8f9fa; }
        [data-bs-theme="light"] .login-container { background-color: white; }
        [data-bs-theme="dark"] body { background-color: #121212; }
        [data-bs-theme="dark"] .login-container { background-color: #1e1e1e; color: #fff; }
        .theme-toggle-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }
    </style>
    <script>
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
        });
    </script>
</head>
<body>

<div class="login-container">
    <button class="theme-toggle-btn" id="btnThemeToggle" title="Cambiar Tema">
        <span id="themeIcon">🌙</span>
    </button>
    <h3 class="text-center text-success mb-4">🎓 Portal de Alumnos</h3>
    <p class="text-center text-muted small mb-4">Consulta tus calificaciones ingresando tus datos.</p>
    
    <form action="procesar_login_alumno.php" method="POST">
        <div class="mb-3">
            <label for="matricula" class="form-label fw-bold">Matrícula</label>
            <input type="text" class="form-control" id="matricula" name="matricula" placeholder="Ej. MAT-2024-001" required>
        </div>
        <div class="mb-3">
            <label for="apellidos" class="form-label fw-bold">Apellidos</label>
            <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Tus apellidos tal como se registraron" required>
        </div>
        <button type="submit" class="btn btn-success w-100 btn-lg mt-3">Ingresar</button>
    </form>

    <div class="text-center mt-4">
        <a href="login_docente.php" class="text-decoration-none small text-info d-block mb-2">👨‍🏫 Acceso Docentes</a>
        <a href="login.php" class="text-decoration-none small d-block">🏢 Acceso Administrativo</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
