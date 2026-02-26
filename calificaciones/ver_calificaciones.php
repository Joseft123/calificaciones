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
        ORDER BY a.nivel ASC, a.grado ASC, a.grupo ASC, a.apellidos ASC, a.nombre ASC, c.fecha_registro DESC";

$resultado = $conexion->query($sql);

// Agrupar calificaciones por nivel y grupo
$calificaciones_agrupadas = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $nivel = $fila['nivel'];
        $grupo = $fila['grado'] . "º " . $fila['grupo']; // Ej. 1º A
        $calificaciones_agrupadas[$nivel][$grupo][] = $fila;
    }
}

// Incluir el diseño principal (menú y apertura del contenedor)
include '../includes/header.php';
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary m-0">📊 Historial General de Calificaciones</h2>
        <a href="calificaciones.php" class="btn btn-success">➕ Nueva Calificación</a>
    </div>
    
    <?php
if (!empty($calificaciones_agrupadas)) {
    foreach ($calificaciones_agrupadas as $nivel => $grupos) {
        // Contenedor para cada Nivel Escolar
        echo "<div class='mb-5'>";
        echo "<h3 class='text-secondary border-bottom pb-2 mb-3'>Nivel: " . htmlspecialchars($nivel) . "</h3>";

        foreach ($grupos as $grupo => $calificaciones) {
            // Tarjeta principal para cada Grupo
            echo "<div class='card mb-4 shadow-sm border-0 rounded-3'>";
            echo "<div class='card-header bg-dark text-white rounded-top'>";
            echo "<h5 class='m-0'>Grupo " . htmlspecialchars($grupo) . "</h5>";
            echo "</div>";
            echo "<div class='card-body bg-light'>";
            echo "<div class='row g-3'>";

            // Tarjetas individuales para cada calificación/estudiante
            foreach ($calificaciones as $cal) {
                $badge_class = ($cal['calificacion'] < 6) ? 'bg-danger' : 'bg-success';
                $fecha = date('d/m/Y H:i', strtotime($cal['fecha_registro']));
                $nombre_completo = htmlspecialchars($cal['nombre'] . ' ' . $cal['apellidos']);

                echo "<div class='col-md-6 col-lg-4 col-xl-3'>";
                echo "<div class='card h-100 border-0 shadow-sm'>";
                echo "<div class='card-body'>";
                echo "<div class='d-flex justify-content-between align-items-center mb-2'>";
                echo "<h6 class='card-title mb-0 text-primary text-truncate' title='{$nombre_completo}'>{$nombre_completo}</h6>";
                echo "<span class='badge {$badge_class} fs-6' title='Calificación'>" . htmlspecialchars($cal['calificacion']) . "</span>";
                echo "</div>";
                echo "<p class='card-text mb-1 small text-muted'><strong>Matrícula:</strong> " . htmlspecialchars($cal['matricula']) . "</p>";
                echo "<p class='card-text mb-1 small text-muted'><strong>Materia:</strong> " . htmlspecialchars($cal['nombre_materia']) . "</p>";
                echo "<p class='card-text mb-1 small text-muted'><strong>Periodo:</strong> " . htmlspecialchars($cal['periodo']) . "</p>";
                echo "</div>";
                echo "<div class='card-footer bg-white border-0 pt-0'>";
                echo "<small class='text-muted'>📅 {$fecha}</small>";
                echo "</div>";
                echo "</div>"; // Fin card estudiante
                echo "</div>"; // Fin col
            }

            echo "</div>"; // Fin row
            echo "</div>"; // Fin card-body del grupo
            echo "</div>"; // Fin card del grupo
        }
        echo "</div>"; // Fin div nivel
    }
}
else {
    echo "<div class='alert alert-info text-center shadow-sm py-4'>No hay calificaciones registradas aún.</div>";
}

// Cerrar la conexión
$conexion->close();
?>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>