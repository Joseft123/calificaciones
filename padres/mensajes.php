<?php
session_start();

// Validar inicio de sesiÃ³n del Padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../auth/login_padre.php");
    exit();
}

include '../includes/conexion.php';

$id_padre = intval($_SESSION['id_padre']);

// Obtener cantidad de mensajes no leÃ­dos
$unread_mensajes_padre = 0;
$res_unread = $conexion->query("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = $id_padre AND tipo_destinatario = 'Padre' AND leido = 0");
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
$stmt = $conexion->prepare($sql_inbox);
$stmt->bind_param("i", $id_padre);
$stmt->execute();
$resultado_inbox = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes Recibidos - Portal Familiar</title>
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
            <h3 class="fw-bold mb-0 text-body"><i class="bi bi-inbox-fill text-success me-2"></i>Bandeja de Entrada</h3>
            <div>
                <a href="mensajes_enviados.php" class="btn btn-outline-secondary rounded-pill me-2 px-3"><i
                        class="bi bi-send me-1"></i>Tus Enviados</a>
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
                                <th class="py-3">De (Docente)</th>
                                <th class="py-3">Asunto</th>
                                <th class="py-3">Fecha</th>
                                <th class="text-center pe-4 py-3">Abrir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado_inbox && $resultado_inbox->num_rows > 0): ?>
                                <?php while ($msg = $resultado_inbox->fetch_assoc()): ?>
                                    <tr class="<?php echo $msg['leido'] == 0 ? 'bg-success bg-opacity-10 fw-bold' : ''; ?> cursor-pointer transition-all"
                                        onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                        <td class="ps-4 text-center text-success">
                                            <?php if ($msg['leido'] == 0): ?>
                                                <i class="bi bi-envelope-fill fs-5" title="No LeÃ­do"></i>
                                            <?php else: ?>
                                                <i class="bi bi-envelope-open text-muted fs-5" title="LeÃ­do"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                                    style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                    <?php echo strtoupper(substr($msg['remitente_nombre'], 0, 1) . substr(explode(' ', $msg['remitente_nombre'])[1] ?? '', 0, 1)); ?>
                                                </div>
                                                <span class="<?php echo $msg['leido'] == 0 ? 'text-success' : 'text-body'; ?>">
                                                    <?php echo htmlspecialchars($msg['remitente_nombre']); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($msg['asunto']); ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo date('d M Y - h:i A', strtotime($msg['fecha_envio'])); ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>"
                                                class="btn btn-sm btn-outline-success rounded-pill px-3">Ver</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-5">
                                        <i class="bi bi-mailbox display-4 d-block opacity-50 mb-3 text-success"></i>
                                        <h5 class="text-body fw-bold">No tienes mensajes nuevos</h5>
                                        <p class="mb-0">AquÃ­ aparecerÃ¡n las comunicaciones oficiales de los maestros de tus
                                            hijos.</p>
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
