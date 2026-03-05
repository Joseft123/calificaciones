<?php
session_start();
// Validar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';

// Obtener la cantidad de alumnos por grupo
$sql = "SELECT nivel, grado, grupo, COUNT(id_alumno) as inscritos 
        FROM alumnos 
        GROUP BY nivel, grado, grupo 
        ORDER BY nivel ASC, grado ASC, grupo ASC";
$resultado = $conexion->query($sql);

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/components.css">

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-primary m-0 fw-bold">👨‍🎓 Gestión de Alumnos por Grupos</h2>
    <div class="d-flex align-items-center flex-wrap gap-2">
        <a href="importar_alumnos.php" class="btn btn-outline-primary shadow-sm px-4 rounded-pill">📁 Importar CSV</a>
        <a href="crear_alumno.php" class="btn btn-success shadow-sm px-4 rounded-pill">➕ Inscribir Alumno</a>
    </div>
</div>

<?php
if ($resultado->num_rows > 0) {
    // 1. Agrupar la información devuelta por 'Nivel'
    $niveles_agrupados = [];
    while ($fila = $resultado->fetch_assoc()) {
        $nivel = $fila['nivel'];
        $niveles_agrupados[$nivel][] = $fila;
    }

    $delay = 0.2;
    // 2. Renderizar visualmente por nivel
    foreach ($niveles_agrupados as $nivel => $grupos) {
        $nivelIdSafe = preg_replace('/[^A-Za-z0-9\-]/', '', $nivel);
        echo "<div class='mb-5 animate-fade-in' style='animation-delay: {$delay}s;'>";
        echo "<h3 class='text-secondary border-bottom pb-2 mb-4 fw-bold'>
                <span class='text-primary'>Nivel:</span> " . htmlspecialchars($nivel) . "
              </h3>";

        $delay += 0.15;
        echo "<div class='row g-4'>"; // Bootstrap Grid para las tarjetas de cada grupo

        $cardDelay = $delay;
        foreach ($grupos as $grupo_data) {
            $grado = htmlspecialchars($grupo_data['grado']);
            $grupo_letra = htmlspecialchars($grupo_data['grupo']);
            $inscritos = htmlspecialchars($grupo_data['inscritos']);

            // Link seguro a la nueva vista de detalle del grupo
            $url_link = "alumnos_grupo.php?nivel=" . urlencode($nivel) . "&grado=" . urlencode($grado) . "&grupo=" . urlencode($grupo_letra);

            echo "<div class='col-md-6 col-lg-4 col-xl-3 animate-fade-in' style='animation-delay: {$cardDelay}s;'>";
            echo "  <a href='{$url_link}' class='text-decoration-none group-link-card'>";
            echo "    <div class='card h-100 shadow border-0 rounded-4 student-group-card transition-all overflow-hidden'>";
            echo "      <div class='card-header text-white text-center py-3' style='background: linear-gradient(135deg, #6610f2 0%, #520dc2 100%);'>";
            echo "          <h4 class='m-0 fw-bold'><i class='bi bi-people-fill me-2'></i>{$grado}º {$grupo_letra}</h4>";
            echo "      </div>";
            echo "      <div class='card-body p-4 text-center d-flex flex-column align-items-center justify-content-center' style='min-height: 120px;'>";
            echo "          <div class='display-1 mb-2 text-primary opacity-25'><i class='bi bi-person-badge'></i></div>";
            echo "          <h5 class='fw-bold text-dark mt-2 mb-0'>{$inscritos} Estudiantes</h5>";
            echo "          <span class='text-muted small mt-1'>Click para ver lista completa</span>";
            echo "      </div>";
            echo "    </div>";
            echo "  </a>";
            echo "</div>";
            $cardDelay += 0.05;
        }
        echo "</div></div>"; // Fin row de tarjetas y div nivel
    }
}
else {
    echo "<div class='empty-state text-center p-5 mx-auto animate-fade-in' style='animation-delay: 0.2s; max-width: 600px;'>
            <div style='font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;'>📭</div>
            <h4 class='text-secondary fw-bold mb-3'>No hay alumnos inscritos aún</h4>
            <p class='text-muted mb-4'>Comienza importando tu lista de estudiantes o agregándolos individualmente para conformar los grupos de la escuela.</p>
            <div class='d-flex justify-content-center gap-3'>
                <a href='importar_alumnos.php' class='btn btn-outline-primary rounded-pill px-4 shadow-sm'>📂 Importar CSV</a>
                <a href='crear_alumno.php' class='btn btn-primary rounded-pill px-4 shadow-sm'>➕ Inscribir Alumno</a>
            </div>
          </div>";
}
?>

<?php $conexion->close(); ?>

<style>
.group-link-card { display: block; }
.group-link-card .student-group-card { transition: all 0.3s ease; }
.group-link-card:hover .student-group-card { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; border-color: #6610f2!important; }
</style>

</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>