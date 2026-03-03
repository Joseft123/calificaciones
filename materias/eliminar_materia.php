<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../includes/conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Eliminar el registro (borrará en cascada las calificaciones vinculadas a esta materia)
    $sql = "DELETE FROM materias WHERE id_materia = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirigir de vuelta a la lista de materias
header("Location: materias.php");
exit();
?>