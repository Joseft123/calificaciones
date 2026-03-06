<?php
session_start();

if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

include '../includes/conexion.php';
include '../includes/funciones_ciclo.php';

$id_docente = intval($_SESSION['id_docente']);
$id_ciclo = getCicloActivo($conexion);

if (!isset($_GET['id_alumno']) || !is_numeric($_GET['id_alumno'])) {
    die("ID de alumno no válido.");
}

$id_alumno = intval($_GET['id_alumno']);

// 1. Validar que el alumno pertenezca a un grupo que imparte el docente y traer sus datos
$sql_alumno = "SELECT DISTINCT a.id_alumno, a.matricula, a.nombre, a.apellidos, a.nivel, a.grado, a.grupo 
               FROM alumnos a
               INNER JOIN docente_materia_grupo dmg ON a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo
               WHERE dmg.id_docente = $id_docente AND a.id_alumno = $id_alumno AND dmg.id_ciclo = $id_ciclo
               LIMIT 1";

$res_alumno = $conexion->query($sql_alumno);

if (!$res_alumno || $res_alumno->num_rows == 0) {
    // Si no está en su grupo, no puede ver el perfil
    die("No tienes permiso para ver el perfil de este alumno o el alumno no existe.");
}

$alumno = $res_alumno->fetch_assoc();

// 2. Obtener materias que ESTE docente le imparte a ESTE alumno
$sql_materias_docente = "SELECT m.id_materia, m.nombre_materia 
                         FROM materias m
                         INNER JOIN docente_materia_grupo dmg ON m.id_materia = dmg.id_materia
                         WHERE dmg.id_docente = $id_docente 
                         AND dmg.id_ciclo = $id_ciclo
                         AND dmg.nivel = '{$alumno['nivel']}' 
                         AND dmg.grado = {$alumno['grado']} 
                         AND dmg.grupo = '{$alumno['grupo']}'";

$res_mats = $conexion->query($sql_materias_docente);
$materias = [];
$ids_materias = [];
if ($res_mats && $res_mats->num_rows > 0) {
    while ($m = $res_mats->fetch_assoc()) {
        $materias[] = $m;
        $ids_materias[] = $m['id_materia'];
    }
}

// 3. Obtener el historial de Asistencias SÓLO de las materias que da este docente
$asistencias_stats = ['Presente' => 0, 'Falta' => 0, 'Retardo' => 0];
if (!empty($ids_materias)) {
    $ids_in = implode(',', $ids_materias);
    $sql_asist = "SELECT estado, COUNT(*) as total 
                  FROM asistencias 
                  WHERE id_docente = $id_docente AND id_alumno = $id_alumno AND id_materia IN ($ids_in) AND id_ciclo = $id_ciclo
                  GROUP BY estado";
    $res_asist = $conexion->query($sql_asist);
    if ($res_asist && $res_asist->num_rows > 0) {
        while ($row = $res_asist->fetch_assoc()) {
            $asistencias_stats[$row['estado']] = (int)$row['total'];
        }
    }
}

// 4. Obtener las calificaciones SÓLO de las materias que da este docente
$historial_notas = [];
if (!empty($ids_materias)) {
    $ids_in = implode(',', $ids_materias);
    $sql_notas = "SELECT c.periodo, c.calificacion, c.fecha_registro, m.nombre_materia 
                  FROM calificaciones c
                  INNER JOIN materias m ON c.id_materia = m.id_materia
                  WHERE c.id_alumno = $id_alumno AND c.id_materia IN ($ids_in) AND c.id_ciclo = $id_ciclo
                  ORDER BY m.nombre_materia ASC, c.periodo ASC";
    $res_notas = $conexion->query($sql_notas);
    if ($res_notas && $res_notas->num_rows > 0) {
        while ($n = $res_notas->fetch_assoc()) {
            $historial_notas[] = $n;
        }
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="mb-4 d-flex align-items-center animate-fade-in" style="animation-delay: 0.1s;">
    <a href="mis_alumnos.php" class="btn btn-outline-secondary btn-sm rounded-circle me-3" style="width: 38px; height: 38px; line-height: 1.5;" title="Volver"><i class="bi bi-arrow-left"></i></a>
    <h2 class="text-primary m-0 fw-bold">📄 Perfil del Alumno</h2>
</div>

<div class="row g-4 mb-5">
    
    <!-- Tarjeta Principal: Info del Estudiante -->
    <div class="col-lg-4 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="card shadow rounded-4 border-0 h-100 text-center">
            <div class="card-body p-5">
                <div class="bg-primary bg-opacity-10 text-primary mx-auto rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                    <i class="bi bi-person-bounding-box" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']); ?></h4>
                <p class="text-muted mb-4"><i class="bi bi-upc-scan me-1"></i><?php echo htmlspecialchars($alumno['matricula']); ?></p>
                
                <hr class="text-secondary opacity-25">
                
                <ul class="list-unstyled text-start mt-4 mb-0 text-muted">
                    <li class="mb-3 d-flex justify-content-between">
                        <span><i class="bi bi-diagram-3-fill me-2"></i>Nivel</span>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($alumno['nivel']); ?></span>
                    </li>
                    <li class="mb-3 d-flex justify-content-between">
                        <span><i class="bi bi-bar-chart-steps me-2"></i>Grado</span>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($alumno['grado']); ?>º</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span><i class="bi bi-people-fill me-2"></i>Grupo</span>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($alumno['grupo']); ?></span>
                    </li>
                </ul>
                
                <div class="d-grid mt-4 pt-2">
                    <a href="../calificaciones/calificaciones.php?id_alumno=<?php echo $id_alumno; ?>" class="btn btn-success rounded-pill shadow-sm"><i class="bi bi-pencil-square me-2"></i>Capturar Nueva Nota</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lado Derecho: Rendimiento -->
    <div class="col-lg-8">
        
        <!-- Resumen de Asistencias -->
        <div class="row g-3 mb-4 animate-fade-in" style="animation-delay: 0.3s;">
            <div class="col-12">
                <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-calendar-check-fill me-2 text-info"></i>Asistencias (Tus Materias)</h5>
            </div>
            <div class="col-md-4">
                <div class="card bg-success bg-opacity-10 border-success border-opacity-25 text-success rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <h2 class="fw-bold mb-0"><?php echo $asistencias_stats['Presente']; ?></h2>
                        <span class="small fw-medium text-uppercase">Asistencias</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <h2 class="fw-bold mb-0"><?php echo $asistencias_stats['Retardo']; ?></h2>
                        <span class="small fw-medium text-uppercase">Retardos</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <h2 class="fw-bold mb-0"><?php echo $asistencias_stats['Falta']; ?></h2>
                        <span class="small fw-medium text-uppercase">Inasistencias</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historial de Calificaciones -->
        <div class="card shadow rounded-4 border-0 animate-fade-in" style="animation-delay: 0.4s;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Rendimiento (Tus Materias)</h5>
            </div>
            <div class="card-body p-4">
                <?php if (empty($historial_notas)): ?>
                    <div class="text-center p-4">
                        <p class="text-muted mb-0">No hay calificaciones registradas por ti para este alumno aún.</p>
                    </div>
                <?php
else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Materia</th>
                                    <th>Periodo</th>
                                    <th class="text-center">Calificación</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial_notas as $nota):
        $is_approved = ($nota['calificacion'] >= 6);
        $badge_class = $is_approved ? 'bg-success' : 'bg-danger';
        $icon = $is_approved ? '✅' : '⚠️';
?>
                                    <tr>
                                        <td class="fw-medium text-primary"><?php echo htmlspecialchars($nota['nombre_materia']); ?></td>
                                        <td><?php echo "Bloque " . htmlspecialchars($nota['periodo']); ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $badge_class; ?> fs-6 rounded-pill px-3"><?php echo $icon . ' ' . htmlspecialchars($nota['calificacion']); ?></span>
                                        </td>
                                        <td class="small text-muted"><?php echo date('d/m/Y', strtotime($nota['fecha_registro'])); ?></td>
                                    </tr>
                                <?php
    endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php
endif; ?>
            </div>
        </div>
        
    </div>
</div>

<?php $conexion->close(); ?>
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
