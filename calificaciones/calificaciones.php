<?php
// Iniciar o retomar la sesión existente
session_start();

// Validar si la variable de sesión 'id_usuario' NO existe
if (!isset($_SESSION['id_usuario'])) {
    // Si no existe, redirigimos al usuario a la pantalla de login
    header("Location: ../auth/login.php");
    exit();
}
// Incluir el archivo de conexión
include '../includes/conexion.php';

// Consultar los alumnos para el menú desplegable
$query_alumnos = "SELECT id_alumno, matricula, nombre, apellidos FROM alumnos";
$result_alumnos = $conexion->query($query_alumnos);

// Consultar las materias para el menú desplegable
$query_materias = "SELECT id_materia, nombre_materia FROM materias";
$result_materias = $conexion->query($query_materias);

// Incluir el diseño principal (menú y apertura del contenedor)
include '../includes/header.php';
?>

<style>
    /* Animación de entrada suave hacia arriba */
    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Clase base para elementos animados */
    .animate-fade-in {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    [data-bs-theme="dark"] .card {
        background-color: #2b2b2b;
        color: #ffffff;
    }
    
    [data-bs-theme="dark"] .bg-light,
    [data-bs-theme="dark"] .bg-white {
        background-color: #1e1e1e !important;
        color: var(--bs-light) !important;
    }
    
    [data-bs-theme="dark"] .form-label.text-secondary {
        color: #adb5bd !important;
    }
    
    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select {
        background-color: #2b2b2b;
        color: #fff;
        border-color: #495057;
    }

    [data-bs-theme="dark"] .form-control:focus,
    [data-bs-theme="dark"] .form-select:focus {
        background-color: #2b2b2b;
        color: #fff;
        border-color: #8db5e3;
    }
</style>

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
                                <option value="<?php echo $row['id_alumno']; ?>">
                                    <?php echo $row['matricula'] . " - " . $row['nombre'] . " " . $row['apellidos']; ?>
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