<?php
session_start();
include '../includes/conexion.php';

// Validar que el usuario sea Docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

$id_docente = $_SESSION['id_docente'];

// Obtener mensajes recibidos (Inbox)
$sql_inbox = "
    SELECT m.id_mensaje, m.asunto, m.fecha_envio, m.leido, 
           CONCAT(p.nombre, ' ', p.apellidos) AS remitente_nombre
    FROM mensajes m
    INNER JOIN padres p ON m.id_remitente = p.id_padre
    WHERE m.id_destinatario = ? 
      AND m.tipo_destinatario = 'Docente' 
      AND m.tipo_remitente = 'Padre'
    ORDER BY m.fecha_envio DESC
";
$stmt = $conexion->prepare($sql_inbox);
$stmt->bind_param("i", $id_docente);
$stmt->execute();
$resultado_inbox = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container animate-fade-in" style="animation-delay: 0.1s;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-envelope-fill text-primary me-2"></i>Bandeja de Entrada</h2>
        <div>
            <a href="mensajes_enviados.php" class="btn btn-outline-secondary me-2"><i
                    class="bi bi-send me-1"></i>Enviados</a>
            <a href="redactar_mensaje.php" class="btn btn-primary shadow-sm"><i
                    class="bi bi-pencil-square me-1"></i>Redactar Mensaje</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3" style="width: 50px;"></th>
                            <th class="py-3">De (Padre/Tutor)</th>
                            <th class="py-3">Asunto</th>
                            <th class="py-3">Fecha</th>
                            <th class="text-center pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_inbox->num_rows > 0): ?>
                            <?php while ($msg = $resultado_inbox->fetch_assoc()): ?>
                                <tr
                                    class="<?php echo $msg['leido'] == 0 ? 'bg-primary bg-opacity-10 fw-bold' : ''; ?> cursor-pointer transition-all">
                                    <td class="ps-4 text-center text-primary">
                                        <?php if ($msg['leido'] == 0): ?>
                                            <i class="bi bi-envelope-fill fs-5" title="No Leído"></i>
                                        <?php else: ?>
                                            <i class="bi bi-envelope-open text-muted fs-5" title="Leído"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                                style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                <?php echo strtoupper(substr($msg['remitente_nombre'], 0, 1) . substr(explode(' ', $msg['remitente_nombre'])[1] ?? '', 0, 1)); ?>
                                            </div>
                                            <span class="<?php echo $msg['leido'] == 0 ? 'text-primary' : 'text-body'; ?>">
                                                <?php echo htmlspecialchars($msg['remitente_nombre']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($msg['asunto']); ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('d M Y - h:i A', strtotime($msg['fecha_envio'])); ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3">Abrir</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted p-5">
                                    <i class="bi bi-mailbox display-4 d-block opacity-50 mb-3"></i>
                                    <h5>No tienes mensajes nuevos</h5>
                                    <p class="mb-0">Aquí aparecerán los mensajes que te envíen los padres de familia.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    tbody tr:hover {
        background-color: var(--bs-secondary-bg) !important;
        transform: scale(1.002);
    }
</style>

<script>
    // Hacer toda la fila clickeable hacia leer_mensaje
    document.addEventListener("DOMContentLoaded", function () {
        const rows = document.querySelectorAll("tbody tr.cursor-pointer");
        rows.forEach(row => {
            row.addEventListener("click", function (e) {
                // Prevenir que haga doble click si ya pinchó el botón
                if (e.target.tagName !== 'A' && !e.target.closest('a')) {
                    const link = this.querySelector("a.btn-outline-primary");
                    if (link) window.location.href = link.href;
                }
            });
        });
    });
</script>

<?php
$stmt->close();
$conexion->close();
?>
</div> <!-- Cierra contenedor-principal de header.php -->

<!-- Scripts requeridos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>

</body>

</html>