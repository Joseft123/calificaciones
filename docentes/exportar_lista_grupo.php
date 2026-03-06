<?php
session_start();

if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

include '../includes/conexion.php';
$id_docente = intval($_SESSION['id_docente']);

// Validar que vengan los parámetros
if (!isset($_GET['nivel']) || !isset($_GET['grado']) || !isset($_GET['grupo'])) {
    die("Faltan parámetros de grupo.");
}

$nivel = $conexion->real_escape_string($_GET['nivel']);
$grado = intval($_GET['grado']);
$grupo = $conexion->real_escape_string($_GET['grupo']);

// 1. Validar que el docente realmente imparte a este grupo
$sql_check = "SELECT 1 FROM docente_materia_grupo 
              WHERE id_docente = $id_docente 
              AND nivel = '$nivel' AND grado = $grado AND grupo = '$grupo' LIMIT 1";
$res_check = $conexion->query($sql_check);

if ($res_check->num_rows == 0) {
    die("No tienes permiso o no impartes materias a este grupo.");
}

// 2. Obtener lista de alumnos del grupo 
$sql_alumnos = "SELECT matricula, apellidos, nombre 
                FROM alumnos 
                WHERE nivel = '$nivel' AND grado = $grado AND grupo = '$grupo'
                ORDER BY apellidos ASC, nombre ASC";

$resultado = $conexion->query($sql_alumnos);

// 3. Si no hay alumnos, imprimir mensaje (o manejar error)
if (!$resultado || $resultado->num_rows == 0) {
    die("No hay alumnos en el grupo mencionado.");
}

// 4. Preparar cabeceras para forzar descarga CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Lista_Grupo_' . $grado . $grupo . '_' . $nivel . '.csv');

// Abrir output en memoria
$salida = fopen('php://output', 'w');

// Por cuestiones de codificación UTF-8 en Excel, añadir el BOM
fprintf($salida, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Encabezados de columnas
fputcsv($salida, ['Matrícula', 'Apellidos', 'Nombre(s)']);

// Llenar CSV
while ($fila = $resultado->fetch_assoc()) {
    fputcsv($salida, [
        $fila['matricula'],
        $fila['apellidos'],
        $fila['nombre']
    ]);
}

fclose($salida);
$conexion->close();
exit();
?>
