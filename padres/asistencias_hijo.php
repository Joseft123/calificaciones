<?php
session_start();

// Validar sesión de Padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../auth/login_padre.php");
    exit();
}
include '../includes/conexion.php';

$id_padre = intval($_SESSION['id_padre']);
$id_hijo_sel = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validar que el hijo pertenece a este padre
$sql_val = "SELECT id_alumno FROM padre_alumno WHERE id_padre = $id_padre AND id_alumno = $id_hijo_sel";
$res_val = $conexion->query($sql_val);

if (!$res_val || $res_val->num_rows === 0) {
    echo "Acceso denegado o alumno no encontrado.";
    exit();
}

// Consultar datos básicos del hijo
$sql_alumno = "SELECT matricula, nombre, apellidos, nivel, grado, grupo FROM alumnos WHERE id_alumno = $id_hijo_sel";
$res_alumno = $conexion->query($sql_alumno);
$alumno = $res_alumno->fetch_assoc();

// Consultar materias y asistencias por materia
$sql_materias = "
    SELECT m.id_materia, m.nombre_materia, 
           SUM(CASE WHEN a.estado='Presente' THEN 1 ELSE 0 END) as presentes,
           SUM(CASE WHEN a.estado='Retardo' THEN 1 ELSE 0 END) as retardos,
           SUM(CASE WHEN a.estado='Falta' THEN 1 ELSE 0 END) as faltas,
           COUNT(a.id_asistencia) as total_clases
    FROM asistencias a
    INNER JOIN materias m ON a.id_materia = m.id_materia
    WHERE a.id_alumno = $id_hijo_sel
    GROUP BY m.id_materia
    ORDER BY m.nombre_materia ASC
";
$res_materias = $conexion->query($sql_materias);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias de <?php echo htmlspecialchars($alumno['nombre']); ?> - Portal Familiar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/components.css">
</head>

<body class="bg-body-tertiary">

    <div class="container py-4">

        <!-- Botón Volver (Estilizado) -->
        <div class="mb-4 animate-fade-in">
            <a href="dashboard.php" class="btn btn-link text-decoration-none text-secondary p-0">
                <i class="bi bi-arrow-left me-2"></i>Volver al Resumen Familiar
            </a>
        </div>

        <!-- Header Alumno (Premium Gradient Card) -->
        <div class="card shadow border-0 rounded-4 overflow-hidden mb-5 animate-fade-in"
            style="background: linear-gradient(135deg, #198754 0%, #157347 100%); color: white;">
            <div class="card-body p-4 p-md-5 position-relative">
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-white text-success rounded-circle d-flex justify-content-center align-items-center fs-2 fw-bold shadow-sm me-3"
                                style="width: 65px; height: 65px;">
                                <?php echo strtoupper(substr($alumno['nombre'], 0, 1)); ?>
                            </div>
                            <div>
                                <h1 class="display-6 fw-bold mb-0"><?php echo htmlspecialchars($alumno['nombre']); ?></h1>
                                <p class="fs-5 opacity-75 mb-0"><?php echo htmlspecialchars($alumno['apellidos']); ?></p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-2 fw-normal fs-6">
                                <i class="bi bi-building me-1"></i><?php echo $alumno['nivel']; ?>
                            </span>
                            <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-2 fw-normal fs-6">
                                <i class="bi bi-people-fill me-1"></i><?php echo $alumno['grado'] . 'º ' . $alumno['grupo']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Icono de fondo decorativo -->
                <i class="bi bi-calendar-check text-white opacity-10"
                    style="font-size: 10rem; position: absolute; right: 20px; top: -30px; z-index: 1;"></i>
            </div>
        </div>

        <h4 class="fw-bold mb-4 text-body animate-fade-in" style="animation-delay: 0.1s;">
            <i class="bi bi-journal-check text-success me-2"></i>Asistencia por Materia
        </h4>

        <?php if ($res_materias && $res_materias->num_rows > 0): ?>
            <div class="row g-4 mb-5">
                <?php 
                $delay = 0.2;
                while ($fila = $res_materias->fetch_assoc()): 
                    $total = $fila['total_clases'];
                    $presentes = $fila['presentes'];
                    $retardos = $fila['retardos'];
                    $faltas = $fila['faltas'];
                    $porcentaje = ($total > 0) ? (($presentes + ($retardos * 0.5)) / $total) * 100 : 0;
                    
                    $color_class = 'success';
                    if ($porcentaje < 85) $color_class = 'warning';
                    if ($porcentaje < 75) $color_class = 'danger';
                ?>
                    <div class="col-md-6 col-lg-4 animate-fade-in" style="animation-delay: <?php echo $delay; ?>s;">
                        <div class="card h-100 border-0 shadow-sm interactive-card rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <h5 class="fw-bold text-body mb-1 text-truncate" title="<?php echo htmlspecialchars($fila['nombre_materia']); ?>">
                                        <?php echo htmlspecialchars($fila['nombre_materia']); ?>
                                    </h5>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-secondary small fw-medium">Asistencia General</span>
                                        <span class="fw-bold text-<?php echo $color_class; ?>"><?php echo number_format($porcentaje, 1); ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px; background-color: rgba(0,0,0,0.05);">
                                        <div class="progress-bar bg-<?php echo $color_class; ?> rounded-pill" 
                                             role="progressbar" 
                                             style="width: <?php echo $porcentaje; ?>%" 
                                             aria-valuenow="<?php echo $porcentaje; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                </div>
                                
                                <div class="row g-0 text-center bg-light rounded-4 p-3 font-monospace">
                                    <div class="col-4 border-end">
                                        <div class="small text-muted mb-1" style="font-size: 0.75rem;">PR</div>
                                        <div class="fw-bold text-success fs-5"><?php echo $presentes; ?></div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="small text-muted mb-1" style="font-size: 0.75rem;">RE</div>
                                        <div class="fw-bold text-warning fs-5"><?php echo $retardos; ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted mb-1" style="font-size: 0.75rem;">FA</div>
                                        <div class="fw-bold text-danger fs-5"><?php echo $faltas; ?></div>
                                    </div>
                                </div>
                                
                                <div class="mt-3 text-center">
                                    <span class="text-muted small">
                                        <i class="bi bi-info-circle me-1"></i><?php echo $total; ?> clases registradas
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                    $delay += 0.05;
                endwhile; 
                ?>
            </div>
            
            <!-- Reglamento Box (Premium Alert) -->
            <div class="col-12 animate-fade-in" style="animation-delay: 0.6s;">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background-color: rgba(25, 135, 84, 0.03); border-left: 4px solid #198754 !important;">
                    <div class="card-body p-4 p-md-5">
                         <div class="d-flex align-items-center mb-3">
                             <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 me-3">
                                 <i class="bi bi-info-lg"></i>
                             </div>
                             <h6 class="fw-bold m-0 text-success">Reglamento de Asistencia Escolar</h6>
                         </div>
                         <p class="text-muted mb-0 lh-lg">
                            Le recordamos que el porcentaje de asistencia se calcula incluyendo los retardos. <strong>Dos retardos</strong> equivalen a una falta oficial en el registro mensual. Mantener la asistencia del alumno por encima del <strong>80%</strong> es un requisito fundamental para la acreditación académica de cada materia según el reglamento vigente de la institución.
                         </p>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center animate-fade-in">
                <div class="mb-4">
                    <i class="bi bi-calendar-x text-muted opacity-25" style="font-size: 5rem;"></i>
                </div>
                <h4 class="text-secondary fw-bold mb-3">Sin registro de asistencias</h4>
                <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">
                    Aún no se ha registrado ninguna asistencia en las materias de este alumno durante el ciclo escolar vigente. Por favor, consulte más tarde.
                </p>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
<?php $conexion->close(); ?>
