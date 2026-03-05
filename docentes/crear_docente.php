<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
include '../includes/conexion.php';
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomina = $conexion->real_escape_string($_POST['nomina']);
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $apellidos = $conexion->real_escape_string($_POST['apellidos']);
    $correo = $conexion->real_escape_string($_POST['correo']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Verificar si el correo ya existe
    $sql_check = "SELECT id_docente FROM docentes WHERE correo = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    // Verificar la nómina también
    $sql_nomina = "SELECT id_docente FROM docentes WHERE nomina = ?";
    $stmt_nomina = $conexion->prepare($sql_nomina);
    $stmt_nomina->bind_param("s", $nomina);
    $stmt_nomina->execute();
    $res_nomina = $stmt_nomina->get_result();


    if ($res_check->num_rows > 0) {
        echo "<div class='alert alert-warning mt-3 shadow-sm'>❌ El correo electrónico <strong>$correo</strong> ya está registrado para otro docente.</div>";
    }
    elseif ($res_nomina->num_rows > 0) {
        echo "<div class='alert alert-warning mt-3 shadow-sm'>❌ El número de nómina <strong>$nomina</strong> ya está en uso. Debe ser único.</div>";
    }
    else {
        $sql = "INSERT INTO docentes (nomina, nombre, apellidos, correo, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sssss", $nomina, $nombre, $apellidos, $correo, $password);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success mt-3 shadow-sm'>✅ Docente registrado de forma exitosa. <a href='docentes.php' class='alert-link'>Volver al directorio</a></div>";
            }
            else {
                echo "<div class='alert alert-danger mt-3 shadow-sm'>❌ Error al registrar: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        else {
            echo "<div class='alert alert-danger mt-3 shadow-sm'>❌ Error al preparar la consulta: " . $conexion->error . "</div>";
        }
    }
}
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">➕ Registrar Nuevo Docente</h2>
</div>

<div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-header bg-success text-white px-4 py-3" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
        <h5 class="m-0 fw-bold"><i class="bi bi-person-fill-add me-2"></i>Datos del Profesor(a)</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="crear_docente.php" method="POST">
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nómina / Identificador</label>
                    <input type="text" name="nomina" class="form-control form-control-lg shadow-sm text-uppercase" placeholder="Ej. DOC-001" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nombre(s)</label>
                    <input type="text" name="nombre" class="form-control form-control-lg shadow-sm" required>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control form-control-lg shadow-sm" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Correo Electrónico Institucional</label>
                    <input type="email" name="correo" class="form-control form-control-lg shadow-sm" placeholder="profesor@escuela.edu.mx" required>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Contraseña Temporal</label>
                    <input type="password" name="password" class="form-control form-control-lg shadow-sm" required minlength="6" placeholder="Mínimo 6 caracteres">
                </div>
                <!-- Not showing Role because it's implicitly a Docente -->
            </div>
            
            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow fw-bold">
                    <i class="bi bi-save me-2"></i> Dar de Alta
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
