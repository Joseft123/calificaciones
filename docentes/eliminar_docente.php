<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
include '../includes/conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Aquí idealmente se debería checar si el docente tiene clases asignadas
    // Si la base de datos tiene ON DELETE CASCADE esto no será problema,
    // de lo contrario podría generar un error de FK constraint (lo cual es bueno para evitar inconsistencias).

    $stmt = $conexion->prepare("DELETE FROM docentes WHERE id_docente = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // Eliminación exitosa
            header("Location: docentes.php");
            exit();
        }
        else {
            // Probably a Foreign Key constraint fail if they have classes
            echo "<script>alert('Error al eliminar: Este docente podría tener clases o materias asignadas. " . $stmt->error . "'); window.location='docentes.php';</script>";
        }
        $stmt->close();
    }
    else {
        echo "<script>alert('Error al preparar eliminación.'); window.location='docentes.php';</script>";
    }
}
else {
    header("Location: docentes.php");
    exit();
}
?>
