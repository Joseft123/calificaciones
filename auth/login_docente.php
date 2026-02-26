<?php
session_start();
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
    <title>Acceso Docentes - Sistema Escolar</title>
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
    <h3 class="text-center text-info mb-4">👨‍🏫 Portal de Docentes</h3>
    <p class="text-center text-muted small mb-4">Ingresa para gestionar calificaciones.</p>
    
    <form action="procesar_login_docente.php" method="POST">
        <div class="mb-3">
            <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-bold">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-info text-white w-100 btn-lg mt-3">Ingresar</button>
    </form>
    
    <div class="text-center mt-4">
        <a href="login.php" class="text-decoration-none small d-block mb-2">🏢 Acceso Administrativo</a>
        <a href="login_alumno.php" class="text-decoration-none small text-success d-block">🎓 Soy Alumno / Consultar Calificaciones</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
