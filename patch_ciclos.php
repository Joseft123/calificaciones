<?php
include 'includes/conexion.php';

echo "<h2>Actualizando Base de Datos para Ciclos Escolares...</h2>";

// 1. Crear tabla ciclos_escolares
$sql_tabla = "CREATE TABLE IF NOT EXISTS ciclos_escolares (
    id_ciclo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_ciclo VARCHAR(100) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    estatus ENUM('Activo', 'Inactivo') DEFAULT 'Inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conexion->query($sql_tabla)) {
    echo "✅ Tabla ciclos_escolares creada o ya existía.<br>";
}
else {
    echo "❌ Error tabla ciclos: " . $conexion->error . "<br>";
}

// 2. Insertar Ciclo Base si no hay ninguno
$res_ciclos = $conexion->query("SELECT * FROM ciclos_escolares");
if ($res_ciclos->num_rows == 0) {
    if ($conexion->query("INSERT INTO ciclos_escolares (nombre_ciclo, estatus) VALUES ('Ciclo 2024-2025', 'Activo')")) {
        echo "✅ Ciclo base '2024-2025' creado y marcado como activo.<br>";
    }
    else {
        echo "❌ Error al crear ciclo base: " . $conexion->error . "<br>";
    }
}
$id_ciclo_base = 1;

// 3. Modificar docente_materia_grupo para agregar id_ciclo
$res_column1 = $conexion->query("SHOW COLUMNS FROM docente_materia_grupo LIKE 'id_ciclo'");
if ($res_column1->num_rows == 0) {
    if ($conexion->query("ALTER TABLE docente_materia_grupo ADD id_ciclo INT DEFAULT 1 AFTER id_materia")) {
        echo "✅ Columna id_ciclo agregada a docente_materia_grupo.<br>";
    }
    else {
        echo "❌ Error en docente_materia_grupo: " . $conexion->error . "<br>";
    }
}

// 4. Modificar calificaciones para agregar id_ciclo
$res_column2 = $conexion->query("SHOW COLUMNS FROM calificaciones LIKE 'id_ciclo'");
if ($res_column2->num_rows == 0) {
    if ($conexion->query("ALTER TABLE calificaciones ADD id_ciclo INT DEFAULT 1 AFTER id_materia")) {
        echo "✅ Columna id_ciclo agregada a calificaciones.<br>";
    }
    else {
        echo "❌ Error en calificaciones: " . $conexion->error . "<br>";
    }
}

// 5. Modificar asistencias para agregar id_ciclo
$res_column3 = $conexion->query("SHOW COLUMNS FROM asistencias LIKE 'id_ciclo'");
if ($res_column3->num_rows == 0) {
    if ($conexion->query("ALTER TABLE asistencias ADD id_ciclo INT DEFAULT 1 AFTER id_materia")) {
        echo "✅ Columna id_ciclo agregada a asistencias.<br>";
    }
    else {
        echo "❌ Error en asistencias: " . $conexion->error . "<br>";
    }
}

echo "<h3>¡Actualización Terminada!</h3>";
?>
