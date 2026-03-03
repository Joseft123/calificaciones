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
    <title>Iniciar Sesión - Sistema Escolar</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            opacity: 0;
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        body { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .login-container { 
            padding: 3rem; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 450px; 
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.4);
        }
        [data-bs-theme="light"] .login-container { background-color: rgba(255, 255, 255, 0.95); }
        [data-bs-theme="dark"] body { background: linear-gradient(135deg, #1f1c2c 0%, #928dab 100%); }
        [data-bs-theme="dark"] .login-container { 
            background-color: rgba(30, 30, 30, 0.95); 
            color: #fff;
            border-color: rgba(255,255,255,0.1);
        }
        .theme-toggle-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--bs-light);
            border: 1px solid var(--bs-border-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        [data-bs-theme="dark"] .theme-toggle-btn {
            background: var(--bs-dark);
            border-color: var(--bs-border-color);
        }
        .theme-toggle-btn:hover {
            transform: scale(1.1);
        }
        .input-group-text {
            background-color: transparent;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
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
                themeIcon.innerHTML = currentTheme === 'dark' ? '<i class="bi bi-sun-fill text-warning"></i>' : '<i class="bi bi-moon-stars-fill text-primary"></i>';

                btnToggle.addEventListener('click', () => {
                    const html = document.documentElement;
                    const newTheme = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                    html.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    themeIcon.innerHTML = newTheme === 'dark' ? '<i class="bi bi-sun-fill text-warning"></i>' : '<i class="bi bi-moon-stars-fill text-primary"></i>';
                });
            }
        });
    </script>
</head>
<body>

<div class="login-container animate-fade-in">
    <button class="theme-toggle-btn shadow-sm" id="btnThemeToggle" title="Cambiar Tema">
        <span id="themeIcon"></span>
    </button>
    
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
            <i class="bi bi-building fs-1"></i>
        </div>
        <h3 class="text-primary fw-bold">Portal Administrativo</h3>
        <p class="text-muted small">Acceso para Directivos, Coordinadores y Administrativos.</p>
    </div>
    
    <form action="procesar_login.php" method="POST">
        <div class="mb-4">
            <label for="correo" class="form-label fw-bold text-secondary ps-1">Correo Electrónico</label>
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" class="form-control border-start-0 ps-0" id="correo" name="correo" placeholder="usuario@escuela.edu" required>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label fw-bold text-secondary ps-1">Contraseña</label>
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="••••••••" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 btn-lg mt-2 mb-4 rounded-pill shadow">
            Ingresar <i class="bi bi-box-arrow-in-right ms-2"></i>
        </button>
    </form>
    
    <div class="text-center pt-3 border-top">
        <a href="login_docente.php" class="text-decoration-none text-info fw-medium d-inline-block mb-3 px-3 py-2 rounded-pill bg-info bg-opacity-10 transition-all hover-scale">
            <i class="bi bi-person-workspace me-1"></i> Soy Docente
        </a>
        <br>
        <a href="login_alumno.php" class="text-decoration-none text-success fw-medium d-inline-block px-3 py-2 rounded-pill bg-success bg-opacity-10">
            <i class="bi bi-mortarboard me-1"></i> Consultar Calificaciones
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>