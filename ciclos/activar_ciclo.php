<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $nuevo_id_activo = intval($_GET['id']);

    // Iniciar transacción por seguridad
    $conexion->begin_transaction();

    try {
        // 1. Apagar todos los ciclos
        $conexion->query("UPDATE ciclos_escolares SET estatus = 'Inactivo'");

        // 2. Encender el nuevo ciclo
        $sql = "UPDATE ciclos_escolares SET estatus = 'Activo' WHERE id_ciclo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $nuevo_id_activo);
        $stmt->execute();

        $conexion->commit();
        header("Location: index.php?msg=activado");

    }
    catch (Exception $e) {
        $conexion->rollback();
        header("Location: index.php?msg=error");
    }

    if (isset($stmt))
        $stmt->close();
}
else {
    header("Location: index.php");
}

$conexion->close();
?>
