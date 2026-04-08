<?php
session_start();

// Esta herramienta NO DEBE SER ACCESIBLE para los usuarios finales.
// Solo es para el desarrollador. Se asume que el desarrollador la subirá a un servidor seguro o la usará en localhost.

$codigo_ingresado = "";
$clave_generada = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_sistema'])) {
    $codigo_ingresado = trim(strtoupper($_POST['codigo_sistema']));
    $hash = strtoupper(substr(md5($codigo_ingresado . 'SISTEMA_ESCOLAR_SECRETO_999'), 0, 16));
    $clave_generada = implode('-', str_split($hash, 4));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Licencias (Developer Mode)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #020617; /* Very dark slate for dev tool */
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            position: relative;
            overflow: hidden;
        }

        .glass-container {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 0 40px rgba(56, 189, 248, 0.1);
            position: relative;
            z-index: 10;
        }

        /* Matrix like text glow for dev tools */
        h2 {
            color: #38bdf8;
            font-weight: 800;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.6);
            margin-bottom: 25px;
            text-align: center;
        }

        .form-control-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            font-family: 'Courier New', Courier, monospace;
            text-align: center;
            font-size: 1.2rem;
            letter-spacing: 2px;
        }
        
        .form-control-glass:focus {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-color: #38bdf8;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.2);
        }

        .btn-dev {
            background: #0ea5e9;
            color: white;
            font-weight: bold;
            width: 100%;
            margin-top: 20px;
            padding: 10px;
        }
        
        .btn-dev:hover {
            background: #0284c7;
            color: white;
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.5);
        }

        .result-box {
            background: rgba(22, 163, 74, 0.1);
            border: 1px solid #22c55e;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
        }

        .result-key {
            font-size: 1.5rem;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
            color: #4ade80;
            letter-spacing: 2px;
            margin-top: 10px;
        }

        .bg-grid {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            z-index: 1;
        }
    </style>
</head>
<body>

    <div class="bg-grid"></div>

    <div class="glass-container">
        <h2><i class="bi bi-terminal-fill me-2"></i> KEYGEN PRO 2026</h2>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.8rem; color: #94a3b8;"><i class="bi bi-hash"></i> CÓDIGO DEL SISTEMA (CLIENTE)</label>
                <input type="text" name="codigo_sistema" class="form-control form-control-glass" value="<?php echo htmlspecialchars($codigo_ingresado); ?>" placeholder="EJ: A1B2C3D4" required autocomplete="off">
            </div>
            <button type="submit" class="btn btn-dev"><i class="bi bi-magic me-2"></i> GENERAR CLAVE MAESTRA</button>
        </form>

        <?php if (!empty($clave_generada)): ?>
        <div class="result-box">
            <div style="font-size: 0.8rem; color: #86efac; text-transform: uppercase;">Licencia Generada Exitosamente</div>
            <div class="result-key"><?php echo $clave_generada; ?></div>
        </div>
        <?php endif; ?>
    </div>

</body>
</html>
