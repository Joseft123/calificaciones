<?php
// Function to quickly get the active cycle ID globally
function getCicloActivo($conexion)
{
    $sql = "SELECT id_ciclo FROM ciclos_escolares WHERE estatus = 'Activo' LIMIT 1";
    $res = $conexion->query($sql);
    if ($res && $res->num_rows > 0) {
        return $res->fetch_assoc()['id_ciclo'];
    }
    return 1; // Fallback to 1 if something errors
}
?>
