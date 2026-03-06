<?php
include __DIR__ . '/../includes/conexion.php';

// Convert existing tables to InnoDB to allow Foreign Keys
$queries = [
    "ALTER TABLE alumnos ENGINE=InnoDB;",
    "ALTER TABLE materias ENGINE=InnoDB;",
    "ALTER TABLE usuarios ENGINE=InnoDB;",
    "ALTER TABLE docente_materia_grupo ENGINE=InnoDB;",
    "ALTER TABLE asistencias ENGINE=InnoDB;"
];

foreach ($queries as $q) {
    if ($conexion->query($q)) {
        echo "✅ Exitoso: $q <br>";
    } else {
        echo "❌ Error: " . $conexion->error . " <br>";
    }
}
?>