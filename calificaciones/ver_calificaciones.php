<?php
// Iniciar o retomar la sesión existente
session_start();

// Validar si la variable de sesión 'id_usuario' NO existe
if (!isset($_SESSION['id_usuario'])) {
    // Si no existe, redirigimos al usuario a la pantalla de login
    header("Location: ../auth/login.php");
    exit();
}
// Incluir la conexión a la base de datos
include '../includes/conexion.php';

// Consulta SQL con INNER JOIN para obtener los nombres en lugar de los IDs
$sql = "SELECT a.matricula, a.nombre, a.apellidos, a.nivel, a.grado, a.grupo, 
               m.nombre_materia, c.periodo, c.calificacion, c.fecha_registro 
        FROM calificaciones c 
        INNER JOIN alumnos a ON c.id_alumno = a.id_alumno 
        INNER JOIN materias m ON c.id_materia = m.id_materia 
        ORDER BY c.fecha_registro DESC";

$resultado = $conexion->query($sql);

// Incluir el diseño principal (menú y apertura del contenedor)
include '../includes/header.php';
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary m-0">📊 Historial General de Calificaciones</h2>
        <a href="calificaciones.php" class="btn btn-success">➕ Nueva Calificación</a>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>Matrícula</th>
                    <th>Alumno</th>
                    <th>Nivel / Grupo</th>
                    <th>Materia</th>
                    <th>Periodo</th>
                    <th>Calificación</th>
                    <th>Fecha de Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php
if ($resultado->num_rows > 0) {
    // Imprimir los datos de cada fila
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $fila['matricula'] . "</td>";
        echo "<td>" . $fila['nombre'] . " " . $fila['apellidos'] . "</td>";
        echo "<td>" . $fila['nivel'] . " - " . $fila['grado'] . "º" . $fila['grupo'] . "</td>";
        echo "<td>" . $fila['nombre_materia'] . "</td>";
        echo "<td>" . $fila['periodo'] . "</td>";

        // Resaltar visualmente si la calificación es aprobatoria o reprobatoria
        if ($fila['calificacion'] < 6) {
            echo "<td><span class='badge bg-danger fs-6'>" . $fila['calificacion'] . "</span></td>";
        }
        else {
            echo "<td><span class='badge bg-success fs-6'>" . $fila['calificacion'] . "</span></td>";
        }

        echo "<td>" . date('d/m/Y H:i', strtotime($fila['fecha_registro'])) . "</td>";
        echo "</tr>";
    }
}
else {
    echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No hay calificaciones registradas aún.</td></tr>";
}
// Cerrar la conexión
$conexion->close();
?>
            </tbody>
        </table>
    </div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>