<?php
$servidor = "localhost";
$usuario = "root"; // Usuario por defecto en WampServer
$password = ""; // Contraseña por defecto (suele estar en blanco)
$base_datos = "sistema_escolar";

// Crear la conexión
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer charset
$conexion->set_charset("utf8mb4");

// Incluir protección CSRF
include_once __DIR__ . '/csrf.php';

// ==========================================
// SISTEMA DE LICENCIA Y VERIFICACIÓN DE PRUEBA
// ==========================================
function generarCodigoSistema() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

$query_config = "SELECT * FROM configuracion_sistema LIMIT 1";
$res_config = $conexion->query($query_config);

$sistema_bloqueado = false;
$dias_prueba = 30;
$dias_restantes = 0;
$codigo_sistema = "";
$estado_licencia = "Prueba";

if ($res_config->num_rows == 0) {
    // Fase inicial tras el parche: primera vez que se carga
    $codigo_sistema = generarCodigoSistema();
    $stmt_insert = $conexion->prepare("INSERT INTO configuracion_sistema (codigo_sistema, fecha_instalacion, estado_licencia) VALUES (?, NOW(), 'Prueba')");
    $stmt_insert->bind_param("s", $codigo_sistema);
    $stmt_insert->execute();
    $dias_restantes = $dias_prueba;
} else {
    $row_config = $res_config->fetch_assoc();
    $codigo_sistema = $row_config['codigo_sistema'];
    $estado_licencia = $row_config['estado_licencia'];
    
    if ($estado_licencia === 'Prueba') {
        $fecha_inst = new DateTime($row_config['fecha_instalacion']);
        $ahora = new DateTime();
        $diferencia = $fecha_inst->diff($ahora)->days; // Días transcurridos
        
        $dias_restantes = $dias_prueba - $diferencia;
        
        if ($dias_restantes <= 0) {
            $dias_restantes = 0;
            $sistema_bloqueado = true;
        }
    }
}

define('SISTEMA_BLOQUEADO', $sistema_bloqueado);
define('DIAS_TRIAL_RESTANTES', $dias_restantes);
define('ESTADO_LICENCIA', $estado_licencia);
define('CODIGO_SISTEMA', $codigo_sistema);

// Forzar redirección si el sistema caducó 
$current_file = basename($_SERVER['PHP_SELF']);
$allowed_files = ['activar_licencia.php', 'login.php', 'cerrar_sesion.php'];

// Evitar bloqueos en directorios esenciales como assets
if (SISTEMA_BLOQUEADO && !in_array($current_file, $allowed_files) && strpos($_SERVER['PHP_SELF'], 'assets/') === false) {
    // Check si la solicitud es AJAX para devolver error o redirect general
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(["status" => "error", "message" => "LICENCIA_EXPIRADA"]);
        exit();
    }
    header("Location: ../auth/activar_licencia.php");
    exit();
}
// ==========================================
?>