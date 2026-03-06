<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conexion->real_escape_string(trim($_POST['titulo']));
    $mensaje = $conexion->real_escape_string(trim($_POST['mensaje']));
    $destinatario = $conexion->real_escape_string($_POST['destinatario']);
    $id_autor = intval($_SESSION['id_usuario']);

    if (!empty($titulo) && !empty($mensaje)) {
        $sql = "INSERT INTO comunicados (titulo, mensaje, destinatario, id_autor) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sssi", $titulo, $mensaje, $destinatario, $id_autor);
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
