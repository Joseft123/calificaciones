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
    $stmt_doc = $conexion->prepare("SELECT * FROM docentes WHERE id_docente = ?");
    $stmt_doc->bind_param("i", $id);
    $stmt_doc->execute();
    $resultado = $stmt_doc->get_result();
    $docente = $resultado->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id_docente']);
    $nomina = $conexion->real_escape_string($_POST['nomina']);
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $apellidos = $conexion->real_escape_string($_POST['apellidos']);
    $correo = $conexion->real_escape_string($_POST['correo']);

    // Verificar si el correo ya existe en OTRO docente
    $sql_check = "SELECT id_docente FROM docentes WHERE correo = ? AND id_docente != ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("si", $correo, $id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    // Verificar la nómina también
    $sql_nomina = "SELECT id_docente FROM docentes WHERE nomina = ? AND id_docente != ?";
    $stmt_nomina = $conexion->prepare($sql_nomina);
    $stmt_nomina->bind_param("si", $nomina, $id);
    $stmt_nomina->execute();
    $res_nomina = $stmt_nomina->get_result();

    if ($res_check->num_rows > 0) {
        echo "<div class='alert alert-warning mt-3'>⚠️ El correo electrónico <strong>$correo</strong> ya está en uso por otro docente.</div>";
    }
    elseif ($res_nomina->num_rows > 0) {
        echo "<div class='alert alert-warning mt-3'>⚠️ El identificador de nómina <strong>$nomina</strong> ya le pertenece a otro docente.</div>";
    }
    else {
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE docentes SET nomina=?, nombre=?, apellidos=?, correo=?, password=? WHERE id_docente=?";
            $stmt = $conexion->prepare($sql);
            if ($stmt)
                $stmt->bind_param("sssssi", $nomina, $nombre, $apellidos, $correo, $password, $id);
        }
        else {
            $sql = "UPDATE docentes SET nomina=?, nombre=?, apellidos=?, correo=? WHERE id_docente=?";
            $stmt = $conexion->prepare($sql);
            if ($stmt)
                $stmt->bind_param("ssssi", $nomina, $nombre, $apellidos, $correo, $id);
        }

        if ($stmt) {
            if ($stmt->execute()) {
                echo "<script>window.location='docentes.php';</script>";
            }
            else {
                echo "<div class='alert alert-danger mt-3'>Error al actualizar: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        else {
            echo "<div class='alert alert-danger mt-3'>Error al preparar actualización: " . $conexion->error . "</div>";
        }
    }
}
?>
<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">✏️ Editar Docente</h2>
</div>

<div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-header bg-warning text-dark px-4 py-3" style="background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);">
        <h5 class="m-0 fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Actualizar Datos del Maestro(a)</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="editar_docente.php" method="POST">
            <input type="hidden" name="id_docente" value="<?php echo $docente['id_docente']; ?>">
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nómina / Identificador</label>
                    <input type="text" name="nomina" class="form-control form-control-lg shadow-sm text-uppercase" value="<?php echo htmlspecialchars($docente['nomina']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nombre(s)</label>
                    <input type="text" name="nombre" class="form-control form-control-lg shadow-sm" value="<?php echo htmlspecialchars($docente['nombre']); ?>" required>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control form-control-lg shadow-sm" value="<?php echo htmlspecialchars($docente['apellidos']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Correo Electrónico Institucional</label>
                    <input type="email" name="correo" class="form-control form-control-lg shadow-sm" value="<?php echo htmlspecialchars($docente['correo']); ?>" required>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Cambiar Contraseña <small class="text-muted fw-normal">(dejar en blanco para conservar actual)</small></label>
                    <input type="password" name="password" class="form-control form-control-lg shadow-sm" placeholder="Mínimo 6 caracteres">
                </div>
            </div>
            
            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-warning btn-lg px-5 rounded-pill shadow fw-bold">
                    <i class="bi bi-arrow-repeat me-2"></i> Guardar Cambios
                </button>
                <a href="docentes.php" class="btn btn-outline-secondary btn-lg ms-2 rounded-pill shadow-sm">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
