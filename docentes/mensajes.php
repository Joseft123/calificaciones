<?php
session_start();
include '../includes/conexion.php';

// Validar que el usuario sea Docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

$id_docente = $_SESSION['id_docente'];

// Obtener cantidad de mensajes no leídos (opcional para badges en tabs si se desea)
$unread_mensajes_docente = 0;
$stmt_unread = $conexion->prepare("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = ? AND tipo_destinatario = 'Docente' AND leido = 0");
$stmt_unread->bind_param("i", $id_docente);
$stmt_unread->execute();
$res_unread = $stmt_unread->get_result();
if ($res_unread) {
    $unread_mensajes_docente = $res_unread->fetch_assoc()['total'];
}

// Obtener mensajes recibidos (Inbox)
$sql_inbox = "
    SELECT m.id_mensaje, m.asunto, m.fecha_envio, m.leido, 
           CONCAT(p.nombre, ' ', p.apellidos) AS remitente_nombre
    FROM mensajes m
    INNER JOIN padres p ON m.id_remitente = p.id_padre
    WHERE m.id_destinatario = ? 
      AND m.tipo_destinatario = 'Docente' 
      AND m.tipo_remitente = 'Padre'
    ORDER BY m.fecha_envio DESC
";
$stmt_inbox = $conexion->prepare($sql_inbox);
$stmt_inbox->bind_param("i", $id_docente);
$stmt_inbox->execute();
$resultado_inbox = $stmt_inbox->get_result();

// Obtener mensajes enviados (Outbox)
$sql_outbox = "
    SELECT m.id_mensaje, m.asunto, m.fecha_envio, m.leido, 
           CONCAT(p.nombre, ' ', p.apellidos) AS destinatario_nombre
    FROM mensajes m
    INNER JOIN padres p ON m.id_destinatario = p.id_padre
    WHERE m.id_remitente = ? 
      AND m.tipo_remitente = 'Docente' 
      AND m.tipo_destinatario = 'Padre'
    ORDER BY m.fecha_envio DESC
";
$stmt_outbox = $conexion->prepare($sql_outbox);
$stmt_outbox->bind_param("i", $id_docente);
$stmt_outbox->execute();
$resultado_outbox = $stmt_outbox->get_result();

include '../includes/header.php';
?>

<div class="container animate-fade-in" style="animation-delay: 0.1s;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-envelope-fill text-primary me-2"></i>Centro de Mensajes</h2>
        <div>
            <a href="redactar_mensaje.php" class="btn btn-primary shadow-sm rounded-pill px-4 hover-scale"><i
                    class="bi bi-pencil-square me-2"></i>Redactar Mensaje</a>
        </div>
    </div>

    <!-- Mostrar alerta si viene de responder -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'respondido'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Tu respuesta ha sido enviada con éxito.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill border-0" id="mensajesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-top-3 fw-bold text-secondary" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox-pane" type="button" role="tab" aria-controls="inbox-pane" aria-selected="true">
                        <i class="bi bi-inbox-fill me-2"></i>Bandeja de Entrada
                        <?php if ($unread_mensajes_docente > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-2"><?php echo $unread_mensajes_docente; ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-top-3 fw-bold text-secondary" id="outbox-tab" data-bs-toggle="tab" data-bs-target="#outbox-pane" type="button" role="tab" aria-controls="outbox-pane" aria-selected="false">
                        <i class="bi bi-send-fill me-2"></i>Enviados
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content" id="mensajesTabContent">
                
                <!-- TAB INBOX -->
                <div class="tab-pane fade show active" id="inbox-pane" role="tabpanel" aria-labelledby="inbox-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0 mensajes-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3" style="width: 50px;"></th>
                                    <th class="py-3 text-secondary fw-semibold">De (Padre/Tutor)</th>
                                    <th class="py-3 text-secondary fw-semibold">Asunto</th>
                                    <th class="py-3 text-secondary fw-semibold">Fecha</th>
                                    <th class="text-center pe-4 py-3 text-secondary fw-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado_inbox->num_rows > 0): ?>
                                    <?php while ($msg = $resultado_inbox->fetch_assoc()): ?>
                                        <tr class="<?php echo $msg['leido'] == 0 ? 'bg-primary bg-opacity-10 fw-bold' : ''; ?> cursor-pointer transition-all">
                                            <td class="ps-4 text-center text-primary">
                                                <?php if ($msg['leido'] == 0): ?>
                                                    <i class="bi bi-envelope-fill fs-5" title="No Leído"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-envelope-open text-muted fs-5" title="Leído"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-gradient text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                                        style="width: 38px; height: 38px; font-size: 1rem; font-weight: bold;">
                                                        <?php echo strtoupper(substr($msg['remitente_nombre'], 0, 1)); ?>
                                                    </div>
                                                    <span class="<?php echo $msg['leido'] == 0 ? 'text-primary' : 'text-body fw-medium'; ?>">
                                                        <?php echo htmlspecialchars($msg['remitente_nombre']); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($msg['asunto']); ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo date('d \d\e M Y', strtotime($msg['fecha_envio'])); ?><br>
                                                <?php echo date('h:i A', strtotime($msg['fecha_envio'])); ?>
                                            </td>
                                            <td class="text-center pe-4">
                                                <a href="leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>"
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium">Leer</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-5 bg-light">
                                            <div class="py-4">
                                                <div class="bg-body shadow-sm rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                                                    <i class="bi bi-inbox fs-1 text-secondary"></i>
                                                </div>
                                                <h5 class="fw-bold">No tienes mensajes recibidos</h5>
                                                <p class="mb-0 text-secondary">Aquí aparecerán los mensajes que te envíen los padres de familia.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB OUTBOX -->
                <div class="tab-pane fade" id="outbox-pane" role="tabpanel" aria-labelledby="outbox-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0 mensajes-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3" style="width: 50px;"></th>
                                    <th class="py-3 text-secondary fw-semibold">Para (Padre/Tutor)</th>
                                    <th class="py-3 text-secondary fw-semibold">Asunto</th>
                                    <th class="py-3 text-secondary fw-semibold">Fecha</th>
                                    <th class="text-center pe-4 py-3 text-secondary fw-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado_outbox->num_rows > 0): ?>
                                    <?php while ($msg = $resultado_outbox->fetch_assoc()): ?>
                                        <tr class="cursor-pointer transition-all">
                                            <td class="ps-4 text-center">
                                                <i class="bi bi-send text-secondary fs-5" title="Enviado"></i>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-secondary bg-gradient text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                                        style="width: 38px; height: 38px; font-size: 1rem; font-weight: bold;">
                                                        <?php echo strtoupper(substr($msg['destinatario_nombre'], 0, 1)); ?>
                                                    </div>
                                                    <span class="text-body fw-medium">
                                                        <?php echo htmlspecialchars($msg['destinatario_nombre']); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($msg['asunto']); ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo date('d \d\e M Y', strtotime($msg['fecha_envio'])); ?><br>
                                                <?php echo date('h:i A', strtotime($msg['fecha_envio'])); ?>
                                            </td>
                                            <td class="text-center pe-4">
                                                <a href="leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>"
                                                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium">Ver</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-5 bg-light">
                                            <div class="py-4">
                                                <div class="bg-body shadow-sm rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                                                    <i class="bi bi-send fs-1 text-secondary"></i>
                                                </div>
                                                <h5 class="fw-bold">No tienes mensajes enviados</h5>
                                                <p class="mb-0 text-secondary">Aquí aparecerán los mensajes que envíes a los padres.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .mensajes-table tbody tr:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.03) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    .nav-tabs .nav-link {
        color: var(--bs-secondary);
        border: none;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link:hover {
        background-color: var(--bs-light);
        color: var(--bs-primary);
        border-color: transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        border-bottom: 3px solid var(--bs-primary);
        background-color: transparent;
    }
</style>

<script>
    // Hacer toda la fila clickeable hacia leer_mensaje
    document.addEventListener("DOMContentLoaded", function () {
        const rows = document.querySelectorAll("tbody tr.cursor-pointer");
        rows.forEach(row => {
            row.addEventListener("click", function (e) {
                // Prevenir que haga doble click si ya pinchó el botón
                if (e.target.tagName !== 'A' && !e.target.closest('a')) {
                    const link = this.querySelector("a.btn-outline-primary, a.btn-outline-secondary");
                    if (link) window.location.href = link.href;
                }
            });
        });
        
        // Mantener la pestaña activa si hay un hash en la URL
        var hash = window.location.hash;
        if (hash) {
            var tabOption = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
            if (tabOption) {
                var tab = new bootstrap.Tab(tabOption);
                tab.show();
            }
        }
        
        // Actualizar hash al cambiar de pestaña para refrescar página y volver a la misma tab
        var tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function (e) {
                var target = e.target.getAttribute('data-bs-target');
                window.location.hash = target;
            });
        });
    });
</script>

<?php
if(isset($stmt_inbox)) $stmt_inbox->close();
if(isset($stmt_outbox)) $stmt_outbox->close();
$conexion->close();
?>
</div> <!-- Cierra contenedor-principal de header.php -->

<!-- Scripts requeridos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>

</body>

</html>