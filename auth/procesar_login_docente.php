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

    $sql = "SELECT id_docente, nombre, apellidos, password FROM docentes WHERE correo = ?";
    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Si el docente existe
        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();

            // Verificar la contraseña 
            if (password_verify($password_ingresada, $usuario['password'])) {

                // Si la contraseña es correcta, guardamos sus datos en variables de sesión
                $_SESSION['id_docente'] = $usuario['id_docente'];
                $_SESSION['nombre_docente'] = $usuario['nombre'] . ' ' . $usuario['apellidos'];

                // Redirigir al sistema
                header("Location: ../docentes/dashboard.php");
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
}


$conexion->close();
?>
