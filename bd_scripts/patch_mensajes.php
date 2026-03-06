<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../includes/conexion.php';

echo "<h2>Patch de Tabla 'Mensajes'</h2>";

// Crear tabla mensajes
$sql_mensajes = "
CREATE TABLE IF NOT EXISTS `mensajes` (
  `id_mensaje` int(11) NOT NULL AUTO_INCREMENT,
  `id_remitente` int(11) NOT NULL,
  `tipo_remitente` enum('Docente','Padre') NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `tipo_destinatario` enum('Docente','Padre') NOT NULL,
  `id_alumno` int(11) DEFAULT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT '2026-03-01 00:00:00',
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_mensaje`),
  KEY `fk_msg_alumno` (`id_alumno`),
  CONSTRAINT `fk_msg_alumno` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conexion->query($sql_mensajes) === TRUE) {
    echo "<p style='color:green;'>Tabla 'mensajes' creada o verificada exitosamente.</p>";
} else {
    echo "<p style='color:red;'>Error creando tabla 'mensajes': " . $conexion->error . "</p>";
}

// Opcional: Insertar un mensaje de prueba (Sistema -> Docente) si está vacío
$res_check = $conexion->query("SELECT COUNT(*) as total FROM mensajes");
if ($res_check && $res_check->fetch_assoc()['total'] == 0) {
    // Buscar un docente y un padre para vincular
    $res_doc = $conexion->query("SELECT id_docente FROM docentes LIMIT 1");
    $res_pad = $conexion->query("SELECT id_padre FROM padres LIMIT 1");

    if ($res_doc->num_rows > 0 && $res_pad->num_rows > 0) {
        $id_doc = $res_doc->fetch_assoc()['id_docente'];
        $id_pad = $res_pad->fetch_assoc()['id_padre'];
        $fecha = date('Y-m-d H:i:s');

        $sql_test = "INSERT INTO mensajes (id_remitente, tipo_remitente, id_destinatario, tipo_destinatario, id_alumno, asunto, mensaje, fecha_envio, leido) 
                     VALUES ($id_doc, 'Docente', $id_pad, 'Padre', NULL, 'Bienvenido al nuevo buzón', 'Estimado tutor: Por este medio podremos estar en contacto más directo acerca de las actividades de su hijo. Saludos cordiales.', '$fecha', 0)";

        if ($conexion->query($sql_test) === TRUE) {
            echo "<p style='color:green;'>Datos de prueba insertados en 'mensajes'.</p>";
        } else {
            echo "<p style='color:red;'>Error insertando datos en 'mensajes': " . $conexion->error . "</p>";
        }
    }
}

$conexion->close();
?>