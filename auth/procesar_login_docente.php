<?php
// Iniciar la sesión para guardar los datos del usuario
session_start();

// Incluir la conexión a la base de datos
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escapar el correo para mayor seguridad
    $correo = $conexion->real_escape_string($_POST['correo']);
    $password_ingresada = $_POST['password'];

    // Buscar al usuario por su correo
    $sql = "SELECT id_usuario, nombre, password, rol FROM usuarios WHERE correo = '$correo'";
    $resultado = $conexion->query($sql);

    // Si el usuario existe
    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();

        // Verificar que sea docente
        if ($usuario['rol'] !== 'Docente') {
            echo "<script>
                    alert('❌ Acceso denegado. Este portal es exclusivo para docentes.'); 
                    window.location='login_docente.php';
                  </script>";
            exit();
        }

        // Verificar la contraseña 
        if (password_verify($password_ingresada, $usuario['password'])) {

            // Si la contraseña es correcta, guardamos sus datos en variables de sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigir al sistema
            header("Location: ../calificaciones/ver_calificaciones.php");
            exit();

        }
        else {
            // Contraseña incorrecta
            echo "<script>
                    alert('❌ Contraseña incorrecta'); 
                    window.location='login_docente.php';
                  </script>";
        }
    }
    else {
        // Usuario no encontrado
        echo "<script>
                alert('❌ El correo no está registrado'); 
                window.location='login_docente.php';
              </script>";
    }
}

$conexion->close();
?>
