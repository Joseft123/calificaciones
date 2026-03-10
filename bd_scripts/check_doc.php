<?php
include __DIR__ . '/../includes/conexion.php';

echo "=== MATERIAS DEL DOCENTE 1 ===\n";
$res = $conexion->query("SELECT * FROM docente_materia_grupo WHERE id_docente = 1");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo $conexion->error;
}
?>