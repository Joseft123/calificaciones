<?php
session_start();

// Validar que el usuario sea un docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';
$id_docente = $_SESSION['id_docente'];

// Consulta SQL para obtener las materias asignadas
$sql = "SELECT m.nombre_materia, dmg.nivel, dmg.grado, dmg.grupo 
        FROM materias m
        INNER JOIN docente_materia_grupo dmg ON m.id_materia = dmg.id_materia
        WHERE dmg.id_docente = $id_docente
        ORDER BY dmg.nivel ASC, dmg.grado ASC, m.nombre_materia ASC";

$resultado = $conexion->query($sql);
$asignaciones = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $asignaciones[] = $fila;
    }
}

include '../includes/header.php';
?>

<style>
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .calendar-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-top: 5px solid #0dcaf0 !important;
        background-color: var(--bs-body-bg);
    }
    .calendar-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.12) !important;
        border-top-color: #0d6efd !important;
    }
    [data-bs-theme="dark"] .card {
        background-color: #2b2b2b;
        color: #ffffff;
    }
    [data-bs-theme="dark"] .calendar-card:hover { box-shadow: 0 12px 24px rgba(0,0,0,0.5) !important; }
    [data-bs-theme="dark"] .bg-light, [data-bs-theme="dark"] .bg-white {
        background-color: #1e1e1e !important;
        color: var(--bs-light) !important;
    }
    .empty-state {
        background: linear-gradient(to right, #f8f9fa, #e9ecef);
        border: 2px dashed #ced4da;
        border-radius: 12px;
    }
    [data-bs-theme="dark"] .empty-state {
        background: linear-gradient(to right, #2b2b2b, #1e1e1e);
        border-color: #495057;
    }
    
    /* Calendario de Cuadrícula Visual */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    .course-time {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }
    [data-bs-theme="dark"] .course-time { color: #adb5bd; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-info m-0 fw-bold">📅 Mi Calendario Académico</h2>
</div>

<div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in mb-5" style="animation-delay: 0.2s;">
    <div class="card-header bg-info text-white px-4 py-3" style="background: linear-gradient(135deg, #0dcaf0 0%, #055160 100%);">
        <h5 class="m-0 fw-bold"><i class="bi bi-calendar-event me-2"></i>Asignaturas Impartidas</h5>
    </div>
    <div class="card-body p-4 p-md-5 bg-light">
        
        <?php if (!empty($asignaciones)): ?>
        <p class="text-muted mb-4 text-center">Aquí se listan las materias que tienes asignadas a impartir este ciclo escolar agrupadas visualmente.</p>
        
        <div class="calendar-grid">
            <?php
    $delay = 0.3;
    $colors = ['text-primary', 'text-success', 'text-info', 'text-warning', 'text-danger'];

    foreach ($asignaciones as $index => $asig):
        $colorClass = $colors[$index % count($colors)];
        $bgClass = str_replace('text-', 'bg-', $colorClass);
?>
                <div class="card h-100 border-0 shadow-sm calendar-card rounded-4 bg-transparent animate-fade-in" style="animation-delay: <?php echo $delay; ?>s;">
                    <div class="card-body p-4 text-center position-relative overflow-hidden">
                        
                        <!-- Ícono decorativo de fondo -->
                        <i class="bi bi-journal-bookmark position-absolute opacity-10" style="font-size: 8rem; right: -20px; bottom: -20px; z-index: 0;"></i>

                        <div class="position-relative" style="z-index: 1;">
                            <div class="mb-3 d-inline-block p-3 rounded-circle <?php echo $bgClass; ?> bg-opacity-10">
                                <i class="bi bi-easel2-fill fs-2 <?php echo $colorClass; ?>"></i>
                            </div>
                            
                            <h5 class="fw-bold mb-1 <?php echo $colorClass; ?>">
                                <?php echo htmlspecialchars($asig['nombre_materia']); ?>
                            </h5>
                            
                            <div class="course-time mb-3">
                                <?php echo htmlspecialchars($asig['nivel']); ?>
                            </div>
                            
                            <hr class="w-25 mx-auto text-secondary opacity-25">
                            
                            <div class="d-flex justify-content-center align-items-center gap-3">
                                <div class="badge bg-secondary p-2 rounded-3 shadow-sm">
                                    <i class="bi bi-mortarboard me-1"></i> <?php echo htmlspecialchars($asig['grado']); ?>º Grado
                                </div>
                                <div class="badge bg-dark p-2 rounded-3 shadow-sm">
                                    <i class="bi bi-people me-1"></i> Grupo <?php echo htmlspecialchars($asig['grupo']); ?>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            <?php
        $delay += 0.1;
    endforeach;
?>
        </div>
        
        <?php
else: ?>
        <div class="empty-state text-center p-5 mx-auto animate-fade-in" style="animation-delay: 0.3s; max-width: 600px;">
            <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;">🗓️</div>
            <h4 class="text-secondary fw-bold mb-3">Sin asignaturas agendadas</h4>
            <p class="text-muted mb-0">Actualmente no tienes materias asignadas para impartir en este ciclo escolar.</p>
        </div>
        <?php
endif; ?>

    </div>
</div>

<?php
$conexion->close();
?>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
