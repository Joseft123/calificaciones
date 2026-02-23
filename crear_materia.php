<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
include 'conexion.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clave = $conexion->real_escape_string($_POST['clave_materia']);
    $nombre = $conexion->real_escape_string($_POST['nombre_materia']);
    $nivel = $_POST['nivel'];
    $grado = intval($_POST['grado']);

    $sql = "INSERT INTO materias (clave_materia, nombre_materia, nivel, grado) 
            VALUES ('$clave', '$nombre', '$nivel', '$grado')";

    if ($conexion->query($sql) === TRUE) {
        echo "<div class='alert alert-success mt-3 shadow-sm'>✅ Materia registrada exitosamente. <a href='materias.php' class='alert-link'>Volver a la lista</a></div>";
    }
    else {
        echo "<div class='alert alert-danger mt-3 shadow-sm'>❌ Error al registrar: " . $conexion->error . "</div>";
    }
}
?>

<h2 class="text-primary mb-4">➕ Registrar Nueva Materia</h2>

<form action="crear_materia.php" method="POST" class="shadow-sm p-4 bg-white rounded border">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Clave de la Materia</label>
            <input type="text" name="clave_materia" class="form-control" required placeholder="Ej. MAT-101">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nombre de la Materia</label>
            <input type="text" name="nombre_materia" class="form-control" required placeholder="Ej. Matemáticas I">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nivel Educativo</label>
            <select name="nivel" class="form-select" required>
                <option value="">Selecciona un nivel...</option>
                <option value="Primaria">Primaria</option>
                <option value="Secundaria">Secundaria</option>
                <option value="Preparatoria">Preparatoria</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Grado</label>
            <input type="number" name="grado" class="form-control" min="1" max="6" required placeholder="Ej. 1, 2, 3...">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">💾 Guardar Materia</button>
        <a href="materias.php" class="btn btn-secondary btn-lg ms-2">Cancelar</a>
    </div>
</form>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>