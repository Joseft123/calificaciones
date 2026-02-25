// crear_usuario.php (Crear)
<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
include '../includes/conexion.php';
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $correo = $conexion->real_escape_string($_POST['correo']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    $sql = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES ('$nombre', '$correo', '$password', '$rol')";

    if ($conexion->query($sql) === TRUE) {
        echo "<div class='alert alert-success mt-3'>Usuario creado exitosamente. <a href='usuarios.php'>Ver usuarios</a></div>";
    }
    else {
        echo "<div class='alert alert-danger mt-3'>Error: " . $conexion->error . "</div>";
    }
}
?>
<h2 class="text-primary mb-4">➕ Crear Usuario</h2>
<form action="crear_usuario.php" method="POST" class="shadow-sm p-4 bg-white rounded">
    <div class="mb-3">
        <label class="form-label fw-bold">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Correo</label>
        <input type="email" name="correo" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Rol</label>
        <select name="rol" class="form-select" required>
            <option value="Director">Director</option>
            <option value="Coordinador">Coordinador</option>
            <option value="Cobranza">Cobranza</option>
            <option value="Docente">Docente</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
    <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
</form>
</div>
</body>
</html>