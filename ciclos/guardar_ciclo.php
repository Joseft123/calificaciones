<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_ciclo = $conexion->real_escape_string(trim($_POST['nombre_ciclo']));
    $fecha_inicio = $conexion->real_escape_string($_POST['fecha_inicio']);
    $fecha_fin = $conexion->real_escape_string($_POST['fecha_fin']);

    if (!empty($nombre_ciclo) && !empty($fecha_inicio) && !empty($fecha_fin)) {
        $sql = "INSERT INTO ciclos_escolares (nombre_ciclo, fecha_inicio, fecha_fin, estatus) VALUES (?, ?, ?, 'Inactivo')";
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sss", $nombre_ciclo, $fecha_inicio, $fecha_fin);
            if ($stmt->execute()) {
                header("Location: index.php?msg=creado");
            }
            else {
                header("Location: index.php?msg=error");
            }
            $stmt->close();
        }
        else {
            header("Location: index.php?msg=error");
        }
    }
    else {
        header("Location: index.php?msg=vacio");
    }
}
else {
    header("Location: index.php");
}

$conexion->close();
?>
