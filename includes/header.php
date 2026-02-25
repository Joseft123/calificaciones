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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: bold; }
        .contenedor-principal {
            margin-top: 30px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        });
    </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="../calificaciones/ver_calificaciones.php">🎓 Sistema Escolar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        
        <li class="nav-item"><a class="nav-link" href="../calificaciones/calificaciones.php">Capturar Notas</a></li>
        <li class="nav-item"><a class="nav-link" href="../calificaciones/ver_calificaciones.php">Historial</a></li>
        <li class="nav-item"><a class="nav-link" href="../alumnos/alumnos.php">Alumnos</a></li>
        <li class="nav-item"><a class="nav-link" href="../materias/materias.php">Materias</a></li>
        
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'Director'): ?>
        <li class="nav-item"><a class="nav-link text-warning fw-bold" href="../usuarios/usuarios.php">Usuarios</a></li>
        <?php
endif; ?>

        <?php if (isset($_SESSION['nombre'])): ?>
        <li class="nav-item ms-lg-3 d-flex align-items-center">
            <span class="text-light me-3">👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</span>
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