<?php
session_start();
// Validar sesión de alumno
if (!isset($_SESSION['id_alumno'])) {
    header("Location: ../auth/login_alumno.php");
    exit();
}
include '../includes/conexion.php';

$id_alumno = $_SESSION['id_alumno'];

// Consultar datos del alumno
$sql_alumno = "SELECT matricula, nombre, apellidos, nivel, grado, grupo FROM alumnos WHERE id_alumno = $id_alumno";
$res_alumno = $conexion->query($sql_alumno);
$alumno = $res_alumno->fetch_assoc();

// Consultar calificaciones
$sql_calif = "SELECT m.clave_materia, m.nombre_materia, c.periodo, c.calificacion, c.fecha_registro 
              FROM calificaciones c 
              INNER JOIN materias m ON c.id_materia = m.id_materia 
              WHERE c.id_alumno = $id_alumno 
              ORDER BY c.periodo ASC, m.nombre_materia ASC";
$res_calif = $conexion->query($sql_calif);

$suma_calificaciones = 0;
$total_materias = 0;

// Consultar resumen de asistencias
$sql_asistencias = "SELECT m.nombre_materia, a.fecha, a.estado 
                    FROM asistencias a 
                    INNER JOIN materias m ON a.id_materia = m.id_materia 
                    WHERE a.id_alumno = $id_alumno 
                    ORDER BY a.fecha DESC, m.nombre_materia ASC";
$res_asistencias = $conexion->query($sql_asistencias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Calificaciones - <?php echo htmlspecialchars($alumno['matricula']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/student_portal.css">
    <link rel="stylesheet" href="../assets/css/components.css">
</head>
<body class="student-portal">

<div class="container">
    <div class="d-flex justify-content-end mb-3 no-print align-items-center">
        <span class="me-3 fw-bold text-success">👤 <?php echo htmlspecialchars($_SESSION['nombre_alumno']); ?></span>
        <button class="theme-toggle-btn me-3" id="btnThemeToggle" title="Cambiar Tema">
            <span id="themeIcon">🌙</span>
        </button>
        <a href="generar_boleta_pdf.php?id=<?php echo $id_alumno; ?>" target="_blank" class="btn btn-outline-info me-2 shadow-sm rounded-pill">📄 Descargar PDF</a>
        <button onclick="window.print()" class="btn btn-outline-primary me-2 shadow-sm rounded-pill">🖨️ Imprimir</button>
        <a href="../auth/cerrar_sesion_alumno.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>

    <div class="boleta-container border">
        <div class="escuela-header">
            <h2 class="text-success fw-bold text-uppercase">SISTEMA ESCOLAR</h2>
            <h4>Portal de Alumnos - Mis Calificaciones</h4>
        </div>

        <div class="datos-alumno row mb-4">
            <div class="col-md-8">
                <p><strong>Alumno:</strong> <?php echo htmlspecialchars($alumno['apellidos'] . " " . $alumno['nombre']); ?></p>
                <p><strong>Matrícula:</strong> <?php echo htmlspecialchars($alumno['matricula']); ?></p>
            </div>
            <div class="col-md-4 text-md-end">
                <p><strong>Nivel:</strong> <?php echo htmlspecialchars($alumno['nivel']); ?></p>
                <p><strong>Grado y Grupo:</strong> <?php echo htmlspecialchars($alumno['grado'] . "º " . $alumno['grupo']); ?></p>
            </div>
        </div>

        <div class="card shadow border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                        <th>Clave</th>
                        <th>Materia</th>
                        <th>Periodo</th>
                        <th>Calificación</th>
                        <th class="no-print">Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if ($res_calif && $res_calif->num_rows > 0) {
    while ($fila = $res_calif->fetch_assoc()) {
        $suma_calificaciones += $fila['calificacion'];
        $total_materias++;

        echo "<tr>";
        echo "<td class='text-center'>" . htmlspecialchars($fila['clave_materia']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['nombre_materia']) . "</td>";
        echo "<td class='text-center'>" . htmlspecialchars($fila['periodo']) . "</td>";

        $color = ($fila['calificacion'] < 6) ? 'text-danger fw-bold' : 'text-success fw-bold';
        echo "<td class='text-center $color fs-5'>" . htmlspecialchars($fila['calificacion']) . "</td>";
        echo "<td class='text-center text-muted small no-print'>" . date('d/m/Y', strtotime($fila['fecha_registro'])) . "</td>";
        echo "</tr>";
    }
}
else {
    echo "<tr><td colspan='5' class='text-center py-4'>Aún no tienes calificaciones registradas en el sistema.</td></tr>";
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php
if ($total_materias > 0) {
    $promedio = $suma_calificaciones / $total_materias;
    $clase_promedio = ($promedio < 6) ? 'text-danger' : 'text-success';
    echo "<h4 class='text-end mt-4 mb-5'>Promedio General: <span class='fw-bold $clase_promedio fs-3'>" . number_format($promedio, 2) . "</span></h4>";
}
?>

        <h3 class="text-secondary fw-bold mb-4 mt-5"><i class="bi bi-calendar-check text-primary me-2"></i> Historial de Asistencias</h3>
        <div class="card shadow border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.3s;">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark text-center" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>Fecha</th>
                                <th>Materia</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
if ($res_asistencias && $res_asistencias->num_rows > 0) {
    while ($asist = $res_asistencias->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='text-center'>" . date('d/m/Y', strtotime($asist['fecha'])) . "</td>";
        echo "<td>" . htmlspecialchars($asist['nombre_materia']) . "</td>";

        $badge_class = 'bg-success';
        if ($asist['estado'] === 'Falta')
            $badge_class = 'bg-danger';
        if ($asist['estado'] === 'Retardo')
            $badge_class = 'bg-warning text-dark';

        echo "<td class='text-center'><span class='badge $badge_class rounded-pill px-3'>" . htmlspecialchars($asist['estado']) . "</span></td>";
        echo "</tr>";
    }
}
else {
    echo "<tr><td colspan='3' class='text-center py-4 text-muted'>Aún no tienes registros de asistencia.</td></tr>";
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 pt-5 text-muted small no-print">
            <p>Este documento es únicamente de carácter informativo. Para un reporte oficial firmado y sellado acude a la dirección escolar.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
