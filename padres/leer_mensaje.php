<?php
session_start();
include '../includes/conexion.php';

// Validar que el usuario sea Padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../auth/login_padre.php");
    exit();
}

$id_padre = $_SESSION['id_padre'];
$id_mensaje = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_mensaje === 0) {
    header("Location: mensajes.php");
    exit();
}

// Obtener cantidad de mensajes no leídos
$unread_mensajes_padre = 0;
$res_unread = $conexion->query("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = $id_padre AND tipo_destinatario = 'Padre' AND leido = 0");
if ($res_unread) {
    // Si estamos abriendo un mensaje y es para nosotros y no está leído, descontamos 1 (porque se marcará como leído ahora)
    $unread_mensajes_padre = $res_unread->fetch_assoc()['total'];
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
                  VALUES ($id_padre, 'Padre', $id_destinatario, 'Docente', $id_alumno, '$nuevo_asunto', '$respuesta_texto', '$fecha', 0)";

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
           CONCAT(d.nombre, ' ', d.apellidos) AS docente_nombre,
           CONCAT(a.nombre, ' ', a.apellidos) AS alumno_nombre
    FROM mensajes m
    LEFT JOIN docentes d ON (m.tipo_remitente = 'Docente' AND m.id_remitente = d.id_docente) 
                         OR (m.tipo_destinatario = 'Docente' AND m.id_destinatario = d.id_docente)
    LEFT JOIN alumnos a ON m.id_alumno = a.id_alumno
    WHERE m.id_mensaje = ? 
      AND ((m.id_destinatario = ? AND m.tipo_destinatario = 'Padre') OR (m.id_remitente = ? AND m.tipo_remitente = 'Padre'))
";
$stmt = $conexion->prepare($sql_msg);
$stmt->bind_param("iii", $id_mensaje, $id_padre, $id_padre);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: mensajes.php");
    exit();
}

$msg = $resultado->fetch_assoc();
$es_inbox = ($msg['id_destinatario'] == $id_padre && $msg['tipo_destinatario'] == 'Padre');

// Marcar como leído si es un mensaje de entrada
if ($es_inbox && $msg['leido'] == 0) {
    $conexion->query("UPDATE mensajes SET leido = 1 WHERE id_mensaje = $id_mensaje");
    // Actualizar el contador de no leídos ya que este se acaba de mostrar
    if ($unread_mensajes_padre > 0)
        $unread_mensajes_padre--;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lectura de Mensaje - Portal Familiar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="bg-body-tertiary">

    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">👨‍👩‍👧‍👦 Portal Familiar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPadre">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarPadre">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold" href="mensajes.php"><i
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
                        <button class="btn btn-outline-light btn-sm me-2" id="btnThemeToggle" title="Modo Visual">
                            <span id="themeIcon">🌙</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container py-4 animate-fade-in" style="animation-delay: 0.1s;">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="<?php echo $es_inbox ? 'mensajes.php' : 'mensajes_enviados.php'; ?>"
                        class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>

                <?php if ($mensaje_alerta): ?>
                    <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm rounded-4"
                        role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <?php echo $mensaje_alerta; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <!-- Header del Mensaje -->
                    <div
                        class="card-header bg-body p-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold text-body mb-2" style="line-height: 1.4;">
                                <?php echo htmlspecialchars($msg['asunto']); ?>
                            </h4>
                            <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                                <?php if ($es_inbox): ?>
                                    <span class="badge bg-success rounded-pill px-3 fw-normal py-2"><i
                                            class="bi bi-box-arrow-in-down me-1"></i>Recibido</span>
                                    <span>De Prof(a): <strong class="text-body fw-bold">
                                            <?php echo htmlspecialchars($msg['docente_nombre']); ?>
                                        </strong></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-3 fw-normal py-2"><i
                                            class="bi bi-send me-1"></i>Enviado</span>
                                    <span>Para Prof(a): <strong class="text-body fw-bold">
                                            <?php echo htmlspecialchars($msg['docente_nombre']); ?>
                                        </strong></span>
                                <?php endif; ?>

                                <?php if ($msg['alumno_nombre']): ?>
                                    <span class="mx-1 text-secondary opacity-50 d-none d-sm-inline">|</span>
                                    <span class="badge bg-info text-dark rounded-pill fw-normal px-2">Referente a:
                                        <?php echo htmlspecialchars($msg['alumno_nombre']); ?>
                                    </span>
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

                        <div class="p-4 bg-body-tertiary rounded-4"
                            style="white-space: pre-line; line-height: 1.8; font-size: 1.1rem; color: var(--bs-body-color);">
                            <?php echo htmlspecialchars($msg['mensaje']); ?>
                        </div>
                    </div>
                </div>

                <!-- Caja de Respuesta (Solo si es Inbox) -->
                <?php if ($es_inbox): ?>
                    <div class="card shadow-sm border-0 rounded-4 bg-success bg-opacity-10 mb-5">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="fw-bold text-success mb-3"><i class="bi bi-reply-fill me-2"></i>Responder al Maestro
                            </h5>
                            <form action="leer_mensaje.php?id=<?php echo $id_mensaje; ?>" method="POST"
                                class="needs-validation" novalidate>
                                <input type="hidden" name="respuesta" value="1">
                                <input type="hidden" name="id_destinatario" value="<?php echo $msg['id_remitente']; ?>">
                                <!-- El autor original es el nuevo destinatario -->
                                <input type="hidden" name="id_alumno" value="<?php echo $msg['id_alumno']; ?>">
                                <input type="hidden" name="asunto_original"
                                    value="<?php echo htmlspecialchars($msg['asunto']); ?>">

                                <div class="mb-4">
                                    <textarea name="respuesta_texto" class="form-control rounded-4 shadow-sm p-4 border-0"
                                        rows="5"
                                        placeholder="Escribe tu respuesta aquí. El maestro la recibirá en su buzón."
                                        required></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit"
                                        class="btn btn-success fw-bold rounded-pill px-5 py-2 shadow-sm hover-scale">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        // Validación Bootstrap
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>

</body>

</html>
<?php
$stmt->close();
$conexion->close();
?>