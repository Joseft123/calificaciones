<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

// Consultar los ciclos
$sql = "SELECT * FROM ciclos_escolares ORDER BY id_ciclo DESC";
$resultado = $conexion->query($sql);

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div>
        <h2 class="text-primary m-0 fw-bold">📅 Ciclos Escolares</h2>
        <p class="text-muted mb-0">Gestiona los periodos académicos históricos y activos.</p>
    </div>
    <button type="button" class="btn btn-primary shadow-sm px-4 rounded-pill hover-scale" data-bs-toggle="modal" data-bs-target="#modalNuevoCiclo">
        <i class="bi bi-plus-lg me-2"></i>Crear Nuevo Ciclo
    </button>
</div>

<!-- Modal Nuevo Ciclo -->
<div class="modal fade" id="modalNuevoCiclo" tabindex="-1" aria-labelledby="modalNuevoCicloLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-bottom-0 pb-3">
                <h5 class="modal-title fw-bold" id="modalNuevoCicloLabel"><i class="bi bi-calendar-plus-fill me-2"></i>Definir Nuevo Ciclo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="guardar_ciclo.php" method="POST">
                <div class="modal-body p-4 bg-light">
                    
                    <div class="mb-3">
                        <label for="nombre_ciclo" class="form-label fw-bold small text-secondary">Nombre del Ciclo</label>
                        <input type="text" class="form-control" id="nombre_ciclo" name="nombre_ciclo" required placeholder="Ej. Ciclo 2025-2026" maxlength="100">
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="fecha_inicio" class="form-label fw-bold small text-secondary">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-6">
                            <label for="fecha_fin" class="form-label fw-bold small text-secondary">Fecha de Cierre (Aprox)</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning d-flex align-items-center mb-0 mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div class="small">
                            El nuevo ciclo se creará en estado <strong>Inactivo</strong>. Tendrás que activarlo manualmente cuando termine el actual.
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bi bi-save-fill me-2"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabla de Ciclos -->
<div class="card shadow rounded-4 border-0 animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre del Ciclo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while ($ciclo = $resultado->fetch_assoc()):
        $es_activo = ($ciclo['estatus'] == 'Activo');
?>
                            <tr>
                                <td class="ps-4 fw-medium text-secondary">#<?php echo $ciclo['id_ciclo']; ?></td>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($ciclo['nombre_ciclo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($ciclo['fecha_inicio'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($ciclo['fecha_fin'])); ?></td>
                                <td class="text-center">
                                    <?php if ($es_activo): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>Activo Actual</span>
                                    <?php        else: ?>
                                        <span class="badge bg-secondary bg-opacity-25 text-secondary rounded-pill px-3 py-2"><i class="bi bi-archive-fill me-1"></i>Histórico / Inactivo</span>
                                    <?php        endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if (!$es_activo): ?>
                                        <a href="activar_ciclo.php?id=<?php echo $ciclo['id_ciclo']; ?>" class="btn btn-sm btn-outline-success rounded-pill" onclick="return confirm('¿Estás seguro? ESTO MOVERÁ TODA LA ESCUELA A ESTE NUEVO CICLO y el actual pasará al archivo histórico.');">
                                            <i class="bi bi-power me-1"></i>Activar
                                        </a>
                                    <?php        else: ?>
                                        <button class="btn btn-sm btn-light text-success fw-bold rounded-pill border-0" disabled><i class="bi bi-check-lg me-1"></i>En Curso</button>
                                    <?php        endif; ?>
                                </td>
                            </tr>
                        <?php
    endwhile; ?>
                    <?php
else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No existen ciclos registrados en el sistema.</td></tr>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $conexion->close(); ?>
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
