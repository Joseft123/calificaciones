<?php
session_start();
// Validar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

include 'conexion.php';

// Obtener la lista de alumnos
$sql = "SELECT id_alumno, matricula, nombre, apellidos, nivel, grado, grupo FROM alumnos ORDER BY nivel, grado, grupo ASC";
$resultado = $conexion->query($sql);

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary m-0">👨‍🎓 Gestión de Alumnos</h2>
    <a href="crear_alumno.php" class="btn btn-success">➕ Inscribir Alumno</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered align-middle shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>Matrícula</th>
                <th>Nombre Completo</th>
                <th>Nivel</th>
                <th>Grado y Grupo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td><span class='badge bg-secondary'>" . $fila['matricula'] . "</span></td>";
        echo "<td>" . $fila['apellidos'] . " " . $fila['nombre'] . "</td>";
        echo "<td>" . $fila['nivel'] . "</td>";
        echo "<td>" . $fila['grado'] . "º " . $fila['grupo'] . "</td>";
        echo "<td>
                            <a href='editar_alumno.php?id=" . $fila['id_alumno'] . "' class='btn btn-warning btn-sm'>Editar</a>
                            <a href='eliminar_alumno.php?id=" . $fila['id_alumno'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"¿Estás seguro de eliminar a este alumno y todo su historial de calificaciones?\");'>Eliminar</a>
                          </td>";
        echo "</tr>";
    }
}
else {
    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No hay alumnos inscritos aún.</td></tr>";
}
?>
        </tbody>
    </table>
</div>

<?php $conexion->close(); ?>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>