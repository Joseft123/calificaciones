<?php
// Iniciar o retomar la sesión existente
session_start();

// Validar si la variable de sesión 'id_usuario' o 'id_docente' NO existen
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login.php");
    exit();
}
// Incluir el archivo de conexión
include '../includes/conexion.php';

// Obtener el ID del docente si está logueado
$id_docente = isset($_SESSION['id_docente']) ? $_SESSION['id_docente'] : null;

// Obtener el id_alumno de la URL si existe para autoseleccionarlo
$id_selected_alumno = isset($_GET['id_alumno']) ? (int)$_GET['id_alumno'] : '';

if ($id_docente) {
    // Si es docente, mostrar solo los alumnos de los niveles, grados y grupos que tiene asignados
    // Además filtramos para asegurar que no se repitan alumnos si están en varias asignaciones (uso de GROUP BY o DISTINCT)
    $query_alumnos = "SELECT DISTINCT a.id_alumno, a.matricula, a.nombre, a.apellidos 
                      FROM alumnos a
                      INNER JOIN docente_materia_grupo dmg ON a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo
                      WHERE dmg.id_docente = $id_docente";

    // Si es docente, mostrar solo las materias que tiene asignadas
    $query_materias = "SELECT DISTINCT m.id_materia, m.nombre_materia 
                       FROM materias m
                       INNER JOIN docente_materia_grupo dmg ON m.id_materia = dmg.id_materia
                       WHERE dmg.id_docente = $id_docente";
}
else {
    // Si no es docente (es decir, es Director/Admin), mostrar todos
    $query_alumnos = "SELECT id_alumno, matricula, nombre, apellidos FROM alumnos";
    $query_materias = "SELECT id_materia, nombre_materia FROM materias";
}

$result_alumnos = $conexion->query($query_alumnos);
$result_materias = $conexion->query($query_materias);

// Incluir el diseño principal (menú y apertura del contenedor)
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
        <h2 class="text-primary m-0 fw-bold">📝 Registrar Calificación</h2>
        <a href="ver_calificaciones.php" class="btn btn-outline-secondary shadow-sm px-4 rounded-pill">⬅️ Volver al Historial</a>
    </div>

    <div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
        <div class="card-header bg-primary text-white px-4 py-3" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
            <h5 class="m-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Nueva Captura</h5>
        </div>
        <div class="card-body p-4 p-md-5">
            <form action="guardar_calificacion.php" method="POST">
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6 animate-fade-in" style="animation-delay: 0.3s;">
                        <label for="id_alumno" class="form-label fw-bold text-secondary"><i class="bi bi-person-fill me-1"></i> Alumno:</label>
                        <select name="id_alumno" id="id_alumno" class="form-select form-select-lg shadow-sm" required>
                            <option value="">Selecciona un alumno...</option>
                            <?php while ($row = $result_alumnos->fetch_assoc()): ?>
                                <?php $selected = ($row['id_alumno'] == $id_selected_alumno) ? 'selected' : ''; ?>
                                <option value="<?php echo $row['id_alumno']; ?>" <?php echo $selected; ?>>
                                    <?php echo $row['matricula'] . " - " . htmlspecialchars($row['nombre']) . " " . htmlspecialchars($row['apellidos']); ?>
                                </option>
                            <?php
endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6 animate-fade-in" style="animation-delay: 0.4s;">
                        <label for="id_materia" class="form-label fw-bold text-secondary"><i class="bi bi-book-fill me-1"></i> Materia:</label>
                        <select name="id_materia" id="id_materia" class="form-select form-select-lg shadow-sm" required>
                            <option value="">Selecciona una materia...</option>
                            <?php while ($row = $result_materias->fetch_assoc()): ?>
                                <option value="<?php echo $row['id_materia']; ?>">
                                    <?php echo $row['nombre_materia']; ?>
                                </option>
                            <?php
endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6 animate-fade-in" style="animation-delay: 0.5s;">
                        <label for="periodo" class="form-label fw-bold text-secondary"><i class="bi bi-calendar2-week-fill me-1"></i> Periodo:</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-123 text-muted"></i></span>
                            <input type="number" name="periodo" id="periodo" class="form-control border-start-0 ps-0" min="1" max="5" placeholder="Ej. 1 para primer parcial" required>
                        </div>
                    </div>

                    <div class="col-md-6 animate-fade-in" style="animation-delay: 0.6s;">
                        <label for="calificacion" class="form-label fw-bold text-secondary"><i class="bi bi-star-fill me-1 text-warning"></i> Calificación:</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-end-0">💯</span>
                            <input type="number" name="calificacion" id="calificacion" class="form-control text-primary fw-bold fs-5 border-start-0 ps-0" step="0.1" min="0" max="10" placeholder="0.0 - 10.0" required>
                        </div>
                    </div>
                </div>

                <div class="mt-5 text-end animate-fade-in" style="animation-delay: 0.7s;">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                        <i class="bi bi-save me-2"></i> Guardar Calificación
                    </button>
                </div>
                
            </form>
        </div>
    </div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>