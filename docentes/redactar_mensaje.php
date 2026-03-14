<?php
session_start();
include '../includes/conexion.php';

// Validar que el usuario sea Docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

$id_docente = $_SESSION['id_docente'];
$mensaje_alerta = '';
$tipo_alerta = '';

// Procesar envío de formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_destinatario = intval($_POST['destinatario']);
    $id_alumno = !empty($_POST['alumno']) ? intval($_POST['alumno']) : "NULL";
    $asunto = $conexion->real_escape_string(trim($_POST['asunto']));
    $mensaje = $conexion->real_escape_string(trim($_POST['mensaje']));
    $fecha = date('Y-m-d H:i:s');

    // El docente le envía al Padre (Padre es el destinatario)
    $sql_insert = "INSERT INTO mensajes (id_remitente, tipo_remitente, id_destinatario, tipo_destinatario, id_alumno, asunto, mensaje, fecha_envio, leido) 
                   VALUES ($id_docente, 'Docente', $id_destinatario, 'Padre', $id_alumno, '$asunto', '$mensaje', '$fecha', 0)";

    if ($conexion->query($sql_insert)) {
        header("Location: mensajes.php#outbox-pane");
        exit();
    } else {
        $mensaje_alerta = 'Error al enviar el mensaje: ' . $conexion->error;
        $tipo_alerta = 'danger';
    }
}

// Obtener la lista de Padres correspondientes a los Alumnos de este Docente
// Solo puede enviarle mensaje a los padres de sus propios estudiantes
$sql_padres = "
    SELECT DISTINCT p.id_padre, p.nombre AS padre_nombre, p.apellidos AS padre_apellidos, 
                    a.id_alumno, a.nombre AS alumno_nombre, a.apellidos AS alumno_apellidos, a.grado, a.grupo
    FROM padres p
    INNER JOIN padre_alumno pa ON p.id_padre = pa.id_padre
    INNER JOIN alumnos a ON pa.id_alumno = a.id_alumno
    INNER JOIN calificaciones c ON a.id_alumno = c.id_alumno
    INNER JOIN docente_materia_grupo dmg ON c.id_materia = dmg.id_materia
        AND a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo
    WHERE dmg.id_docente = $id_docente
    ORDER BY p.apellidos, a.apellidos
";
$resultado_padres = $conexion->query($sql_padres);

$destinatarios = [];
if ($resultado_padres && $resultado_padres->num_rows > 0) {
    while ($row = $resultado_padres->fetch_assoc()) {
        $destinatarios[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="container animate-fade-in" style="animation-delay: 0.1s;">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-pencil-square text-primary me-2"></i>Escribir Mensaje</h2>
                <a href="mensajes.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancelar</a>
            </div>

            <?php if ($mensaje_alerta): ?>
                <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm rounded-3"
                    role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?php echo $mensaje_alerta; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="redactar_mensaje.php" method="POST" class="needs-validation" novalidate>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary"><i class="bi bi-person-badge me-2"></i>Para
                                (Tutor del Alumno) <span class="text-danger">*</span></label>
                            <select name="destinatario" id="destinatario"
                                class="form-select border-start-0 border-top-0 border-end-0 rounded-0 shadow-none px-0"
                                style="border-width: 2px;" required onchange="actualizarAlumnoSeleccionado(this)">
                                <option value="" selected disabled>Selecciona a la familia destinataria...</option>
                                <?php if (count($destinatarios) > 0): ?>
                                    <?php foreach ($destinatarios as $dest): ?>
                                        <option value="<?php echo $dest['id_padre']; ?>"
                                            data-alumno="<?php echo $dest['id_alumno']; ?>">
                                            <?php echo htmlspecialchars($dest['padre_apellidos'] . ' ' . $dest['padre_nombre']) . ' — (Hijo/a: ' . htmlspecialchars($dest['alumno_nombre'] . ' ' . $dest['alumno_apellidos']) . ' | ' . $dest['grado'] . 'º ' . $dest['grupo'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No tienes padres de familia registrados asociados a tus
                                        grupos.</option>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Debes seleccionar a un destinatario.</div>
                        </div>

                        <!-- Campo oculto para pasar el ID del alumno automáticamente -->
                        <input type="hidden" name="alumno" id="alumno_oculto" value="">

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary"><i
                                    class="bi bi-card-heading me-2"></i>Asunto <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="asunto"
                                class="form-control border-start-0 border-top-0 border-end-0 rounded-0 shadow-none px-0"
                                style="border-width: 2px;" placeholder="Escribe el motivo del mensaje..." required
                                maxlength="200">
                            <div class="invalid-feedback">Por favor, escribe un asunto para el mensaje.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary"><i
                                    class="bi bi-chat-text-fill me-2"></i>Mensaje <span
                                    class="text-danger">*</span></label>
                            <textarea name="mensaje" class="form-control rounded-3 shadow-none p-3" rows="6"
                                placeholder="Escribe aquí tu mensaje detallado para la familia..." required></textarea>
                            <div class="invalid-feedback">El cuerpo del mensaje no puede estar vacío.</div>
                        </div>

                        <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                            <button type="submit"
                                class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm hover-scale">
                                Enviar Mensaje <i class="bi bi-send-fill ms-2"></i>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus,
    .form-select:focus {
        border-color: var(--bs-primary) !important;
        box-shadow: none !important;
    }
</style>

<script>
    function actualizarAlumnoSeleccionado(selectElement) {
        var id_alumno_asociado = selectElement.options[selectElement.selectedIndex].getAttribute('data-alumno');
        document.getElementById('alumno_oculto').value = id_alumno_asociado;
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
$conexion->close();
?>
</div> <!-- Cierra contenedor-principal de header.php -->

<!-- Scripts requeridos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>

</body>

</html>