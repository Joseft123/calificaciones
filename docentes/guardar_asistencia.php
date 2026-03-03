<?php
session_start();
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tomar el ID desde id_docente o id_usuario (el que esté activo en la sesión)
    $id_docente = isset($_SESSION['id_docente']) ? $_SESSION['id_docente'] : (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 0);
    $id_materia = isset($_POST['id_materia']) ? (int)$_POST['id_materia'] : 0;
    $nivel = isset($_POST['nivel']) ? $_POST['nivel'] : '';
    $grado = isset($_POST['grado']) ? (int)$_POST['grado'] : 0;
    $grupo = isset($_POST['grupo']) ? $_POST['grupo'] : '';
    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
    $estados = isset($_POST['estado']) ? $_POST['estado'] : [];

    if ($id_materia > 0 && !empty($estados)) {
        // Prepare the SQL statement for inserting or updating attendance
        $sql = "INSERT INTO asistencias (id_alumno, id_materia, id_docente, fecha, estado) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE estado = VALUES(estado)";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $exito_total = true;
            foreach ($estados as $id_alumno => $estado) {
                $id_alum = (int)$id_alumno;
                // Validar que el estado sea correcto
                if (in_array($estado, ['Presente', 'Falta', 'Retardo'])) {
                    $stmt->bind_param("iiiss", $id_alum, $id_materia, $id_docente, $fecha, $estado);
                    if (!$stmt->execute()) {
                        $exito_total = false;
                        error_log("Error al insertar asistencia para alumno $id_alum: " . $stmt->error);
                    }
                }
            }
            $stmt->close();

            if ($exito_total) {
                // Redirect back with success message and keep the required variables to load the group again
                $redirect_url = "pasar_lista.php?id_materia=" . $id_materia . "&nivel=" . urlencode($nivel) . "&grado=" . $grado . "&grupo=" . urlencode($grupo) . "&fecha=" . urlencode($fecha) . "&msg=success";
                header("Location: " . $redirect_url);
            }
            else {
                header("Location: pasar_lista.php?msg=error");
            }
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
