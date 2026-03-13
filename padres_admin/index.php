<?php
session_start();

// Validar que el usuario sea administrador (Director)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

// Manejo de eliminación
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $conexion->query("DELETE FROM padres WHERE id_padre = $id_eliminar");
    header("Location: index.php?msg=eliminado");
    exit();
}

// Consultar todos los padres y la cantidad de hijos asignados
$sql = "SELECT p.*, COUNT(pa.id_alumno) as cantidad_hijos
        FROM padres p
        LEFT JOIN padre_alumno pa ON p.id_padre = pa.id_padre
        GROUP BY p.id_padre
        ORDER BY p.apellidos, p.nombre";
$resultado = $conexion->query($sql);

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people-fill text-primary me-2"></i>Directorio de Padres/Tutores</h2>
    <a href="crear_padre.php" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>Registrar Padre</a>
</div>

<div class="card shadow-sm border-0 rounded-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Correo Electrónico</th>
                        <th>Teléfono</th>
                        <th class="text-center">Hijos Asignados</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while ($padre = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-medium">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($padre['nombre'], 0, 1) . substr($padre['apellidos'], 0, 1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($padre['apellidos'] . ' ' . $padre['nombre']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($padre['correo']); ?></td>
                                <td><?php echo htmlspecialchars($padre['telefono'] ? $padre['telefono'] : 'N/A'); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark rounded-pill px-3 py-2">
                                        <?php echo $padre['cantidad_hijos']; ?> Alumno(s)
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="editar_padre.php?id=<?php echo $padre['id_padre']; ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmarEliminacion(<?php echo $padre['id_padre']; ?>)">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay padres o tutores registrados en el sistema.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(id) {
    if (confirm("¿Estás seguro de que deseas eliminar a este padre/tutor? Se eliminará su acceso al sistema, pero NO se borrarán los alumnos asociados.")) {
        window.location.href = "index.php?eliminar=" + id;
    }
}
</script>

<?php 
$conexion->close();
?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
