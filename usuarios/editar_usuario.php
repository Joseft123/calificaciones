// editar_usuario.php (Actualizar)
<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
include '../includes/conexion.php';
include '../includes/header.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $resultado = $conexion->query("SELECT * FROM usuarios WHERE id_usuario = $id");
    $usuario = $resultado->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id_usuario']);
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $correo = $conexion->real_escape_string($_POST['correo']);
    $rol = $_POST['rol'];

    // Verificar si el correo ya existe en OTRO usuario
    $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("si", $correo, $id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        echo "<div class='alert alert-warning mt-3'>El correo electrónico <strong>$correo</strong> ya está en uso por otro usuario.</div>";
    }
    else {
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre=?, correo=?, password=?, rol=? WHERE id_usuario=?";
            $stmt = $conexion->prepare($sql);
            if ($stmt)
                $stmt->bind_param("ssssi", $nombre, $correo, $password, $rol, $id);
        }
        else {
            $sql = "UPDATE usuarios SET nombre=?, correo=?, rol=? WHERE id_usuario=?";
            $stmt = $conexion->prepare($sql);
            if ($stmt)
                $stmt->bind_param("sssi", $nombre, $correo, $rol, $id);
        }

        if ($stmt) {
            if ($stmt->execute()) {
                echo "<script>window.location='usuarios.php';</script>";
            }
            else {
                echo "<div class='alert alert-danger mt-3'>Error al actualizar: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        else {
            echo "<div class='alert alert-danger mt-3'>Error al preparar actualización: " . $conexion->error . "</div>";
        }
    } // Cierra el else del check de correo
}
?>
<h2 class="text-primary mb-4">✏️ Editar Usuario</h2>
<form action="editar_usuario.php" method="POST" class="shadow-sm p-4 bg-white rounded">
    <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
    <div class="mb-3">
        <label class="form-label fw-bold">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?php echo $usuario['nombre']; ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Correo</label>
        <input type="email" name="correo" class="form-control" value="<?php echo $usuario['correo']; ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Nueva Contraseña (dejar en blanco para no cambiar)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Rol</label>
        <select name="rol" class="form-select" required>
            <option value="Director" <?php if ($usuario['rol'] == 'Director')
    echo 'selected'; ?>>Director</option>
            <option value="Coordinador" <?php if ($usuario['rol'] == 'Coordinador')
    echo 'selected'; ?>>Coordinador</option>
            <option value="Cobranza" <?php if ($usuario['rol'] == 'Cobranza')
    echo 'selected'; ?>>Cobranza</option>
            <option value="Docente" <?php if ($usuario['rol'] == 'Docente')
    echo 'selected'; ?>>Docente</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
</form>
</div>
</body>
</html>