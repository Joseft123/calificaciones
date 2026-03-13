<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ../calificaciones/ver_calificaciones.php");
    exit();
}
include '../includes/conexion.php';
$sql = "SELECT id_usuario, nombre, correo, rol FROM usuarios";
$resultado = $conexion->query($sql);
include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/components.css">
<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">👥 Gestión de Usuarios</h2>
    <a href="crear_usuario.php" class="btn btn-success shadow-sm px-4 rounded-pill">➕ Nuevo Usuario</a>
</div>
<div class="card shadow border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-dark">
                    <tr>
                        <th class="py-3 px-4 border-0">ID</th>
                        <th class="py-3 border-0">Nombre</th>
                        <th class="py-3 border-0">Correo</th>
                        <th class="py-3 border-0">Rol</th>
                        <th class="py-3 px-4 border-0 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
$delay = 0.3;
while ($fila = $resultado->fetch_assoc()):
    $rolClass = ($fila['rol'] == 'Director') ? 'bg-danger' : 'bg-primary';
?>
                    <tr class="animate-fade-in table-row" style="animation-delay: <?php echo $delay; ?>s;">
                        <td class="px-4"><span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">#<?php echo $fila['id_usuario']; ?></span></td>
                        <td class="fw-medium text-dark"><i class="bi bi-person-badge text-secondary me-2"></i><?php echo htmlspecialchars($fila['nombre']); ?></td>
                        <td><a href="mailto:<?php echo htmlspecialchars($fila['correo']); ?>" class="text-decoration-none"><i class="bi bi-envelope-at text-muted me-1"></i><?php echo htmlspecialchars($fila['correo']); ?></a></td>
                        <td><span class="badge <?php echo $rolClass; ?> rounded-pill px-3"><i class="bi bi-shield-lock border-end pe-1 me-1"></i><?php echo htmlspecialchars($fila['rol']); ?></span></td>
                        <td class="px-4 text-center">
                            <a href="editar_usuario.php?id=<?php echo $fila['id_usuario']; ?>" class="btn btn-outline-warning btn-sm rounded-circle me-1 shadow-sm" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                            <a href="eliminar_usuario.php?id=<?php echo $fila['id_usuario']; ?>" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" title="Eliminar" onclick="return confirm('¿Eliminar usuario?');"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                    <?php
    $delay += 0.05;
endwhile;
if ($resultado->num_rows == 0) {
    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>
                            <div class='fs-1 mb-2'>🚫</div>
                            <p class='mb-0 fw-bold'>No hay usuarios registrados aún.</p>
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