<?php
session_start();

// Validar inicio de sesiÃ³n del Padre
if (!isset($_SESSION['id_padre'])) {
    header("Location: ../auth/login_padre.php");
    exit();
}

include '../includes/conexion.php';
include '../includes/funciones_ciclo.php';

$id_padre = intval($_SESSION['id_padre']);
$id_ciclo_actual = getCicloActivo($conexion);

// Obtener cantidad de mensajes no leÃ­dos
$unread_mensajes_padre = 0;
$stmt_unread = $conexion->prepare("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = ? AND tipo_destinatario = 'Padre' AND leido = 0");
$stmt_unread->bind_param("i", $id_padre);
$stmt_unread->execute();
$res_unread = $stmt_unread->get_result();
if ($res_unread) {
    $unread_mensajes_padre = $res_unread->fetch_assoc()['total'];
}

// 1. Obtener los hijos (alumnos) asignados a este padre
$sql_hijos = "SELECT a.id_alumno, a.matricula, a.nombre, a.apellidos, a.nivel, a.grado, a.grupo, pa.parentesco 
              FROM alumnos a
              INNER JOIN padre_alumno pa ON a.id_alumno = pa.id_alumno
              WHERE pa.id_padre = $id_padre";
$res_hijos = $conexion->query($sql_hijos);

$hijos = [];
if ($res_hijos && $res_hijos->num_rows > 0) {
    while ($fila = $res_hijos->fetch_assoc()) {
        $id_al = $fila['id_alumno'];

        // Obtener promedio del hijo en el ciclo actual
        $sql_promedio = "SELECT AVG(c.calificacion) as promedio_general
                         FROM calificaciones c
                         INNER JOIN docente_materia_grupo dmg ON c.id_materia = dmg.id_materia 
                              AND dmg.nivel = '{$fila['nivel']}' AND dmg.grado = {$fila['grado']} AND dmg.grupo = '{$fila['grupo']}'
                         WHERE c.id_alumno = $id_al AND dmg.id_ciclo = $id_ciclo_actual";
        $res_prom = $conexion->query($sql_promedio);
        $promedio = $res_prom->fetch_assoc()['promedio_general'];
        $fila['promedio'] = $promedio ? round($promedio, 2) : 'S/C'; // Sin Calificaciones

        // Obtener total de faltas y retardos en el ciclo actual
        $sql_faltas = "SELECT estado, COUNT(id_asistencia) as total 
                       FROM asistencias 
                       WHERE id_alumno = $id_al AND estado IN ('Falta', 'Retardo')
                       GROUP BY estado";
        $res_faltas = $conexion->query($sql_faltas);
        $faltas = 0;
        $retardos = 0;
        if ($res_faltas && $res_faltas->num_rows > 0) {
            while ($f = $res_faltas->fetch_assoc()) {
                if ($f['estado'] == 'Falta')
                    $faltas = $f['total'];
                if ($f['estado'] == 'Retardo')
                    $retardos = $f['total'];
            }
        }
        $fila['faltas'] = $faltas;
        $fila['retardos'] = $retardos;

        $hijos[] = $fila;
    }
}

// 2. Obtener Avisos para Padres o Todos
$sql_avisos = "SELECT c.titulo, c.mensaje, c.destinatario, c.fecha_publicacion, u.nombre AS autor 
               FROM comunicados c
               INNER JOIN usuarios u ON c.id_autor = u.id_usuario
               WHERE c.destinatario IN ('Todos', 'Padres')
               ORDER BY c.fecha_publicacion DESC LIMIT 4";
$res_avisos = $conexion->query($sql_avisos);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Familiar - Sistema Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="bg-body-tertiary">

    <nav class="navbar navbar-expand-lg navbar-dark floating-nav floating-nav-success mx-auto">
        <div class="container-fluid px-2">
            <a class="navbar-brand fw-bold" href="dashboard.php">ðŸ‘¨â€ðŸ‘©â€ðŸ‘§â€ðŸ‘¦ Portal Familiar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPadre">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarPadre">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold" href="mensajes.php"><i
                                class="bi bi-envelope-fill me-1"></i> Mensajes
                            <?php if ($unread_mensajes_padre > 0): ?>
                                <span
                                    class="badge bg-danger rounded-pill ms-1 shadow-sm"><?php echo $unread_mensajes_padre; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3 d-flex align-items-center">
                        <span class="text-light me-3 fw-medium d-flex align-items-center gap-2">
                            <?php if (!empty($_SESSION['foto_perfil'])): ?>
                                <img src="../assets/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" 
                                     alt="Perfil" class="rounded-circle border border-2 border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                            <?php else: ?>
                                👋 
                            <?php endif; ?>
                            Hola, <?php echo htmlspecialchars($_SESSION['nombre_padre']); ?>
                        </span>
                        <a class="btn btn-outline-light btn-sm me-2 rounded-circle premium-icon-btn" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i class="bi bi-person-gear"></i></a>
                        <button class="btn btn-outline-light btn-sm me-2 rounded-circle premium-icon-btn" id="btnThemeToggle" title="Modo Visual">
                            <span id="themeIcon">🌙</span>
                        </button>
                        <a class="btn btn-danger btn-sm rounded-pill px-3 d-flex justify-content-center align-items-center" href="../auth/cerrar_sesion_padre.php"><i
                                class="bi bi-box-arrow-right me-1"></i>Salir</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <!-- Header Familiar -->
        <div class="card shadow border-0 rounded-4 overflow-hidden mb-5 animate-fade-in"
            style="background: linear-gradient(135deg, #198754 0%, #157347 100%); color: white;">
            <div class="card-body p-4 p-md-5 position-relative">
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-md-8">
                        <h1 class="display-6 fw-bold mb-2">Resumen Familiar</h1>
                        <p class="fs-5 opacity-75 mb-0">Bienvenido al espacio de seguimiento acadÃ©mico. AquÃ­ puedes ver
                            el progreso de tus hijos durante el ciclo escolar y revisar anuncios importantes de la
                            escuela.</p>
                    </div>
                    <div class="col-md-4 text-center mt-4 mt-md-0">
                        <i class="bi bi-house-heart text-white opacity-25"
                            style="font-size: 8rem; position: absolute; right: 20px; top: -20px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Izquierda: Hijos -->
            <div class="col-lg-8 animate-fade-in" style="animation-delay: 0.2s;">
                <h4 class="fw-bold mb-3 text-body"><i class="bi bi-person-lines-fill text-primary me-2"></i>Mis Hijos (
                    <?php echo count($hijos); ?>)
                </h4>

                <?php if (count($hijos) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($hijos as $hijo): ?>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-0 rounded-4 hover-scale">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center fs-3 fw-bold shadow-sm me-3"
                                                style="width: 60px; height: 60px;">
                                                <?php echo strtoupper(substr($hijo['nombre'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold m-0 text-truncate text-body">
                                                    <?php echo htmlspecialchars($hijo['nombre']); ?>
                                                </h5>
                                                <span class="text-muted small">
                                                    <?php echo htmlspecialchars($hijo['apellidos']); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <span class="badge bg-secondary rounded-pill px-3 py-2 fw-normal fs-6 shadow-sm"><i
                                                    class="bi bi-building me-1"></i>
                                                <?php echo $hijo['nivel']; ?>
                                            </span>
                                            <span
                                                class="badge bg-info text-dark rounded-pill px-3 py-2 fw-normal fs-6 shadow-sm ms-1"><i
                                                    class="bi bi-people-fill me-1"></i>
                                                <?php echo $hijo['grado'] . 'Âº ' . $hijo['grupo']; ?>
                                            </span>
                                        </div>

                                        <div class="row g-2 mb-4 text-center">
                                            <div class="col-4">
                                                <div class="p-2 border rounded-3 bg-body-tertiary">
                                                    <div class="small text-muted mb-1">Promedio</div>
                                                    <div
                                                        class="fw-bold fs-5 <?php echo (is_numeric($hijo['promedio']) && $hijo['promedio'] < 6) ? 'text-danger' : 'text-success'; ?>">
                                                        <?php echo $hijo['promedio']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2 border rounded-3 bg-body-tertiary">
                                                    <div class="small text-muted mb-1">Faltas</div>
                                                    <div
                                                        class="fw-bold fs-5 <?php echo ($hijo['faltas'] > 0) ? 'text-danger' : 'text-body'; ?>">
                                                        <?php echo $hijo['faltas']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2 border rounded-3 bg-body-tertiary">
                                                    <div class="small text-muted mb-1">Retardos</div>
                                                    <div
                                                        class="fw-bold fs-5 <?php echo ($hijo['retardos'] > 0) ? 'text-warning' : 'text-body'; ?>">
                                                        <?php echo $hijo['retardos']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <a href="detalle_hijo.php?id=<?php echo $hijo['id_alumno']; ?>"
                                                class="btn btn-primary rounded-pill fw-medium text-start ps-4">
                                                <i class="bi bi-journal-text me-2"></i> Calificaciones Detalladas
                                                <i class="bi bi-arrow-right-short float-end mt-1 fs-5"></i>
                                            </a>
                                            <a href="asistencias_hijo.php?id=<?php echo $hijo['id_alumno']; ?>"
                                                class="btn btn-info text-white rounded-pill fw-medium text-start ps-4">
                                                <i class="bi bi-calendar2-check-fill me-2"></i> Récord de Asistencia
                                                <i class="bi bi-arrow-right-short float-end mt-1 fs-5"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-5 text-center">
                            <i class="bi bi-person-x text-muted opacity-50 mb-3" style="font-size: 4rem;"></i>
                            <h4 class="text-body fw-bold">No tienes alumnos asociados</h4>
                            <p class="text-muted mb-0">ComunÃ­cate en DirecciÃ³n Escolar para que la administraciÃ³n asocie a
                                tus hijos con tu cuenta familiar.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Derecha: Avisos -->
            <div class="col-lg-4 animate-fade-in" style="animation-delay: 0.3s;">
                <h4 class="fw-bold mb-3 text-body"><i class="bi bi-megaphone-fill text-warning me-2"></i>Avisos
                    Recientes</h4>

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-0">
                        <?php if ($res_avisos && $res_avisos->num_rows > 0): ?>
                            <div class="list-group list-group-flush rounded-4 overflow-hidden">
                                <?php while ($aviso = $res_avisos->fetch_assoc()):
                                    $badge = 'bg-primary';
                                    if ($aviso['destinatario'] == 'Padres')
                                        $badge = 'bg-success';
                                    ?>
                                    <div class="list-group-item p-4 pb-3 border-bottom">
                                        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-bold text-body">
                                                <?php echo htmlspecialchars($aviso['titulo']); ?>
                                            </h6>
                                            <span class="badge <?php echo $badge; ?> rounded-pill small">
                                                <?php echo htmlspecialchars($aviso['destinatario']); ?>
                                            </span>
                                        </div>
                                        <p class="mb-2 small text-muted">
                                            <?php echo nl2br(htmlspecialchars($aviso['mensaje'])); ?>
                                        </p>
                                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                                            <span><i class="bi bi-person-fill me-1"></i>
                                                <?php echo htmlspecialchars($aviso['autor']); ?>
                                            </span>
                                            <span><i class="bi bi-calendar3 me-1"></i>
                                                <?php echo date('d/m/Y', strtotime($aviso['fecha_publicacion'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-5 text-center">
                                <i class="bi bi-bell-slash text-muted opacity-50 mb-3" style="font-size: 3rem;"></i>
                                <p class="text-muted m-0">No hay avisos recientes de la escuela.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>
<?php $conexion->close(); ?>
