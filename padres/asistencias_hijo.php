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

        <!-- Navbar Superior Estilo Floating -->
        <nav class="navbar navbar-expand-lg navbar-dark floating-nav floating-nav-success mx-auto mb-4 border shadow-sm animate-fade-in">
            <div class="container-fluid px-2">
                <div class="d-flex align-items-center w-100 justify-content-between">
                    <div class="d-flex align-items-center">
                        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-circle me-3 shadow-sm premium-icon-btn d-flex justify-content-center align-items-center" title="Volver al Inicio">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm premium-icon-btn"
                             style="font-size: 1.2rem;">
                             <?php echo strtoupper(substr($alumno['nombre'], 0, 1)); ?>
                        </div>
                        <div>
                            <h5 class="m-0 fw-bold text-white">Asistencias - <?php echo htmlspecialchars($alumno['nombre']); ?></h5>
                            <span class="text-white opacity-75 small fw-medium"><?php echo htmlspecialchars($alumno['nivel'] . ' | ' . $alumno['grado'] . 'º ' . $alumno['grupo']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <?php if ($res_materias && $res_materias->num_rows > 0): ?>
            <div class="row g-4">
                <?php 
                $delay = 0.2;
                while ($fila = $res_materias->fetch_assoc()): 
                    $total = $fila['total_clases'];
                    $presentes = $fila['presentes'];
                    $retardos = $fila['retardos'];
                    $faltas = $fila['faltas'];
                    
                    // Cálculo de porcentaje: (Presentes + Mitad de Retardos) / Total
                    $porcentaje = ($total > 0) ? (($presentes + ($retardos * 0.5)) / $total) * 100 : 0;
                    
                    $color_class = 'primary';
                    if ($porcentaje < 80) $color_class = 'warning';
                    if ($porcentaje < 60) $color_class = 'danger';
                ?>
                    <div class="col-md-6 col-lg-4 animate-fade-in" style="animation-delay: <?php echo $delay; ?>s;">
                        <div class="card h-100 border-0 shadow-sm interactive-card border-start border-4 border-<?php echo $color_class; ?> rounded-4 p-3 position-relative overflow-hidden">
                            
                            <!-- Icono de fondo marca de agua -->
                            <i class="bi bi-journal-check position-absolute text-<?php echo $color_class; ?> opacity-10" 
                               style="font-size: 6rem; right: -15px; bottom: -15px; z-index: 0;"></i>
                            
                            <div class="card-body position-relative z-1">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title fw-bold text-truncate pe-2 m-0 text-body" title="<?php echo htmlspecialchars($fila['nombre_materia']); ?>">
                                        <?php echo htmlspecialchars($fila['nombre_materia']); ?>
                                    </h5>
                                    <span class="badge bg-<?php echo ($color_class == 'warning') ? 'warning text-dark' : $color_class; ?> fs-6 rounded-pill px-3 py-2 shadow-sm">
                                        <?php echo number_format($porcentaje, 1); ?>%
                                    </span>
                                </div>
                                
                                <div class="row text-center mt-4">
                                    <div class="col-4 border-end">
                                        <div class="fs-4 fw-bold text-success"><?php echo $presentes; ?></div>
                                        <div class="small text-muted"><i class="bi bi-check-lg"></i> Asist.</div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="fs-4 fw-bold text-warning text-dark"><?php echo $retardos; ?></div>
                                        <div class="small text-muted"><i class="bi bi-clock-history"></i> Ret.</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="fs-4 fw-bold text-danger"><?php echo $faltas; ?></div>
                                        <div class="small text-muted"><i class="bi bi-x-lg"></i> Faltas</div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <span class="text-secondary small fw-medium">
                                        <i class="bi bi-calendar3 me-1"></i> Clases impartidas en la materia: <?php echo $total; ?>
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
            
            <div class="col-12 mt-5 animate-fade-in" style="animation-delay: 1s;">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5 text-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                         <h6 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-primary me-2"></i> Reglamento de Asistencia</h6>
                         <p class="text-muted mb-0 mx-auto" style="max-width: 600px;">
                            Le recordamos que el porcentaje de asistencia se calcula incluyendo los retardos. <strong>Dos retardos</strong> equivalen a una falta oficial. Mantener la asistencia del alumno por encima del 80% es requisito fundamental para la acreditación de la materia según el reglamento escolar.
                         </p>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <div class='text-center p-5 mx-auto animate-fade-in' style='animation-delay: 0.2s; max-width: 600px; background: var(--bs-tertiary-bg); border-radius: 12px; border: 2px dashed #ced4da;'>
                <div style='font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;'>📭</div>
                <h4 class='text-secondary fw-bold mb-3'>Sin registro de asistencias</h4>
                <p class='text-muted mb-0'>Aún no se ha registrado ninguna asistencia en las materias de este alumno durante el ciclo escolar vigente.</p>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
