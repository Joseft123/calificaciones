<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: ver_calificaciones.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0;
        }
        .login-container { 
            background: white; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
        }
    </style>
</head>
<body>

<div class="login-container">
    <h3 class="text-center text-primary mb-4">🎓 Sistema Escolar</h3>
    
    <form action="procesar_login.php" method="POST">
        <div class="mb-3">
            <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-bold">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">Ingresar</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>