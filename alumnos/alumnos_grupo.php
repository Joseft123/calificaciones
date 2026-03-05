<?php
session_start();
// Validar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

// Validar parámetros
if (!isset($_GET['nivel']) || !isset($_GET['grado']) || !isset($_GET['grupo'])) {
    header("Location: alumnos.php");
    exit();
}

$nivel = $conexion->real_escape_string($_GET['nivel']);
$grado = $conexion->real_escape_string($_GET['grado']);
$grupo = $conexion->real_escape_string($_GET['grupo']);

// Obtener los alumnos específicos de este grupo
$sql = "SELECT id_alumno, matricula, nombre, apellidos, nivel, grado, grupo 
        FROM alumnos 
        WHERE nivel = ? AND grado = ? AND grupo = ? 
        ORDER BY apellidos ASC, nombre ASC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sss", $nivel, $grado, $grupo);
$stmt->execute();
$resultado = $stmt->get_result();

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <a href="alumnos.php" class="btn btn-outline-secondary btn-sm mb-3 rounded-pill fw-medium shadow-sm"><i class="bi bi-arrow-left me-1"></i> Volver a Grupos</a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="text-primary m-0 fw-bold d-flex align-items-center">
                <i class="bi bi-people-fill me-2 fs-3"></i> Alumnos del Grupo
            </h2>
            <h5 class="text-secondary mt-1 mb-0 fw-semibold"><?php echo htmlspecialchars($nivel); ?> - <?php echo htmlspecialchars($grado); ?>º <?php echo htmlspecialchars($grupo); ?></h5>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Buscar alumno...">
            </div>
            <button id="exportBtn" class="btn btn-outline-success shadow-sm rounded-pill"><i class="bi bi-file-earmark-excel me-1"></i>Exportar Grupo</button>
            <a href="crear_alumno.php" class="btn btn-success shadow-sm px-4 rounded-pill">➕ Añadir</a>
        </div>
    </div>
</div>

<div class="card shadow border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="alumnosTable" class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="py-3 px-4 border-0" style="width: 15%;">Matrícula</th>
                        <th class="py-3 border-0">Apellidos, Nombre</th>
                        <th class="py-3 px-4 border-0 text-end" style="width: 25%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if ($resultado->num_rows > 0) {
    $delay = 0.3;
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr class='animate-fade-in table-row' style='animation-delay: {$delay}s;'>";
        echo "<td class='px-4'><span class='badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm text-monospace'><i class='bi bi-hash text-muted me-1'></i>" . htmlspecialchars($fila['matricula']) . "</span></td>";
        echo "<td class='fw-bold text-dark'>" . htmlspecialchars($fila['apellidos']) . " <span class='fw-normal'>" . htmlspecialchars($fila['nombre']) . "</span></td>";
        echo "<td class='px-4 text-end'>
                            <a href='../calificaciones/generar_boleta_pdf.php?id=" . $fila['id_alumno'] . "' target='_blank' class='btn btn-outline-danger btn-sm rounded-pill shadow-sm me-1' title='Imprimir Boleta PDF'><i class='bi bi-file-earmark-pdf-fill me-1'></i>Boleta</a>
                            <a href='editar_alumno.php?id=" . $fila['id_alumno'] . "' class='btn btn-outline-warning btn-sm rounded-circle shadow-sm me-1' title='Editar Perfil'><i class='bi bi-pencil'></i></a>
                            <a href='eliminar_alumno.php?id=" . $fila['id_alumno'] . "' class='btn btn-outline-danger btn-sm rounded-circle shadow-sm' title='Dar de Baja' onclick='return confirm(\"¿Estás seguro de eliminar a este alumno y todo su historial de calificaciones?\");'><i class='bi bi-trash'></i></a>
                          </td>";
        echo "</tr>";
        $delay += 0.05;
    }
}
else {
    echo "<tr><td colspan='3' class='text-center py-5 text-muted'>
                            <div class='fs-1 mb-2'>📭</div>
                            <p class='mb-0 fw-bold'>Este grupo está vacío.</p>
                            <a href='crear_alumno.php' class='btn btn-outline-primary btn-sm mt-3 rounded-pill'>Inscribir el primer alumno</a>
                        </td></tr>";
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php

$stmt->close();
$conexion->close();

?>

</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/search_filter.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Exportar a CSV sólo este grupo
        const exportBtn = document.getElementById("exportBtn");
        const table = document.getElementById("alumnosTable");
        const groupName = "<?php echo addslashes($nivel . '_' . $grado . 'o_' . $grupo); ?>";
        
        if (exportBtn && table) {
            exportBtn.addEventListener("click", function() {
                let csv = [];
                const rows = table.querySelectorAll("tr");
                
                for (let i = 0; i < rows.length; i++) {
                    let row = [], cols = rows[i].querySelectorAll("td, th");
                    
                    // No exportar la columna de acciones (la última)
                    for (let j = 0; j < cols.length - 1; j++) {
                        let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/"/g, '""');
                        row.push('"' + data + '"');
                    }
                    if (cols.length > 1) { 
                        csv.push(row.join(","));
                    }
                }
                
                // Descargar el archivo CSV
                const csvFile = new Blob(["\uFEFF" + csv.join("\n")], {type: "text/csv;charset=utf-8;"});
                const downloadLink = document.createElement("a");
                downloadLink.download = `Alumnos_${groupName}.csv`;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            });
        }
    });
</script>
</body>
</html>
