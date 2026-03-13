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

// Consultar calificaciones agrupadas por ciclo
$sql_calif = "SELECT m.clave_materia, m.nombre_materia, c.periodo, c.calificacion, c.fecha_registro,
                     ce.nombre_ciclo, ce.id_ciclo
              FROM calificaciones c 
              INNER JOIN materias m ON c.id_materia = m.id_materia 
              INNER JOIN ciclos_escolares ce ON c.id_ciclo = ce.id_ciclo
              WHERE c.id_alumno = $id_alumno 
              ORDER BY ce.id_ciclo DESC, c.periodo ASC, m.nombre_materia ASC";
$res_calif = $conexion->query($sql_calif);

$calificaciones_por_ciclo = [];
if ($res_calif && $res_calif->num_rows > 0) {
    while ($fila = $res_calif->fetch_assoc()) {
        $calificaciones_por_ciclo[$fila['nombre_ciclo']][] = $fila;
    }
}

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
        <a class="btn btn-outline-success btn-sm me-2 rounded-pill shadow-sm" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i class="bi bi-person-gear mb-1"></i> Mi Perfil</a>
        <button class="theme-toggle-btn me-3" id="btnThemeToggle" title="Cambiar Tema">
            <span id="themeIcon">🌙</span>
        </button>
        <a href="../auth/cerrar_sesion.php" class="btn btn-outline-danger btn-sm shadow-sm rounded-pill"><i class="bi bi-box-arrow-right me-1"></i>Salir</a>
    </div>

    <div class="boleta-container border">
        <div class="escuela-header">
            <h2 class="text-success fw-bold text-uppercase">SISTEMA ESCOLAR</h2>
            <h4>Historial Detallado - Portal de Alumnos</h4>
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

        <?php if (!empty($calificaciones_por_ciclo)): ?>
            <?php foreach ($calificaciones_por_ciclo as $nombre_ciclo => $calificaciones):
        $suma_ciclo = 0;
        $total_ciclo = 0;
?>
                <h4 class="text-secondary fw-bold mb-3 mt-5 border-bottom pb-2">
                    <i class="bi bi-calendar-range text-success me-2"></i> <?php echo htmlspecialchars($nombre_ciclo); ?>
                </h4>
                
                <div class="card shadow border-0 rounded-4 overflow-hidden animate-fade-in mb-3">
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
                                    <?php foreach ($calificaciones as $fila):
            $suma_ciclo += $fila['calificacion'];
            $total_ciclo++;
?>
                                        <tr>
                                            <td class='text-center'><?php echo htmlspecialchars($fila['clave_materia']); ?></td>
                                            <td><?php echo htmlspecialchars($fila['nombre_materia']); ?></td>
                                            <td class='text-center'><?php echo htmlspecialchars($fila['periodo']); ?></td>
                                            <?php $color = ($fila['calificacion'] < 6) ? 'text-danger fw-bold' : 'text-success fw-bold'; ?>
                                            <td class='text-center <?php echo $color; ?> fs-5'><?php echo htmlspecialchars($fila['calificacion']); ?></td>
                                            <td class='text-center text-muted small no-print'><?php echo date('d/m/Y', strtotime($fila['fecha_registro'])); ?></td>
                                        </tr>
                                    <?php
        endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php
        if ($total_ciclo > 0) {
            $promedio_ciclo = $suma_ciclo / $total_ciclo;
            $clase_promedio = ($promedio_ciclo < 6) ? 'text-danger' : 'text-success';
            echo "<div class='text-end mb-4'>Promedio del Ciclo: <span class='fw-bold $clase_promedio fs-4'>" . number_format($promedio_ciclo, 2) . "</span></div>";
        }
?>
            <?php
    endforeach; ?>
        <?php
else: ?>
            <div class="card shadow border-0 rounded-4 overflow-hidden animate-fade-in text-center p-5">
                <p class="text-muted">Aún no tienes calificaciones registradas en el sistema.</p>
            </div>
        <?php
endif; ?>

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
