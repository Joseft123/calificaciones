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
            <h3 class="fw-bold mb-0 text-body">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex justify-content-center align-items-center me-2 shadow-sm" style="width: 45px; height: 45px; font-size: 1.4rem;">
                    <i class="bi bi-chat-dots-fill"></i>
                </div> Mensajes Directos
            </h3>
            <div>
                <a href="redactar_mensaje.php" class="btn btn-success rounded-pill px-4 shadow py-2 fw-medium d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2"></i> Redactar Mensaje
                </a>
            </div>
        </div>

        <div class="card shadow border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 pb-0">
                <ul class="nav nav-tabs nav-fill fw-bold" id="mensajesTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-success border-0 border-bottom border-3 border-success mb-0 pb-3" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox" type="button" role="tab" aria-controls="inbox" aria-selected="true">
                            <i class="bi bi-inbox-fill me-2"></i> Bandeja de Entrada
                            <?php if ($unread_mensajes_padre > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-2 shadow-sm"><?php echo $unread_mensajes_padre; ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-secondary border-0 mb-0 pb-3 hover-text-success" id="outbox-tab" data-bs-toggle="tab" data-bs-target="#outbox" type="button" role="tab" aria-controls="outbox" aria-selected="false">
                            <i class="bi bi-send-fill me-2"></i> Mensajes Enviados
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-0">
                <div class="tab-content" id="mensajesTabsContent">
                    
                    <!-- TAB INBOX -->
                    <div class="tab-pane fade show active" id="inbox" role="tabpanel" aria-labelledby="inbox-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0 table-borderless table-striped">
                                <thead>
                                    <tr class="border-bottom">
                                        <th class="ps-4 py-3 text-secondary ps-4" style="width: 60px;">Est.</th>
                                        <th class="py-3 text-secondary">Remitente</th>
                                        <th class="py-3 text-secondary">Asunto</th>
                                        <th class="py-3 text-secondary">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultado_inbox && $resultado_inbox->num_rows > 0): ?>
                                        <?php while ($msg = $resultado_inbox->fetch_assoc()): ?>
                                            <tr class="cursor-pointer transition-all border-bottom position-relative" onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                                <td class="ps-4 text-center">
                                                    <?php if ($msg['leido'] == 0): ?>
                                                        <i class="bi bi-circle-fill text-success small shadow-sm" title="No Leído"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-envelope-open text-muted" title="Leído"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center py-2">
                                                        <div class="bg-success text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm flex-shrink-0" style="width: 40px; height: 40px; font-size: 1rem; font-weight: bold;">
                                                            <?php echo strtoupper(substr($msg['remitente_nombre'], 0, 1)); ?>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="mb-0 fw-bold <?php echo $msg['leido'] == 0 ? 'text-success' : 'text-body'; ?>">
                                                                <?php echo htmlspecialchars($msg['remitente_nombre']); ?>
                                                            </span>
                                                            <span class="text-muted small">Docente</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="<?php echo $msg['leido'] == 0 ? 'fw-bold text-dark' : 'text-muted'; ?>">
                                                        <?php echo htmlspecialchars($msg['asunto']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo date('d M Y, h:i a', strtotime($msg['fecha_envio'])); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-5">
                                                <div class="empty-state p-5 mx-auto text-center border-0 bg-transparent">
                                                    <i class="bi bi-inbox display-3 d-block text-success opacity-50 mb-3"></i>
                                                    <h5 class="text-body fw-bold">Bandeja Vacía</h5>
                                                    <p class="text-muted mb-0">No has recibido mensajes de los docentes de tus hijos.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- TAB OUTBOX -->
                    <div class="tab-pane fade" id="outbox" role="tabpanel" aria-labelledby="outbox-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0 table-borderless table-striped">
                                <thead>
                                    <tr class="border-bottom">
                                        <th class="ps-4 py-3 text-secondary" style="width: 60px;">Est.</th>
                                        <th class="py-3 text-secondary">Destinatario</th>
                                        <th class="py-3 text-secondary">Asunto</th>
                                        <th class="py-3 text-secondary">Enviado el</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultado_outbox && $resultado_outbox->num_rows > 0): ?>
                                        <?php while ($msg = $resultado_outbox->fetch_assoc()): ?>
                                            <tr class="cursor-pointer transition-all border-bottom position-relative" onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                                <td class="ps-4 text-center">
                                                    <?php if ($msg['leido'] == 1): ?>
                                                        <i class="bi bi-check-all text-success fs-5" title="Visto por el docente"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-check2 text-muted fs-5" title="Enviado"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center py-2">
                                                        <div class="bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 40px; height: 40px; font-size: 1rem; font-weight: bold;">
                                                            <?php echo strtoupper(substr($msg['destinatario_nombre'], 0, 1)); ?>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="mb-0 fw-bold text-body">
                                                                Para: <?php echo htmlspecialchars($msg['destinatario_nombre']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-body fw-medium">
                                                    <?php echo htmlspecialchars($msg['asunto']); ?>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo date('d M Y, h:i a', strtotime($msg['fecha_envio'])); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-5">
                                                <div class="empty-state p-5 mx-auto text-center border-0 bg-transparent">
                                                    <i class="bi bi-send-x display-3 d-block text-secondary opacity-50 mb-3"></i>
                                                    <h5 class="text-body fw-bold">Bandeja Vacía</h5>
                                                    <p class="text-muted mb-0">No has enviado ningún mensaje a los docentes.</p>
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
        .cursor-pointer { cursor: pointer; }
        .transition-all { transition: all 0.2s ease-in-out; }
        tbody tr:hover {
            background-color: rgba(25, 135, 84, 0.05) !important;
            transform: translateX(4px);
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
</body>
</html>
<?php
$stmt_inb->close();
$stmt_out->close();
$conexion->close();
?>>
<?php
$stmt->close();
$conexion->close();
?>
