<?php
// Iniciar o retomar la sesión existente
session_start();

// Validar si la variable de sesión 'id_usuario' NO existe
if (!isset($_SESSION['id_usuario'])) {
    // Si no existe, redirigimos al usuario a la pantalla de login
    header("Location: login.php");
    exit();
}
// Incluir el archivo de conexión
include 'conexion.php';

// Consultar los alumnos para el menú desplegable
$query_alumnos = "SELECT id_alumno, matricula, nombre, apellidos FROM alumnos";
$result_alumnos = $conexion->query($query_alumnos);

// Consultar las materias para el menú desplegable
$query_materias = "SELECT id_materia, nombre_materia FROM materias";
$result_materias = $conexion->query($query_materias);

// Incluir el diseño principal (menú y apertura del contenedor)
include 'header.php';
?>

    <h2 class="mb-4 text-primary">📝 Registrar Calificación</h2>

    <form action="guardar_calificacion.php" method="POST">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="id_alumno" class="form-label fw-bold">Alumno:</label>
                <select name="id_alumno" id="id_alumno" class="form-select" required>
                    <option value="">Selecciona un alumno...</option>
                    <?php while ($row = $result_alumnos->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_alumno']; ?>">
                            <?php echo $row['matricula'] . " - " . $row['nombre'] . " " . $row['apellidos']; ?>
                        </option>
                    <?php
endwhile; ?>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="id_materia" class="form-label fw-bold">Materia:</label>
                <select name="id_materia" id="id_materia" class="form-select" required>
                    <option value="">Selecciona una materia...</option>
                    <?php while ($row = $result_materias->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_materia']; ?>">
                            <?php echo $row['nombre_materia']; ?>
                        </option>
                    <?php
endwhile; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="periodo" class="form-label fw-bold">Periodo (Ej. 1 para primer parcial):</label>
                <input type="number" name="periodo" id="periodo" class="form-control" min="1" max="5" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="calificacion" class="form-label fw-bold">Calificación:</label>
                <input type="number" name="calificacion" id="calificacion" class="form-control" step="0.1" min="0" max="10" required>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg w-100">💾 Guardar Calificación</button>
        </div>
        
    </form>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>