<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema Escolar</title>
  <link rel="icon"
    href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>">
  <link rel="manifest" href="/calificaciones/manifest.json">
  <meta name="theme-color" content="#0d6efd">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/main.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  
  <!-- DataTables Buttons Extension -->
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

  <script src="../assets/js/main.js"></script>
  <script>
    $(document).ready(function() {
        if ($('.datatable').length > 0) {
            $('.datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                },
                "pageLength": 10,
                "responsive": true,
                "dom": '<"row mb-3 align-items-center"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6 text-end"f>>rt<"row mt-3 align-items-center"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "buttons": [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel-fill me-1"></i> Excel',
                        className: 'btn btn-success btn-sm rounded-pill shadow-sm px-3'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF',
                        className: 'btn btn-danger btn-sm rounded-pill shadow-sm px-3 ms-2',
                        orientation: 'landscape'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer-fill me-1"></i> Imprimir',
                        className: 'btn btn-secondary btn-sm rounded-pill shadow-sm px-3 ms-2'
                    }
                ]
            });
        }
    });
  </script>
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/calificaciones/sw.js')
          .then(registration => {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
          })
          .catch(err => {
            console.log('ServiceWorker registration failed: ', err);
          });
      });
    }
  </script>
</head>

<body>

  <?php
  $unread_mensajes_docente = 0;
  if (isset($_SESSION['id_docente']) && isset($conexion)) {
    $id_doc = intval($_SESSION['id_docente']);
    $stmt_unread = $conexion->prepare("SELECT COUNT(*) as total FROM mensajes WHERE id_destinatario = ? AND tipo_destinatario = 'Docente' AND leido = 0");
    $stmt_unread->bind_param("i", $id_doc);
    $stmt_unread->execute();
    $res_unread = $stmt_unread->get_result();
    if ($res_unread) {
      $unread_mensajes_docente = $res_unread->fetch_assoc()['total'];
    }
  }
  ?>
  <nav class="navbar navbar-expand-lg navbar-dark floating-nav mx-auto">
    <div class="container-fluid px-2">
      <a class="navbar-brand"
        href="<?php echo isset($_SESSION['id_docente']) ? '../docentes/mis_alumnos.php' : '../calificaciones/dashboard.php'; ?>">🎓
        Sistema Escolar</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">

          <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'Director'): ?>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../calificaciones/dashboard.php">
                <span class="bg-primary text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-speedometer2 small"></i>
                </span> Panel</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../calificaciones/calificaciones.php">
                <span class="bg-success text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-pencil-square small"></i>
                </span> Notas</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../calificaciones/ver_calificaciones.php">
                <span class="bg-info text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-clock-history small"></i>
                </span> Historial</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../alumnos/alumnos.php">
                <span class="bg-warning text-dark rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-people-fill small"></i>
                </span> Alumnos</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../materias/materias.php">
                <span class="bg-danger text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-book-half small"></i>
                </span> Materias</a></li>
            <li class="nav-item"><a class="nav-link text-primary fw-bold d-flex align-items-center" href="../ciclos/index.php">
                <span class="bg-primary text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-calendar-range small"></i>
                </span> Ciclos</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../docentes/docentes.php">
                <span class="bg-secondary text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-person-video3 small"></i>
                </span> Docentes</a></li>
            <li class="nav-item"><a class="nav-link text-warning fw-bold d-flex align-items-center" href="../usuarios/usuarios.php">
                <span class="bg-warning text-dark rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-person-badge-fill small"></i>
                </span> Usuarios</a></li>
            <li class="nav-item"><a class="nav-link text-success fw-bold d-flex align-items-center" href="../padres_admin/index.php">
                <span class="bg-success text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-people-fill small"></i>
                </span> Padres</a></li>
            <li class="nav-item"><a class="nav-link text-info fw-bold d-flex align-items-center" href="../comunicados/index.php">
                <span class="bg-info text-dark rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-megaphone-fill small"></i>
                </span> Avisos</a></li>

            <?php if (defined('ESTADO_LICENCIA') && ESTADO_LICENCIA === 'Prueba'): ?>
            <li class="nav-item ms-lg-2 d-flex align-items-center me-2">
                <a href="../auth/activar_licencia.php" class="btn btn-sm d-flex align-items-center" style="background: rgba(239, 68, 68, 0.15); border: 1px dashed rgba(239, 68, 68, 0.6); color: #fecaca; font-size: 0.75rem; border-radius: 20px; white-space: nowrap; font-weight: bold; box-shadow: 0 0 10px rgba(239,68,68,0.2);">
                    <i class="bi bi-shield-exclamation me-1 text-danger fs-6"></i> 
                    Prueba: <?php echo DIAS_TRIAL_RESTANTES; ?> días
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item ms-lg-3 d-flex align-items-center">
              <span class="text-light me-3 d-flex align-items-center gap-2">
                <?php if (!empty($_SESSION['foto_perfil'])): ?>
                    <img src="../assets/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" 
                         alt="Perfil" class="rounded-circle border border-2 border-primary shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                <?php else: ?>
                    👤 
                <?php endif; ?>
                <?php echo htmlspecialchars($_SESSION['nombre']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)
              </span>
              <a class="btn btn-outline-light btn-sm me-2" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i
                  class="bi bi-person-gear"></i></a>
              <button class="btn btn-outline-light btn-sm me-2" id="btnThemeToggle" title="Cambiar Tema">
                <span id="themeIcon">🌙</span>
              </button>
              <a class="btn btn-danger btn-sm" href="../auth/cerrar_sesion.php">Salir</a>
            </li>

            <?php
          elseif (isset($_SESSION['id_docente'])): ?>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../docentes/dashboard.php">
                <span class="bg-primary text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-speedometer2 small"></i>
                </span> Inicio</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../calificaciones/calificaciones.php">
                <span class="bg-success text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-pencil-square small"></i>
                </span> Capturar Notas</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../calificaciones/ver_calificaciones.php">
                <span class="bg-info text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-clock-history small"></i>
                </span> Historial</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../docentes/mis_alumnos.php">
                <span class="bg-warning text-dark rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-person-lines-fill small"></i>
                </span> Mis Alumnos</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../docentes/mi_calendario.php">
                <span class="bg-danger text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-calendar-week small"></i>
                </span> Calendario</a></li>
            <li class="nav-item"><a class="nav-link text-primary fw-bold d-flex align-items-center" href="../docentes/mensajes.php">
                <span class="bg-primary text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-envelope-fill small"></i>
                </span> Mensajes
                <?php if (isset($unread_mensajes_docente) && $unread_mensajes_docente > 0): ?>
                  <span class="badge bg-danger rounded-pill ms-1 shadow-sm"><?php echo $unread_mensajes_docente; ?></span>
                <?php endif; ?>
              </a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../docentes/pasar_lista.php">
                <span class="bg-secondary text-white rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-clipboard-check small"></i>
                </span> Pasar Lista</a></li>
            <li class="nav-item"><a class="nav-link d-flex align-items-center" href="../docentes/reporte_asistencia.php">
                <span class="bg-info text-dark rounded-circle p-1 d-inline-flex justify-content-center align-items-center me-2" style="width: 28px; height: 28px;">
                    <i class="bi bi-file-earmark-bar-graph small"></i>
                </span> Reporte Asist.</a></li>

            <li class="nav-item ms-lg-3 d-flex align-items-center">
              <span class="text-light me-3 d-flex align-items-center gap-2">
                <?php if (!empty($_SESSION['foto_perfil'])): ?>
                    <img src="../assets/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" 
                         alt="Perfil" class="rounded-circle border border-2 border-info shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                <?php else: ?>
                    👨‍🏫 
                <?php endif; ?>
                <?php echo htmlspecialchars($_SESSION['nombre_docente']); ?> (Docente)
              </span>
              <a class="btn btn-outline-light btn-sm me-2" href="../auth/mi_perfil.php" title="Configurar Mi Perfil"><i
                  class="bi bi-person-gear"></i></a>
              <button class="btn btn-outline-light btn-sm me-2" id="btnThemeToggle" title="Cambiar Tema">
                <span id="themeIcon">🌙</span>
              </button>
              <a class="btn btn-danger btn-sm" href="../auth/cerrar_sesion.php">Salir</a>
            </li>

            <?php
          else: ?>
            <li class="nav-item ms-lg-3 d-flex align-items-center">
              <button class="btn btn-outline-light btn-sm" id="btnThemeToggle" title="Cambiar Tema">
                <span id="themeIcon">🌙</span>
              </button>
            </li>
            <?php
          endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container contenedor-principal">

    <!-- PREMIUM UI/UX: Global Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
      <div id="globalToast" class="toast align-items-center border-0" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body fw-bold" id="toastMessage"></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
      </div>
    </div>