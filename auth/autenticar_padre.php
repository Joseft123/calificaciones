<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !validar_token_csrf($_POST['csrf_token'])) {
        die("Error CSRF: Petición inválida o expirada.");
    }
    $correo = $conexion->real_escape_string($_POST['correo']);
    $password = $_POST['password'];

    $sql = "SELECT id_padre, nombre, apellidos, password, foto_perfil FROM padres WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($password, $usuario['password'])) {
            // Contraseña correcta: Iniciar sesión
            $_SESSION['id_padre'] = $usuario['id_padre'];
            $_SESSION['nombre_padre'] = $usuario['nombre'];
            $_SESSION['apellidos_padre'] = $usuario['apellidos'];
            $_SESSION['foto_perfil'] = $usuario['foto_perfil'];

            // Redirigir al dashboard del padre
            header("Location: ../padres/dashboard.php");
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: login_padre.php?error=credenciales");
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: login_padre.php?error=credenciales");
        exit();
    }
} else {
    // Si intentan acceder directamente sin POST
    header("Location: login_padre.php");
    exit();
}
?>