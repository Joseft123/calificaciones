<?php
include __DIR__ . '/../includes/conexion.php';

echo "=== ALUMNOS 1 A ===\n";
$res = $conexion->query("SELECT id_alumno, matricula, nombre, apellidos FROM alumnos WHERE nivel = 'Primaria' AND grado = '1' AND grupo = 'A'");
if ($res) {
    if ($res->num_rows == 0)
        echo "NO HAY ALUMNOS\n";
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo $conexion->error;
}
?>