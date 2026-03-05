<?php
session_start();
// Validar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

// Obtener la lista de alumnos
$sql = "SELECT id_alumno, matricula, nombre, apellidos, nivel, grado, grupo FROM alumnos ORDER BY nivel, grado, grupo ASC";
$resultado = $conexion->query($sql);

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">👨‍🎓 Gestión de Alumnos</h2>
    <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="input-group" style="max-width: 250px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Buscar alumno...">
        </div>
        <button id="exportBtn" class="btn btn-outline-success shadow-sm rounded-pill"><i class="bi bi-file-earmark-excel me-1"></i>Exportar CSV</button>
        <a href="importar_alumnos.php" class="btn btn-outline-primary shadow-sm px-4 rounded-pill">📁 Importar CSV</a>
        <a href="crear_alumno.php" class="btn btn-success shadow-sm px-4 rounded-pill">➕ Inscribir Alumno</a>
    </div>
</div>

<?php
if ($resultado->num_rows > 0) {
    // 1. Agrupar alumnos
    $alumnos_agrupados = [];
    while ($fila = $resultado->fetch_assoc()) {
        $nivel = $fila['nivel'];
        $grupo = $fila['grado'] . "º " . $fila['grupo'];
        $alumnos_agrupados[$nivel][$grupo][] = $fila;
    }

    $delay = 0.2;
    // 2. Renderizar por grupos
    foreach ($alumnos_agrupados as $nivel => $grupos) {
        $nivelIdSafe = preg_replace('/[^A-Za-z0-9\-]/', '', $nivel);
        echo "<div class='mb-5 animate-fade-in' style='animation-delay: {$delay}s;'>";
        echo "<h3 class='text-secondary border-bottom pb-2 mb-4 fw-bold'>
                <span class='text-primary'>Nivel:</span> " . htmlspecialchars($nivel) . "
              </h3>";

        $delay += 0.15;
        echo "<div class='accordion shadow-sm rounded-4 overflow-hidden' id='accordionNivel{$nivelIdSafe}'>";

        foreach ($grupos as $grupo => $alumnos) {
            $grupoIdSafe = preg_replace('/[^A-Za-z0-9\-]/', '', $grupo) . mt_rand(100, 999);

            echo "<div class='accordion-item border-0 mb-3 rounded-4 shadow-sm student-group-card animate-fade-in' style='animation-delay: {$delay}s;'>";
            echo "<h2 class='accordion-header' id='heading{$grupoIdSafe}'>";
            echo "<button class='accordion-button collapsed px-4 py-3 fw-bold' type='button' data-bs-toggle='collapse' data-bs-target='#collapse{$grupoIdSafe}' aria-expanded='false' aria-controls='collapse{$grupoIdSafe}' style='background: linear-gradient(135deg, #6610f2 0%, #520dc2 100%); color: white; border-radius: 1rem;'>";
            echo "<i class='bi bi-people-fill me-2'></i>Grupo " . htmlspecialchars($grupo) . " <span class='badge bg-light text-primary ms-auto rounded-pill px-3 shadow-sm'>" . count($alumnos) . " inscritos</span>";
            echo "</button>";
            echo "</h2>";

            echo "<div id='collapse{$grupoIdSafe}' class='accordion-collapse collapse' aria-labelledby='heading{$grupoIdSafe}' data-bs-parent='#accordionNivel{$nivelIdSafe}'>";
            echo "<div class='accordion-body p-0'>";

            // Tabla interna por grupo
            echo "<div class='table-responsive rounded-bottom-4'>";
            echo "<table class='table table-hover align-middle mb-0 group-table'>";
            echo "<thead class='table-light text-muted small position-sticky top-0 bg-white' style='z-index: 1;'>
                    <tr>
                        <th class='py-3 px-4 fw-semibold border-bottom'>Matrícula</th>
                        <th class='py-3 fw-semibold border-bottom'>Nombre Completo</th>
                        <th class='py-3 px-4 fw-semibold border-bottom text-end'>Acciones</th>
                    </tr>
                  </thead>";
            echo "<tbody>";

            foreach ($alumnos as $alumno) {
                echo "<tr>";
                echo "<td class='px-4' style='width: 20%;'><span class='badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm text-monospace'><i class='bi bi-hash text-muted me-1'></i>" . htmlspecialchars($alumno['matricula']) . "</span></td>";
                echo "<td class='fw-medium text-dark'>" . htmlspecialchars($alumno['apellidos']) . " " . htmlspecialchars($alumno['nombre']) . "</td>";
                echo "<td class='px-4 text-end' style='width: 25%;'>
                        <a href='../calificaciones/generar_boleta_pdf.php?id=" . $alumno['id_alumno'] . "' target='_blank' class='btn btn-outline-danger btn-sm rounded-pill shadow-sm me-1' title='Imprimir Boleta PDF'><i class='bi bi-file-earmark-pdf-fill me-1'></i>Boleta</a>
                        <a href='editar_alumno.php?id=" . $alumno['id_alumno'] . "' class='btn btn-outline-warning btn-sm rounded-circle shadow-sm me-1' title='Editar Perfil'><i class='bi bi-pencil'></i></a>
                        <a href='eliminar_alumno.php?id=" . $alumno['id_alumno'] . "' class='btn btn-outline-danger btn-sm rounded-circle shadow-sm' title='Dar de Baja' onclick='return confirm(\"¿Estás seguro de eliminar a este alumno y todo su historial de calificaciones?\");'><i class='bi bi-trash'></i></a>
                      </td>";
                echo "</tr>";
            }

            echo "</tbody></table></div>"; // Fin tabla
            echo "</div></div></div>"; // Fin collapse, body y accordion-item
            $delay += 0.1;
        }
        echo "</div></div>"; // Fin accordion container y nivel
    }
}
else {
    echo "<div class='empty-state text-center p-5 mx-auto animate-fade-in' style='animation-delay: 0.2s; max-width: 600px;'>
            <div style='font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;'>📭</div>
            <h4 class='text-secondary fw-bold mb-3'>No hay alumnos inscritos aún</h4>
            <p class='text-muted mb-4'>El directorio está vacío. Comienza importando una lista o agregándolos manualmente.</p>
            <div class='d-flex justify-content-center gap-3'>
                <a href='importar_alumnos.php' class='btn btn-outline-primary rounded-pill px-4 shadow-sm'>📂 Importar CSV</a>
                <a href='crear_alumno.php' class='btn btn-primary rounded-pill px-4 shadow-sm'>➕ Inscribir Alumno</a>
            </div>
          </div>";
}
?>

<?php $conexion->close(); ?>

</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/search_filter.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Exportar a CSV
        const exportBtn = document.getElementById("exportBtn");
        const table = document.getElementById("alumnosTable");
        
        if (exportBtn && table) {
            exportBtn.addEventListener("click", function() {
                let csv = [];
                const rows = table.querySelectorAll("tr");
                
                for (let i = 0; i < rows.length; i++) {
                    let row = [], cols = rows[i].querySelectorAll("td, th");
                    
                    // No exportar la columna de acciones (índice 4)
                    for (let j = 0; j < cols.length - 1; j++) {
                        let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/"/g, '""');
                        row.push('"' + data + '"');
                    }
                    if (cols.length > 1) { // No incluir filas sin datos válidos
                        csv.push(row.join(","));
                    }
                }
                
                // Descargar el archivo CSV
                const csvFile = new Blob(["\uFEFF" + csv.join("\n")], {type: "text/csv;charset=utf-8;"});
                const downloadLink = document.createElement("a");
                downloadLink.download = "Lista_de_Alumnos.csv";
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