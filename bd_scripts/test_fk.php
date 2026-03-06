<?php
$c = new mysqli('localhost', 'root', '', 'sistema_escolar');
if ($c->connect_error)
    die('ConnError');

$sql = "INSERT INTO asistencias (id_alumno, id_materia, id_docente, fecha, estado) VALUES (1, 1, 1, '2026-03-03', 'Presente')";
$r = $c->query($sql);
if (!$r) {
    echo "ERROR: " . $c->error . "\n";
}
else {
    echo "WORKS! inserted 1 row\n";
}
?>
