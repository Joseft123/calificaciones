<?php
session_start();

if (isset($_SESSION['id_usuario']) || isset($_SESSION['id_docente'])) {
    header("Location: calificaciones/ver_calificaciones.php");
}
elseif (isset($_SESSION['id_alumno'])) {
    header("Location: calificaciones/mis_calificaciones.php");
}
else {
    header("Location: auth/login.php");
}
exit();
?>
