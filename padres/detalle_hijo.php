<?php
session_start();

// Validar inicio de sesiÃ³n del Padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../auth/login_padre.php");
    exit();
}

include '../includes/conexion.php';
include '../includes/funciones_ciclo.php';

$id_padre = intval($_SESSION['id_padre']);
$id_ciclo_actual = getCicloActivo($conexion);

// Obtener cantidad de mensajes no leÃ­dos
$unread_mensajes_padre = 0;
$res_unread = $conexion->query("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = $id_padre AND tipo_destinatario = 'Padre' AND leido = 0");
if ($res_unread) {
    $unread_mensajes_padre = $res_unread->fetch_assoc()['total'];
}

$id_hijo = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validar que el hijo solicitado pertenezca al padre logueado
$sql_valida = "SELECT a.nombre, a.apellidos, a.nivel, a.grado, a.grupo 
               FROM alumnos a
               INNER JOIN padre_alumno pa ON a.id_alumno = pa.id_alumno
               WHERE a.id_alumno = $id_hijo AND pa.id_padre = $id_padre";
$res_valida = $conexion->query($sql_valida);

if (!$res_valida || $res_valida->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}
$hijo = $res_valida->fetch_assoc();

// Obtener todas las materias del ciclo actual asignadas a su nivel/grado
$sql_materias = "SELECT DISTINCT m.id_materia, m.nombre_materia 
                 FROM materias m
                 INNER JOIN docente_materia_grupo dmg ON m.id_materia = dmg.id_materia
                 WHERE dmg.nivel = '{$hijo['nivel']}' AND dmg.grado = {$hijo['grado']} AND dmg.grupo = '{$hijo['grupo']}' AND dmg.id_ciclo = $id_ciclo_actual";
$res_materias = $conexion->query($sql_materias);
$materias = [];
if ($res_materias) {
    while ($mat = $res_materias->fetch_assoc()) {
        // Obtener la calificaciÃ³n de esta materia (bloque/periodo 1 por ahora o promedio)
        $sql_calif = "SELECT calificacion FROM calificaciones WHERE id_alumno = $id_hijo AND id_materia = {$mat['id_materia']} ORDER BY periodo DESC LIMIT 1";
        $res_calif = $conexion->query($sql_calif);
        $mat['calificacion'] = ($res_calif && $res_calif->num_rows > 0) ? round($res_calif->fetch_assoc()['calificacion'], 2) : 'S/C';
        $materias[] = $mat;
    }
}

// Obtener el historial completo de asistencias del hijo
$sql_asistencias = "SELECT asi.fecha, asi.estado, m.nombre_materia 
                    FROM asistencias asi
                    INNER JOIN materias m ON asi.id_materia = m.id_materia
                    WHERE asi.id_alumno = $id_hijo
                    ORDER BY asi.fecha DESC";
$res_asistencias = $conexion->query($sql_asistencias);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de <?php echo htmlspecialchars($hijo['nombre']); ?> - Portal Familiar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="bg-body-tertiary">

    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-arrow-left me-2"></i>Regresar al
                Resumen</a>
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3">
                    <a class="nav-link text-white fw-bold" href="mensajes.php"><i class="bi bi-envelope-fill me-1"></i>
                        Mensajes
                        <?php if ($unread_mensajes_padre > 0): ?>
                            <span
                                class="badge bg-danger rounded-pill ms-1 shadow-sm"><?php echo $unread_mensajes_padre; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm me-2" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i class="bi bi-person-gear mb-1"></i></a>
                    <button class="btn btn-outline-light btn-sm" id="btnThemeToggle" title="Modo Visual">
                        <span id="themeIcon">🌙</span>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Header Alumno -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 bg-body animate-fade-in"
            style="animation-delay: 0.1s;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center fs-1 fw-bold shadow-sm me-4"
                        style="width: 80px; height: 80px;">
                        <?php echo strtoupper(substr($hijo['nombre'], 0, 1)); ?>
                    </div>
                    <div>
                        <h2 class="fw-bold m-0 text-body mb-1">
                            <?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellidos']); ?></h2>
                        <div class="d-flex flex-wrap gap-2 text-muted">
                            <span class="badge bg-secondary rounded-pill px-3 fw-normal fs-6 text-white"><i
                                    class="bi bi-building me-1"></i><?php echo $hijo['nivel']; ?></span>
                            <span class="badge bg-info text-dark rounded-pill px-3 fw-normal fs-6"><i
                                    class="bi bi-people-fill me-1"></i><?php echo $hijo['grado'] . 'Âº ' . $hijo['grupo']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PestaÃ±as de NavegaciÃ³n -->
        <ul class="nav nav-pills mb-4 animate-fade-in" id="pills-tab" role="tablist" style="animation-delay: 0.2s;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4 rounded-pill" id="pills-calif-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-calif" type="button" role="tab">
                    <i class="bi bi-card-checklist me-2"></i>Calificaciones
                </button>
            </li>
            <li class="nav-item ms-2" role="presentation">
                <button class="nav-link px-4 rounded-pill" id="pills-asist-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-asist" type="button" role="tab">
                    <i class="bi bi-calendar3 me-2"></i>Historial de Asistencia
                </button>
            </li>
        </ul>

        <!-- Contenido de PestaÃ±as -->
        <div class="tab-content animate-fade-in" id="pills-tabContent" style="animation-delay: 0.3s;">

            <!-- Tab Calificaciones -->
            <div class="tab-pane fade show active" id="pills-calif" role="tabpanel">
                <div class="row g-4">
                    <?php if (count($materias) > 0): ?>
                        <?php foreach ($materias as $m): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card shadow-sm border-0 rounded-4 h-100 hover-scale">
                                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3 text-center">
                                                <i class="bi bi-journal-text fs-4" style="line-height: .5;"></i>
                                            </div>
                                            <h6 class="fw-bold m-0 text-body text-truncate" style="max-width: 150px;"
                                                title="<?php echo htmlspecialchars($m['nombre_materia']); ?>">
                                                <?php echo htmlspecialchars($m['nombre_materia']); ?>
                                            </h6>
                                        </div>
                                        <div class="text-end">
                                            <div
                                                class="fs-3 fw-bold <?php echo ($m['calificacion'] !== 'S/C' && $m['calificacion'] < 6) ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo $m['calificacion']; ?>
                                            </div>
                                            <div class="text-muted"
                                                style="font-size: 0.70rem; text-transform: uppercase; letter-spacing: 1px;">
                                                EvaluaciÃ³n</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="card shadow-sm border-0 rounded-4 p-5 text-center">
                                <i class="bi bi-folder-x text-muted opacity-50 mb-3" style="font-size: 3rem;"></i>
                                <h5 class="text-body fw-bold">Sin Materias Asignadas</h5>
                                <p class="text-muted m-0">El alumno no cuenta con materias evaluadas en el ciclo actual.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Asistencias -->
            <div class="tab-pane fade" id="pills-asist" role="tabpanel">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4 py-3">Fecha</th>
                                        <th class="py-3">Materia</th>
                                        <th class="text-center pe-4 py-3">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($res_asistencias && $res_asistencias->num_rows > 0): ?>
                                        <?php while ($asist = $res_asistencias->fetch_assoc()):
                                            $badgeClass = 'bg-success';
                                            if ($asist['estado'] == 'Falta')
                                                $badgeClass = 'bg-danger';
                                            if ($asist['estado'] == 'Retardo')
                                                $badgeClass = 'bg-warning text-dark';
                                            ?>
                                            <tr>
                                                <td class="ps-4 fw-medium">
                                                    <?php echo date('d/m/Y', strtotime($asist['fecha'])); ?></td>
                                                <td class="text-muted"><?php echo htmlspecialchars($asist['nombre_materia']); ?>
                                                </td>
                                                <td class="text-center pe-4">
                                                    <span
                                                        class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2 fw-medium">
                                                        <?php echo $asist['estado']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted p-5">
                                                <i class="bi bi-calendar-check opacity-50 mb-2"
                                                    style="font-size: 2rem; display: block;"></i>
                                                El alumno no tiene registros de asistencia.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>
<?php $conexion->close(); ?>
