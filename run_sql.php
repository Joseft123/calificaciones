<?php
include 'includes/conexion.php';
$sql_file = file_get_contents('patch_asignaciones.sql');
if ($conexion->multi_query($sql_file)) {
    echo "SQL ejecutado exitosamente.\n";
}
else {
    echo "Error ejecutando SQL: " . $conexion->error . "\n";
}
$conexion->close();
?>
