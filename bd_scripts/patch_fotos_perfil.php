<?php
include '../includes/conexion.php';

$tablas = ['usuarios', 'docentes', 'alumnos', 'padres'];

echo "<h2>Aplicando parche para fotos de perfil...</h2>";

foreach ($tablas as $tabla) {
    // Revisar si la columna existe
    $res = $conexion->query("SHOW COLUMNS FROM $tabla LIKE 'foto_perfil'");
    if ($res->num_rows == 0) {
        $sql = "ALTER TABLE $tabla ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL";
        if ($conexion->query($sql)) {
            echo "<p>✅ Columna 'foto_perfil' añadida a la tabla <b>$tabla</b>.</p>";
        } else {
            echo "<p>❌ Error en tabla <b>$tabla</b>: " . $conexion->error . "</p>";
        }
    } else {
        echo "<p>ℹ️ La tabla <b>$tabla</b> ya tiene la columna 'foto_perfil'.</p>";
    }
}

echo "<p>Proceso finalizado.</p>";
$conexion->close();
?>
