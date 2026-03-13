<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
include '../includes/conexion.php';
$sql = "SELECT id_docente, nomina, nombre, apellidos, correo FROM docentes";
$resultado = $conexion->query($sql);
include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">👨‍🏫 Directorio de Docentes</h2>
    <a href="crear_docente.php" class="btn btn-success shadow-sm px-4 rounded-pill">➕ Registrar Docente</a>
</div>

<div class="card shadow border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-dark">
                    <tr>
                        <th class="py-3 px-4 border-0">Nómina</th>
                        <th class="py-3 border-0">Nombre Completo</th>
                        <th class="py-3 border-0">Correo Institucional</th>
                        <th class="py-3 px-4 border-0 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
$delay = 0.3;
while ($fila = $resultado->fetch_assoc()):
?>
                    <tr class="animate-fade-in table-row" style="animation-delay: <?php echo $delay; ?>s;">
                        <td class="px-4"><span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-person-video3 text-primary me-2"></i><?php echo htmlspecialchars($fila['nomina']); ?></span></td>
                        <td class="fw-medium text-dark"><?php echo htmlspecialchars($fila['nombre'] . ' ' . $fila['apellidos']); ?></td>
                        <td><a href="mailto:<?php echo htmlspecialchars($fila['correo']); ?>" class="text-decoration-none"><i class="bi bi-envelope-at text-muted me-1"></i><?php echo htmlspecialchars($fila['correo']); ?></a></td>
                        <td class="px-4 text-center">
                            <a href="editar_docente.php?id=<?php echo $fila['id_docente']; ?>" class="btn btn-outline-warning btn-sm rounded-circle me-1 shadow-sm" title="Editar Docente"><i class="bi bi-pencil-fill"></i></a>
                            <a href="eliminar_docente.php?id=<?php echo $fila['id_docente']; ?>" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" title="Despedir/Eliminar Docente" onclick="return confirm('¿Estás seguro de que deseas eliminar este docente? No podrás revertir esta acción de forma directa, y podrías dejar materias sin asignar.');"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                    <?php
    $delay += 0.05;
endwhile;

if ($resultado->num_rows == 0) {
    echo "<tr><td colspan='4' class='text-center py-5 text-muted'>
        <div class='mb-3'>
            <i class='bi bi-person-video3 text-primary' style='font-size: 4rem; opacity: 0.5;'></i>
        </div>
        <h5 class='fw-bold text-body'>Directorio docente vacío</h5>
        <p class='mb-3'>No hay maestros registrados en la base de datos.</p>
        <a href='crear_docente.php' class='btn btn-primary rounded-pill btn-sm px-4'>Crear el Primer Docente</a>
    </td></tr>";
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
