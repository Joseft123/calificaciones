<?php
session_start();

// Validar que el usuario sea administrador (Director)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conexion->real_escape_string(trim($_POST['nombre']));
    $apellidos = $conexion->real_escape_string(trim($_POST['apellidos']));
    $correo = $conexion->real_escape_string(trim($_POST['correo']));
    $telefono = $conexion->real_escape_string(trim($_POST['telefono']));
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $alumnos_seleccionados = isset($_POST['alumnos']) ? $_POST['alumnos'] : [];
    $parentesco = $conexion->real_escape_string(trim($_POST['parentesco']));

    // 1. Insertar el Padre
    $sql_insert = "INSERT INTO padres (nombre, apellidos, correo, telefono, password) 
                   VALUES ('$nombre', '$apellidos', '$correo', '$telefono', '$password')";

    try {
        if ($conexion->query($sql_insert)) {
            $id_padre_nuevo = $conexion->insert_id;

            // 2. Insertar relaciones (hijos)
            if (!empty($alumnos_seleccionados)) {
                $stmt = $conexion->prepare("INSERT INTO padre_alumno (id_padre, id_alumno, parentesco) VALUES (?, ?, ?)");
                foreach ($alumnos_seleccionados as $id_alumno) {
                    $stmt->bind_param("iis", $id_padre_nuevo, $id_alumno, $parentesco);
                    $stmt->execute();
                }
                $stmt->close();
            }
            header("Location: index.php?msg=creado");
            exit();
        } else {
            $mensaje = 'Error al registrar padre: ' . $conexion->error;
            $tipo_mensaje = 'danger';
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // 1062 es código de entrada duplicada
            $mensaje = 'Error: El correo electrónico ya está registrado.';
            $tipo_mensaje = 'warning';
        } else {
            $mensaje = 'Error general en la base de datos: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}

// Consultar todos los alumnos para el select
$sql_alumnos = "SELECT id_alumno, matricula, nombre, apellidos, nivel, grado, grupo 
                FROM alumnos 
                ORDER BY nivel, grado, grupo, apellidos";
$res_alumnos = $conexion->query($sql_alumnos);

include '../includes/header.php';
?>

<div class="row mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2><i class="bi bi-person-plus-fill text-success me-2"></i>Registrar Padre o Tutor</h2>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Regresar</a>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show shadow-sm rounded-4 animate-fade-in"
        style="animation-delay: 0.2s;" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-4 animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="crear_padre.php" class="needs-validation" novalidate>

            <h5 class="fw-bold text-primary mb-4 border-bottom pb-2">Datos Personales del Tutor</h5>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium"><i class="bi bi-person me-2"></i>Nombre(s) <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="nombre" required placeholder="Ej. Carlos">
                    <div class="invalid-feedback">Ingresa el nombre del tutor.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="apellidos" required
                        placeholder="Ej. Pérez García">
                    <div class="invalid-feedback">Ingresa los apellidos.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium"><i class="bi bi-envelope me-2"></i>Correo Electrónico <span
                            class="text-danger">*</span></label>
                    <input type="email" class="form-control rounded-3" name="correo" required
                        placeholder="correo@ejemplo.com">
                    <div class="form-text">Se usará como usuario para iniciar sesión.</div>
                    <div class="invalid-feedback">Ingresa un correo válido.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium"><i class="bi bi-telephone me-2"></i>Teléfono</label>
                    <input type="text" class="form-control rounded-3" name="telefono" placeholder="Opcional">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium"><i class="bi bi-key me-2"></i>Contraseña Inicial <span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control rounded-start-3" name="password" id="password"
                            required>
                        <button class="btn btn-outline-secondary rounded-end-3" type="button"
                            onclick="togglePassword()"><i class="bi bi-eye-fill"></i></button>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold text-success mt-5 mb-4 border-bottom pb-2">Asignación de Hijos (Alumnos)</h5>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-medium"><i class="bi bi-diagram-2 me-2"></i>Parentesco</label>
                    <select name="parentesco" class="form-select rounded-3">
                        <option value="Madre">Madre</option>
                        <option value="Padre" selected>Padre</option>
                        <option value="Tutor">Tutor / Apoderado</option>
                        <option value="Familiar">Familiar (Tío, Abuelo...)</option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-medium"><i class="bi bi-people-fill me-2"></i>Seleccionar Alumnos <span
                            class="text-danger">*</span></label>
                    <div class="card border border-light-subtle rounded-3 shadow-sm bg-body-tertiary">
                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                            <?php if ($res_alumnos && $res_alumnos->num_rows > 0): ?>
                                <?php while ($alumno = $res_alumnos->fetch_assoc()): ?>
                                    <div class="form-check custom-checkbox mb-2 p-2 rounded hover-bg-light transition-all">
                                        <input class="form-check-input ms-1" type="checkbox" name="alumnos[]"
                                            value="<?php echo $alumno['id_alumno']; ?>"
                                            id="al_<?php echo $alumno['id_alumno']; ?>">
                                        <label class="form-check-label ms-2 d-inline-block w-100 cursor-pointer"
                                            for="al_<?php echo $alumno['id_alumno']; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-body">
                                                    <?php echo htmlspecialchars($alumno['apellidos'] . ' ' . $alumno['nombre']); ?>
                                                </span>
                                                <span class="badge bg-secondary rounded-pill fw-normal">
                                                    <?php echo $alumno['nivel'] . ' | ' . $alumno['grado'] . 'º ' . $alumno['grupo']; ?>
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center m-0 py-3">No hay alumnos registrados en el sistema.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Puedes seleccionar más de un
                        alumno si son hermanos.</div>
                </div>
            </div>

            <div class="d-grid mt-5">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm py-2">
                    <i class="bi bi-check-circle-fill me-2"></i>Guardar y Registrar Tutor
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .hover-bg-light:hover {
        background-color: var(--bs-secondary-bg);
    }

    .custom-checkbox .form-check-input {
        width: 1.25em;
        height: 1.25em;
        margin-top: 0.15em;
        cursor: pointer;
    }
</style>

<script>
    // Mostrar/ocultar contraseña
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const icon = event.currentTarget.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
        }
    }

    // Validación de Bootstrap 5
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                const checkboxes = document.querySelectorAll('input[type="checkbox"][name="alumnos[]"]');
                let checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);

                if (!checkedOne && checkboxes.length > 0) {
                    alert('Por favor, selecciona al menos a un alumno para asociarlo con este tutor.');
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>

<?php include '../includes/footer.php'; ?>