<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../includes/conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Eliminar el registro
    $sql = "DELETE FROM alumnos WHERE id_alumno = $id";
    $conexion->query($sql);
}

// Redirigir de vuelta a la lista de alumnos
header("Location: alumnos.php");
exit();
?>