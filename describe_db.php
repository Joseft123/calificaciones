<?php
include 'includes/conexion.php';
$tablas = ['docentes', 'materias', 'alumnos', 'calificaciones'];
foreach ($tablas as $t) {
    echo "\n=== Tabla $t ===\n";
    $res = $conexion->query("DESCRIBE $t");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    }
    else {
        echo "No existe la tabla\n";
    }
}
$conexion->close();
?>
