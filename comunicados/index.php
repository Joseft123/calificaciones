<?php
session_start();

// Validar que el usuario sea administrador (Director)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

// Obtener la lista de comunicados
$sql = "SELECT c.id_comunicado, c.titulo, c.mensaje, c.destinatario, c.fecha_publicacion, u.nombre AS autor
        FROM comunicados c
        INNER JOIN usuarios u ON c.id_autor = u.id_usuario
        ORDER BY c.fecha_publicacion DESC";
$resultado = $conexion->query($sql);

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div>
        <h2 class="text-primary m-0 fw-bold">📢 Tablón de Comunicados</h2>
        <p class="text-muted mb-0">Gestiona los avisos para la comunidad escolar.</p>
    </div>
    <button type="button" class="btn btn-primary shadow-sm px-4 rounded-pill hover-scale" data-bs-toggle="modal" data-bs-target="#modalNuevoComunicado">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Aviso
    </button>
</div>

<!-- Modal Nuevo Comunicado -->
<div class="modal fade" id="modalNuevoComunicado" tabindex="-1" aria-labelledby="modalNuevoComunicadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-bottom-0 pb-3">
                <h5 class="modal-title fw-bold" id="modalNuevoComunicadoLabel"><i class="bi bi-megaphone-fill me-2"></i>Redactar Aviso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="guardar_comunicado.php" method="POST">
                <div class="modal-body p-4 bg-light">
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label fw-bold small text-secondary">Título del Aviso</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ej. Suspensión de Clases" maxlength="150">
                    </div>
                    
                    <div class="mb-3">
                        <label for="mensaje" class="form-label fw-bold small text-secondary">Mensaje Detallado</label>
                        <textarea class="form-control" id="mensaje" name="mensaje" rows="4" required placeholder="Escribe el cuerpo del mensaje..." style="resize: none;"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="destinatario" class="form-label fw-bold small text-secondary">¿A quién va dirigido?</label>
                        <select class="form-select" id="destinatario" name="destinatario" required>
                            <option value="Todos" selected>👥 Todos (Docentes y Alumnos)</option>
                            <option value="Docentes">👨‍🏫 Solo Docentes</option>
                            <option value="Alumnos">🎓 Solo Alumnos</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer bg-light border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bi bi-send-fill me-2"></i>Publicar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Listado de Comunicados -->
<div class="row g-4 animate-fade-in" style="animation-delay: 0.2s;">
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <?php while ($comunicado = $resultado->fetch_assoc()):
        // Color según el destinatario
        $bg_class = 'bg-primary';
        $icon = 'bi-people-fill';
        if ($comunicado['destinatario'] == 'Docentes') {
            $bg_class = 'bg-info';
            $icon = 'bi-person-workspace';
        }
        elseif ($comunicado['destinatario'] == 'Alumnos') {
            $bg_class = 'bg-success';
            $icon = 'bi-mortarboard-fill';
        }
?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 rounded-4 border-0 hover-scale position-relative overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge <?php echo $bg_class; ?> rounded-pill mb-2 px-3 py-2 fw-medium">
                                <i class="bi <?php echo $icon; ?> me-1"></i>Para: <?php echo htmlspecialchars($comunicado['destinatario']); ?>
                            </span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light text-muted rounded-circle border-0 dropdown-toggle-no-caret" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow text-sm">
                                    <li><a class="dropdown-item text-danger" href="eliminar_comunicado.php?id=<?php echo $comunicado['id_comunicado']; ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este aviso permanentemente?');"><i class="bi bi-trash-fill me-2"></i>Eliminar</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <h5 class="fw-bold mb-3 text-dark"><?php echo htmlspecialchars($comunicado['titulo']); ?></h5>
                        <p class="text-muted small mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?php echo nl2br(htmlspecialchars($comunicado['mensaje'])); ?></p>
                        
                    </div>
                    <div class="card-footer bg-transparent border-top p-3 text-muted small d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($comunicado['autor']); ?></span>
                        <span class="fw-medium text-dark"><i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($comunicado['fecha_publicacion'])); ?></span>
                    </div>
                </div>
            </div>
        <?php
    endwhile; ?>
    <?php
else: ?>
        <div class="col-12 text-center p-5">
            <div class="empty-state bg-white p-5 rounded-4 shadow-sm mx-auto" style="max-width: 600px;">
                <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;">📭</div>
                <h4 class="text-secondary fw-bold mb-3">No hay comunicados publicados</h4>
                <p class="text-muted mb-0">El tablón está vacío. Comienza creando un nuevo aviso para informar a la comunidad escolar.</p>
            </div>
        </div>
    <?php
endif; ?>
</div>

<?php $conexion->close(); ?>
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
