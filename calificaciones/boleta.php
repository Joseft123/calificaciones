<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../includes/conexion.php';

// Validar que se reciba el ID del alumno
if (!isset($_GET['id'])) {
    echo "<div style='padding:20px; text-align:center;'><h3>No se ha seleccionado ningún alumno.</h3><a href='../alumnos/alumnos.php'>Volver</a></div>";
    exit();
}

$id_alumno = intval($_GET['id']);

// Consultar datos del alumno
$sql_alumno = "SELECT matricula, nombre, apellidos, nivel, grado, grupo FROM alumnos WHERE id_alumno = $id_alumno";
$res_alumno = $conexion->query($sql_alumno);

if ($res_alumno->num_rows == 0) {
    echo "<div style='padding:20px; text-align:center;'><h3>Alumno no encontrado.</h3><a href='../alumnos/alumnos.php'>Volver</a></div>";
    exit();
}
$alumno = $res_alumno->fetch_assoc();

// Consultar calificaciones del alumno con INNER JOIN a materias
$sql_calif = "SELECT m.clave_materia, m.nombre_materia, c.periodo, c.calificacion 
              FROM calificaciones c 
              INNER JOIN materias m ON c.id_materia = m.id_materia 
              WHERE c.id_alumno = $id_alumno 
              ORDER BY c.periodo ASC, m.nombre_materia ASC";
$res_calif = $conexion->query($sql_calif);

$suma_calificaciones = 0;
$total_materias = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Calificaciones - <?php echo $alumno['matricula']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .boleta-container { background: white; padding: 40px; border-radius: 8px; max-width: 800px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .escuela-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 20px; }
        .datos-alumno { margin-bottom: 30px; }
        .datos-alumno p { margin: 0; font-size: 1.1rem; }
        
        /* Estilos para impresión */
        @media print {
            body { background-color: white; padding: 0; }
            .boleta-container { box-shadow: none; max-width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="boleta-container border">
    
    <div class="no-print text-end mb-3">
        <a href="alumnos.php" class="btn btn-secondary">⬅️ Volver a Alumnos</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir Boleta</button>
    </div>

    <div class="escuela-header">
        <h2 class="text-primary fw-bold">SISTEMA ESCOLAR</h2>
        <h4>Reporte Oficial de Calificaciones</h4>
    </div>

    <div class="datos-alumno row">
        <div class="col-md-8">
            <p><strong>Alumno:</strong> <?php echo $alumno['apellidos'] . " " . $alumno['nombre']; ?></p>
            <p><strong>Matrícula:</strong> <?php echo $alumno['matricula']; ?></p>
        </div>
        <div class="col-md-4 text-md-end">
            <p><strong>Nivel:</strong> <?php echo $alumno['nivel']; ?></p>
            <p><strong>Grado y Grupo:</strong> <?php echo $alumno['grado'] . "º " . $alumno['grupo']; ?></p>
        </div>
    </div>

    <table class="table table-bordered table-striped align-middle mt-4">
        <thead class="table-dark text-center">
            <tr>
                <th>Clave</th>
                <th>Materia</th>
                <th>Periodo</th>
                <th>Calificación</th>
            </tr>
        </thead>
        <tbody>
            <?php
if ($res_calif->num_rows > 0) {
    while ($fila = $res_calif->fetch_assoc()) {
        $suma_calificaciones += $fila['calificacion'];
        $total_materias++;

        echo "<tr>";
        echo "<td class='text-center'>" . $fila['clave_materia'] . "</td>";
        echo "<td>" . $fila['nombre_materia'] . "</td>";
        echo "<td class='text-center'>" . $fila['periodo'] . "</td>";

        $color = ($fila['calificacion'] < 6) ? 'text-danger fw-bold' : 'text-success fw-bold';
        echo "<td class='text-center $color'>" . $fila['calificacion'] . "</td>";
        echo "</tr>";
    }
}
else {
    echo "<tr><td colspan='4' class='text-center py-3'>El alumno no tiene calificaciones registradas.</td></tr>";
}
?>
        </tbody>
    </table>

    <?php
if ($total_materias > 0) {
    $promedio = $suma_calificaciones / $total_materias;
    $clase_promedio = ($promedio < 6) ? 'text-danger' : 'text-primary';
    echo "<h4 class='text-end mt-4'>Promedio General: <span class='fw-bold $clase_promedio'>" . number_format($promedio, 2) . "</span></h4>";
}
?>

    <div class="text-center mt-5 pt-5" style="border-top: 1px dashed #ccc;">
        <p class="mb-5">_______________________________________</p>
        <p class="fw-bold">Firma del Director / Sello de la Escuela</p>
    </div>

</div>

</body>
</html>