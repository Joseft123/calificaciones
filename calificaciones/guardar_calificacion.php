<?php
// Incluir la conexión a la base de datos
include '../includes/conexion.php';

// Incluir el diseño principal (menú y apertura del contenedor)
include '../includes/header.php';

// Verificar si los datos llegaron a través del formulario (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recibir las variables del formulario
    $id_alumno = $_POST['id_alumno'];
    $id_materia = $_POST['id_materia'];
    $periodo = $_POST['periodo'];
    $calificacion = $_POST['calificacion'];

    // Armar la consulta SQL para insertar el registro
    $sql = "INSERT INTO calificaciones (id_alumno, id_materia, periodo, calificacion) 
            VALUES ('$id_alumno', '$id_materia', '$periodo', '$calificacion')";

    // Mostrar el resultado dentro del contenedor de Bootstrap
    echo "<div class='mt-4'>";

    if ($conexion->query($sql) === TRUE) {
        // Mensaje de Éxito
        echo "<div class='alert alert-success shadow-sm text-center' role='alert'>";
        echo "<h4 class='alert-heading mb-3'>✅ ¡Calificación guardada con éxito!</h4>";
        echo "<p>El registro se ha añadido correctamente a la base de datos.</p>";
        echo "<hr>";
        echo "<div class='d-flex justify-content-center gap-3 mt-3'>";
        echo "<a href='calificaciones.php' class='btn btn-success'>Capturar otra calificación</a>";
        echo "<a href='ver_calificaciones.php' class='btn btn-outline-success'>Ver historial general</a>";
        echo "</div>";
        echo "</div>";
    }
    else {
        // Mensaje de Error
        echo "<div class='alert alert-danger shadow-sm text-center' role='alert'>";
        echo "<h4 class='alert-heading mb-3'>❌ Error al guardar</h4>";
        echo "<p>Hubo un problema al procesar la solicitud: " . $conexion->error . "</p>";
        echo "<hr>";
        echo "<a href='calificaciones.php' class='btn btn-danger'>Intentar de nuevo</a>";
        echo "</div>";
    }

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