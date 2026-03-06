<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header("Location: calificaciones/dashboard.php");
}
elseif (isset($_SESSION['id_docente'])) {
    header("Location: docentes/dashboard.php");
}
elseif (isset($_SESSION['id_alumno'])) {
    header("Location: calificaciones/mis_calificaciones.php");
}
else {
    header("Location: auth/login.php");
}
exit();
?>
