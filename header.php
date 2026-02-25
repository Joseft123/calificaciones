
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
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .contenedor-principal {
            margin-top: 30px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">🎓 Sistema Escolar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
          <a class="nav-link" href="calificaciones.php">Capturar Calificaciones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="ver_calificaciones.php">Ver Historial</a>
        </li>
        <?php if (isset($_SESSION['nombre'])): ?>
        <li class="nav-item ms-lg-3 d-flex align-items-center">
            <span class="text-light me-3">👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a class="btn btn-danger btn-sm" href="cerrar_sesion.php">Cerrar Sesión</a>
        </li>
        <?php
endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container contenedor-principal">