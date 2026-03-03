<?php
session_start();

// Validar que el usuario sea un docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

$id_docente = $_SESSION['id_docente'];

// Variables para el filtro de lista
$id_materia_sel = isset($_GET['id_materia']) ? (int)$_GET['id_materia'] : 0;
$nivel_sel = isset($_GET['nivel']) ? $_GET['nivel'] : '';
$grado_sel = isset($_GET['grado']) ? (int)$_GET['grado'] : 0;
$grupo_sel = isset($_GET['grupo']) ? $_GET['grupo'] : '';

include '../includes/header.php';
?>

<style>
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .group-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        border-left: 4px solid #0dcaf0 !important;
        background-color: var(--bs-body-bg);
    }
    .group-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.12) !important;
        border-left-color: #0d6efd !important; 
        z-index: 2;
    }
    [data-bs-theme="dark"] .group-card {
        background-color: #2b2b2b;
        color: #ffffff;
    }
    [data-bs-theme="dark"] .group-card:hover {
        box-shadow: 0 12px 24px rgba(0,0,0,0.5) !important;
    }
    .table-hover tbody tr:hover td {
        background-color: rgba(13, 202, 240, 0.05);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-info m-0 fw-bold"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reporte de Asistencia</h2>
    <?php if ($id_materia_sel > 0): ?>
        <a href="reporte_asistencia.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver a Grupos</a>
    <?php
endif; ?>
</div>

<?php
// Paso 1: Si no hay materia seleccionada, mostrar grupos
if ($id_materia_sel === 0):
    $sql_grupos = "SELECT dmg.id_materia, m.nombre_materia, dmg.nivel, dmg.grado, dmg.grupo 
                   FROM docente_materia_grupo dmg
                   INNER JOIN materias m ON dmg.id_materia = m.id_materia
                   WHERE dmg.id_docente = $id_docente
                   ORDER BY dmg.nivel ASC, dmg.grado ASC, dmg.grupo ASC, m.nombre_materia ASC";

    $res_grupos = $conexion->query($sql_grupos);

    if ($res_grupos && $res_grupos->num_rows > 0):
        $delay = 0.2;
        echo "<div class='row g-4'>";
        while ($fila = $res_grupos->fetch_assoc()):
            $id_materia = $fila['id_materia'];
            $nombre_materia = htmlspecialchars($fila['nombre_materia']);
            $nivel = htmlspecialchars($fila['nivel']);
            $grado = htmlspecialchars($fila['grado']);
            $grupo = htmlspecialchars($fila['grupo']);

            $url = "?id_materia={$id_materia}&nivel=" . urlencode($nivel) . "&grado={$grado}&grupo=" . urlencode($grupo);
?>
            <div class='col-md-6 col-lg-4 animate-fade-in' style='animation-delay: <?php echo $delay; ?>s;'>
                <div class='card h-100 border-0 shadow-sm group-card rounded-4 p-3'>
                    <div class='card-body'>
                        <div class='d-flex align-items-center mb-3'>
                            <div class='bg-info bg-opacity-10 text-info rounded-circle p-3 me-3 text-center' style='width: 50px; height: 50px;'>
                                <i class='bi bi-people-fill fs-4' style='line-height: .5;'></i>
                            </div>
                            <h5 class='card-title mb-0 fw-bold text-truncate' title='<?php echo $nombre_materia; ?>'><?php echo $nombre_materia; ?></h5>
                        </div>
                        <ul class="list-unstyled text-body-secondary mb-4">
                            <li><i class="bi bi-diagram-3 me-2 opacity-75"></i> <strong>Nivel:</strong> <?php echo $nivel; ?></li>
                            <li><i class="bi bi-person-badge me-2 opacity-75"></i> <strong>Grupo:</strong> <?php echo $grado; ?>º <?php echo $grupo; ?></li>
                        </ul>
                        <div class='d-grid'>
                            <a href='reporte_asistencia.php<?php echo $url; ?>' class='btn btn-info text-white rounded-pill'><i class="bi bi-eye me-1"></i> Ver Reporte</a>
                        </div>
                    </div>
                </div>
            </div>
<?php
            $delay += 0.05;
        endwhile;
        echo "</div>";
    else:
?>
        <div class='text-center p-5 mx-auto animate-fade-in' style='animation-delay: 0.2s; max-width: 600px; background: var(--bs-tertiary-bg); border-radius: 12px; border: 2px dashed #ced4da;'>
            <div style='font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;'>📭</div>
            <h4 class='text-secondary fw-bold mb-3'>Sin grupos asignados</h4>
            <p class='text-muted mb-0'>No tienes ninguna materia ni grupo asignado actualmente para reportar.</p>
        </div>
<?php
    endif;

// Paso 2: Materia y Grupo seleccionados, mostrar resumen de asistencia
else:
    // Obtener información general de la materia seleccionada
    $sql_m = "SELECT nombre_materia FROM materias WHERE id_materia = $id_materia_sel";
    $materia_nombre = $conexion->query($sql_m)->fetch_assoc()['nombre_materia'] ?? 'Desconocida';

    // OBTENER REPORTE ACUMULADO
    $sql_reporte = "SELECT a.id_alumno, al.matricula, al.nombre, al.apellidos,
                           SUM(CASE WHEN a.estado='Presente' THEN 1 ELSE 0 END) as presentes,
                           SUM(CASE WHEN a.estado='Retardo' THEN 1 ELSE 0 END) as retardos,
                           SUM(CASE WHEN a.estado='Falta' THEN 1 ELSE 0 END) as faltas,
                           COUNT(a.id_asistencia) as total_clases
                    FROM asistencias a 
                    INNER JOIN alumnos al ON a.id_alumno = al.id_alumno
                    WHERE a.id_docente = ? AND a.id_materia = ?
                    GROUP BY a.id_alumno
                    ORDER BY al.apellidos ASC, al.nombre ASC";

    $stmt_rep = $conexion->prepare($sql_reporte);
    if ($stmt_rep) {
        $stmt_rep->bind_param("ii", $id_docente, $id_materia_sel);
        $stmt_rep->execute();
        $res_reporte = $stmt_rep->get_result();
?>
        <div class="card shadow-sm border-0 rounded-4 animate-fade-in" style="animation-delay: 0.2s;">
            <div class="card-header bg-info text-white px-4 py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold"><i class="bi bi-bar-chart-fill me-2"></i> Resumen: <?php echo htmlspecialchars($materia_nombre); ?> - <?php echo $grado_sel; ?>º <?php echo htmlspecialchars($grupo_sel); ?></h5>
                <button onclick="window.print()" class="btn btn-light btn-sm fw-bold text-info"><i class="bi bi-printer me-1"></i> Imprimir</button>
            </div>
            
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Matrícula</th>
                                <th class="text-start">Nombre del Alumno</th>
                                <th title="Clases Totales Pasadas"><i class="bi bi-calendar3"></i> Clases Disp.</th>
                                <th class="text-success"><i class="bi bi-check-lg"></i> Presentes</th>
                                <th class="text-warning text-dark"><i class="bi bi-clock-history"></i> Retardos</th>
                                <th class="text-danger"><i class="bi bi-x-lg"></i> Faltas</th>
                                <th>% Asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($res_reporte->num_rows > 0): ?>
                                <?php while ($row = $res_reporte->fetch_assoc()):
                $nombre_completo = htmlspecialchars($row['apellidos'] . ' ' . $row['nombre']);

                $total = $row['total_clases'];
                $porcentaje = ($total > 0) ? (($row['presentes'] + ($row['retardos'] * 0.5)) / $total) * 100 : 0;

                $badge_color = 'bg-success';
                if ($porcentaje < 80)
                    $badge_color = 'bg-warning text-dark';
                if ($porcentaje < 60)
                    $badge_color = 'bg-danger';
?>
                                <tr class="text-center">
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['matricula']); ?></span></td>
                                    <td class="text-start fw-bold"><?php echo $nombre_completo; ?></td>
                                    <td class="fs-5 text-muted"><?php echo $total; ?></td>
                                    <td class="fs-5 fw-bold text-success"><?php echo $row['presentes']; ?></td>
                                    <td class="fs-5 fw-bold text-warning text-dark"><?php echo $row['retardos']; ?></td>
                                    <td class="fs-5 fw-bold text-danger"><?php echo $row['faltas']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $badge_color; ?> px-3 py-2 fs-6 rounded-pill">
                                            <?php echo number_format($porcentaje, 1); ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php
            endwhile; ?>
                            <?php
        else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                        No hay registros de asistencia capturados para este grupo todavía.
                                    </td>
                                </tr>
                            <?php
        endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 pt-3 border-top d-flex gap-3 text-muted small justify-content-center flex-wrap">
                    <span><i class="bi bi-info-circle text-primary"></i> <strong>Nota:</strong> Dos retardos equivalen a una falta teórica (el % cuenta retardo como medio punto).</span>
                </div>
            </div>
        </div>
<?php
    }
    else {
        echo "<div class='alert alert-danger'>Error al generar el reporte de asistencia.</div>";
    }
endif;

$conexion->close();
?>

</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
