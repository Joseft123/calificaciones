<?php
session_start();

// Validar que el usuario sea un docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

include '../includes/conexion.php';
include '../includes/funciones_ciclo.php';

$id_docente = intval($_SESSION['id_docente']);
$id_ciclo_actual = getCicloActivo($conexion);

// Definir cabeceras para forzar la descarga de un archivo Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Mis_Alumnos_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Agregar el BOM para UTF-8
echo "\xEF\xBB\xBF";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .nivel-header {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>Listado de Mis Alumnos</h2>
    <p>Profesor:
        <?php echo htmlspecialchars($_SESSION['nombre_docente']); ?>
    </p>
    <p>Fecha de generación:
        <?php echo date('d/m/Y H:i:s'); ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Nombre del Alumno</th>
                <th>Nivel</th>
                <th>Grado y Grupo</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consultar los alumnos que pertenecen a los grupos/materias de este docente en el ciclo actual
            $sql = "SELECT DISTINCT a.matricula, a.nombre, a.apellidos, a.nivel, a.grado, a.grupo
                    FROM alumnos a
                    INNER JOIN docente_materia_grupo dmg ON a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo
                    WHERE dmg.id_docente = $id_docente AND dmg.id_ciclo = $id_ciclo_actual
                    ORDER BY a.nivel, a.grado, a.grupo, a.apellidos, a.nombre";

            $resultado = $conexion->query($sql);

            if ($resultado && $resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $nombre_completo = mb_convert_encoding($fila['apellidos'] . ' ' . $fila['nombre'], 'HTML-ENTITIES', 'UTF-8');
                    $nivel = mb_convert_encoding($fila['nivel'], 'HTML-ENTITIES', 'UTF-8');

                    echo "<tr>";
                    echo "<td>{$fila['matricula']}</td>";
                    echo "<td>{$nombre_completo}</td>";
                    echo "<td>{$nivel}</td>";
                    echo "<td>{$fila['grado']}º {$fila['grupo']}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No tienes alumnos asignados en el ciclo escolar actual.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>

</html>
<?php
$conexion->close();
?>