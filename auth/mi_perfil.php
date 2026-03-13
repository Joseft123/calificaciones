<?php
session_start();
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['id_docente']) && !isset($_SESSION['id_padre']) && !isset($_SESSION['id_alumno'])) {
    header("Location: login.php");
    exit();
}

include '../includes/conexion.php';
include_once '../includes/csrf.php';
$csrf_token = generar_token_csrf();
$msg = '';
$msg_type = '';

$table = '';
$id_col = '';
$id_val = 0;

if (isset($_SESSION['id_usuario'])) {
    $table = 'usuarios';
    $id_col = 'id_usuario';
    $id_val = $_SESSION['id_usuario'];
} elseif (isset($_SESSION['id_docente'])) {
    $table = 'docentes';
    $id_col = 'id_docente';
    $id_val = $_SESSION['id_docente'];
} elseif (isset($_SESSION['id_padre'])) {
    $table = 'padres';
    $id_col = 'id_padre';
    $id_val = $_SESSION['id_padre'];
} elseif (isset($_SESSION['id_alumno'])) {
    $table = 'alumnos';
    $id_col = 'id_alumno';
    $id_val = $_SESSION['id_alumno'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !validar_token_csrf($_POST['csrf_token'])) {
        die("Error CSRF: Petición inválida o expirada.");
    }

    // Procesamiento de Cambio de Contraseña
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $sql = "SELECT password FROM $table WHERE $id_col = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_val);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if ($user && password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = "UPDATE $table SET password = ? WHERE $id_col = ?";
                    $stmt_up = $conexion->prepare($update);
                    $stmt_up->bind_param("si", $hash, $id_val);
                    if ($stmt_up->execute()) {
                        $msg = 'Contraseña actualizada correctamente.';
                        $msg_type = 'success';
                    } else {
                        $msg = 'Error al actualizar en la base de datos.';
                        $msg_type = 'danger';
                    }
                } else {
                    $msg = 'La nueva contraseña debe tener al menos 6 caracteres.';
                    $msg_type = 'warning';
                }
            } else {
                $msg = 'Las contraseñas nuevas no coinciden.';
                $msg_type = 'warning';
            }
        } else {
            $msg = 'Tu contraseña actual es incorrecta.';
            $msg_type = 'danger';
        }
    }

    // Procesamiento de Foto de Perfil
    if (isset($_POST['update_photo']) && isset($_FILES['foto_perfil'])) {
        $foto = $_FILES['foto_perfil'];
        if ($foto['error'] == 0) {
            $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($foto['type'], $permitidos)) {
                $max_size = 2 * 1024 * 1024; // 2MB
                if ($foto['size'] <= $max_size) {
                    $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
                    $nuevo_nombre = $table . '_' . $id_val . '_' . time() . '.' . $ext;
                    $ruta_destino = '../assets/uploads/perfiles/' . $nuevo_nombre;

                    if (move_uploaded_file($foto['tmp_name'], $ruta_destino)) {
                        // Actualizar BD
                        $sql_upd = "UPDATE $table SET foto_perfil = ? WHERE $id_col = ?";
                        $stmt_upd = $conexion->prepare($sql_upd);
                        $stmt_upd->bind_param("si", $nuevo_nombre, $id_val);
                        if ($stmt_upd->execute()) {
                            $_SESSION['foto_perfil'] = $nuevo_nombre; // Actualizar sesión
                            $msg = 'Foto de perfil actualizada correctamente.';
                            $msg_type = 'success';
                        } else {
                            $msg = 'Error al guardar la foto en la base de datos.';
                            $msg_type = 'danger';
                        }
                    } else {
                        $msg = 'Error al subir el archivo al servidor.';
                        $msg_type = 'danger';
                    }
                } else {
                    $msg = 'La imagen es muy pesada. Máximo 2MB.';
                    $msg_type = 'warning';
                }
            } else {
                $msg = 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.';
                $msg_type = 'warning';
            }
        } else {
            $msg = 'Selecciona una imagen válida.';
            $msg_type = 'warning';
        }
    }
}

// Obtener foto actual
$sql_foto = "SELECT foto_perfil FROM $table WHERE $id_col = $id_val";
$res_foto = $conexion->query($sql_foto);
$current_foto = $res_foto->fetch_assoc()['foto_perfil'];

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="row justify-content-center animate-fade-in" style="animation-delay: 0.1s;">
    <div class="col-md-10 col-lg-8">
        <h2 class="text-primary fw-bold text-center mb-4"><i class="bi bi-person-bounding-box me-2"></i> Mi Perfil Escolar</h2>
        
        <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo htmlspecialchars($msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php
            $cancel_url = '../index.php';
            if (isset($_SESSION['id_usuario'])) $cancel_url = '../calificaciones/dashboard.php';
            if (isset($_SESSION['id_docente'])) $cancel_url = '../docentes/dashboard.php';
            if (isset($_SESSION['id_padre'])) $cancel_url = '../padres/dashboard.php';
            if (isset($_SESSION['id_alumno'])) $cancel_url = '../alumnos/dashboard.php';
        ?>

        <div class="mb-3 text-start">
            <a href="<?php echo htmlspecialchars($cancel_url); ?>" class="btn btn-outline-secondary btn-sm rounded-pill fw-medium shadow-sm"><i class="bi bi-arrow-left me-1"></i> Volver al Inicio</a>
        </div>

        <div class="row g-4 d-flex align-items-stretch">
            <!-- Panel de Foto de Perfil -->
            <div class="col-md-5">
                <div class="card shadow border-0 rounded-4 h-100 text-center animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                        <h5 class="fw-bold mb-4 text-secondary">Foto de Perfil</h5>
                        
                        <div class="position-relative mb-4">
                            <?php if ($current_foto && file_exists('../assets/uploads/perfiles/' . $current_foto)): ?>
                                <img src="../assets/uploads/perfiles/<?php echo htmlspecialchars($current_foto); ?>" 
                                     alt="Foto de Perfil" 
                                     class="rounded-circle shadow object-fit-cover border border-4 border-primary" 
                                     style="width: 150px; height: 150px;">
                            <?php else: ?>
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center shadow-sm border border-4 border-primary border-opacity-25" 
                                     style="width: 150px; height: 150px; font-size: 4rem;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            <?php endif; ?>
                            
                            <span class="position-absolute bottom-0 end-0 bg-white shadow-sm p-2 rounded-circle border d-flex justify-content-center align-items-center" style="transform: translate(-10px, -10px); width: 40px; height: 40px;">
                                <i class="bi bi-camera-fill text-primary"></i>
                            </span>
                        </div>

                        <form action="mi_perfil.php" method="POST" enctype="multipart/form-data" class="w-100 mt-auto">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <div class="mb-3 text-start">
                                <label for="foto_perfil" class="form-label small fw-bold text-muted">Subir nueva imagen (JPG, PNG)</label>
                                <input type="file" class="form-control form-control-sm rounded-pill" name="foto_perfil" id="foto_perfil" accept="image/png, image/jpeg, image/webp" required>
                            </div>
                            <button type="submit" name="update_photo" class="btn btn-outline-primary btn-sm rounded-pill shadow-sm w-100"><i class="bi bi-upload me-1"></i> Subir Foto</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel de Contraseña -->
            <div class="col-md-7">
                <div class="card shadow border-0 rounded-4 h-100 animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="card-header bg-primary bg-gradient text-white p-3 d-flex align-items-center rounded-top-4 border-0">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 fs-5"></i> Seguridad de la Cuenta</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="mi_perfil.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-secondary">Contraseña Actual</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-key text-muted"></i></span>
                                    <input type="password" name="current_password" class="form-control bg-light" placeholder="Escribe tu clave actual" required auto_complete="off">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-secondary">Nueva Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock text-primary"></i></span>
                                    <input type="password" name="new_password" class="form-control bg-light" placeholder="Mínimo 6 caracteres" required minlength="6">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-secondary">Confirmar Nueva Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-success"></i></span>
                                    <input type="password" name="confirm_password" class="form-control bg-light" placeholder="Repetir nueva clave" required minlength="6">
                                </div>
                            </div>

                            <div class="d-grid mt-auto">
                                <button type="submit" name="update_password" class="btn btn-primary rounded-pill shadow-sm"><i class="bi bi-save me-1"></i> Actualizar Contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted small pb-5">
            Mantén tu contraseña segura y no la compartas con nadie. <br>
            Tus datos están protegidos bajo protocolos de cifrado seguros.
        </div>
    </div>
</div>

</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
