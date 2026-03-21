<?php
session_start();
include '../includes/conexion.php';
include '../includes/funciones_ciclo.php';

// Validar que el usuario sea Padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../auth/login_padre.php");
    exit();
}

$id_padre = $_SESSION['id_padre'];
$id_ciclo_actual = getCicloActivo($conexion);
$mensaje_alerta = '';
$tipo_alerta = '';

// Obtener cantidad de mensajes no leídos
$unread_mensajes_padre = 0;
$stmt_unread = $conexion->prepare("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = ? AND tipo_destinatario = 'Padre' AND leido = 0");
$stmt_unread->bind_param("i", $id_padre);
$stmt_unread->execute();
$res_unread = $stmt_unread->get_result();
if ($res_unread) {
    $unread_mensajes_padre = $res_unread->fetch_assoc()['total'];
}

// Procesar envío de formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_destinatario = intval($_POST['destinatario']);
    $id_alumno = !empty($_POST['alumno']) ? intval($_POST['alumno']) : "NULL";
    $asunto = $conexion->real_escape_string(trim($_POST['asunto']));
    $mensaje = $conexion->real_escape_string(trim($_POST['mensaje']));
    $fecha = date('Y-m-d H:i:s');

    // El Padre le envía al Docente
    $sql_insert = "INSERT INTO mensajes (id_remitente, tipo_remitente, id_destinatario, tipo_destinatario, id_alumno, asunto, mensaje, fecha_envio, leido) 
                   VALUES ($id_padre, 'Padre', $id_destinatario, 'Docente', $id_alumno, '$asunto', '$mensaje', '$fecha', 0)";

    if ($conexion->query($sql_insert)) {
        header("Location: mensajes.php?msg=enviado");
        exit();
    } else {
        $mensaje_alerta = 'Error al enviar el mensaje: ' . $conexion->error;
        $tipo_alerta = 'danger';
    }
}

// Obtener la lista de Docentes correspondientes a los Alumnos de este Padre
$sql_docentes = "
    SELECT DISTINCT d.id_docente, d.nombre AS docente_nombre, d.apellidos AS docente_apellidos, 
                    a.id_alumno, a.nombre AS alumno_nombre, a.apellidos AS alumno_apellidos, 
                    m.nombre_materia
    FROM alumnos a
    INNER JOIN padre_alumno pa ON a.id_alumno = pa.id_alumno
    INNER JOIN docente_materia_grupo dmg ON a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo
    INNER JOIN docentes d ON dmg.id_docente = d.id_docente
    INNER JOIN materias m ON dmg.id_materia = m.id_materia
    WHERE pa.id_padre = $id_padre AND dmg.id_ciclo = $id_ciclo_actual
    ORDER BY a.apellidos, d.apellidos
";
$resultado_docentes = $conexion->query($sql_docentes);

$destinatarios = [];
if ($resultado_docentes && $resultado_docentes->num_rows > 0) {
    while ($row = $resultado_docentes->fetch_assoc()) {
        $destinatarios[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escribir Nuevo Mensaje - Portal Familiar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="bg-body-tertiary">

    <nav class="navbar navbar-expand-lg navbar-dark floating-nav floating-nav-success mx-auto">
        <div class="container-fluid px-2">
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
                        <span class="text-light me-3 fw-medium d-flex align-items-center gap-2">
                            <?php if (!empty($_SESSION['foto_perfil'])): ?>
                                <img src="../assets/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" alt="Perfil" class="rounded-circle border border-2 border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                            <?php else: ?> 👋 <?php endif; ?>
                            Hola, <?php echo htmlspecialchars($_SESSION['nombre_padre']); ?>
                        </span>
                        <a class="btn btn-outline-light btn-sm me-2 rounded-circle shadow-sm premium-icon-btn d-flex justify-content-center align-items-center" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i class="bi bi-person-gear mb-1"></i></a>
                        <button class="btn btn-outline-light btn-sm me-2 rounded-circle shadow-sm premium-icon-btn d-flex justify-content-center align-items-center" id="btnThemeToggle" title="Modo Visual">
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

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <h3 class="fw-bold mb-0 text-body d-flex align-items-center">
                        <div class="bg-success bg-gradient text-white rounded-circle d-inline-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 48px; height: 48px; font-size: 1.4rem;">
                            <i class="bi bi-pencil-square"></i>
                        </div> Escribir Mensaje
                    </h3>
                    <a href="mensajes.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm hover-scale d-flex align-items-center">
                        <i class="bi bi-arrow-left me-2"></i>Volver a Mensajes
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

                <div class="card shadow-lg border-0 rounded-4 bg-body">
                    <div class="card-body p-4 p-md-5">
                        <form action="redactar_mensaje.php" method="POST" class="needs-validation" novalidate>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-body"><i
                                        class="bi bi-person-badge me-2 text-success"></i>Para (Maestro) <span
                                        class="text-danger">*</span></label>
                                <select name="destinatario" id="destinatario"
                                    class="form-select border-start-0 border-top-0 border-end-0 rounded-0 shadow-none px-0"
                                    style="border-width: 2px;" required onchange="actualizarAlumnoSeleccionado(this)">
                                    <option value="" selected disabled>Selecciona al maestro...</option>
                                    <?php if (count($destinatarios) > 0): ?>
                                        <?php foreach ($destinatarios as $dest): ?>
                                            <option value="<?php echo $dest['id_docente']; ?>"
                                                data-alumno="<?php echo $dest['id_alumno']; ?>">
                                                <?php echo htmlspecialchars($dest['docente_nombre'] . ' ' . $dest['docente_apellidos']) . ' — ' . htmlspecialchars($dest['nombre_materia']) . ' (Alumno/a: ' . htmlspecialchars($dest['alumno_nombre']) . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay maestros asignados para contactar en este momento.
                                        </option>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback">Debes seleccionar al maestro con el que deseas
                                    comunicarte.</div>
                            </div>

                            <!-- Campo oculto para pasar el ID del alumno automáticamente -->
                            <input type="hidden" name="alumno" id="alumno_oculto" value="">

                            <div class="mb-4">
                                <label class="form-label fw-bold text-body"><i
                                        class="bi bi-card-heading me-2 text-success"></i>Asunto <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="asunto"
                                    class="form-control border-start-0 border-top-0 border-end-0 rounded-0 shadow-none px-0"
                                    style="border-width: 2px;" placeholder="¿De qué trata este mensaje corto?" required
                                    maxlength="200">
                                <div class="invalid-feedback">Por favor, escribe un asunto.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-body"><i
                                        class="bi bi-chat-text-fill me-2 text-success"></i>Mensaje <span
                                        class="text-danger">*</span></label>
                                <textarea name="mensaje"
                                    class="form-control rounded-4 shadow-sm p-4 bg-body-tertiary border-0" rows="6"
                                    placeholder="Escribe tu mensaje o inquietud para el maestro. Será recibido en su bandeja interna."
                                    required></textarea>
                                <div class="invalid-feedback">El cuerpo del mensaje no puede estar vacío.</div>
                            </div>

                            <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                                <button type="submit"
                                    class="btn bg-gradient-success text-white btn-lg rounded-pill px-5 shadow hover-scale fw-bold border-0">
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
            border-color: var(--bs-success) !important;
            box-shadow: none !important;
        }
        .bg-gradient-success {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
        }
        .card {
            transition: all 0.3s ease;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

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

</body>

</html>
<?php
$conexion->close();
?>
