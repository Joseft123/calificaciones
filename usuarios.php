// usuarios.php (Leer)
<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'Director') {
    header("Location: ver_calificaciones.php");
    exit();
}
include 'conexion.php';
$sql = "SELECT id_usuario, nombre, correo, rol FROM usuarios";
$resultado = $conexion->query($sql);
include 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary m-0">👥 Gestión de Usuarios</h2>
    <a href="crear_usuario.php" class="btn btn-success">➕ Nuevo Usuario</a>
</div>
<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered align-middle shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?php echo $fila['id_usuario']; ?></td>
                <td><?php echo $fila['nombre']; ?></td>
                <td><?php echo $fila['correo']; ?></td>
                <td><?php echo $fila['rol']; ?></td>
                <td>
                    <a href="editar_usuario.php?id=<?php echo $fila['id_usuario']; ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="eliminar_usuario.php?id=<?php echo $fila['id_usuario']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar usuario?');">Eliminar</a>
                </td>
            </tr>
            <?php
endwhile; ?>
        </tbody>
    </table>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>