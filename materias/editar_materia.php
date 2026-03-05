<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../includes/conexion.php';
include '../includes/header.php';

// Cargar los datos de la materia si recibimos un ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $resultado = $conexion->query("SELECT * FROM materias WHERE id_materia = $id");
    $materia = $resultado->fetch_assoc();
}

// Procesar la actualización cuando se envíe el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_materia = intval($_POST['id_materia']);
    $clave = $conexion->real_escape_string($_POST['clave_materia']);
    $nombre = $conexion->real_escape_string($_POST['nombre_materia']);
    $nivel = $_POST['nivel'];
    $grado = intval($_POST['grado']);

    // Verificar si la clave de materia ya existe en OTRA materia
    $sql_check = "SELECT id_materia FROM materias WHERE clave_materia = ? AND id_materia != ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("si", $clave, $id_materia);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        echo "<div class='alert alert-warning mt-3 shadow-sm'>❌ La clave de materia <strong>$clave</strong> ya está siendo utilizada por otra asignatura.</div>";
    }
    else {
        $sql = "UPDATE materias SET clave_materia=?, nombre_materia=?, nivel=?, grado=? WHERE id_materia=?";
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssssi", $clave, $nombre, $nivel, $grado, $id_materia);
            if ($stmt->execute()) {
                echo "<script>window.location='materias.php';</script>";
            }
            else {
                echo "<div class='alert alert-danger mt-3'>❌ Error al actualizar: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        else {
            echo "<div class='alert alert-danger mt-3'>❌ Error al preparar actualización: " . $conexion->error . "</div>";
        }
    } // Cierra el else del check de clave
}
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">✏️ Editar Materia</h2>
</div>

<div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-header bg-warning text-dark px-4 py-3" style="background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);">
        <h5 class="m-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Actualizar Datos de la Asignatura</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="editar_materia.php" method="POST">
            <input type="hidden" name="id_materia" value="<?php echo $materia['id_materia']; ?>">
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Clave de la Materia</label>
                    <input type="text" name="clave_materia" class="form-control form-control-lg shadow-sm text-uppercase" value="<?php echo htmlspecialchars($materia['clave_materia']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nombre de la Materia</label>
                    <input type="text" name="nombre_materia" class="form-control form-control-lg shadow-sm" value="<?php echo htmlspecialchars($materia['nombre_materia']); ?>" required>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Nivel Educativo</label>
                    <select name="nivel" class="form-select form-select-lg shadow-sm" required>
                        <option value="Primaria" <?php if ($materia['nivel'] == 'Primaria')
    echo 'selected'; ?>>Primaria</option>
                        <option value="Secundaria" <?php if ($materia['nivel'] == 'Secundaria')
    echo 'selected'; ?>>Secundaria</option>
                        <option value="Preparatoria" <?php if ($materia['nivel'] == 'Preparatoria')
    echo 'selected'; ?>>Preparatoria</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Grado</label>
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-123 text-muted"></i></span>
                        <input type="number" name="grado" class="form-control border-start-0 ps-0" min="1" max="6" value="<?php echo htmlspecialchars($materia['grado']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-warning btn-lg px-5 rounded-pill shadow fw-bold">
                    <i class="bi bi-arrow-repeat me-2"></i> Actualizar Materia
                </button>
                <a href="materias.php" class="btn btn-outline-secondary btn-lg ms-2 rounded-pill shadow-sm">Cancelar</a>
            </div>
            
        </form>
    </div>
</div>

</div> </body>
</html>