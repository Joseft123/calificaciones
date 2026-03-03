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

<style>
    /* Animación de entrada suave hacia arriba */
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    [data-bs-theme="dark"] .card {
        background-color: #2b2b2b;
        color: #ffffff;
    }
    [data-bs-theme="dark"] .bg-white {
        background-color: transparent !important;
    }
    [data-bs-theme="dark"] .text-dark {
        color: #f8f9fa !important;
    }
    [data-bs-theme="dark"] .badge.bg-light {
        background-color: #495057 !important;
        color: #f8f9fa !important;
        border-color: #6c757d !important;
    }
    [data-bs-theme="dark"] .table-hover > tbody > tr:hover > * {
        background-color: rgba(255, 255, 255, 0.05);
        color: #ffffff;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">👨‍🎓 Gestión de Alumnos</h2>
    <a href="crear_alumno.php" class="btn btn-success shadow-sm px-4 rounded-pill">➕ Inscribir Alumno</a>
</div>

<div class="card shadow border-0 rounded-4 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="py-3 px-4 border-0">Matrícula</th>
                        <th class="py-3 border-0">Nombre Completo</th>
                        <th class="py-3 border-0">Nivel</th>
                        <th class="py-3 border-0">Grado y Grupo</th>
                        <th class="py-3 px-4 border-0 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if ($resultado->num_rows > 0) {
    $delay = 0.3;
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr class='animate-fade-in' style='animation-delay: {$delay}s;'>";
        echo "<td class='px-4'><span class='badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm'><i class='bi bi-hash text-muted'></i>" . $fila['matricula'] . "</span></td>";
        echo "<td class='fw-medium text-dark'>" . $fila['apellidos'] . " " . $fila['nombre'] . "</td>";
        echo "<td><span class='badge bg-info text-dark rounded-pill px-3'>" . $fila['nivel'] . "</span></td>";
        echo "<td><span class='badge bg-secondary rounded-pill px-3'>" . $fila['grado'] . "º " . $fila['grupo'] . "</span></td>";
        echo "<td class='px-4 text-center'>
        <a href='../calificaciones/generar_boleta_pdf.php?id=" . $fila['id_alumno'] . "' target='_blank' class='btn btn-outline-danger btn-sm rounded-circle me-1 shadow-sm' title='Imprimir Boleta PDF'><i class='bi bi-file-earmark-pdf-fill'></i></a>
        <a href='editar_alumno.php?id=" . $fila['id_alumno'] . "' class='btn btn-outline-warning btn-sm rounded-circle me-1 shadow-sm' title='Editar'><i class='bi bi-pencil'></i></a>
        <a href='eliminar_alumno.php?id=" . $fila['id_alumno'] . "' class='btn btn-outline-danger btn-sm rounded-circle shadow-sm' title='Eliminar' onclick='return confirm(\"¿Estás seguro de eliminar a este alumno y todo su historial de calificaciones?\");'><i class='bi bi-trash'></i></a>
      </td>";
        echo "</tr>";
        $delay += 0.05;
    }
}
else {
    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>
        <div class='fs-1 mb-2'>📭</div>
        <p class='mb-0 fw-bold'>No hay alumnos inscritos aún.</p>
    </td></tr>";
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $conexion->close(); ?>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>