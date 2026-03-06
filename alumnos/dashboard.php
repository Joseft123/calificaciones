<?php
session_start();

// Validar sesión de alumno
if (!isset($_SESSION['id_alumno'])) {
    header("Location: ../auth/login_alumno.php");
    exit();
}
include '../includes/conexion.php';

$id_alumno = intval($_SESSION['id_alumno']);

// 1. Consultar datos básicos del alumno
$sql_alumno = "SELECT matricula, nombre, apellidos, nivel, grado, grupo FROM alumnos WHERE id_alumno = $id_alumno";
$res_alumno = $conexion->query($sql_alumno);
$alumno = $res_alumno->fetch_assoc();

// 2. Consultar Promedio General y Total de Materias
$sql_calif = "SELECT COUNT(id_calificacion) as total_materias, AVG(calificacion) as promedio 
              FROM calificaciones 
              WHERE id_alumno = $id_alumno";
$res_calif = $conexion->query($sql_calif);
$stats_calif = $res_calif->fetch_assoc();
$promedio = $stats_calif['promedio'] ? number_format($stats_calif['promedio'], 2) : '0.00';
$total_materias = $stats_calif['total_materias'];

// 3. Consultar Resumen de Asistencias (Presente, Falta, Retardo)
$sql_asist = "SELECT estado, COUNT(*) as total 
              FROM asistencias 
              WHERE id_alumno = $id_alumno 
              GROUP BY estado";
$res_asist = $conexion->query($sql_asist);
$asistencias_stats = ['Presente' => 0, 'Falta' => 0, 'Retardo' => 0];
if ($res_asist && $res_asist->num_rows > 0) {
    while ($row = $res_asist->fetch_assoc()) {
        $asistencias_stats[$row['estado']] = (int) $row['total'];
    }
}

// 4. Consultar comunicados (Solo Todos o Alumnos)
$sql_avisos = "SELECT c.titulo, c.mensaje, c.destinatario, c.fecha_publicacion, u.nombre AS autor 
               FROM comunicados c
               INNER JOIN usuarios u ON c.id_autor = u.id_usuario
               WHERE c.destinatario IN ('Todos', 'Alumnos')
               ORDER BY c.fecha_publicacion DESC LIMIT 4";
$res_avisos = $conexion->query($sql_avisos);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Alumno - <?php echo htmlspecialchars($alumno['matricula']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/student_portal.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <script src="../assets/js/main.js"></script>
</head>

<body class="student-portal bg-body-tertiary">

    <div class="container py-4">

        <!-- Navbar Superior -->
        <div class="d-flex justify-content-between mb-4 align-items-center bg-body p-3 rounded-4 shadow-sm animate-fade-in"
            style="animation-delay: 0.1s;">
            <div class="d-flex align-items-center">
                <div class="bg-success text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                    style="width: 45px; height: 45px; font-size: 1.5rem;">
                    🎓
                </div>
                <div>
                    <h5 class="m-0 fw-bold text-body">Portal del Alumno</h5>
                    <span
                        class="text-muted small"><?php echo htmlspecialchars($alumno['nivel'] . ' | ' . $alumno['grado'] . 'º ' . $alumno['grupo']); ?></span>
                </div>
            </div>
            <div>
                <span class="me-3 fw-bold text-success d-none d-md-inline">👋 Hola,
                    <?php echo htmlspecialchars($alumno['nombre']); ?></span>
                <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" id="btnThemeToggle"
                    title="Cambiar Tema" style="width: 32px; height: 32px; padding: 0;">
                    <span id="themeIcon">🌙</span>
                </button>
                <a href="../auth/cerrar_sesion_alumno.php"
                    class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm"><i
                        class="bi bi-box-arrow-right me-1"></i>Salir</a>
            </div>
        </div>

        <div class="row g-4 mb-4">

            <!-- Lado Izquierdo: KPIs y Accesos -->
            <div class="col-lg-4 d-flex flex-column gap-4">

                <!-- Tarjeta KPI Principal: Promedio -->
                <div class="card bg-success text-white rounded-4 border-0 shadow-sm animate-fade-in"
                    style="animation-delay: 0.2s;">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        <i class="bi bi-award-fill position-absolute text-white opacity-25"
                            style="font-size: 8rem; right: -20px; top: -10px;"></i>
                        <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1px;">Promedio General</h6>
                        <h1 class="display-3 fw-bold mb-0"><?php echo $promedio; ?></h1>
                        <p class="mb-0 mt-2 opacity-75">Basado en <?php echo $total_materias; ?> materias evaluadas.</p>
                    </div>
                </div>

                <!-- Resumen de Asistencias -->
                <div class="card shadow-sm rounded-4 border-0 animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-secondary mb-3"><i
                                class="bi bi-calendar-check-fill text-primary me-2"></i>Récord de Asistencias</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted"><i
                                    class="bi bi-check-circle-fill text-success me-2"></i>Asistencias</span>
                            <span class="fw-bold fs-5"><?php echo $asistencias_stats['Presente']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted"><i
                                    class="bi bi-exclamation-circle-fill text-warning me-2"></i>Retardos</span>
                            <span class="fw-bold fs-5"><?php echo $asistencias_stats['Retardo']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="bi bi-x-circle-fill text-danger me-2"></i>Faltas</span>
                            <span class="fw-bold fs-5"><?php echo $asistencias_stats['Falta']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-grid gap-3 animate-fade-in" style="animation-delay: 0.4s;">
                    <a href="../calificaciones/mis_calificaciones.php"
                        class="btn btn-primary btn-lg rounded-4 shadow-sm d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-journal-text me-2"></i>Historial Detallado</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="../calificaciones/generar_boleta_pdf.php?id=<?php echo $id_alumno; ?>" target="_blank"
                        class="btn btn-outline-secondary rounded-4 shadow-sm d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Descargar Boleta (PDF)</span>
                        <i class="bi bi-download"></i>
                    </a>
                </div>

            </div>

            <!-- Lado Derecho: Tablón de Avisos -->
            <div class="col-lg-8 animate-fade-in" style="animation-delay: 0.5s;">
                <div class="card shadow-sm rounded-4 border-0 h-100">
                    <div
                        class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold m-0"><i class="bi bi-megaphone-fill text-info me-2"></i>Avisos y Comunicados
                        </h5>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <?php if ($res_avisos && $res_avisos->num_rows > 0): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php while ($aviso = $res_avisos->fetch_assoc()): ?>
                                    <div
                                        class="p-4 border rounded-4 bg-info bg-opacity-10 border-info border-opacity-25 shadow-sm position-relative">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="fw-bold text-body m-0"><?php echo htmlspecialchars($aviso['titulo']); ?>
                                            </h5>
                                            <span class="badge bg-primary bg-opacity-75 rounded-pill px-3 py-2"><i
                                                    class="bi bi-clock me-1"></i><?php echo date('d M', strtotime($aviso['fecha_publicacion'])); ?></span>
                                        </div>
                                        <p class="text-muted mb-3" style="font-size: 0.95rem;">
                                            <?php echo nl2br(htmlspecialchars($aviso['mensaje'])); ?>
                                        </p>
                                        <div class="small text-body-secondary fw-medium">
                                            <i class="bi bi-person-fill text-info me-1"></i>Enviado por:
                                            <?php echo htmlspecialchars($aviso['autor']); ?>
                                        </div>
                                    </div>
                                    <?php
                                endwhile; ?>
                            </div>
                            <?php
                        else: ?>
                            <div class="text-center p-5 mx-auto">
                                <i class="bi bi-inbox text-muted opacity-25" style="font-size: 4rem;"></i>
                                <h5 class="text-secondary fw-bold mt-3">Sin avisos recientes</h5>
                                <p class="text-muted">No hay comunicados o alertas publicadas en este momento.</p>
                            </div>
                            <?php
                        endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>