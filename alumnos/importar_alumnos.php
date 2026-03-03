<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}
include '../includes/conexion.php';
include '../includes/header.php';

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo_csv"])) {
    $archivo = $_FILES["archivo_csv"]["tmp_name"];

    // Verificar que haya un archivo válido subido
    if ($_FILES["archivo_csv"]["size"] > 0) {
        $file = fopen($archivo, "r");

        // Determinar el delimitador detectando qué caracter es más frecuente en la primera fila
        $primera_linea = fgets($file);
        $delimitador = (substr_count($primera_linea, ';') > substr_count($primera_linea, ',')) ? ';' : ',';
        rewind($file); // Volver al inicio del archivo

        $primera_fila = true;

        $insertados = 0;
        $errores = 0;

        $sql = "INSERT INTO alumnos (matricula, nombre, apellidos, nivel, grado, grupo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            while (($datos = fgetcsv($file, 10000, $delimitador)) !== FALSE) {
                // Saltar la cabecera
                if ($primera_fila) {
                    $primera_fila = false;
                    continue;
                }

                // Asegurar que haya suficientes columnas
                if (count($datos) >= 6) {
                    $matricula = trim($datos[0]);
                    $nombre = trim($datos[1]);
                    $apellidos = trim($datos[2]);
                    $nivel = trim($datos[3]);
                    $grado = intval(trim($datos[4]));
                    $grupo = trim($datos[5]);

                    if (!empty($matricula) && !empty($nombre) && !empty($apellidos)) {
                        $stmt->bind_param("ssssss", $matricula, $nombre, $apellidos, $nivel, $grado, $grupo);
                        if ($stmt->execute()) {
                            $insertados++;
                        }
                        else {
                            $errores++; // Probablemente matrícula duplicada
                        }
                    }
                }
            }
            $stmt->close();
            fclose($file);

            if ($insertados > 0) {
                $mensaje = "✅ Se importaron $insertados alumnos exitosamente.";
                $tipo_mensaje = "success";
                if ($errores > 0) {
                    $mensaje .= " Hubo $errores registros que no se pudieron importar (revisar duplicados o campos vacíos).";
                    $tipo_mensaje = "warning";
                }
            }
            else {
                $mensaje = "❌ No se importó ningún alumno. Revisa el formato del archivo o posibles alumnos ya registrados.";
                $tipo_mensaje = "danger";
            }
        }
        else {
            $mensaje = "❌ Error interno del servidor al preparar la carga.";
            $tipo_mensaje = "danger";
        }
    }
    else {
        $mensaje = "❌ El archivo está vacío o no es válido.";
        $tipo_mensaje = "danger";
    }
}
?>

<h2 class="text-primary mb-4">📁 Importar Alumnos desde CSV</h2>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> shadow-sm">
        <?php echo $mensaje; ?>
    </div>
<?php
endif; ?>

<div class="card shadow-sm border-0 rounded-4 p-4 bg-white mb-4">
    <div class="alert alert-info border-0 shadow-sm">
        <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Instrucciones:</h5>
        <p class="mb-0">Tu archivo de Excel debe ser guardado como <strong>CSV (delimitado por comas)</strong>. Debe contener exactamente las siguientes columnas en este orden y <strong class="text-danger">la primera fila debe ser el encabezado</strong>:</p>
        <hr>
        <ul class="mb-0">
            <li><strong>Matrícula</strong> (ej: MAT-01)</li>
            <li><strong>Nombre(s)</strong> (ej: Juan)</li>
            <li><strong>Apellidos</strong> (ej: Pérez García)</li>
            <li><strong>Nivel</strong> (Valores: Primaria, Secundaria, Preparatoria)</li>
            <li><strong>Grado</strong> (Valores: 1 al 6)</li>
            <li><strong>Grupo</strong> (ej: A, B, C)</li>
        </ul>
        <p class="mt-3 mb-0 text-muted"><small>*Soporta CSV separados tanto por comas (,) como por punto y coma (;).</small></p>
    </div>

    <form action="importar_alumnos.php" method="POST" enctype="multipart/form-data" class="mt-3">
        <div class="mb-4">
            <label for="archivo_csv" class="form-label fw-bold">Selecciona el archivo .csv:</label>
            <input class="form-control form-control-lg" type="file" name="archivo_csv" id="archivo_csv" accept=".csv" required>
        </div>
        
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg shadow-sm">🚀 Subir e Importar</button>
            <a href="alumnos.php" class="btn btn-secondary btn-lg shadow-sm">Volver</a>
        </div>
    </form>
</div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
