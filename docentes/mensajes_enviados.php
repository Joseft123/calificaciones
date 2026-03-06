<?php
session_start();
include '../includes/conexion.php';

// Validar que el usuario sea Docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login_docente.php");
    exit();
}

$id_docente = $_SESSION['id_docente'];

// Obtener mensajes enviados (Outbox)
$sql_outbox = "
    SELECT m.id_mensaje, m.asunto, m.fecha_envio, m.leido, 
           CONCAT(p.nombre, ' ', p.apellidos) AS destinatario_nombre
    FROM mensajes m
    INNER JOIN padres p ON m.id_destinatario = p.id_padre
    WHERE m.id_remitente = ? 
      AND m.tipo_remitente = 'Docente' 
      AND m.tipo_destinatario = 'Padre'
    ORDER BY m.fecha_envio DESC
";
$stmt = $conexion->prepare($sql_outbox);
$stmt->bind_param("i", $id_docente);
$stmt->execute();
$resultado_outbox = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container animate-fade-in" style="animation-delay: 0.1s;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-send-fill text-primary me-2"></i>Mensajes Enviados</h2>
        <div>
            <a href="mensajes.php" class="btn btn-outline-secondary me-2"><i class="bi bi-inbox me-1"></i>Recibidos</a>
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
                            <th class="py-3">Para (Padre/Tutor)</th>
                            <th class="py-3">Asunto</th>
                            <th class="py-3">Fecha de Envío</th>
                            <th class="text-center pe-4 py-3">Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_outbox->num_rows > 0): ?>
                            <?php while ($msg = $resultado_outbox->fetch_assoc()): ?>
                                <tr class="cursor-pointer transition-all"
                                    onclick="window.location='leer_mensaje.php?id=<?php echo $msg['id_mensaje']; ?>'">
                                    <td class="ps-4 text-center text-secondary">
                                        <i class="bi bi-send"></i>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-circle d-flex justify-content-center align-items-center me-3"
                                                style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                <?php echo strtoupper(substr($msg['destinatario_nombre'], 0, 1) . substr(explode(' ', $msg['destinatario_nombre'])[1] ?? '', 0, 1)); ?>
                                            </div>
                                            <span class="text-body fw-medium">
                                                <?php echo htmlspecialchars($msg['destinatario_nombre']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-body">
                                        <?php echo htmlspecialchars($msg['asunto']); ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('d M Y - h:i A', strtotime($msg['fecha_envio'])); ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <?php if ($msg['leido'] == 1): ?>
                                            <span class="badge bg-success rounded-pill"><i
                                                    class="bi bi-check2-all me-1"></i>Leído</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill"><i
                                                    class="bi bi-check2 me-1"></i>Entregado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted p-5">
                                    <i class="bi bi-send-x display-4 d-block opacity-50 mb-3"></i>
                                    <h5>No has enviado mensajes</h5>
                                    <p class="mb-0">Tus mensajes enviados a los padres aparecerán aquí.</p>
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