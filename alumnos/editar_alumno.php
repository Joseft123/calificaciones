<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../includes/conexion.php';
include '../includes/header.php';

// Cargar los datos del alumno si recibimos un ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $resultado = $conexion->query("SELECT * FROM alumnos WHERE id_alumno = $id");
    $alumno = $resultado->fetch_assoc();
}

// Procesar la actualización cuando se envíe el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_alumno = intval($_POST['id_alumno']);
    $matricula = $conexion->real_escape_string($_POST['matricula']);
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $apellidos = $conexion->real_escape_string($_POST['apellidos']);
    $nivel = $_POST['nivel'];
    $grado = intval($_POST['grado']);
    $grupo = $conexion->real_escape_string($_POST['grupo']);

    $sql = "UPDATE alumnos SET matricula='$matricula', nombre='$nombre', apellidos='$apellidos', nivel='$nivel', grado='$grado', grupo='$grupo' WHERE id_alumno=$id_alumno";

    if ($conexion->query($sql) === TRUE) {
        echo "<script>window.location='alumnos.php';</script>";
    }
    else {
        echo "<div class='alert alert-danger mt-3'>❌ Error al actualizar: " . $conexion->error . "</div>";
    }
}
?>

<h2 class="text-primary mb-4">✏️ Editar Alumno</h2>

<form action="editar_alumno.php" method="POST" class="shadow-sm p-4 bg-white rounded border">
    <input type="hidden" name="id_alumno" value="<?php echo $alumno['id_alumno']; ?>">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Matrícula</label>
            <input type="text" name="matricula" class="form-control" value="<?php echo $alumno['matricula']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nivel Educativo</label>
            <select name="nivel" class="form-select" required>
                <option value="Primaria" <?php if ($alumno['nivel'] == 'Primaria')
    echo 'selected'; ?>>Primaria</option>
                <option value="Secundaria" <?php if ($alumno['nivel'] == 'Secundaria')
    echo 'selected'; ?>>Secundaria</option>
                <option value="Preparatoria" <?php if ($alumno['nivel'] == 'Preparatoria')
    echo 'selected'; ?>>Preparatoria</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Nombre(s)</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo $alumno['nombre']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Apellidos</label>
            <input type="text" name="apellidos" class="form-control" value="<?php echo $alumno['apellidos']; ?>" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Grado</label>
            <input type="number" name="grado" class="form-control" min="1" max="6" value="<?php echo $alumno['grado']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Grupo</label>
            <input type="text" name="grupo" class="form-control" maxlength="5" value="<?php echo $alumno['grupo']; ?>" required>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">💾 Actualizar Alumno</button>
        <a href="alumnos.php" class="btn btn-secondary btn-lg ms-2">Cancelar</a>
    </div>
</form>

</div> </body>
</html>