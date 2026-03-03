// eliminar_usuario.php (Eliminar)
<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
include '../includes/conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Evitar que el usuario se elimine a sí mismo
    if ($id != $_SESSION['id_usuario']) {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}
header("Location: usuarios.php");
exit();
?>