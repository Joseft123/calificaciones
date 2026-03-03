<?php
session_start();
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_docente = $_SESSION['id_docente'];
    $id_materia = isset($_POST['id_materia']) ? (int)$_POST['id_materia'] : 0;
    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
    $estados = isset($_POST['estado']) ? $_POST['estado'] : [];

    if ($id_materia > 0 && !empty($estados)) {
        // Prepare the SQL statement for inserting or updating attendance
        $sql = "INSERT INTO asistencias (id_alumno, id_materia, id_docente, fecha, estado) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE estado = VALUES(estado)";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            foreach ($estados as $id_alumno => $estado) {
                $id_alum = (int)$id_alumno;
                // Validar que el estado sea correcto
                if (in_array($estado, ['Presente', 'Falta', 'Retardo'])) {
                    $stmt->bind_param("iiiss", $id_alum, $id_materia, $id_docente, $fecha, $estado);
                    $stmt->execute();
                }
            }
            $stmt->close();

            // Redirect back with success message
            header("Location: pasar_lista.php?id_materia=" . $id_materia . "&fecha=" . urlencode($fecha) . "&msg=success");
            exit();
        }
        else {
            // Log error
            error_log("Error preparing SQL for attendance: " . $conexion->error);
            header("Location: pasar_lista.php?msg=error");
            exit();
        }
    }
}

// Fallback redirect
header("Location: pasar_lista.php");
exit();
?>
