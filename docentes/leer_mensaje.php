<?php
session_start();
include '../includes/conexion.php';

// Validar que el usuario sea Docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

$id_docente = $_SESSION['id_docente'];
$id_mensaje = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_mensaje === 0) {
    header("Location: mensajes.php");
    exit();
}

$mensaje_alerta = '';
$tipo_alerta = '';

// Procesar respuesta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['respuesta'])) {
    $id_destinatario = intval($_POST['id_destinatario']);
    $id_alumno = !empty($_POST['id_alumno']) ? intval($_POST['id_alumno']) : "NULL";
    $asunto_original = $conexion->real_escape_string($_POST['asunto_original']);
    $respuesta_texto = $conexion->real_escape_string(trim($_POST['respuesta_texto']));
    $fecha = date('Y-m-d H:i:s');

    // Prefix "Re:" si no lo tiene
    $nuevo_asunto = (strpos($asunto_original, 'Re:') === 0) ? $asunto_original : "Re: " . $asunto_original;

    $sql_reply = "INSERT INTO mensajes (id_remitente, tipo_remitente, id_destinatario, tipo_destinatario, id_alumno, asunto, mensaje, fecha_envio, leido) 
                  VALUES ($id_docente, 'Docente', $id_destinatario, 'Padre', $id_alumno, '$nuevo_asunto', '$respuesta_texto', '$fecha', 0)";

    if ($conexion->query($sql_reply)) {
        header("Location: mensajes_enviados.php?msg=respondido");
        exit();
    } else {
        $mensaje_alerta = 'Error al enviar respuesta: ' . $conexion->error;
        $tipo_alerta = 'danger';
    }
}

// Obtener el mensaje completo
$sql_msg = "
    SELECT m.*, 
           CONCAT(p.nombre, ' ', p.apellidos) AS padre_nombre,
           CONCAT(a.nombre, ' ', a.apellidos) AS alumno_nombre
    FROM mensajes m
    LEFT JOIN padres p ON (m.tipo_remitente = 'Padre' AND m.id_remitente = p.id_padre) 
                       OR (m.tipo_destinatario = 'Padre' AND m.id_destinatario = p.id_padre)
    LEFT JOIN alumnos a ON m.id_alumno = a.id_alumno
    WHERE m.id_mensaje = ? 
      AND ((m.id_destinatario = ? AND m.tipo_destinatario = 'Docente') OR (m.id_remitente = ? AND m.tipo_remitente = 'Docente'))
";
$stmt = $conexion->prepare($sql_msg);
$stmt->bind_param("iii", $id_mensaje, $id_docente, $id_docente);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: mensajes.php");
    exit();
}

$msg = $resultado->fetch_assoc();
$es_inbox = ($msg['id_destinatario'] == $id_docente && $msg['tipo_destinatario'] == 'Docente');

// Marcar como leído si es un mensaje de entrada
if ($es_inbox && $msg['leido'] == 0) {
    $stmt_upd = $conexion->prepare("UPDATE mensajes SET leido = 1 WHERE id_mensaje = ?");
    $stmt_upd->bind_param("i", $id_mensaje);
    $stmt_upd->execute();
    $stmt_upd->close();
}

include '../includes/header.php';
?>

<div class="container animate-fade-in" style="animation-delay: 0.1s;">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="<?php echo $es_inbox ? 'mensajes.php' : 'mensajes_enviados.php'; ?>"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>

            <?php if ($mensaje_alerta): ?>
                <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm rounded-3"
                    role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?php echo $mensaje_alerta; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <!-- Header del Mensaje -->
                <div class="card-header bg-body p-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold text-body mb-1 mb-md-2" style="line-height: 1.4;">
                            <?php echo htmlspecialchars($msg['asunto']); ?>
                        </h4>
                        <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                            <?php if ($es_inbox): ?>
                                <span class="badge bg-primary rounded-pill fw-normal"><i
                                        class="bi bi-box-arrow-in-down me-1"></i>Recibido</span>
                                <span>De: <strong class="text-body">
                                        <?php echo htmlspecialchars($msg['padre_nombre']); ?>
                                    </strong> (Familia/Tutor)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill fw-normal"><i
                                        class="bi bi-send me-1"></i>Enviado</span>
                                <span>Para: <strong class="text-body">
                                        <?php echo htmlspecialchars($msg['padre_nombre']); ?>
                                    </strong> (Familia/Tutor)</span>
                            <?php endif; ?>

                            <?php if ($msg['alumno_nombre']): ?>
                                <span class="mx-1 text-secondary opacity-50">|</span>
                                <span>Referente a: <strong class="text-info-emphasis">
                                        <?php echo htmlspecialchars($msg['alumno_nombre']); ?>
                                    </strong></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="text-end d-none d-md-block ms-4 text-muted small">
                        <?php echo date('d M Y', strtotime($msg['fecha_envio'])); ?><br>
                        <strong>
                            <?php echo date('h:i A', strtotime($msg['fecha_envio'])); ?>
                        </strong>
                    </div>
                </div>

                <!-- Cuerpo del Mensaje -->
                <div class="card-body p-4 p-md-5" style="min-height: 250px;">
                    <div class="d-md-none text-end mb-4 text-muted small border-bottom pb-2">
                        <?php echo date('d M Y - h:i A', strtotime($msg['fecha_envio'])); ?>
                    </div>

                    <p class="fs-5 text-body" style="white-space: pre-line; line-height: 1.7;">
                        <?php echo htmlspecialchars($msg['mensaje']); ?>
                    </p>
                </div>
            </div>

            <!-- Caja de Respuesta (Solo si es Inbox) -->
            <?php if ($es_inbox): ?>
                <div class="card shadow-sm border-0 rounded-4 bg-primary bg-opacity-10 mb-5">
                    <div class="card-body p-4 p-md-5">
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-reply-fill me-2"></i>Responder al Tutor</h5>
                        <form action="leer_mensaje.php?id=<?php echo $id_mensaje; ?>" method="POST">
                            <input type="hidden" name="respuesta" value="1">
                            <input type="hidden" name="id_destinatario" value="<?php echo $msg['id_remitente']; ?>">
                            <!-- El autor original es el nuevo destinatario -->
                            <input type="hidden" name="id_alumno" value="<?php echo $msg['id_alumno']; ?>">
                            <input type="hidden" name="asunto_original"
                                value="<?php echo htmlspecialchars($msg['asunto']); ?>">

                            <div class="mb-3">
                                <textarea name="respuesta_texto"
                                    class="form-control rounded-3 shadow-none p-3 border-primary border-opacity-25" rows="4"
                                    placeholder="Escribe tu respuesta aquí..." required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 hover-scale">
                                    Enviar Respuesta <i class="bi bi-send-fill ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
$stmt->close();
$conexion->close();
?>
</div> <!-- Cierra contenedor-principal de header.php -->

<!-- Scripts requeridos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>

</body>

</html>