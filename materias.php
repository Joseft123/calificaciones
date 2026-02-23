<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
include 'conexion.php';

// Obtener la lista de materias ordenadas por nivel y grado
$sql = "SELECT id_materia, clave_materia, nombre_materia, nivel, grado FROM materias ORDER BY nivel, grado ASC";
$resultado = $conexion->query($sql);

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary m-0">📚 Gestión de Materias</h2>
    <a href="crear_materia.php" class="btn btn-success">➕ Nueva Materia</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered align-middle shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>Clave</th>
                <th>Materia</th>
                <th>Nivel</th>
                <th>Grado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td><span class='badge bg-info text-dark'>" . $fila['clave_materia'] . "</span></td>";
        echo "<td>" . $fila['nombre_materia'] . "</td>";
        echo "<td>" . $fila['nivel'] . "</td>";
        echo "<td>" . $fila['grado'] . "º</td>";
        echo "<td>
                            <a href='editar_materia.php?id=" . $fila['id_materia'] . "' class='btn btn-warning btn-sm'>Editar</a>
                            <a href='eliminar_materia.php?id=" . $fila['id_materia'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"¿Seguro que deseas eliminar esta materia? Se borrarán también las calificaciones asociadas.\");'>Eliminar</a>
                          </td>";
        echo "</tr>";
    }
}
else {
    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No hay materias registradas aún.</td></tr>";
}
?>
        </tbody>
    </table>
</div>

<?php $conexion->close(); ?>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>