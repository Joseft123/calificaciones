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
}
?>

<h2 class="text-primary mb-4">✏️ Editar Materia</h2>

<form action="editar_materia.php" method="POST" class="shadow-sm p-4 bg-white rounded border">
    <input type="hidden" name="id_materia" value="<?php echo $materia['id_materia']; ?>">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Clave de la Materia</label>
            <input type="text" name="clave_materia" class="form-control" value="<?php echo $materia['clave_materia']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nombre de la Materia</label>
            <input type="text" name="nombre_materia" class="form-control" value="<?php echo $materia['nombre_materia']; ?>" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nivel Educativo</label>
            <select name="nivel" class="form-select" required>
                <option value="Primaria" <?php if ($materia['nivel'] == 'Primaria')
    echo 'selected'; ?>>Primaria</option>
                <option value="Secundaria" <?php if ($materia['nivel'] == 'Secundaria')
    echo 'selected'; ?>>Secundaria</option>
                <option value="Preparatoria" <?php if ($materia['nivel'] == 'Preparatoria')
    echo 'selected'; ?>>Preparatoria</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Grado</label>
            <input type="number" name="grado" class="form-control" min="1" max="6" value="<?php echo $materia['grado']; ?>" required>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">💾 Actualizar Materia</button>
        <a href="materias.php" class="btn btn-secondary btn-lg ms-2">Cancelar</a>
    </div>
</form>

</div> </body>
</html>