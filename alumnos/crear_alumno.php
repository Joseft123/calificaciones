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

    $sql = "INSERT INTO alumnos (matricula, nombre, apellidos, nivel, grado, grupo) 
            VALUES ('$matricula', '$nombre', '$apellidos', '$nivel', '$grado', '$grupo')";

    if ($conexion->query($sql) === TRUE) {
        echo "<div class='alert alert-success mt-3 shadow-sm'>✅ Alumno inscrito exitosamente. <a href='alumnos.php' class='alert-link'>Volver a la lista</a></div>";
    }
    else {
        echo "<div class='alert alert-danger mt-3 shadow-sm'>❌ Error al inscribir: " . $conexion->error . "</div>";
    }
}
?>

<h2 class="text-primary mb-4">➕ Inscribir Nuevo Alumno</h2>

<form action="crear_alumno.php" method="POST" class="shadow-sm p-4 bg-white rounded border">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Matrícula</label>
            <input type="text" name="matricula" class="form-control" required placeholder="Ej. MAT-2026-001">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nivel Educativo</label>
            <select name="nivel" class="form-select" required>
                <option value="">Selecciona un nivel...</option>
                <option value="Primaria">Primaria</option>
                <option value="Secundaria">Secundaria</option>
                <option value="Preparatoria">Preparatoria</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nombre(s)</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Apellidos</label>
            <input type="text" name="apellidos" class="form-control" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Grado</label>
            <input type="number" name="grado" class="form-control" min="1" max="6" required placeholder="Ej. 1, 2, 3...">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Grupo</label>
            <input type="text" name="grupo" class="form-control" maxlength="5" required placeholder="Ej. A, B, Único...">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">💾 Guardar Alumno</button>
        <a href="alumnos.php" class="btn btn-secondary btn-lg ms-2">Cancelar</a>
    </div>
</form>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>