<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../includes/conexion.php';
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = $conexion->real_escape_string($_POST['matricula']);
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $apellidos = $conexion->real_escape_string($_POST['apellidos']);
    $nivel = $_POST['nivel'];
    $grado = intval($_POST['grado']);
    $grupo = $conexion->real_escape_string($_POST['grupo']);

    // Verificar si la matrícula ya existe
    $sql_check = "SELECT id_alumno FROM alumnos WHERE matricula = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("s", $matricula);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        echo "<div class='alert alert-warning mt-3 shadow-sm'>❌ La matrícula <strong>$matricula</strong> ya está registrada en el sistema.</div>";
    }
    else {
        $sql = "INSERT INTO alumnos (matricula, nombre, apellidos, nivel, grado, grupo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssssss", $matricula, $nombre, $apellidos, $nivel, $grado, $grupo);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success mt-3 shadow-sm'>✅ Alumno inscrito exitosamente. <a href='alumnos.php' class='alert-link'>Volver a la lista</a></div>";
            }
            else {
                echo "<div class='alert alert-danger mt-3 shadow-sm'>❌ Error al inscribir: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        else {
            echo "<div class='alert alert-danger mt-3 shadow-sm'>❌ Error al preparar la consulta: " . $conexion->error . "</div>";
        }
    } // Cierra el else del check de matrícula
}
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">➕ Inscribir Nuevo Alumno</h2>
</div>

<div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-header bg-primary text-white px-4 py-3" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <h5 class="m-0 fw-bold"><i class="bi bi-person-badge-fill me-2"></i>Datos del Estudiante</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="crear_alumno.php" method="POST">
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Matrícula</label>
                    <input type="text" name="matricula" class="form-control form-control-lg shadow-sm" required placeholder="Ej. MAT-2026-001">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nivel Educativo</label>
                    <select name="nivel" class="form-select form-select-lg shadow-sm" required>
                        <option value="">Selecciona un nivel...</option>
                        <option value="Primaria">Primaria</option>
                        <option value="Secundaria">Secundaria</option>
                        <option value="Preparatoria">Preparatoria</option>
                    </select>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nombre(s)</label>
                    <input type="text" name="nombre" class="form-control form-control-lg shadow-sm" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control form-control-lg shadow-sm" required>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Grado</label>
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-123 text-muted"></i></span>
                        <input type="number" name="grado" class="form-control border-start-0 ps-0" min="1" max="6" required placeholder="Ej. 1, 2, 3...">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Grupo</label>
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-alphabet text-muted"></i></span>
                        <input type="text" name="grupo" class="form-control border-start-0 ps-0 text-uppercase" maxlength="5" required placeholder="Ej. A, B, Único...">
                    </div>
                </div>
            </div>

            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                    <i class="bi bi-save me-2"></i> Guardar Alumno
                </button>
                <a href="alumnos.php" class="btn btn-outline-secondary btn-lg ms-2 rounded-pill shadow-sm">Cancelar</a>
            </div>
            
        </form>
    </div>
</div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>