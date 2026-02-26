<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = $conexion->real_escape_string($_POST['matricula']);
    $apellidos = $conexion->real_escape_string($_POST['apellidos']);

    // Buscar al alumno por matrícula y apellidos (se asume validación exacta)
    $sql = "SELECT id_alumno, nombre, apellidos FROM alumnos WHERE matricula = '$matricula' AND apellidos = '$apellidos'";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {
        $alumno = $resultado->fetch_assoc();

        // Guardar sesión del alumno
        $_SESSION['id_alumno'] = $alumno['id_alumno'];
        $_SESSION['nombre_alumno'] = $alumno['nombre'] . ' ' . $alumno['apellidos'];

        // Redirigir a sus calificaciones
        header("Location: ../calificaciones/mis_calificaciones.php");
        exit();

    }
    else {
        // Datos incorrectos
        echo "<script>
                alert('❌ Datos incorrectos. Por favor verifica tu matrícula y escríbela correctamente.'); 
                window.location='login_alumno.php';
              </script>";
    }
}

$conexion->close();
?>
