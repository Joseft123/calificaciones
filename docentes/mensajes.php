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
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3">
        <h3 class="fw-bold mb-0 text-body d-flex align-items-center">
            <div class="bg-primary bg-gradient text-white rounded-circle d-inline-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 48px; height: 48px; font-size: 1.4rem;">
                <i class="bi bi-chat-dots-fill"></i>
            </div> Centro de Mensajes
        </h3>
        <div>
            <a href="redactar_mensaje.php" class="btn btn-primary shadow-sm rounded-pill px-4 hover-scale"><i class="bi bi-pencil-square me-2"></i>Redactar Mensaje</a>
        </div>
    </div>

    <!-- Mostrar alerta si viene de responder -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'respondido'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Tu respuesta ha sido enviada con éxito.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0 rounded-4 overflow-hidden bg-transparent">
        <div class="card-header bg-body border-bottom-0 pt-4 px-4 pb-0 rounded-top-4 shadow-sm z-1 position-relative">
            <ul class="nav nav-tabs nav-fill fw-bold" id="mensajesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-top-3 fw-bold text-primary" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox-pane" type="button" role="tab" aria-controls="inbox-pane" aria-selected="true" style="border-bottom-width: 3px !important; border-bottom-color: var(--bs-primary) !important;">
                        <i class="bi bi-inbox-fill me-2 fs-5 align-middle"></i> Bandeja de Entrada
                        <?php if ($unread_mensajes_docente > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-2 shadow-sm"><?php echo $unread_mensajes_docente; ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-top-3 fw-bold text-secondary" id="outbox-tab" data-bs-toggle="tab" data-bs-target="#outbox-pane" type="button" role="tab" aria-controls="outbox-pane" aria-selected="false">
                        <i class="bi bi-send-fill me-2 fs-5 align-middle"></i> Enviados
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content" id="mensajesTabContent">
                
                <!-- TAB INBOX -->
                <div class="tab-pane fade show active" id="inbox-pane" role="tabpanel" aria-labelledby="inbox-tab" tabindex="0">
                    <div class="p-3 bg-body-tertiary custom-scroll rounded-bottom-4" style="max-height: 65vh; overflow-y: auto;" id="inbox-list">
                        <?php if ($resultado_inbox->num_rows > 0): ?>
                            <?php while ($msg = $resultado_inbox->fetch_assoc()): ?>
                                <div class="message-card d-flex align-items-center p-3 mb-3 rounded-4 bg-body shadow-sm position-relative transition-all" onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                    
                                    <!-- Unread dot -->
                                    <div class="msg-status me-3">
                                        <?php if ($msg['leido'] == 0): ?>
                                            <div class="status-dot bg-primary rounded-circle shadow-sm" title="No Leído"></div>
                                        <?php else: ?>
                                            <div class="status-dot border border-2 border-muted rounded-circle opacity-50" title="Leído"></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Avatar -->
                                    <div class="msg-avatar bg-gradient-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0 fw-bold shadow-sm">
                                        <?php echo strtoupper(substr($msg['remitente_nombre'], 0, 1)); ?>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="msg-content flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                                            <h6 class="mb-0 fw-bold <?php echo $msg['leido'] == 0 ? 'text-primary' : 'text-body'; ?> text-truncate">
                                                <?php echo htmlspecialchars($msg['remitente_nombre']); ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-normal ms-2 small rounded-pill px-2">Padre/Tutor</span>
                                            </h6>
                                            <small class="text-muted ms-2 flex-shrink-0 fw-medium">
                                                <?php echo date('d M, h:i a', strtotime($msg['fecha_envio'])); ?>
                                            </small>
                                        </div>
                                        <p class="mb-0 text-truncate <?php echo $msg['leido'] == 0 ? 'fw-bold text-body-emphasis' : 'text-secondary'; ?>">
                                            <?php echo htmlspecialchars($msg['asunto']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state text-center p-5 rounded-4 bg-body shadow-sm border-0 my-3">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                    <i class="bi bi-inbox fs-1"></i>
                                </div>
                                <h5 class="fw-bold text-body">Bandeja Vacía</h5>
                                <p class="text-muted mb-0">No tienes mensajes recibidos de los padres.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB OUTBOX -->
                <div class="tab-pane fade" id="outbox-pane" role="tabpanel" aria-labelledby="outbox-tab" tabindex="0">
                    <div class="p-3 bg-body-tertiary custom-scroll rounded-bottom-4" style="max-height: 65vh; overflow-y: auto;" id="outbox-list">
                        <?php if ($resultado_outbox->num_rows > 0): ?>
                            <?php while ($msg = $resultado_outbox->fetch_assoc()): ?>
                                <div class="message-card d-flex align-items-center p-3 mb-3 rounded-4 bg-body shadow-sm position-relative transition-all" onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                    
                                    <!-- Read status icon -->
                                    <div class="msg-status me-3 text-center" style="width: 24px;">
                                        <?php if ($msg['leido'] == 1): ?>
                                            <i class="bi bi-check-all text-primary fs-5" title="Visto por el Padre"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check2 text-muted fs-5 opacity-75" title="Enviado"></i>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Avatar -->
                                    <div class="msg-avatar bg-gradient-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0 fw-bold shadow-sm">
                                        <?php echo strtoupper(substr($msg['destinatario_nombre'], 0, 1)); ?>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="msg-content flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                                            <h6 class="mb-0 fw-bold text-body text-truncate">
                                                Para: <?php echo htmlspecialchars($msg['destinatario_nombre']); ?>
                                            </h6>
                                            <small class="text-muted ms-2 flex-shrink-0 fw-medium">
                                                <?php echo date('d M, h:i a', strtotime($msg['fecha_envio'])); ?>
                                            </small>
                                        </div>
                                        <p class="mb-0 text-truncate text-secondary">
                                            <?php echo htmlspecialchars($msg['asunto']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state text-center p-5 rounded-4 bg-body shadow-sm border-0 my-3">
                                <div class="icon-circle bg-secondary bg-opacity-10 text-secondary mx-auto mb-3">
                                    <i class="bi bi-send-x fs-1"></i>
                                </div>
                                <h5 class="fw-bold text-body">Sin Enviados</h5>
                                <p class="text-muted mb-0">No has enviado ningún mensaje a los padres.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .message-card {
        cursor: pointer;
        border: 1px solid transparent !important;
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .message-card:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 0.5rem 1.5rem rgba(13, 110, 253, 0.15) !important;
        border-color: rgba(13, 110, 253, 0.2) !important;
        z-index: 2;
    }
    .status-dot {
        width: 12px;
        height: 12px;
    }
    .msg-avatar {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
    }
    .bg-gradient-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);
    }
    .icon-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: rgba(13, 110, 253, 0.2); 
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(13, 110, 253, 0.4); 
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
        background-color: transparent;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Mantener la pestaña activa si hay un hash en la URL
        var hash = window.location.hash;
        if (hash) {
            var tabOption = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
            if (tabOption) {
                var tab = new bootstrap.Tab(tabOption);
                tab.show();
                
                // Active styles update needed on initial load if hash is present
                document.querySelectorAll('.nav-tabs .nav-link').forEach(t => {
                    t.style.borderBottomWidth = '0';
                });
                tabOption.style.borderBottomWidth = '3px';
                tabOption.style.borderBottomColor = 'var(--bs-primary)';
            }
        }
        
        // Actualizar hash al cambiar de pestaña
        var tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function (e) {
                var target = e.target.getAttribute('data-bs-target');
                window.location.hash = target;
                
                // Active link styles
                document.querySelectorAll('.nav-tabs .nav-link').forEach(t => {
                    t.style.borderBottomWidth = '0';
                });
                e.target.style.borderBottomWidth = '3px';
                e.target.style.borderBottomColor = 'var(--bs-primary)';
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

    <!-- Intervalo de polling para tiempo real -->
    <script>
        setInterval(() => {
            fetch(window.location.href)
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    
                    // Actualizar Inbox
                    const inboxSelector = '#inbox-list';
                    const currentInbox = document.querySelector(inboxSelector);
                    const newInbox = doc.querySelector(inboxSelector);
                    if(currentInbox && newInbox && currentInbox.innerHTML !== newInbox.innerHTML) {
                        currentInbox.innerHTML = newInbox.innerHTML;
                    }
                    
                    // Actualizar Outbox
                    const outboxSelector = '#outbox-list';
                    const currentOutbox = document.querySelector(outboxSelector);
                    const newOutbox = doc.querySelector(outboxSelector);
                    if(currentOutbox && newOutbox && currentOutbox.innerHTML !== newOutbox.innerHTML) {
                        currentOutbox.innerHTML = newOutbox.innerHTML;
                    }
                    
                    // Actualizar Badges en Navbar y Tab
                    const navLink = document.querySelector('a[href="mensajes.php"]');
                    const newNavLink = doc.querySelector('a[href="mensajes.php"]');
                    if(navLink && newNavLink && navLink.innerHTML !== newNavLink.innerHTML) {
                        navLink.innerHTML = newNavLink.innerHTML;
                    }
                    
                    const inboxTab = document.querySelector('#inbox-tab');
                    const newInboxTab = doc.querySelector('#inbox-tab');
                    if(inboxTab && newInboxTab && inboxTab.innerHTML !== newInboxTab.innerHTML) {
                        inboxTab.innerHTML = newInboxTab.innerHTML;
                    }
                })
                .catch(err => console.error('Error polling mensajes:', err));
        }, 5000);
    </script>
</body>

</html>