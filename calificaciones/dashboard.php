<?php
session_start();

// Validar que el usuario sea un administrador (Director)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

// --- CONSULTAS PARA KPIs ---

// 1. Total de Alumnos
$res_alumnos = $conexion->query("SELECT COUNT(*) AS total FROM alumnos");
$total_alumnos = $res_alumnos->fetch_assoc()['total'];

// 2. Total de Docentes
$res_docentes = $conexion->query("SELECT COUNT(*) AS total FROM docentes");
$total_docentes = $res_docentes->fetch_assoc()['total'];

// 3. Total de Materias
$res_materias = $conexion->query("SELECT COUNT(*) AS total FROM materias");
$total_materias = $res_materias->fetch_assoc()['total'];

// 4. Total de Usuarios
$res_usuarios = $conexion->query("SELECT COUNT(*) AS total FROM usuarios");
$total_usuarios = $res_usuarios->fetch_assoc()['total'];

// --- CONSULTAS PARA GRÁFICAS ---

// 4. Distribución de Alumnos por Nivel
$sql_niveles = "SELECT nivel, COUNT(*) AS cantidad FROM alumnos GROUP BY nivel";
$res_niveles = $conexion->query($sql_niveles);

$niveles_labels = [];
$niveles_data = [];

if ($res_niveles->num_rows > 0) {
    while ($fila = $res_niveles->fetch_assoc()) {
        $niveles_labels[] = $fila['nivel'];
        $niveles_data[] = $fila['cantidad'];
    }
}

// 4.5. Promedio General de Calificaciones por Nivel
$sql_promedios = "SELECT a.nivel, AVG(c.calificacion) AS promedio_general 
                  FROM alumnos a
                  INNER JOIN calificaciones c ON a.id_alumno = c.id_alumno
                  GROUP BY a.nivel";
$res_promedios = $conexion->query($sql_promedios);

$promedios_labels = [];
$promedios_data = [];

if ($res_promedios && $res_promedios->num_rows > 0) {
    while ($fila = $res_promedios->fetch_assoc()) {
        $promedios_labels[] = $fila['nivel'];
        $promedios_data[] = round($fila['promedio_general'], 2);
    }
}

// 5. Avisos Recientes
$sql_avisos = "SELECT c.titulo, c.mensaje, c.destinatario, c.fecha_publicacion, u.nombre AS autor 
               FROM comunicados c
               INNER JOIN usuarios u ON c.id_autor = u.id_usuario
               ORDER BY c.fecha_publicacion DESC LIMIT 3";
$res_avisos = $conexion->query($sql_avisos);

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div>
        <h2 class="text-primary m-0 fw-bold">👋 Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
        <p class="text-muted mb-0">Resumen general del ciclo escolar actual.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="exportar_excel.php" class="btn btn-outline-success shadow-sm rounded-pill px-3">
            <i class="bi bi-file-earmark-excel-fill me-2"></i>Exportar Excel
        </a>
        <a href="calificaciones.php" class="btn btn-primary shadow-sm px-4 rounded-pill">
            <i class="bi bi-pencil-square me-2"></i>Capturar Notas
        </a>
    </div>
</div>

<!-- Tarjetas de KPIs -->
<div class="row g-4 mb-5">

    <!-- Tarjeta Alumnos -->
    <div class="col-md-3 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase"
                            style="font-size: 0.85rem; letter-spacing: 0.5px;">Total Alumnos</p>
                        <h2 class="fw-bold mb-0 text-dark counter" data-target="<?php echo $total_alumnos; ?>">0</h2>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="../alumnos/alumnos.php" class="text-decoration-none text-success fw-medium small">Ver
                        directorio <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Docentes -->
    <div class="col-md-3 animate-fade-in" style="animation-delay: 0.3s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase"
                            style="font-size: 0.85rem; letter-spacing: 0.5px;">Plantilla Docente</p>
                        <h2 class="fw-bold mb-0 text-dark counter" data-target="<?php echo $total_docentes; ?>">0</h2>
                    </div>
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-person-vcard-fill"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="../docentes/docentes.php" class="text-decoration-none text-info fw-medium small">Ver
                        directorio <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Materias -->
    <div class="col-md-3 animate-fade-in" style="animation-delay: 0.4s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase"
                            style="font-size: 0.85rem; letter-spacing: 0.5px;">Materias Impartidas</p>
                        <h2 class="fw-bold mb-0 text-dark counter" data-target="<?php echo $total_materias; ?>">0</h2>
                    </div>
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-journals"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="../materias/materias.php"
                        class="text-decoration-none text-warning fw-medium small">Gestionar currícula <i
                            class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Usuarios -->
    <div class="col-md-3 animate-fade-in" style="animation-delay: 0.5s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase"
                            style="font-size: 0.85rem; letter-spacing: 0.5px;">Total Usuarios</p>
                        <h2 class="fw-bold mb-0 text-dark counter" data-target="<?php echo $total_usuarios; ?>">0</h2>
                    </div>
                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="../usuarios/usuarios.php"
                        class="text-decoration-none text-primary fw-medium small">Gestionar accesos <i
                            class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Acciones Rápidas -->
<div class="row mb-5 animate-fade-in" style="animation-delay: 0.6s;">
    <div class="col-12">
        <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Acciones Rápidas</h5>
        <div class="d-flex gap-3 flex-wrap">
            <a href="../alumnos/crear_alumno.php" class="btn btn-outline-success rounded-pill px-4 shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i>Registrar Alumno
            </a>
            <a href="../docentes/crear_docente.php" class="btn btn-outline-info rounded-pill px-4 shadow-sm">
                <i class="bi bi-person-badge-fill me-2"></i>Registrar Docente
            </a>
            <a href="../usuarios/usuarios.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-shield-lock-fill me-2"></i>Gestionar Usuarios
            </a>
        </div>
    </div>
</div>

<!-- Sección Principal Inferior -->
<div class="row g-4 animate-fade-in" style="animation-delay: 0.7s;">

    <!-- Tablón de Anuncios -->
    <div class="col-lg-5">
        <div class="card shadow rounded-4 border-0 h-100">
            <div
                class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0"><i class="bi bi-megaphone-fill text-info me-2"></i>Avisos Recientes</h5>
                <a href="../comunicados/index.php" class="btn btn-sm btn-outline-info rounded-pill">Ver todos</a>
            </div>
            <div class="card-body p-4">
                <?php if ($res_avisos && $res_avisos->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while ($aviso = $res_avisos->fetch_assoc()):
                            $badge = 'bg-primary';
                            if ($aviso['destinatario'] == 'Docentes')
                                $badge = 'bg-info';
                            if ($aviso['destinatario'] == 'Alumnos')
                                $badge = 'bg-success';
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
                        <p class="text-muted mb-0">No hay avisos recientes publicados.</p>
                    </div>
                    <?php
                endif; ?>
            </div>
        </div>
    </div>

    <!-- Sección de Gráficas -->
    <div class="col-lg-7">
        <div class="row g-4">
            <!-- Gráfica 1: Distribución -->
            <div class="col-12">
                <div class="card shadow rounded-4 border-0 chart-container">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold m-0"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Distribución de
                            Alumnos por Nivel</h5>
                    </div>
                    <div class="card-body p-4 d-flex justify-content-center">
                        <div style="height: 300px; width: 100%; max-width: 500px;">
                            <canvas id="nivelesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfica 2: Promedios -->
            <div class="col-12">
                <div class="card shadow rounded-4 border-0 chart-container">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold m-0"><i class="bi bi-bar-chart-fill text-success me-2"></i>Promedio General
                            por Nivel</h5>
                    </div>
                    <div class="card-body p-4 d-flex justify-content-center">
                        <div style="height: 300px; width: 100%;">
                            <canvas id="promediosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $conexion->close(); ?>

</div>

<!-- Scripts de Bootstrap y Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Inyectar datos desde PHP al contexto global de JS para las gráficas
    window.chartLabels = <?php echo json_encode($niveles_labels); ?>;
    window.chartData = <?php echo json_encode($niveles_data); ?>;

    // Datos para la nueva gráfica
    window.promediosLabels = <?php echo json_encode($promedios_labels); ?>;
    window.promediosData = <?php echo json_encode($promedios_data); ?>;
</script>
<script src="../assets/js/dashboard.js"></script>

</body>

</html>