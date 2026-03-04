// Iniciar la sesión
session_start();

// Validar si la variable de sesión 'id_usuario' o 'id_docente' NO existen
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Incluir la conexión a la base de datos
include '../includes/conexion.php';

// Incluir el diseño principal (menú y apertura del contenedor)
include '../includes/header.php';

// Verificar si los datos llegaron a través del formulario (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recibir las variables del formulario de forma segura
    $id_alumno = intval($_POST['id_alumno']);
    $id_materia = intval($_POST['id_materia']);
    $periodo = intval($_POST['periodo']);
    $calificacion = floatval($_POST['calificacion']);

    // Funciones auxiliares para mostrar mensajes
    function mostrarMensajeExito($titulo, $mensaje) {
        echo "<div class='alert alert-success shadow-sm text-center' role='alert'>";
        echo "<h4 class='alert-heading mb-3'>✅ $titulo</h4>";
        echo "<p>$mensaje</p>";
        echo "<hr>";
        echo "<div class='d-flex justify-content-center gap-3 mt-3'>";
        echo "<a href='calificaciones.php' class='btn btn-success'>Capturar otra calificación</a>";
        echo "<a href='ver_calificaciones.php' class='btn btn-outline-success'>Ver historial general</a>";
        echo "</div>";
        echo "</div>";
    }

    function mostrarMensajeError($mensaje) {
        echo "<div class='alert alert-danger shadow-sm text-center' role='alert'>";
        echo "<h4 class='alert-heading mb-3'>❌ Error</h4>";
        echo "<p>$mensaje</p>";
        echo "<hr>";
        echo "<a href='calificaciones.php' class='btn btn-danger'>Intentar de nuevo</a>";
        echo "</div>";
    }

    // 1. Double Validation server-side (0-10)
    if ($calificacion < 0 || $calificacion > 10) {
        mostrarMensajeError("La calificación debe estar entre 0 y 10.");
        exit();
    }

    if ($periodo < 1 || $periodo > 5) {
        mostrarMensajeError("El periodo debe estar entre 1 y 5.");
        exit();
    }

    // 2. IDOR Protection (Teacher only grades their students)
    if (isset($_SESSION['id_docente'])) {
        $id_docente = $_SESSION['id_docente'];
        $sql_permiso = "SELECT 1 FROM alumnos a 
                        INNER JOIN docente_materia_grupo dmg 
                        ON a.nivel = dmg.nivel AND a.grado = dmg.grado AND a.grupo = dmg.grupo 
                        WHERE a.id_alumno = ? AND dmg.id_materia = ? AND dmg.id_docente = ?";
        
        $stmt_permiso = $conexion->prepare($sql_permiso);
        $stmt_permiso->bind_param("iii", $id_alumno, $id_materia, $id_docente);
        $stmt_permiso->execute();
        $res_permiso = $stmt_permiso->get_result();

        if ($res_permiso->num_rows === 0) {
            mostrarMensajeError("No tienes permisos para calificar a este alumno en esta materia.");
            exit();
        }
        $stmt_permiso->close();
    }

    // 3. Duplicate Grade Check (Update instead of Insert)
    $sql_check = "SELECT id_calificacion FROM calificaciones WHERE id_alumno = ? AND id_materia = ? AND periodo = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("iii", $id_alumno, $id_materia, $periodo);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    
    // Mostrar el resultado dentro del contenedor de Bootstrap
    echo "<div class='mt-4'>";
    
    if ($res_check->num_rows > 0) {
        // UPDATE (Ya existe)
        $fila = $res_check->fetch_assoc();
        $id_calificacion = $fila['id_calificacion'];
        
        $sql_update = "UPDATE calificaciones SET calificacion = ?, fecha_registro = CURRENT_TIMESTAMP WHERE id_calificacion = ?";
        $stmt_update = $conexion->prepare($sql_update);
        
        if ($stmt_update) {
            $stmt_update->bind_param("di", $calificacion, $id_calificacion);
            if ($stmt_update->execute()) {
                mostrarMensajeExito("¡Calificación actualizada con éxito!", "El alumno ya tenía una nota en este periodo y ha sido modificada.");
            } else {
                mostrarMensajeError("Hubo un problema al actualizar la calificación: " . $stmt_update->error);
            }
            $stmt_update->close();
        }
    } else {
        // INSERT (Nueva)
        $sql_insert = "INSERT INTO calificaciones (id_alumno, id_materia, periodo, calificacion) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conexion->prepare($sql_insert);
        
        if ($stmt_insert) {
            $stmt_insert->bind_param("iiid", $id_alumno, $id_materia, $periodo, $calificacion);
            if ($stmt_insert->execute()) {
                mostrarMensajeExito("¡Calificación guardada con éxito!", "El registro se ha añadido correctamente a la base de datos.");
            } else {
                mostrarMensajeError("Hubo un problema al procesar la solicitud: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        } else {
            mostrarMensajeError("Hubo un problema al preparar la consulta: " . $conexion->error);
        }
    }
    
    $stmt_check->close();
    echo "</div>";

    echo "</div>";

    // Cerrar la conexión
    $conexion->close();

}
else {
    // Si intentan entrar al archivo directamente desde la URL
    echo "<div class='alert alert-warning mt-4 text-center' role='alert'>
            Acceso no autorizado. Por favor, utiliza el formulario para enviar datos.
          </div>";
}
?>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>