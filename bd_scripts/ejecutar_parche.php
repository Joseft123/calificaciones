<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "sistema_escolar";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);
if ($conexion->connect_error) {
    die("Error: " . $conexion->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_sistema` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único de esta instalación',
  `fecha_instalacion` datetime NOT NULL,
  `estado_licencia` enum('Prueba','Activado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Prueba',
  `clave_activacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conexion->query($sql) === TRUE) {
    echo "Tabla configuracion_sistema creada o ya existe.\n";
} else {
    echo "Error creando tabla: " . $conexion->error . "\n";
}

// Ahora simularemos que han pasado 35 días para probar el bloqueo (Opcionalmente, para que el usuario pueda ver el bloqueo).
// Primero vaciamos para forzar reinicio 
$conexion->query("TRUNCATE TABLE configuracion_sistema");

// Insertamos un registro con 35 dias en el pasado
$codigo_falso = "TEST-BLOQUEO";
$conexion->query("INSERT INTO configuracion_sistema (codigo_sistema, fecha_instalacion, estado_licencia) VALUES ('$codigo_falso', DATE_SUB(NOW(), INTERVAL 35 DAY), 'Prueba')");

echo "Sistema configurado como caducado hace 5 días, para probar la pantalla de bloqueo.\n";

$conexion->close();
?>
