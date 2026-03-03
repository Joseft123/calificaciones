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
$fecha_sel = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

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
        border-left: 4px solid #198754 !important;
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
    .btn-radio-group .btn {
        min-width: 45px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-success m-0 fw-bold">📅 Pasar Lista</h2>
    <?php if ($id_materia_sel > 0): ?>
        <a href="pasar_lista.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver a Grupos</a>
    <?php
endif; ?>
</div>

<?php
// Mostrar mensajes de éxito o error
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'success') {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Asistencia guardada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
    elseif ($_GET['msg'] == 'error') {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Ocurrió un error al guardar la asistencia.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

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
                            <div class='bg-success bg-opacity-10 text-success rounded-circle p-3 me-3 text-center' style='width: 50px; height: 50px;'>
                                <i class='bi bi-book fs-4' style='line-height: .5;'></i>
                            </div>
                            <h5 class='card-title mb-0 fw-bold text-truncate' title='<?php echo $nombre_materia; ?>'><?php echo $nombre_materia; ?></h5>
                        </div>
                        <ul class="list-unstyled text-body-secondary mb-4">
                            <li><i class="bi bi-diagram-3 me-2 opacity-75"></i> <strong>Nivel:</strong> <?php echo $nivel; ?></li>
                            <li><i class="bi bi-people me-2 opacity-75"></i> <strong>Grupo:</strong> <?php echo $grado; ?>º <?php echo $grupo; ?></li>
                        </ul>
                        <div class='d-grid'>
                            <a href='pasar_lista.php<?php echo $url; ?>' class='btn btn-success rounded-pill'><i class="bi bi-clipboard-check me-1"></i> Tomar Asistencia</a>
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
            <p class='text-muted mb-0'>No tienes ninguna materia ni grupo asignado actualmente para pasar lista.</p>
        </div>
<?php
    endif;

// Paso 2: Materia y Grupo seleccionados, mostrar tabla de alumnos
else:
    // Obtener asistencias previas para hoy (para pre-seleccionar si ya se tomó lista)
    $asistencias_previas = [];
    $stmt_asist = $conexion->prepare("SELECT id_alumno, estado FROM asistencias WHERE id_docente = ? AND id_materia = ? AND fecha = ?");
    if ($stmt_asist) {
        $stmt_asist->bind_param("iis", $id_docente, $id_materia_sel, $fecha_sel);
        $stmt_asist->execute();
        $res_asist = $stmt_asist->get_result();
        while ($row = $res_asist->fetch_assoc()) {
            $asistencias_previas[$row['id_alumno']] = $row['estado'];
        }
        $stmt_asist->close();
    }

    // Obtener los alumnos del grupo
    $stmt_alumnos = $conexion->prepare("SELECT id_alumno, matricula, nombre, apellidos FROM alumnos WHERE nivel = ? AND grado = ? AND grupo = ? ORDER BY apellidos ASC, nombre ASC");
    if ($stmt_alumnos) {
        $stmt_alumnos->bind_param("sis", $nivel_sel, $grado_sel, $grupo_sel);
        $stmt_alumnos->execute();
        $res_alumnos = $stmt_alumnos->get_result();

        // Obtener info de la materia
        $sql_m = "SELECT nombre_materia FROM materias WHERE id_materia = $id_materia_sel";
        $materia_nombre = $conexion->query($sql_m)->fetch_assoc()['nombre_materia'] ?? 'Desconocida';
?>
        <div class="card shadow-sm border-0 rounded-4 animate-fade-in" style="animation-delay: 0.2s;">
            <div class="card-header bg-success text-white px-4 py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold"><i class="bi bi-list-check me-2"></i> <?php echo htmlspecialchars($materia_nombre); ?> - <?php echo $grado_sel; ?>º <?php echo htmlspecialchars($grupo_sel); ?></h5>
            </div>
            <div class="card-body p-4">
                <form action="guardar_asistencia.php" method="POST">
                    <input type="hidden" name="id_materia" value="<?php echo $id_materia_sel; ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="fecha" class="form-label fw-bold">Fecha de Asistencia:</label>
                            <input type="date" class="form-control form-control-lg" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha_sel); ?>" required onchange="window.location.href='pasar_lista.php?id_materia=<?php echo $id_materia_sel; ?>&nivel=<?php echo urlencode($nivel_sel); ?>&grado=<?php echo $grado_sel; ?>&grupo=<?php echo urlencode($grupo_sel); ?>&fecha='+this.value">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%">Matrícula</th>
                                    <th>Nombre del Alumno</th>
                                    <th class="text-center" style="width: 30%">Asistencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($res_alumnos->num_rows > 0): ?>
                                    <?php while ($alumno = $res_alumnos->fetch_assoc()):
                $id_alum = $alumno['id_alumno'];
                $nombre_completo = htmlspecialchars($alumno['apellidos'] . ' ' . $alumno['nombre']);
                $estado_actual = isset($asistencias_previas[$id_alum]) ? $asistencias_previas[$id_alum] : 'Presente';
?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($alumno['matricula']); ?></span></td>
                                        <td class="fw-bold"><?php echo $nombre_completo; ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-radio-group" role="group">
                                                <input type="radio" class="btn-check" name="estado[<?php echo $id_alum; ?>]" id="pres_<?php echo $id_alum; ?>" value="Presente" autocomplete="off" <?php echo $estado_actual == 'Presente' ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-success" for="pres_<?php echo $id_alum; ?>" title="Presente"><i class="bi bi-check-lg"></i> P</label>

                                                <input type="radio" class="btn-check" name="estado[<?php echo $id_alum; ?>]" id="tar_<?php echo $id_alum; ?>" value="Retardo" autocomplete="off" <?php echo $estado_actual == 'Retardo' ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-warning" for="tar_<?php echo $id_alum; ?>" title="Retardo"><i class="bi bi-clock-history"></i> R</label>

                                                <input type="radio" class="btn-check" name="estado[<?php echo $id_alum; ?>]" id="fal_<?php echo $id_alum; ?>" value="Falta" autocomplete="off" <?php echo $estado_actual == 'Falta' ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-danger" for="fal_<?php echo $id_alum; ?>" title="Falta"><i class="bi bi-x-lg"></i> F</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
            endwhile; ?>
                                <?php
        else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No hay alumnos inscritos en este grupo referenciado.</td>
                                    </tr>
                                <?php
        endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($res_alumnos->num_rows > 0): ?>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow"><i class="bi bi-save me-2"></i> Guardar Asistencias</button>
                    </div>
                    <?php
        endif; ?>
                </form>
            </div>
        </div>
<?php
    }
    else {
        echo "<div class='alert alert-danger'>Error al consultar los alumnos.</div>";
    }
endif;

$conexion->close();
?>

</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
