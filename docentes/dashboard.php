<?php
session_start();

// Validar que el usuario sea un docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

include '../includes/conexion.php';
include '../includes/funciones_ciclo.php';

$id_docente = intval($_SESSION['id_docente']);
$id_ciclo_actual = getCicloActivo($conexion);

// --- CONSULTAS PARA KPIs DEL DOCENTE ---

// 1. Obtener total de alumnos asignados AL CICLO ACTUAL
$sql_alumnos = "SELECT COUNT(DISTINCT a.id_alumno) as total_alumnos 
                FROM alumnos a
                INNER JOIN docente_materia_grupo dmg ON a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo
                WHERE dmg.id_docente = $id_docente AND dmg.id_ciclo = $id_ciclo_actual";
$res_alumnos = $conexion->query($sql_alumnos);
$total_alumnos = $res_alumnos->fetch_assoc()['total_alumnos'] ?? 0;

// 2. Obtener total de grupos asignados AL CICLO ACTUAL
$sql_grupos = "SELECT COUNT(DISTINCT CONCAT(nivel, grado, grupo)) as total_grupos 
               FROM docente_materia_grupo 
               WHERE id_docente = $id_docente AND id_ciclo = $id_ciclo_actual";
$res_grupos = $conexion->query($sql_grupos);
$total_grupos = $res_grupos->fetch_assoc()['total_grupos'] ?? 0;

// 3. Obtener listado de sus materias para el resumen rápido AL CICLO ACTUAL
$sql_materias = "SELECT m.nombre_materia, dmg.nivel, dmg.grado, dmg.grupo 
                 FROM materias m
                 INNER JOIN docente_materia_grupo dmg ON m.id_materia = dmg.id_materia
                 WHERE dmg.id_docente = $id_docente AND dmg.id_ciclo = $id_ciclo_actual
                 ORDER BY dmg.nivel ASC, dmg.grado ASC, m.nombre_materia ASC";
$res_materias = $conexion->query($sql_materias);
$materias_asignadas = [];
if ($res_materias && $res_materias->num_rows > 0) {
    while ($fila = $res_materias->fetch_assoc()) {
        $materias_asignadas[] = $fila;
    }
}

// 3.5. Obtener el Promedio de Calificaciones por Materia del Docente (para la gráfica de barras)
$sql_promedios_materia = "SELECT m.nombre_materia, AVG(c.calificacion) AS promedio_materia
                          FROM materias m
                          INNER JOIN calificaciones c ON m.id_materia = c.id_materia
                          INNER JOIN docente_materia_grupo dmg ON c.id_materia = dmg.id_materia 
                               AND c.id_alumno IN (SELECT id_alumno FROM alumnos a WHERE a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo)
                          WHERE dmg.id_docente = $id_docente AND dmg.id_ciclo = $id_ciclo_actual
                          GROUP BY m.id_materia";
$res_promedios_materia = $conexion->query($sql_promedios_materia);

$promedios_materia_labels = [];
$promedios_materia_data = [];

if ($res_promedios_materia && $res_promedios_materia->num_rows > 0) {
    while ($fila = $res_promedios_materia->fetch_assoc()) {
        $promedios_materia_labels[] = $fila['nombre_materia'];
        $promedios_materia_data[] = round($fila['promedio_materia'], 2);
    }
}

// 3.6. Obtener el total de Asistencias, Faltas y Retardos de todos sus alumnos (para la gráfica de dona)
$sql_asistencias = "SELECT asi.estado, COUNT(asi.id_asistencia) AS total_estado
                    FROM asistencias asi
                    INNER JOIN docente_materia_grupo dmg ON asi.id_materia = dmg.id_materia
                           AND asi.id_alumno IN (SELECT id_alumno FROM alumnos a WHERE a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo)
                    WHERE dmg.id_docente = $id_docente AND dmg.id_ciclo = $id_ciclo_actual
                    GROUP BY asi.estado";
$res_asistencias = $conexion->query($sql_asistencias);

$asistencias_labels = [];
$asistencias_data = [];

// Initialize to 0 so the chart doesn't break if there are no records yet
$estados_defaults = ['Presente' => 0, 'Falta' => 0, 'Retardo' => 0];

if ($res_asistencias && $res_asistencias->num_rows > 0) {
    while ($fila = $res_asistencias->fetch_assoc()) {
        $estados_defaults[$fila['estado']] = $fila['total_estado'];
    }
}

foreach ($estados_defaults as $label => $data) {
    $asistencias_labels[] = $label;
    $asistencias_data[] = $data;
}

// 4. Obtener avisos (comunicados) para Docentes o Todos
$sql_avisos = "SELECT c.titulo, c.mensaje, c.destinatario, c.fecha_publicacion, u.nombre AS autor 
               FROM comunicados c
               INNER JOIN usuarios u ON c.id_autor = u.id_usuario
               WHERE c.destinatario IN ('Todos', 'Docentes')
               ORDER BY c.fecha_publicacion DESC LIMIT 3";
$res_avisos = $conexion->query($sql_avisos);

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div>
        <h2 class="text-success m-0 fw-bold">👋 Bienvenido, Profesor
            <?php echo htmlspecialchars($_SESSION['nombre_docente']); ?></h2>
        <p class="text-muted mb-0">Resumen de tu actividad académica.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="exportar_alumnos_excel.php" class="btn btn-outline-success shadow-sm rounded-pill px-3">
            <i class="bi bi-file-earmark-excel-fill me-2"></i>Exportar Mis Alumnos
        </a>
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
                        <p class="text-muted fw-bold mb-1 text-uppercase"
                            style="font-size: 0.85rem; letter-spacing: 0.5px;">Alumnos Asignados</p>
                        <h2 class="fw-bold mb-0 text-dark counter" data-target="<?php echo $total_alumnos; ?>">0</h2>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="mis_alumnos.php" class="text-decoration-none text-success fw-medium small">Ver mis alumnos
                        <i class="bi bi-arrow-right ms-1"></i></a>
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
                        <p class="text-muted fw-bold mb-1 text-uppercase"
                            style="font-size: 0.85rem; letter-spacing: 0.5px;">Grupos/Materias</p>
                        <h2 class="fw-bold mb-0 text-dark counter" data-target="<?php echo $total_grupos; ?>">0</h2>
                    </div>
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-journals"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="mi_calendario.php" class="text-decoration-none text-info fw-medium small">Ver mi calendario
                        <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- ACCIONES RÁPIDAS -->
    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.4s;">
        <div class="card bg-primary text-white shadow-sm h-100 border-0"
            style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Acciones Rápidas
                </h5>
                <div class="d-grid gap-2">
                    <a href="pasar_lista.php"
                        class="btn btn-light btn-sm text-primary fw-bold text-start rounded-pill px-3 shadow-sm">
                        <i class="bi bi-clipboard-check me-2"></i>Pasar Lista Hoy
                    </a>
                    <a href="../calificaciones/calificaciones.php"
                        class="btn btn-outline-light btn-sm fw-bold text-start rounded-pill px-3">
                        <i class="bi bi-pencil-square me-2"></i>Capturar Notas
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row g-4 animate-fade-in" style="animation-delay: 0.5s;">

    <!-- Tablón de Anuncios -->
    <div class="col-lg-5">
        <div class="card shadow rounded-4 border-0 h-100">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-megaphone-fill text-info me-2"></i>Avisos Recientes</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($res_avisos && $res_avisos->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while ($aviso = $res_avisos->fetch_assoc()):
                            $badge = 'bg-primary';
                            if ($aviso['destinatario'] == 'Docentes')
                                $badge = 'bg-info';
                            ?>
                            <div class="p-3 border rounded-4 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-dark m-0"><?php echo htmlspecialchars($aviso['titulo']); ?></h6>
                                    <span
                                        class="badge <?php echo $badge; ?> rounded-pill text-xs px-2"><?php echo htmlspecialchars($aviso['destinatario']); ?></span>
                                </div>
                                <p class="small text-muted mb-2"
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php echo htmlspecialchars($aviso['mensaje']); ?>
                                </p>
                                <div class="small text-body-secondary d-flex justify-content-between">
                                    <span><i
                                            class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($aviso['autor']); ?></span>
                                    <span><?php echo date('d/m/Y', strtotime($aviso['fecha_publicacion'])); ?></span>
                                </div>
                            </div>
                            <?php
                        endwhile; ?>
                    </div>
                    <?php
                else: ?>
                    <div class="text-center p-4">
                        <p class="text-muted mb-0">No hay avisos recientes para tu departamento.</p>
                    </div>
                    <?php
                endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen de Materias -->
    <div class="col-lg-7">
        <h5 class="fw-bold mb-4"><i class="bi bi-book-half text-secondary me-2"></i>Tus Clases</h3>
            <div class="row g-4">
                <?php if (!empty($materias_asignadas)): ?>
                    <?php foreach ($materias_asignadas as $mat): ?>
                        <div class="col-md-6">
                            <div class="card shadow-sm rounded-4 border-0 h-100 hover-scale"
                                style="transition: transform 0.2s;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-3 me-3 text-center"
                                            style="width: 50px; height: 50px;">
                                            <i class="bi bi-journal-text fs-4 text-primary" style="line-height: .5;"></i>
                                        </div>
                                        <h6 class="card-title fw-bold m-0 text-truncate">
                                            <?php echo htmlspecialchars($mat['nombre_materia']); ?></h6>
                                    </div>
                                    <ul class="list-unstyled text-muted small mb-0">
                                        <li class="mb-1"><i class="bi bi-building me-2"></i>Nivel:
                                            <?php echo htmlspecialchars($mat['nivel']); ?></li>
                                        <li><i class="bi bi-people-fill me-2"></i>Grupo:
                                            <?php echo htmlspecialchars($mat['grado']) . 'º ' . htmlspecialchars($mat['grupo']); ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach; ?>
                    <?php
                else: ?>
                    <div class="col-12">
                        <div class="empty-state text-center p-4 mx-auto bg-white rounded-4 shadow-sm"
                            style="max-width: 600px;">
                            <p class="text-muted mb-0">Actualmente no tienes grupos asignados en el sistema.</p>
                        </div>
                    </div>
                    <?php
                endif; ?>
            </div>
    </div>

    <!-- SECCIÓN DE GRÁFICAS -->
    <div class="col-12 mt-4">
        <div class="row g-4">
            <!-- Gráfica 1: Promedios por Materia -->
            <div class="col-lg-7">
                <div class="card shadow rounded-4 border-0 chart-container h-100">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold m-0"><i class="bi bi-bar-chart-fill text-success me-2"></i>Promedio General por Materias</h5>
                    </div>
                    <div class="card-body p-4 d-flex justify-content-center">
                        <div style="height: 300px; width: 100%;">
                            <canvas id="promediosMateriaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfica 2: Asistencias Globales -->
            <div class="col-lg-5">
                <div class="card shadow rounded-4 border-0 chart-container h-100">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold m-0"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Estatus de Asistencia Global</h5>
                    </div>
                    <div class="card-body p-4 d-flex justify-content-center">
                        <div style="height: 300px; width: 100%; max-width: 400px;">
                            <canvas id="asistenciasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $conexion->close(); ?>

<!-- Cierre Container desde header -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Variables inyectadas por PHP para las gráficas del Docente
    window.promediosMateriaLabels = <?php echo json_encode($promedios_materia_labels); ?>;
    window.promediosMateriaData = <?php echo json_encode($promedios_materia_data); ?>;

    window.asistenciasLabels = <?php echo json_encode($asistencias_labels); ?>;
    window.asistenciasData = <?php echo json_encode($asistencias_data); ?>;

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

    const getTextColor = () => {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#adb5bd' : '#495057';
    };

    // Gráfica de Promedios por Materia (Bar Chart)
    const ctxPromedios = document.getElementById('promediosMateriaChart');
    if (ctxPromedios && typeof window.promediosMateriaData !== 'undefined') {
        const barColors = [
            'rgba(25, 135, 84, 0.7)',
            'rgba(13, 110, 253, 0.7)',
            'rgba(255, 193, 7, 0.7)',
            'rgba(13, 202, 240, 0.7)',
            'rgba(111, 66, 193, 0.7)'
        ];
        
        let promediosChart = new Chart(ctxPromedios, {
            type: 'bar',
            data: {
                labels: window.promediosMateriaLabels.length > 0 ? window.promediosMateriaLabels : ['Sin datos'],
                datasets: [{
                    label: 'Promedio General',
                    data: window.promediosMateriaData.length > 0 ? window.promediosMateriaData : [0],
                    backgroundColor: window.promediosMateriaData.length > 0 ? barColors : ['rgba(200, 200, 200, 0.2)'],
                    borderRadius: 6,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 10, ticks: { color: getTextColor() } },
                    x: { ticks: { color: getTextColor(), font: { weight: 'bold' } } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
        
        const observerP = new MutationObserver(m => {
            m.forEach(mut => {
                if (mut.attributeName === "data-bs-theme") {
                    promediosChart.options.scales.x.ticks.color = getTextColor();
                    promediosChart.options.scales.y.ticks.color = getTextColor();
                    promediosChart.update();
                }
            });
        });
        observerP.observe(document.documentElement, { attributes: true });
    }

    // Gráfica de Asistencias (Doughnut Chart)
    const ctxAsistencias = document.getElementById('asistenciasChart');
    if (ctxAsistencias && typeof window.asistenciasData !== 'undefined') {
        const doughnutColors = [
            'rgba(25, 135, 84, 0.8)',  // Presente - Success
            'rgba(220, 53, 69, 0.8)',  // Falta - Danger
            'rgba(255, 193, 7, 0.8)'   // Retardo - Warning
        ];
        
        let validAsistData = window.asistenciasData.reduce((a, b) => a + b, 0) > 0;
        
        let asistenciasChart = new Chart(ctxAsistencias, {
            type: 'doughnut',
            data: {
                labels: window.asistenciasLabels.length > 0 ? window.asistenciasLabels : ['Sin datos'],
                datasets: [{
                    data: validAsistData ? window.asistenciasData : [1],
                    backgroundColor: validAsistData ? doughnutColors : ['rgba(200, 200, 200, 0.2)'],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: getTextColor(), usePointStyle: true }
                    }
                }
            }
        });
        
        const observerA = new MutationObserver(m => {
            m.forEach(mut => {
                if (mut.attributeName === "data-bs-theme") {
                    asistenciasChart.options.plugins.legend.labels.color = getTextColor();
                    asistenciasChart.update();
                }
            });
        });
        observerA.observe(document.documentElement, { attributes: true });
    }
</script>
</body>

</html>