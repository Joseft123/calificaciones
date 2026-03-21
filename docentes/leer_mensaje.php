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
        header("Location: mensajes.php#outbox-pane");
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

<style>
    .chat-container {
        max-height: 60vh;
        overflow-y: auto;
        scroll-behavior: smooth;
    }

    /* Estilos Burbujas Chat */
    .chat-bubble {
        max-width: 80%;
        padding: 1rem 1.25rem;
        border-radius: 1.5rem;
        position: relative;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        margin-bottom: 1rem;
        line-height: 1.5;
        font-size: 1.05rem;
    }

    .chat-bubble-received {
        background-color: var(--bs-white);
        border: 1px solid rgba(0,0,0,0.05);
        border-top-left-radius: 0.25rem;
        color: var(--bs-body-color);
    }

    .chat-bubble-sent {
        background-color: var(--bs-primary);
        color: white;
        border-top-right-radius: 0.25rem;
        margin-left: auto;
    }

    .chat-meta {
        font-size: 0.8rem;
        margin-top: 0.5rem;
        opacity: 0.8;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .chat-bubble-sent .chat-meta {
        color: rgba(255,255,255,0.9);
    }
    
    .chat-bubble-received .chat-meta {
        color: var(--bs-secondary);
    }

    /* Scrollbar custom */
    .chat-container::-webkit-scrollbar {
        width: 6px;
    }
    .chat-container::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.02);
        border-radius: 10px;
    }
    .chat-container::-webkit-scrollbar-thumb {
        background: rgba(13, 110, 253, 0.2); 
        border-radius: 10px;
    }
    .chat-container::-webkit-scrollbar-thumb:hover {
        background: rgba(13, 110, 253, 0.4); 
    }

    .reply-box textarea {
        resize: none;
        border-radius: 1.5rem;
    }
</style>

<div class="container animate-fade-in" style="animation-delay: 0.1s;">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="<?php echo $es_inbox ? 'mensajes.php' : 'mensajes.php#outbox-pane'; ?>"
                    class="btn btn-outline-secondary rounded-pill px-4 shadow-sm hover-scale d-flex align-items-center">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
                
                <?php if ($msg['alumno_nombre']): ?>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3 py-2 fw-medium shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-person-video2"></i> Referente a: <?php echo htmlspecialchars($msg['alumno_nombre']); ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($mensaje_alerta): ?>
                <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm rounded-4" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?php echo $mensaje_alerta; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden mb-4 d-flex flex-column" style="height: 75vh;">
                
                <!-- Header Chat -->
                <div class="card-header bg-white p-3 border-bottom d-flex align-items-center shadow-sm z-1">
                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm flex-shrink-0" style="width: 50px; height: 50px; font-size: 1.2rem; font-weight: bold;">
                        <?php echo strtoupper(substr($msg['padre_nombre'], 0, 1)); ?>
                    </div>
                    <div class="d-flex flex-column w-100">
                        <h5 class="fw-bold text-body mb-0 text-truncate" style="max-width: 90%;">
                            <?php echo htmlspecialchars($msg['asunto']); ?>
                        </h5>
                        <span class="text-secondary small d-flex align-items-center gap-1">
                            <i class="bi <?php echo $es_inbox ? 'bi-box-arrow-in-down text-primary' : 'bi-send text-secondary'; ?>"></i>
                            <?php echo $es_inbox ? 'De: Padre/Tutor' : 'Para: Padre/Tutor'; ?> 
                            <strong class="text-dark"><?php echo htmlspecialchars($msg['padre_nombre']); ?></strong>
                        </span>
                    </div>
                </div>

                <!-- Cuerpo Chat (Mensajes Históricos / Actual) -->
                <div class="card-body p-4 chat-container bg-light d-flex flex-column" id="chatWindow">
                    
                    <div class="text-center mb-4">
                        <span class="badge bg-secondary bg-opacity-25 text-secondary rounded-pill px-3 py-1 fw-normal shadow-sm">
                            <?php echo date('d \d\e F, Y', strtotime($msg['fecha_envio'])); ?>
                        </span>
                    </div>

                    <div class="chat-bubble <?php echo $es_inbox ? 'chat-bubble-received' : 'chat-bubble-sent'; ?> animate-fade-in" style="animation-delay: 0.2s;">
                        <div style="white-space: pre-line;">
                            <?php echo htmlspecialchars($msg['mensaje']); ?>
                        </div>
                        <div class="chat-meta">
                            <span><?php echo date('h:i A', strtotime($msg['fecha_envio'])); ?></span>
                            <?php if (!$es_inbox): ?>
                                <i class="bi <?php echo $msg['leido'] == 1 ? 'bi-check-all text-light' : 'bi-check2 text-light opacity-75'; ?>"></i>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Input de Respuesta (Fijado abajo) -->
                <?php if ($es_inbox): ?>
                    <div class="card-footer bg-white p-3 border-top reply-box z-1 shadow-sm">
                        <form action="leer_mensaje.php?id=<?php echo $id_mensaje; ?>" method="POST" class="needs-validation m-0 d-flex gap-2" novalidate id="replyForm">
                            <input type="hidden" name="respuesta" value="1">
                            <input type="hidden" name="id_destinatario" value="<?php echo $msg['id_remitente']; ?>">
                            <input type="hidden" name="id_alumno" value="<?php echo $msg['id_alumno']; ?>">
                            <input type="hidden" name="asunto_original" value="<?php echo htmlspecialchars($msg['asunto']); ?>">

                            <div class="flex-grow-1 position-relative">
                                <textarea name="respuesta_texto" id="replyTextarea" class="form-control form-control-lg bg-light border-0 px-4 py-3" rows="1" style="max-height: 120px; overflow-y: auto;" placeholder="Escribe un mensaje de respuesta..." required></textarea>
                            </div>
                            <div class="d-flex align-items-end">
                                <button type="submit" class="btn btn-primary rounded-circle shadow-sm hover-scale d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;" title="Enviar Respuesta">
                                    <i class="bi bi-send-fill fs-5"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
    // Autoajustar altura del textarea de respuesta
    const tx = document.getElementById('replyTextarea');
    if(tx) {
        tx.setAttribute('style', 'height:' + (tx.scrollHeight) + 'px; overflow-y:hidden;');
        tx.addEventListener("input", OnInput, false);
    }

    function OnInput() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        if(this.scrollHeight > 120) {
             this.style.overflowY = 'auto'; // habilitar scroll si crece mucho
        }
    }

    // Scroll al fondo inicial del chat
    const chatWindow = document.getElementById('chatWindow');
    if(chatWindow) {
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

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

<?php
$stmt->close();
$conexion->close();
?>
</div> <!-- Cierra contenedor-principal de header.php -->

<!-- Scripts requeridos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>

    <!-- Intervalo de polling para tiempo real en chat -->
    <script>
        setInterval(() => {
            fetch(window.location.href)
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    
                    // Actualizar Burbuja y estados de lectura
                    const currentChat = document.getElementById('chatWindow');
                    const newChat = doc.getElementById('chatWindow');
                    if(currentChat && newChat && currentChat.innerHTML !== newChat.innerHTML) {
                        currentChat.innerHTML = newChat.innerHTML;
                    }
                    
                    // Actualizar Badges en Navbar
                    const navLink = document.querySelector('a[href="mensajes.php"]');
                    const newNavLink = doc.querySelector('a[href="mensajes.php"]');
                    if(navLink && newNavLink && navLink.innerHTML !== newNavLink.innerHTML) {
                        navLink.innerHTML = newNavLink.innerHTML;
                    }
                })
                .catch(err => console.error('Error polling chat:', err));
        }, 5000);
    </script>
</body>

</html>