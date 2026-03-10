<?php
include __DIR__ . '/../includes/conexion.php';

echo "=== ALUMNOS ===\n";
$res = $conexion->query("SELECT id_alumno, nombre, apellidos, nivel, grado, grupo FROM alumnos");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n=== GRUPOS DEL DOCENTE 1 ===\n";
$res = $conexion->query("SELECT * FROM docente_materia_grupo WHERE id_docente = 1");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>