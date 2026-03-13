<?php
session_start();
include '../includes/conexion.php';

// Validar inicio de sesiÃ³n del Padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../auth/login_padre.php");
    exit();
}

$id_padre = intval($_SESSION['id_padre']);

// Obtener cantidad de mensajes no leÃ­dos
$unread_mensajes_padre = 0;
$res_unread = $conexion->query("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = $id_padre AND tipo_destinatario = 'Padre' AND leido = 0");
if ($res_unread) {
    $unread_mensajes_padre = $res_unread->fetch_assoc()['total'];
}

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
$stmt = $conexion->prepare($sql_outbox);
$stmt->bind_param("i", $id_padre);
$stmt->execute();
$resultado_outbox = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes Enviados - Portal Familiar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="bg-body-tertiary">

    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">ðŸ‘¨â€ðŸ‘©â€ðŸ‘§â€ðŸ‘¦ Portal Familiar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPadre">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarPadre">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold active" href="mensajes.php"><i
                                class="bi bi-envelope-fill me-1"></i> Mensajes
                            <?php if ($unread_mensajes_padre > 0): ?>
                                <span
                                    class="badge bg-danger rounded-pill ms-1 shadow-sm"><?php echo $unread_mensajes_padre; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3 d-flex align-items-center">
                        <span class="text-light me-3 fw-medium">👋 Hola,
                            <?php echo htmlspecialchars($_SESSION['nombre_padre']); ?>
                        </span>
                        <a class="btn btn-outline-light btn-sm me-2" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i class="bi bi-person-gear mb-1"></i></a>
                        <button class="btn btn-outline-light btn-sm me-2" id="btnThemeToggle" title="Modo Visual">
                            <span id="themeIcon">🌙</span>
                        </button>
                        <a class="btn btn-danger btn-sm rounded-pill px-3" href="../auth/cerrar_sesion_padre.php"><i
                                class="bi bi-box-arrow-right me-1"></i>Salir</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h3 class="fw-bold mb-0 text-body"><i class="bi bi-send-fill text-success me-2"></i>Mensajes Enviados</h3>
            <div>
                <a href="mensajes.php" class="btn btn-outline-secondary rounded-pill me-2 px-3"><i
                        class="bi bi-inbox me-1"></i>Bandeja de Entrada</a>
                <a href="redactar_mensaje.php" class="btn btn-success rounded-pill px-3 shadow-sm"><i
                        class="bi bi-pencil-square me-1"></i>Nuevo Mensaje</a>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3" style="width: 50px;"></th>
                                <th class="py-3">Para (Maestro)</th>
                                <th class="py-3">Asunto</th>
                                <th class="py-3">Fecha de EnvÃ­o</th>
                                <th class="text-center pe-4 py-3">Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado_outbox && $resultado_outbox->num_rows > 0): ?>
                                <?php while ($msg = $resultado_outbox->fetch_assoc()): ?>
                                    <tr class="cursor-pointer transition-all"
                                        onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                        <td class="ps-4 text-center text-secondary">
                                            <i class="bi bi-send"></i>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-circle d-flex justify-content-center align-items-center me-3"
                                                    style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                    <?php echo strtoupper(substr($msg['destinatario_nombre'], 0, 1) . substr(explode(' ', $msg['destinatario_nombre'])[1] ?? '', 0, 1)); ?>
                                                </div>
                                                <span class="text-body fw-medium">
                                                    <?php echo htmlspecialchars($msg['destinatario_nombre']); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-body">
                                            <?php echo htmlspecialchars($msg['asunto']); ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo date('d M Y - h:i A', strtotime($msg['fecha_envio'])); ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <?php if ($msg['leido'] == 1): ?>
                                                <span class="badge bg-success rounded-pill px-3"><i
                                                        class="bi bi-check2-all me-1"></i>LeÃ­do</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill px-3"><i
                                                        class="bi bi-check2 me-1"></i>Enviado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-5">
                                        <i class="bi bi-send-x display-4 d-block opacity-50 mb-3 text-success"></i>
                                        <h5 class="text-body fw-bold">No has enviado mensajes</h5>
                                        <p class="mb-0">Tus comunicaciones enviadas directamente a los docentes aparecerÃ¡n
                                            aquÃ­.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        tbody tr:hover {
            background-color: var(--bs-secondary-bg) !important;
            transform: scale(1.002);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

</body>

</html>
<?php
$stmt->close();
$conexion->close();
?>
