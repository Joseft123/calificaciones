<?php
// Iniciar o retomar la sesión existente
session_start();

// Validar si la variable de sesión 'id_usuario' o 'id_docente' NO existen
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['id_docente'])) {
    // Si no existe ninguna, redirigimos al usuario a la pantalla de login
    header("Location: ../auth/login.php");
    exit();
}
// Incluir la conexión a la base de datos
include '../includes/conexion.php';

// Consulta SQL con INNER JOIN para obtener los nombres en lugar de los IDs
$sql = "SELECT a.id_alumno, a.matricula, a.nombre, a.apellidos, a.nivel, a.grado, a.grupo, 
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

<style>
    /* Animación de entrada suave hacia arriba */
    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Clase base para elementos animados */
    .animate-fade-in {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Efectos hover para las tarjetas de estudiantes */
    .student-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        border-left: 4px solid transparent !important;
        background-color: var(--bs-body-bg);
    }
    
    .student-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.12) !important;
        border-left-color: #0d6efd !important; /* Resalta con un borde azul a la izquierda */
        z-index: 2;
    }

    [data-bs-theme="dark"] .card {
        background-color: #2b2b2b;
        color: #ffffff;
    }
    
    [data-bs-theme="dark"] .student-card:hover {
        box-shadow: 0 12px 24px rgba(0,0,0,0.5) !important;
    }
    
    /* Evitar que clases como bg-light y bg-white sobreescriban el dark mode */
    [data-bs-theme="dark"] .bg-light,
    [data-bs-theme="dark"] .bg-white {
        background-color: #1e1e1e !important;
        color: var(--bs-light) !important;
    }

    /* Gradiente para los encabezados de grupo */
    .group-card-header {
        background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
    }

    /* Estilo mejorado para los badges (calificaciones) */
    .grade-badge {
        font-size: 1.1rem;
        padding: 0.4em 0.8em;
        border-radius: 8px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }
    
    /* Estado vacío interactivo */
    .empty-state {
        background: linear-gradient(to right, #f8f9fa, #e9ecef);
        border: 2px dashed #ced4da;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .empty-state:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }
    [data-bs-theme="dark"] .empty-state {
        background: linear-gradient(to right, #2b2b2b, #1e1e1e);
        border-color: #495057;
    }
    [data-bs-theme="dark"] .empty-state:hover {
        background: #2b2b2b;
        border-color: #6c757d;
    }
</style>

    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
        <h2 class="text-primary m-0 fw-bold">📊 Historial General de Calificaciones</h2>
        <a href="calificaciones.php" class="btn btn-success shadow-sm px-4 rounded-pill">➕ Nueva Calificación</a>
    </div>
    
    <?php
if (!empty($calificaciones_agrupadas)) {
    $delay = 0.2; // Retraso inicial para la animación en cascada

    foreach ($calificaciones_agrupadas as $nivel => $grupos) {
        // Contenedor para cada Nivel Escolar
        echo "<div class='mb-5 animate-fade-in' style='animation-delay: " . $delay . "s;'>";
        echo "<h3 class='text-secondary border-bottom pb-2 mb-4 fw-bold'>
                <span class='text-primary'>Nivel:</span> " . htmlspecialchars($nivel) . "
              </h3>";

        $delay += 0.15; // Incrementar retraso por nivel

        foreach ($grupos as $grupo => $calificaciones) {
            // Tarjeta principal para cada Grupo
            echo "<div class='card mb-4 shadow rounded-4 overflow-hidden animate-fade-in student-group-card' style='animation-delay: " . $delay . "s; border: none;'>";
            echo "<div class='card-header group-card-header text-white px-4 py-3'>";
            echo "<h5 class='m-0 fw-bold'><i class='bi bi-people-fill me-2'></i>Grupo " . htmlspecialchars($grupo) . "</h5>";
            echo "</div>";
            echo "<div class='card-body p-4'>";
            echo "<div class='row g-4'>";

            $delay += 0.1; // Pequeño retraso por grupo

            // Tarjetas individuales para cada calificación/estudiante
            $cardDelay = $delay;
            foreach ($calificaciones as $cal) {
                // Determinar el color según si está aprobado o reprobado
                $is_approved = ($cal['calificacion'] >= 6);
                $badge_class = $is_approved ? 'bg-success' : 'bg-danger';
                $icon = $is_approved ? '✅' : '⚠️';

                $fecha = date('d/m/Y H:i', strtotime($cal['fecha_registro']));
                $nombre_completo = htmlspecialchars($cal['nombre'] . ' ' . $cal['apellidos']);

                echo "<div class='col-md-6 col-lg-4 col-xl-3 animate-fade-in' style='animation-delay: " . $cardDelay . "s;'>";
                echo "<div class='card h-100 border-0 shadow-sm student-card rounded-4 bg-transparent'>";
                echo "<div class='card-body p-4'>";

                echo "<div class='d-flex justify-content-between align-items-start mb-3'>";
                echo "<h6 class='card-title mb-0 fw-bold text-truncate pe-2' title='{$nombre_completo}'>{$nombre_completo}</h6>";
                echo "<span class='badge {$badge_class} grade-badge' title='Calificación'>{$icon} " . htmlspecialchars($cal['calificacion']) . "</span>";
                echo "</div>";

                echo "<div class='mb-3 text-body-secondary'>";
                echo "<div class='small mb-1'><i class='bi bi-person-badge opacity-75 me-1'></i> <strong>Matrícula:</strong> " . htmlspecialchars($cal['matricula']) . "</div>";
                echo "<div class='small mb-1'><i class='bi bi-book opacity-75 me-1'></i> <strong>Materia:</strong> <span class='text-primary'>" . htmlspecialchars($cal['nombre_materia']) . "</span></div>";
                echo "<div class='small mb-1'><i class='bi bi-calendar2-range opacity-75 me-1'></i> <strong>Periodo:</strong> " . htmlspecialchars($cal['periodo']) . "</div>";
                echo "</div>";

                echo "</div>"; // Fin card-body

                echo "<div class='card-footer border-top-0 pt-0 pb-3 px-4 bg-transparent d-flex justify-content-between align-items-center'>";
                echo "<small class='text-body-secondary mb-0'>📅 Registro: <span class='fw-medium'>{$fecha}</span></small>";
                echo "<a href='generar_boleta_pdf.php?id=" . $cal['id_alumno'] . "' target='_blank' class='btn btn-sm btn-outline-danger py-0 px-2 rounded-3' title='Generar Boleta en PDF'><i class='bi bi-file-pdf-fill me-1'></i>PDF</a>";
                echo "</div>";

                echo "</div>"; // Fin card estudiante
                echo "</div>"; // Fin col

                $cardDelay += 0.05; // Incremento súper rápido por tarjeta para el efecto dominó
            }

            echo "</div>"; // Fin row
            echo "</div>"; // Fin card-body del grupo
            echo "</div>"; // Fin card del grupo
        }
        echo "</div>"; // Fin div nivel
    }
}
else {
    echo "
    <div class='empty-state text-center p-5 mx-auto animate-fade-in' style='animation-delay: 0.2s; max-width: 600px;'>
        <div style='font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;'>📭</div>
        <h4 class='text-secondary fw-bold mb-3'>No hay calificaciones registradas aún</h4>
        <p class='text-muted mb-4'>Parece que no se han subido notas al sistema. Comienza capturando la primera calificación.</p>
        <a href='calificaciones.php' class='btn btn-primary btn-lg rounded-pill px-5 shadow-sm'>
            Capturar Ahora
        </a>
    </div>";
}

// Cerrar la conexión
$conexion->close();
?>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>