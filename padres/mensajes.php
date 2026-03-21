<?php
session_start();

// Validar inicio de sesión del Padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../auth/login_padre.php");
    exit();
}

include '../includes/conexion.php';

$id_padre = intval($_SESSION['id_padre']);

// Obtener cantidad de mensajes no leídos
$unread_mensajes_padre = 0;
$stmt_unread = $conexion->prepare("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = ? AND tipo_destinatario = 'Padre' AND leido = 0");
$stmt_unread->bind_param("i", $id_padre);
$stmt_unread->execute();
$res_unread = $stmt_unread->get_result();
if ($res_unread) {
    $unread_mensajes_padre = $res_unread->fetch_assoc()['total'];
}

// Obtener mensajes recibidos (Inbox)
$sql_inbox = "
    SELECT m.id_mensaje, m.asunto, m.fecha_envio, m.leido, 
           CONCAT(d.nombre, ' ', d.apellidos) AS remitente_nombre
    FROM mensajes m
    INNER JOIN docentes d ON m.id_remitente = d.id_docente
    WHERE m.id_destinatario = ? 
      AND m.tipo_destinatario = 'Padre' 
      AND m.tipo_remitente = 'Docente'
    ORDER BY m.fecha_envio DESC
";
$stmt_inb = $conexion->prepare($sql_inbox);
$stmt_inb->bind_param("i", $id_padre);
$stmt_inb->execute();
$resultado_inbox = $stmt_inb->get_result();

// Obtener mensajes enviados (Outbox)
$sql_outbox = "
    SELECT m.id_mensaje, m.asunto, m.fecha_envio, m.leido, 
           CONCAT(d.nombre, ' ', d.apellidos) AS destinatario_nombre
    FROM mensajes m
    INNER JOIN docentes d ON m.id_destinatario = d.id_docente
    WHERE m.id_remitente = ? 
      AND m.tipo_remitente = 'Padre' 
      AND m.tipo_destinatario = 'Docente'
    ORDER BY m.fecha_envio DESC
";
$stmt_out = $conexion->prepare($sql_outbox);
$stmt_out->bind_param("i", $id_padre);
$stmt_out->execute();
$resultado_outbox = $stmt_out->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - Portal Familiar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <style>
        .message-card {
            cursor: pointer;
            border: 1px solid transparent !important;
            transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .message-card:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 0.5rem 1.5rem rgba(25, 135, 84, 0.15) !important;
            border-color: rgba(25, 135, 84, 0.2) !important;
            z-index: 2;
        }
        .status-dot {
            width: 12px;
            height: 12px;
        }
        .msg-avatar {
            width: 50px;
            height: 50px;
            font-size: 1.1rem;
        }
        .bg-gradient-success {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
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
            background: rgba(25, 135, 84, 0.2); 
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(25, 135, 84, 0.4); 
        }

        .nav-tabs .nav-link {
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom-color: transparent !important;
            color: var(--bs-success) !important;
            background-color: var(--bs-body-bg);
        }
        .nav-tabs .nav-link.active {
            background-color: transparent !important;
            border-bottom-width: 3px !important;
        }
    </style>
</head>
<body class="bg-body-tertiary">

    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark floating-nav floating-nav-success mx-auto mb-4 bg-success shadow-sm">
        <div class="container-fluid px-2">
            <a class="navbar-brand fw-bold" href="dashboard.php">👨‍👩‍👧‍👦 Portal Familiar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPadre">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarPadre">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold active d-flex align-items-center" href="mensajes.php">
                            <span class="bg-light text-success rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2 shadow-sm" style="width: 28px; height: 28px;">
                                <i class="bi bi-envelope-fill small"></i>
                            </span> Mensajes
                            <?php if ($unread_mensajes_padre > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-1 shadow-sm"><?php echo $unread_mensajes_padre; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3 d-flex align-items-center">
                        <span class="text-light me-3 fw-medium d-flex align-items-center gap-2">
                            <?php if (!empty($_SESSION['foto_perfil'])): ?>
                                <img src="../assets/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" alt="Perfil" class="rounded-circle border border-2 border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                            <?php else: ?> 👋 <?php endif; ?>
                            Hola, <?php echo htmlspecialchars($_SESSION['nombre_padre']); ?>
                        </span>
                        <a class="btn btn-outline-light btn-sm me-2 rounded-circle shadow-sm premium-icon-btn d-flex justify-content-center align-items-center" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i class="bi bi-person-gear"></i></a>
                        <button class="btn btn-outline-light btn-sm me-2 rounded-circle shadow-sm premium-icon-btn d-flex justify-content-center align-items-center" id="btnThemeToggle" title="Modo Visual">
                            <span id="themeIcon">🌙</span>
                        </button>
                        <a class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm d-flex justify-content-center align-items-center" href="../auth/cerrar_sesion_padre.php"><i class="bi bi-box-arrow-right me-1"></i>Salir</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-3 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h3 class="fw-bold mb-0 text-body d-flex align-items-center">
                <div class="bg-success bg-gradient text-white rounded-circle d-inline-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 48px; height: 48px; font-size: 1.4rem;">
                    <i class="bi bi-chat-dots-fill"></i>
                </div> Mensajes
            </h3>
            <div>
                <a href="redactar_mensaje.php" class="btn btn-success rounded-pill px-4 shadow-sm py-2 fw-medium d-flex align-items-center hover-scale">
                    <i class="bi bi-pencil-square me-2"></i> Escribir Mensaje
                </a>
            </div>
        </div>

        <div class="card shadow border-0 rounded-4 overflow-hidden bg-transparent">
            <div class="card-header bg-body border-bottom-0 pt-4 px-4 pb-0 rounded-top-4 shadow-sm z-1 position-relative">
                <ul class="nav nav-tabs nav-fill fw-bold" id="mensajesTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-success border-0 border-bottom border-3 border-success mb-0 pb-3" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox" type="button" role="tab" aria-controls="inbox" aria-selected="true">
                            <i class="bi bi-inbox-fill me-2 fs-5 align-middle"></i> Bandeja de Entrada
                            <?php if ($unread_mensajes_padre > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-2 shadow-sm"><?php echo $unread_mensajes_padre; ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-secondary border-0 mb-0 pb-3 hover-text-success" id="outbox-tab" data-bs-toggle="tab" data-bs-target="#outbox" type="button" role="tab" aria-controls="outbox" aria-selected="false">
                            <i class="bi bi-send-fill me-2 fs-5 align-middle"></i> Mensajes Enviados
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-0">
                <div class="tab-content" id="mensajesTabsContent">
                    
                    <!-- TAB INBOX -->
                    <div class="tab-pane fade show active" id="inbox" role="tabpanel" aria-labelledby="inbox-tab">
                        <div class="p-3 bg-body-tertiary custom-scroll rounded-bottom-4" style="max-height: 65vh; overflow-y: auto;" id="inbox-list">
                            <?php if ($resultado_inbox && $resultado_inbox->num_rows > 0): ?>
                                <?php while ($msg = $resultado_inbox->fetch_assoc()): ?>
                                    <div class="message-card d-flex align-items-center p-3 mb-3 rounded-4 bg-body shadow-sm position-relative transition-all" onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                        <!-- Unread dot -->
                                        <div class="msg-status me-3">
                                            <?php if ($msg['leido'] == 0): ?>
                                                <div class="status-dot bg-success rounded-circle shadow-sm" title="No Leído"></div>
                                            <?php else: ?>
                                                <div class="status-dot border border-2 border-muted rounded-circle opacity-50" title="Leído"></div>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Avatar -->
                                        <div class="msg-avatar bg-gradient-success text-white rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0 fw-bold shadow-sm">
                                            <?php echo strtoupper(substr($msg['remitente_nombre'], 0, 1)); ?>
                                        </div>
                                        <!-- Content -->
                                        <div class="msg-content flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                                <h6 class="mb-0 fw-bold <?php echo $msg['leido'] == 0 ? 'text-success' : 'text-body'; ?> text-truncate">
                                                    <?php echo htmlspecialchars($msg['remitente_nombre']); ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success fw-normal ms-2 small rounded-pill px-2">Docente</span>
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
                                    <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3">
                                        <i class="bi bi-inbox fs-1"></i>
                                    </div>
                                    <h5 class="fw-bold text-body">Bandeja Vacía</h5>
                                    <p class="text-muted mb-0">No has recibido mensajes de los docentes.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- TAB OUTBOX -->
                    <div class="tab-pane fade" id="outbox" role="tabpanel" aria-labelledby="outbox-tab">
                        <div class="p-3 bg-body-tertiary custom-scroll rounded-bottom-4" style="max-height: 65vh; overflow-y: auto;" id="outbox-list">
                            <?php if ($resultado_outbox && $resultado_outbox->num_rows > 0): ?>
                                <?php while ($msg = $resultado_outbox->fetch_assoc()): ?>
                                    <div class="message-card d-flex align-items-center p-3 mb-3 rounded-4 bg-body shadow-sm position-relative transition-all" onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                        <!-- Read status icon -->
                                        <div class="msg-status me-3 text-center" style="width: 24px;">
                                            <?php if ($msg['leido'] == 1): ?>
                                                <i class="bi bi-check-all text-success fs-5" title="Visto"></i>
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
                                    <p class="text-muted mb-0">No has enviado ningún mensaje a los docentes.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        // Manejo de tabs visuales active state overrides si se necesita
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', event => {
                // Remove all bottom borders and active classes
                document.querySelectorAll('.nav-tabs .nav-link').forEach(t => {
                    t.classList.remove('border-bottom', 'border-3', 'border-success', 'text-success');
                    t.classList.add('text-secondary', 'border-0');
                });
                // Add to newly active
                event.target.classList.remove('text-secondary', 'border-0');
                event.target.classList.add('border-bottom', 'border-3', 'border-success', 'text-success');
            })
        });
    </script>
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
<?php
if(isset($stmt_unread)) $stmt_unread->close();
if(isset($stmt_inb)) $stmt_inb->close();
if(isset($stmt_out)) $stmt_out->close();
if(isset($conexion)) $conexion->close();
?>
