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

include '../includes/header.php';
?>

<style>
    /* Animaciones suaves */
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    /* Configuración de Tarjetas */
    .kpi-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 1rem;
        border: none;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .icon-box {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.8rem;
    }

    /* Compatibilidad Dark Mode */
    [data-bs-theme="dark"] .kpi-card {
        background-color: #2b2b2b;
        color: #ffffff;
    }
    [data-bs-theme="dark"] .kpi-card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.4) !important;
    }
    [data-bs-theme="dark"] .chart-container {
        background-color: #2b2b2b !important;
    }
    [data-bs-theme="dark"] .text-muted {
        color: #adb5bd !important;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div>
        <h2 class="text-primary m-0 fw-bold">👋 Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
        <p class="text-muted mb-0">Resumen general del ciclo escolar actual.</p>
    </div>
    <a href="calificaciones.php" class="btn btn-primary shadow-sm px-4 rounded-pill">
        <i class="bi bi-pencil-square me-2"></i>Capturar Notas
    </a>
</div>

<!-- Tarjetas de KPIs -->
<div class="row g-4 mb-5">
    
    <!-- Tarjeta Alumnos -->
    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.2s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Total Alumnos</p>
                        <h2 class="fw-bold mb-0 text-dark"><?php echo $total_alumnos; ?></h2>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="../alumnos/alumnos.php" class="text-decoration-none text-success fw-medium small">Ver directorio <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Docentes -->
    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.3s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Plantilla Docente</p>
                        <h2 class="fw-bold mb-0 text-dark"><?php echo $total_docentes; ?></h2>
                    </div>
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-person-vcard-fill"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Profesores activos en el sistema</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Materias -->
    <div class="col-md-4 animate-fade-in" style="animation-delay: 0.4s;">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Materias Impartidas</p>
                        <h2 class="fw-bold mb-0 text-dark"><?php echo $total_materias; ?></h2>
                    </div>
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-journals"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="../materias/materias.php" class="text-decoration-none text-warning fw-medium small">Gestionar currícula <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Sección de Gráficas -->
<div class="row g-4 animate-fade-in" style="animation-delay: 0.5s;">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow rounded-4 border-0 chart-container">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold m-0"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Distribución de Alumnos por Nivel</h5>
            </div>
            <div class="card-body p-4 d-flex justify-content-center">
                <div style="height: 300px; width: 100%; max-width: 500px;">
                    <canvas id="nivelesChart"></canvas>
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
    document.addEventListener('DOMContentLoaded', function() {
        
        // Función para obtener el color del texto según el tema
        const getTextColor = () => {
            return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#adb5bd' : '#495057';
        };

        // Datos inyectados desde PHP
        const chartLabels = <?php echo json_encode($niveles_labels); ?>;
        const chartData = <?php echo json_encode($niveles_data); ?>;

        const ctx = document.getElementById('nivelesChart').getContext('2d');
        
        // Variables de diseño
        const bgColors = [
            'rgba(25, 135, 84, 0.8)',  // Success (Primaria)
            'rgba(13, 110, 253, 0.8)', // Primary (Secundaria)
            'rgba(255, 193, 7, 0.8)',  // Warning (Preparatoria)
            'rgba(13, 202, 240, 0.8)'  // Info (Otros)
        ];
        const borderColors = [
            'rgb(25, 135, 84)',
            'rgb(13, 110, 253)',
            'rgb(255, 193, 7)',
            'rgb(13, 202, 240)'
        ];

        let nivelesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels.length > 0 ? chartLabels : ['Sin datos'],
                datasets: [{
                    data: chartData.length > 0 ? chartData : [1],
                    backgroundColor: chartData.length > 0 ? bgColors : ['rgba(200, 200, 200, 0.2)'],
                    borderColor: chartData.length > 0 ? borderColors : ['rgba(200, 200, 200, 0.5)'],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: getTextColor(),
                            padding: 20,
                            font: {
                                size: 14,
                                family: "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif"
                            },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 14, weight: 'bold' },
                        displayColors: false
                    }
                }
            }
        });

        // Observar cambios de tema (Modo Oscuro/Claro) para actualizar el color de letra en la gráfica
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "data-bs-theme") {
                    nivelesChart.options.plugins.legend.labels.color = getTextColor();
                    nivelesChart.update();
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true
        });
    });
</script>

</body>
</html>
