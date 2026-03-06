<?php
include 'includes/conexion.php';
$sql = "CREATE TABLE IF NOT EXISTS `comunicados` (
    `id_comunicado` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(150) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `destinatario` ENUM('Todos', 'Docentes', 'Alumnos') DEFAULT 'Todos',
    `fecha_publicacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `id_autor` INT NOT NULL, 
    FOREIGN KEY (`id_autor`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
if ($conexion->query($sql)) {
    echo "Exito";
}
else {
    echo "Falla: " . $conexion->error;
}
?>
