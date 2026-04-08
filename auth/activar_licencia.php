<?php
session_start();
include '../includes/conexion.php';

// Si ya está activado, redirigir al index o dashboard
if (ESTADO_LICENCIA === 'Activado') {
    header("Location: ../index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clave_activacion'])) {
    $clave_ingresada = trim(strtoupper($_POST['clave_activacion']));
    
    // Lógica de validación
    $hash = strtoupper(substr(md5(CODIGO_SISTEMA . 'SISTEMA_ESCOLAR_SECRETO_999'), 0, 16));
    $clave_esperada = implode('-', str_split($hash, 4));
    
    // Permitir ingreso de clave con o sin guiones
    $clave_ingresada_limpia = str_replace('-', '', $clave_ingresada);
    $clave_esperada_limpia = str_replace('-', '', $clave_esperada);
    
    if ($clave_ingresada_limpia === $clave_esperada_limpia) {
        $stmt = $conexion->prepare("UPDATE configuracion_sistema SET estado_licencia = 'Activado', clave_activacion = ?");
        $stmt->bind_param("s", $clave_esperada);
        if ($stmt->execute()) {
            $success = "¡El sistema ha sido activado con éxito! Redirigiendo...";
            echo "<script>setTimeout(function(){ window.location.href = '../index.php'; }, 3000);</script>";
        } else {
            $error = "Ocurrió un error al activar en la base de datos.";
        }
    } else {
        $error = "La clave ingresada es incorrecta. Por favor, contacta al soporte técnico.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #0f172a;
            color: white;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Gradients to look premium */
        .glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.4) 0%, rgba(15, 23, 42, 0) 70%);
            z-index: 0;
            filter: blur(50px);
            animation: pulse 4s infinite alternate;
        }
        
        .glow-2 {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.3) 0%, rgba(15, 23, 42, 0) 70%);
            z-index: 0;
            filter: blur(50px);
            bottom: -50px;
            right: -50px;
            animation: pulse-2 5s infinite alternate;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.3); opacity: 0.4; }
        }
        @keyframes pulse-2 {
            0% { transform: scale(1) translate(0, 0); opacity: 0.5; }
            100% { transform: scale(1.5) translate(-20px, -20px); opacity: 0.8; }
        }

        /* Glassmorphism Container */
        .glass-container {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            z-index: 10;
            text-align: center;
        }

        .icon-lock {
            font-size: 3rem;
            color: #38bdf8;
            margin-bottom: 10px;
            text-shadow: 0 0 15px rgba(56, 189, 248, 0.5);
        }

        .title {
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 5px;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .subtitle {
            font-size: 0.9rem;
            color: #94a3b8;
            margin-bottom: 30px;
        }

        .code-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px dashed rgba(56, 189, 248, 0.5);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .code-label {
            font-size: 0.75rem;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            display: block;
        }

        .code-value {
            font-size: 1.4rem;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
            color: #ec4899;
            letter-spacing: 2px;
        }

        .form-control-glass {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.1rem;
            font-family: 'Courier New', Courier, monospace;
            transition: all 0.3s ease;
        }

        .form-control-glass:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #38bdf8;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
            color: white;
            outline: none;
        }

        .form-control-glass::placeholder {
            color: #475569;
            font-family: 'Inter', sans-serif;
            letter-spacing: normal;
        }

        .btn-activate {
            background: linear-gradient(90deg, #0284c7, #38bdf8);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.4);
        }

        .btn-activate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.6);
            color: white;
        }
        
        .alert-glass {
            background: rgba(220, 38, 38, 0.2);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: #fca5a5;
            backdrop-filter: blur(10px);
            border-radius: 10px;
        }

        .alert-success-glass {
            background: rgba(22, 163, 74, 0.2);
            border: 1px solid rgba(22, 163, 74, 0.3);
            color: #86efac;
            backdrop-filter: blur(10px);
            border-radius: 10px;
        }

    </style>
</head>
<body>

    <div class="glow"></div>
    <div class="glow-2"></div>

    <div class="glass-container">
        <div class="icon-lock">
            <?php echo SISTEMA_BLOQUEADO ? '<i class="bi bi-lock-fill"></i>' : '<i class="bi bi-shield-lock-fill"></i>'; ?>
        </div>
        
        <?php if(SISTEMA_BLOQUEADO): ?>
            <h2 class="title" style="color: #ef4444; background: none; -webkit-text-fill-color: currentcolor;">Prueba Expirada</h2>
            <p class="subtitle text-danger">Tu periodo de prueba ha finalizado. Adquiere una licencia para desbloquear el sistema.</p>
        <?php else: ?>
            <h2 class="title">Activar Licencia</h2>
            <p class="subtitle">Ingresa tu clave de producto para obtener acceso de por vida.</p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-glass p-2 mb-4 text-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success-glass p-3 mb-4 text-center">
                <i class="bi bi-check-circle-fill me-2 fs-4 d-block mb-2"></i>
                <div><?php echo $success; ?></div>
            </div>
        <?php else: ?>
            <div class="code-box">
                <span class="code-label">Proporciona este código al soporte técnico:</span>
                <span class="code-value"><?php echo CODIGO_SISTEMA; ?></span>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="form-group text-start">
                    <label class="form-label text-light" style="font-size: 0.85rem;"><i class="bi bi-key-fill me-1"></i> Clave de Activación</label>
                    <input type="text" name="clave_activacion" class="form-control form-control-glass" placeholder="XXXX-XXXX-XXXX-XXXX" required autocomplete="off">
                </div>
                <button type="submit" class="btn btn-activate"><i class="bi bi-unlock-fill me-2"></i> Activar Sistema</button>
            </form>
        <?php endif; ?>
        
        <div class="mt-4" style="font-size: 0.75rem; color: #64748b;">
            Sistema Escolar Pro &copy; <?php echo date('Y'); ?>
        </div>
    </div>

</body>
</html>
