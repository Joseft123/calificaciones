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
$id_padre = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_padre == 0) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conexion->real_escape_string(trim($_POST['nombre']));
    $apellidos = $conexion->real_escape_string(trim($_POST['apellidos']));
    $correo = $conexion->real_escape_string(trim($_POST['correo']));
    $telefono = $conexion->real_escape_string(trim($_POST['telefono']));
    $alumnos_seleccionados = isset($_POST['alumnos']) ? $_POST['alumnos'] : [];
    $parentesco = $conexion->real_escape_string(trim($_POST['parentesco']));

    // Actualizar datos del padre
    $sql_update = "UPDATE padres SET nombre='$nombre', apellidos='$apellidos', correo='$correo', telefono='$telefono' WHERE id_padre=$id_padre";

    // Si escribió una nueva contraseña
    if (!empty($_POST['password'])) {
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $sql_update = "UPDATE padres SET nombre='$nombre', apellidos='$apellidos', correo='$correo', telefono='$telefono', password='$password' WHERE id_padre=$id_padre";
    }

    try {
        if ($conexion->query($sql_update)) {
            $stmt_del = $conexion->prepare("DELETE FROM padre_alumno WHERE id_padre = ?");
            $stmt_del->bind_param("i", $id_padre);
            $stmt_del->execute();
            $stmt_del->close();

            if (!empty($alumnos_seleccionados)) {
                $stmt = $conexion->prepare("INSERT INTO padre_alumno (id_padre, id_alumno, parentesco) VALUES (?, ?, ?)");
                foreach ($alumnos_seleccionados as $id_alumno) {
                    $stmt->bind_param("iis", $id_padre, $id_alumno, $parentesco);
                    $stmt->execute();
                }
                $stmt->close();
            }

            $mensaje = 'Datos del tutor y alumnos actualizados correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al actualizar: ' . $conexion->error;
            $tipo_mensaje = 'danger';
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $mensaje = 'Error: El correo electrónico ya está registrado por otro usuario.';
            $tipo_mensaje = 'warning';
        } else {
            $mensaje = 'Error general en la BD: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}

// Obtener datos actuales del padre
$sql_padre = "SELECT * FROM padres WHERE id_padre = $id_padre";
$res_padre = $conexion->query($sql_padre);
if ($res_padre->num_rows == 0) {
    header("Location: index.php");
    exit();
}
$padre = $res_padre->fetch_assoc();

// Obtener alumnos actualmente asociados a este padre
$sql_asociados = "SELECT id_alumno, parentesco FROM padre_alumno WHERE id_padre = $id_padre";
$res_asociados = $conexion->query($sql_asociados);
$hijos_actuales = [];
$parentesco_actual = 'Padre'; // Default
if ($res_asociados && $res_asociados->num_rows > 0) {
    while ($fila = $res_asociados->fetch_assoc()) {
        $hijos_actuales[] = $fila['id_alumno'];
        $parentesco_actual = $fila['parentesco']; // Tomamos el primer parentesco que encontremos (suelen ser el mismo)
    }
}

// Consultar todos los alumnos para mostrarlos en la lista
$sql_alumnos = "SELECT id_alumno, matricula, nombre, apellidos, nivel, grado, grupo 
                FROM alumnos 
                ORDER BY nivel, grado, grupo, apellidos";
$res_alumnos = $conexion->query($sql_alumnos);

include '../includes/header.php';
?>

<div class="row mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2><i class="bi bi-pencil-square text-primary me-2"></i>Editar Tutor:
            <?php echo htmlspecialchars($padre['nombre']); ?>
        </h2>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Regresar</a>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show shadow-sm rounded-4 animate-fade-in"
        style="animation-delay: 0.2s;" role="alert">
        <?php if ($tipo_mensaje == 'success')
            echo '<i class="bi bi-check-circle-fill me-2"></i>';
        else
            echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>'; ?>
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-4 animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="editar_padre.php?id=<?php echo $id_padre; ?>" class="needs-validation" novalidate>

            <h5 class="fw-bold text-primary mb-4 border-bottom pb-2">Información del Tutor</h5>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium"><i class="bi bi-person me-2"></i>Nombre(s) <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="nombre"
                        value="<?php echo htmlspecialchars($padre['nombre']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" name="apellidos"
                        value="<?php echo htmlspecialchars($padre['apellidos']); ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium"><i class="bi bi-envelope me-2"></i>Correo Electrónico <span
                            class="text-danger">*</span></label>
                    <input type="email" class="form-control rounded-3" name="correo"
                        value="<?php echo htmlspecialchars($padre['correo']); ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium"><i class="bi bi-telephone me-2"></i>Teléfono</label>
                    <input type="text" class="form-control rounded-3" name="telefono"
                        value="<?php echo htmlspecialchars($padre['telefono']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium text-warning"><i class="bi bi-key me-2"></i>Nueva
                        Contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control rounded-start-3 border-warning" name="password"
                            id="password" placeholder="Rellenar solo para cambiarla">
                        <button class="btn btn-outline-warning rounded-end-3" type="button"
                            onclick="togglePassword()"><i class="bi bi-eye-fill"></i></button>
                    </div>
                    <div class="form-text">Si dejas este campo vacío, la contraseña actual se mantendrá.</div>
                </div>
            </div>

            <h5 class="fw-bold text-success mt-5 mb-4 border-bottom pb-2">Hijos Asignados</h5>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-medium"><i class="bi bi-diagram-2 me-2"></i>Parentesco</label>
                    <select name="parentesco" class="form-select rounded-3">
                        <option value="Madre" <?php if ($parentesco_actual == 'Madre')
                            echo 'selected'; ?>>Madre</option>
                        <option value="Padre" <?php if ($parentesco_actual == 'Padre')
                            echo 'selected'; ?>>Padre</option>
                        <option value="Tutor" <?php if ($parentesco_actual == 'Tutor')
                            echo 'selected'; ?>>Tutor /
                            Apoderado</option>
                        <option value="Familiar" <?php if ($parentesco_actual == 'Familiar')
                            echo 'selected'; ?>>Familiar
                        </option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-medium"><i class="bi bi-people-fill me-2"></i>Herederos / Alumnos <span
                            class="text-danger">*</span></label>
                    <div class="card border border-light-subtle rounded-3 shadow-sm bg-body-tertiary">
                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                            <?php if ($res_alumnos && $res_alumnos->num_rows > 0): ?>
                                <?php while ($alumno = $res_alumnos->fetch_assoc()):
                                    $checked = in_array($alumno['id_alumno'], $hijos_actuales) ? 'checked' : '';
                                    ?>
                                    <div
                                        class="form-check custom-checkbox mb-2 p-2 rounded hover-bg-light transition-all <?php echo $checked ? 'bg-success bg-opacity-10 border border-success border-opacity-25' : ''; ?>">
                                        <input class="form-check-input ms-1" type="checkbox" name="alumnos[]"
                                            value="<?php echo $alumno['id_alumno']; ?>"
                                            id="al_<?php echo $alumno['id_alumno']; ?>" <?php echo $checked; ?>>
                                        <label class="form-check-label ms-2 d-inline-block w-100 cursor-pointer"
                                            for="al_<?php echo $alumno['id_alumno']; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold <?php echo $checked ? 'text-success' : 'text-body'; ?>">
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
                </div>
            </div>

            <div class="d-grid mt-5">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm py-2">
                    <i class="bi bi-save2-fill me-2"></i>Guardar Cambios
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
        background-color: var(--bs-secondary-bg) !important;
    }

    .custom-checkbox .form-check-input {
        width: 1.25em;
        height: 1.25em;
        margin-top: 0.15em;
        cursor: pointer;
    }
</style>

<script>
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

    // Toggle styling when checking checkboxes
    document.querySelectorAll('.form-check-input').forEach(box => {
        box.addEventListener('change', function () {
            const container = this.closest('.custom-checkbox');
            const studentName = container.querySelector('.fw-bold');
            if (this.checked) {
                container.classList.add('bg-success', 'bg-opacity-10', 'border', 'border-success', 'border-opacity-25');
                studentName.classList.add('text-success');
                studentName.classList.remove('text-body');
            } else {
                container.classList.remove('bg-success', 'bg-opacity-10', 'border', 'border-success', 'border-opacity-25');
                studentName.classList.remove('text-success');
                studentName.classList.add('text-body');
            }
        });
    });

    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                const checkboxes = document.querySelectorAll('input[type="checkbox"][name="alumnos[]"]');
                let checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);

                if (!checkedOne && checkboxes.length > 0) {
                    alert('Por favor, selecciona al menos a un alumno para asociarlo con este tutor. Un tutor no puede estar sin hijos asignados.');
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