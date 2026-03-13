<?php
// Iniciar la sesión para guardar los datos del usuario
session_start();

// Incluir la conexión a la base de datos
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !validar_token_csrf($_POST['csrf_token'])) {
        die("Error CSRF: Petición inválida o expirada.");
    }
    // Escapar el correo para mayor seguridad
    $correo = $conexion->real_escape_string($_POST['correo']);
    $password_ingresada = $_POST['password'];

    $sql = "SELECT id_usuario, nombre, password, rol, foto_perfil FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Si el usuario existe
        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();

            // Verificar que NO sea docente
            if ($usuario['rol'] === 'Docente') {
                echo "<script>
                    alert('❌ Acceso denegado. Los docentes deben ingresar por su portal.'); 
                    window.location='login.php';
                  </script>";
                exit();
            }

            // Verificar la contraseña 
            // Nota: El hash que insertamos en el paso anterior corresponde a la contraseña: '123456'
            if (password_verify($password_ingresada, $usuario['password'])) {

                // Si la contraseña es correcta, guardamos sus datos en variables de sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION['foto_perfil'] = $usuario['foto_perfil'];

                // Redirigir al sistema
                header("Location: ../calificaciones/dashboard.php");
                exit();

            }
            else {
                // Contraseña incorrecta
                echo "<script>
                    alert('❌ Contraseña incorrecta'); 
                    window.location='login.php';
                  </script>";
            }
        }
        else {
            // Usuario no encontrado
            echo "<script>
                alert('❌ El correo no está registrado'); 
                window.location='login.php';
              </script>";
        }
    }
}


$conexion->close();
?>