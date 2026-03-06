<?php
session_start();

// Validar que el usuario sea un administrador (Director)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

// Definir cabeceras para forzar la descarga de un archivo Excel (Tabla HTML interpretada)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_General_Alumnos_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Agregar el BOM para que Excel reconozca correctamente los caracteres especiales (UTF-8)
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

        .promedio-alto {
            color: #198754;
            font-weight: bold;
        }

        .promedio-bajo {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2>Reporte General de Alumnos y Promedios</h2>
    <p>Fecha de generación:
        <?php echo date('d/m/Y H:i:s'); ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Nombre y Apellidos</th>
                <th>Nivel</th>
                <th>Grado y Grupo</th>
                <th>Promedio General</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consultar alumnos y sus promedios
            $sql = "SELECT a.matricula, a.nombre, a.apellidos, a.nivel, a.grado, a.grupo, 
                           IFNULL(AVG(c.calificacion), 0) AS promedio_general
                    FROM alumnos a
                    LEFT JOIN calificaciones c ON a.id_alumno = c.id_alumno
                    GROUP BY a.id_alumno
                    ORDER BY a.nivel, a.grado, a.grupo, a.apellidos, a.nombre";

            $resultado = $conexion->query($sql);

            if ($resultado && $resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $promedio = round($fila['promedio_general'], 2);

                    // Clases para colorear en excel
                    $clase_promedio = "";
                    if ($promedio > 0) {
                        $clase_promedio = ($promedio >= 6) ? 'class="promedio-alto"' : 'class="promedio-bajo"';
                    }

                    $nombre_completo = mb_convert_encoding($fila['nombre'] . ' ' . $fila['apellidos'], 'HTML-ENTITIES', 'UTF-8');
                    $nivel = mb_convert_encoding($fila['nivel'], 'HTML-ENTITIES', 'UTF-8');

                    echo "<tr>";
                    echo "<td>{$fila['matricula']}</td>";
                    echo "<td>{$nombre_completo}</td>";
                    echo "<td>{$nivel}</td>";
                    echo "<td>{$fila['grado']}º {$fila['grupo']}</td>";
                    echo "<td {$clase_promedio}>" . ($promedio > 0 ? $promedio : 'N/A') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No se encontraron alumnos registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>

</html>
<?php
$conexion->close();
?>