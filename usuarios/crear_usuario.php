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

    // Verificar si el correo ya existe
    $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        echo "<div class='alert alert-warning mt-3'>El correo electrónico <strong>$correo</strong> ya está registrado. Por favor, utiliza otro.</div>";
    }
    else {
        $sql = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssss", $nombre, $correo, $password, $rol);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success mt-3'>Usuario creado exitosamente. <a href='usuarios.php'>Ver usuarios</a></div>";
            }
            else {
                echo "<div class='alert alert-danger mt-3'>Error al registrar: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        else {
            echo "<div class='alert alert-danger mt-3'>Error al preparar la consulta: " . $conexion->error . "</div>";
        }
    } // Cierra el else del check de correo
}
?>
<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">➕ Crear Usuario</h2>
</div>

<div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-header bg-primary text-white px-4 py-3" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <h5 class="m-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Datos del Nuevo Usuario</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="crear_usuario.php" method="POST">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nombre de Usuario</label>
                    <input type="text" name="nombre" class="form-control form-control-lg shadow-sm" placeholder="Ej. Juan Pérez" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control form-control-lg shadow-sm" placeholder="usuario@escuela.edu" required>
                </div>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Contraseña</label>
                    <input type="password" name="password" class="form-control form-control-lg shadow-sm" placeholder="Mínimo 8 caracteres" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Rol del Usuario</label>
                    <select name="rol" class="form-select form-select-lg shadow-sm" required>
                        <option value="">Selecciona un rol...</option>
                        <option value="Director">Director</option>
                        <option value="Coordinador">Coordinador</option>
                        <option value="Cobranza">Cobranza</option>
                        <option value="Docente">Docente</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                    <i class="bi bi-save me-2"></i> Guardar Usuario
                </button>
                <a href="usuarios.php" class="btn btn-outline-secondary btn-lg ms-2 rounded-pill shadow-sm">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</div>
</body>
</html>