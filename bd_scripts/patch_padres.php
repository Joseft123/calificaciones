<?php
include __DIR__ . '/../includes/conexion.php';

echo "<h2>Actualizando Base de Datos para el Portal de Padres...</h2>";

// 1. Crear tabla 'padres'
$sql_padres = "CREATE TABLE IF NOT EXISTS padres (
    id_padre INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conexion->query($sql_padres)) {
    echo "✅ Tabla 'padres' creada o ya existía.<br>";
} else {
    echo "❌ Error al crear tabla 'padres': " . $conexion->error . "<br>";
}

// 2. Crear tabla intermedia 'padre_alumno' (Relación N:M)
$sql_padre_alumno = "CREATE TABLE IF NOT EXISTS padre_alumno (
    id_relacion INT AUTO_INCREMENT PRIMARY KEY,
    id_padre INT NOT NULL,
    id_alumno INT NOT NULL,
    parentesco ENUM('Madre', 'Padre', 'Tutor', 'Familiar') DEFAULT 'Tutor',
    FOREIGN KEY (id_padre) REFERENCES padres(id_padre) ON DELETE CASCADE,
    FOREIGN KEY (id_alumno) REFERENCES alumnos(id_alumno) ON DELETE CASCADE,
    UNIQUE KEY unica_relacion (id_padre, id_alumno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conexion->query($sql_padre_alumno)) {
    echo "✅ Tabla intermedia 'padre_alumno' creada o ya existía.<br>";
} else {
    echo "❌ Error al crear tabla 'padre_alumno': " . $conexion->error . "<br>";
}

// 3. Crear Padre de Prueba (Padre de Jose Fabian)
$password_padre = password_hash('padre123', PASSWORD_BCRYPT);
$sql_insert_padre = "INSERT IGNORE INTO padres (id_padre, nombre, apellidos, correo, telefono, password) 
                     VALUES (1, 'Juan', 'Muñozcano', 'padre@escuela.com', '555-123-4567', '$password_padre')";
if ($conexion->query($sql_insert_padre)) {
    echo "✅ Padre de prueba ('padre@escuela.com' / padra123) insertado correctamente.<br>";
} else {
    echo "❌ Error insertando padre de prueba: " . $conexion->error . "<br>";
}

// 4. Relacionar Padre de Prueba con Alumno 1 (Jose Fabian Muñozcano Guzman)
$sql_insert_relacion = "INSERT IGNORE INTO padre_alumno (id_padre, id_alumno, parentesco) VALUES (1, 1, 'Padre')";
if ($conexion->query($sql_insert_relacion)) {
    echo "✅ Relación entre Padre de prueba y Alumno ID 1 creada exitosamente.<br>";
} else {
    echo "❌ Error creando relación de prueba: " . $conexion->error . "<br>";
}

echo "<br><h3>¡Actualización de BD Terminada!</h3>";
?>