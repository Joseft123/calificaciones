<?php
session_start();

// Validar que el usuario sea un docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

$id_docente = $_SESSION['id_docente'];

// Consulta SQL con INNER JOIN para obtener los alumnos asignados al docente logueado
$sql = "SELECT DISTINCT a.id_alumno, a.matricula, a.nombre, a.apellidos, a.nivel, a.grado, a.grupo 
        FROM alumnos a
        INNER JOIN docente_materia_grupo dmg ON a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo
        WHERE dmg.id_docente = $id_docente
        ORDER BY a.nivel ASC, a.grado ASC, a.grupo ASC, a.apellidos ASC";

$resultado = $conexion->query($sql);

// Agrupar alumnos por nivel y grupo
$alumnos_agrupados = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $nivel = $fila['nivel'];
        $grupo = $fila['grado'] . "º " . $fila['grupo'];
        $alumnos_agrupados[$nivel][$grupo][] = $fila;
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-success m-0 fw-bold">👨‍🎓 Mis Alumnos</h2>
</div>

<?php
if (!empty($alumnos_agrupados)) {
    $delay = 0.2;

    foreach ($alumnos_agrupados as $nivel => $grupos) {
        echo "<div class='mb-5 animate-fade-in' style='animation-delay: {$delay}s;'>";
        echo "<h3 class='text-body-secondary border-bottom pb-2 mb-4 fw-bold'>
                <span class='text-success'>Nivel:</span> " . htmlspecialchars($nivel) . "
              </h3>";

        $delay += 0.15;

        foreach ($grupos as $grupo => $alumnos) {
            echo "<div class='card mb-4 shadow rounded-4 overflow-hidden animate-fade-in' style='animation-delay: {$delay}s; border: none;'>";
            echo "<div class='card-header group-card-header text-white px-4 py-3'>";
            echo "<h5 class='m-0 fw-bold'><i class='bi bi-people-fill me-2'></i>Grupo " . htmlspecialchars($grupo) . "</h5>";
            echo "</div>";
            echo "<div class='card-body p-4'>";
            echo "<div class='row g-4'>";

            $delay += 0.1;

            $cardDelay = $delay;
            foreach ($alumnos as $al) {
                $nombre_completo = htmlspecialchars($al['nombre'] . ' ' . $al['apellidos']);

                echo "<div class='col-md-6 col-lg-4 col-xl-3 animate-fade-in' style='animation-delay: {$cardDelay}s;'>";
                echo "<div class='card h-100 border-0 shadow-sm student-card rounded-4 bg-transparent'>";
                echo "<div class='card-body p-4'>";

                echo "<div class='d-flex align-items-center justify-content-start mb-3'>";
                echo "<div class='bg-light rounded-circle p-3 me-3 text-center' style='width: 50px; height: 50px;'><i class='bi bi-person-fill fs-4 text-secondary' style='line-height: .5;'></i></div>";
                echo "<h6 class='card-title mb-0 fw-bold text-truncate' title='{$nombre_completo}'>{$nombre_completo}</h6>";
                echo "</div>";

                echo "<div class='mb-2 text-body-secondary'>";
                echo "<div class='small mb-2'><i class='bi bi-card-text opacity-75 me-2'></i><strong>Matrícula:</strong> " . htmlspecialchars($al['matricula']) . "</div>";
                echo "</div>";

                echo "<div class='d-grid mt-3'>";
                echo "<a href='../calificaciones/calificaciones.php?id_alumno=" . $al['id_alumno'] . "' class='btn btn-outline-success btn-sm rounded-pill'>Calificar</a>";
                echo "</div>";

                echo "</div>"; // Fin card-body
                echo "</div>"; // Fin card estudiante
                echo "</div>"; // Fin col
                $cardDelay += 0.05;
            }

            echo "</div></div></div>";
        }
        echo "</div>";
    }
}
else {
    echo "
    <div class='empty-state text-center p-5 mx-auto animate-fade-in' style='animation-delay: 0.2s; max-width: 600px;'>
        <div style='font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;'>📭</div>
        <h4 class='text-secondary fw-bold mb-3'>Sin alumnos asignados</h4>
        <p class='text-muted mb-0'>No tienes ningún grupo asignado a tus materias actualmente.</p>
    </div>";
}

$conexion->close();
?>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
