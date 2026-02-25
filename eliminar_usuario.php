// eliminar_usuario.php (Eliminar)
<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ver_calificaciones.php");
    exit();
}
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Evitar que el usuario se elimine a sí mismo
    if ($id != $_SESSION['id_usuario']) {
        $conexion->query("DELETE FROM usuarios WHERE id_usuario = $id");
    }
}
header("Location: usuarios.php");
exit();
?>