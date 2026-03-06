<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_comunicado = intval($_GET['id']);

    $sql = "DELETE FROM comunicados WHERE id_comunicado = ?";
    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id_comunicado);
        if ($stmt->execute()) {
            header("Location: index.php?msg=eliminado");
        }
        else {
            header("Location: index.php?msg=error");
        }
        $stmt->close();
    }
}
else {
    header("Location: index.php");
}

$conexion->close();
?>
