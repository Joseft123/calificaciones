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
<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">✏️ Editar Usuario</h2>
</div>

<div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-header bg-warning text-dark px-4 py-3" style="background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);">
        <h5 class="m-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Actualizar Datos del Usuario</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="editar_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nombre de Usuario</label>
                    <input type="text" name="nombre" class="form-control form-control-lg shadow-sm" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control form-control-lg shadow-sm" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nueva Contraseña <small class="text-muted fw-normal">(dejar en blanco para no cambiar)</small></label>
                    <input type="password" name="password" class="form-control form-control-lg shadow-sm" placeholder="Mínimo 8 caracteres">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Rol del Usuario</label>
                    <select name="rol" class="form-select form-select-lg shadow-sm" required>
                        <option value="Director" <?php if ($usuario['rol'] == 'Director')
    echo 'selected'; ?>>Director (Control Total)</option>
                        <option value="Coordinador" <?php if ($usuario['rol'] == 'Coordinador')
    echo 'selected'; ?>>Coordinador (Gestión Académica)</option>
                        <option value="Cobranza" <?php if ($usuario['rol'] == 'Cobranza')
    echo 'selected'; ?>>Cobranza (Pagos y Finanzas)</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-warning btn-lg px-5 rounded-pill shadow fw-bold">
                    <i class="bi bi-arrow-repeat me-2"></i> Actualizar Usuario
                </button>
                <a href="usuarios.php" class="btn btn-outline-secondary btn-lg ms-2 rounded-pill shadow-sm">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</div>
</body>
</html>