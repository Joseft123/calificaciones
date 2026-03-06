<?php
session_start();

// Validar que el usuario sea un docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

include '../includes/conexion.php';

$id_docente = $_SESSION['id_docente'];

// --- CONSULTAS PARA KPIs DEL DOCENTE ---

// 1. Total de Alumnos (Únicos)
$sql_alumnos = "SELECT COUNT(DISTINCT a.id_alumno) AS total_alumnos 
                FROM alumnos a
                INNER JOIN docente_materia_grupo dmg 
                ON a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo
                WHERE dmg.id_docente = $id_docente";
$res_alumnos = $conexion->query($sql_alumnos);
$total_alumnos = $res_alumnos->fetch_assoc()['total_alumnos'] ?? 0;

// 2. Total de Grupos/Materias Asignadas
$sql_grupos = "SELECT COUNT(*) AS total_grupos FROM docente_materia_grupo WHERE id_docente = $id_docente";
$res_grupos = $conexion->query($sql_grupos);
$total_grupos = $res_grupos->fetch_assoc()['total_grupos'] ?? 0;

// 3. Obtener listado de sus materias para el resumen rápido
$sql_materias = "SELECT m.nombre_materia, dmg.nivel, dmg.grado, dmg.grupo 
                 FROM materias m
                 INNER JOIN docente_materia_grupo dmg ON m.id_materia = dmg.id_materia
                 WHERE dmg.id_docente = $id_docente
                 ORDER BY dmg.nivel ASC, dmg.grado ASC, m.nombre_materia ASC";
$res_materias = $conexion->query($sql_materias);
$materias_asignadas = [];
if ($res_materias && $res_materias->num_rows > 0) {
    while ($fila = $res_materias->fetch_assoc()) {
        $materias_asignadas[] = $fila;
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div>
        <h2 class="text-success m-0 fw-bold">👋 Bienvenido, Profesor <?php echo htmlspecialchars($_SESSION['nombre_docente']); ?></h2>
        <p class="text-muted mb-0">Resumen de tu actividad académica.</p>
    </div>
</div>

<!-- Tarjetas de KPIs y Acciones -->
<div class="row g-4 mb-5">
    
    <!-- KPI: Alumnos -->
    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Alumnos Asignados</p>
                        <h2 class="fw-bold mb-0 text-dark counter" data-target="<?php echo $total_alumnos; ?>">0</h2>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="mis_alumnos.php" class="text-decoration-none text-success fw-medium small">Ver mis alumnos <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI: Grupos -->
    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.3s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Grupos/Materias</p>
                        <h2 class="fw-bold mb-0 text-dark counter" data-target="<?php echo $total_grupos; ?>">0</h2>
                    </div>
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-journals"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="mi_calendario.php" class="text-decoration-none text-info fw-medium small">Ver mi calendario <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- ACCIONES RÁPIDAS -->
    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.4s;">
        <div class="card bg-primary text-white shadow-sm h-100 border-0" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Acciones Rápidas</h5>
                <div class="d-grid gap-2">
                    <a href="pasar_lista.php" class="btn btn-light btn-sm text-primary fw-bold text-start rounded-pill px-3 shadow-sm">
                        <i class="bi bi-clipboard-check me-2"></i>Pasar Lista Hoy
                    </a>
                    <a href="../calificaciones/calificaciones.php" class="btn btn-outline-light btn-sm fw-bold text-start rounded-pill px-3">
                        <i class="bi bi-pencil-square me-2"></i>Capturar Notas
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Resumen de Materias -->
<h4 class="fw-bold mb-4 animate-fade-in" style="animation-delay: 0.5s;"><i class="bi bi-book-half text-secondary me-2"></i>Resumen de tus Clases</h4>

<div class="row g-4 animate-fade-in" style="animation-delay: 0.6s;">
    <?php if (!empty($materias_asignadas)): ?>
        <?php foreach ($materias_asignadas as $mat): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow rounded-4 border-0 h-100 hover-scale" style="transition: transform 0.2s;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light rounded-circle p-3 me-3 text-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-journal-text fs-4 text-primary" style="line-height: .5;"></i>
                            </div>
                            <h5 class="card-title fw-bold m-0 text-truncate"><?php echo htmlspecialchars($mat['nombre_materia']); ?></h5>
                        </div>
                        <ul class="list-unstyled text-muted small mb-0">
                            <li class="mb-1"><i class="bi bi-building me-2"></i>Nivel: <?php echo htmlspecialchars($mat['nivel']); ?></li>
                            <li><i class="bi bi-people-fill me-2"></i>Grupo: <?php echo htmlspecialchars($mat['grado']) . 'º ' . htmlspecialchars($mat['grupo']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php
    endforeach; ?>
    <?php
else: ?>
        <div class="col-12">
            <div class="empty-state text-center p-5 mx-auto bg-white rounded-4 shadow-sm" style="max-width: 600px;">
                <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;">📝</div>
                <h5 class="text-secondary fw-bold mb-2">Sin materias asignadas</h5>
                <p class="text-muted mb-0">Actualmente no tienes grupos asignados en el sistema.</p>
            </div>
        </div>
    <?php
endif; ?>
</div>

<?php $conexion->close(); ?>

<!-- Cierre Container desde header -->
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script básico para el contador animado de los KPIs
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.counter');
        const speed = 200;

        counters.forEach(counter => {
            const animate = () => {
                const value = +counter.getAttribute('data-target');
                const data = +counter.innerText;
                const time = value / speed;

                if (data < value) {
                    counter.innerText = Math.ceil(data + time);
                    setTimeout(animate, 1);
                } else {
                    counter.innerText = value;
                }
            }
            animate();
        });
    });
</script>
</body>
</html>
