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
    <link rel="stylesheet" href="../assets/css/main.css">
    <script src="../assets/js/main.js"></script>
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
            <li class="nav-item"><a class="nav-link" href="../docentes/docentes.php"><i class="bi bi-person-video3 me-1"></i> Docentes</a></li>
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
            <li class="nav-item"><a class="nav-link" href="../docentes/dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="../calificaciones/calificaciones.php"><i class="bi bi-pencil-square me-1"></i> Capturar Notas</a></li>
            <li class="nav-item"><a class="nav-link" href="../calificaciones/ver_calificaciones.php"><i class="bi bi-clock-history me-1"></i> Historial</a></li>
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