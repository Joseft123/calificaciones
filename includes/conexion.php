<?php
$servidor = "localhost";
$usuario = "root"; // Usuario por defecto en WampServer
$password = ""; // Contraseña por defecto (suele estar en blanco)
$base_datos = "sistema_escolar";

// Crear la conexión
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Puedes descomentar la siguiente línea temporalmente para comprobar que funciona:
// echo "¡Conexión exitosa a la base de datos!";
?>