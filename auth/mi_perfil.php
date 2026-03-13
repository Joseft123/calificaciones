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

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password first
    $sql = "SELECT password FROM $table WHERE $id_col = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_val);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                // Generar el hash BCRYPT con costo seguro
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = "UPDATE $table SET password = ? WHERE $id_col = ?";
                $stmt_up = $conexion->prepare($update);
                $stmt_up->bind_param("si", $hash, $id_val);
                if ($stmt_up->execute()) {
                    $msg = 'Contraseña actualizada correctamente.';
                    $msg_type = 'success';
                }
                else {
                    $msg = 'Error al actualizar en la base de datos. Intenta de nuevo.';
                    $msg_type = 'danger';
                }
            }
            else {
                $msg = 'La nueva contraseña debe tener al menos 6 caracteres.';
                $msg_type = 'warning';
            }
        }
        else {
            $msg = 'Las contraseñas nuevas no coinciden.';
            $msg_type = 'warning';
        }
    }
    else {
        $msg = 'Tu contraseña actual es incorrecta.';
        $msg_type = 'danger';
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="row justify-content-center animate-fade-in">
    <div class="col-md-6 col-lg-5">
        <h2 class="text-primary fw-bold text-center mb-4"><i class="bi bi-person-bounding-box me-2"></i> Mi Perfil Escolar</h2>
        
        <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo htmlspecialchars($msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php
endif; ?>

        <div class="card shadow border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary bg-gradient text-white p-3 d-flex align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 fs-4"></i> Cambiar Contraseña</h5>
            </div>
            <div class="card-body p-4">
                <form action="mi_perfil.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Contraseña Actual</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key text-muted"></i></span>
                            <input type="password" name="current_password" class="form-control" placeholder="Escribe tu clave actual" required auto_complete="off">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Nueva Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock text-primary"></i></span>
                            <input type="password" name="new_password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Confirmar Nueva Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill text-success"></i></span>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repetir nueva clave" required minlength="6">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm"><i class="bi bi-save me-1"></i> Actualizar Contraseña</button>
                        <?php
                            $cancel_url = '../index.php';
                            if (isset($_SESSION['id_usuario'])) $cancel_url = '../calificaciones/dashboard.php';
                            if (isset($_SESSION['id_docente'])) $cancel_url = '../docentes/dashboard.php';
                            if (isset($_SESSION['id_padre'])) $cancel_url = '../padres/dashboard.php';
                            if (isset($_SESSION['id_alumno'])) $cancel_url = '../calificaciones/mis_calificaciones.php';
                        ?>
                        <a href="<?php echo htmlspecialchars($cancel_url); ?>" class="btn btn-light border text-secondary rounded-pill">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted small">
            Mantén tu contraseña segura y no la compartas con nadie. <br>
            Roles de sistema monitorizados.
        </div>
    </div>
</div>

</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
