<?php
session_start();

// Si el usuario ya tiene sesión, lo redirigimos directamente sin mostrar el loading
if (isset($_SESSION['id_usuario'])) {
    header("Location: calificaciones/dashboard.php");
    exit();
}
elseif (isset($_SESSION['id_docente'])) {
    header("Location: docentes/dashboard.php");
    exit();
}
elseif (isset($_SESSION['id_alumno'])) {
    header("Location: calificaciones/mis_calificaciones.php");
    exit();
}

// Si no hay sesión, se mostrará esta animación 3D y luego irá al login
$redirect_url = "auth/login.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargando Sistema...</title>
    <!-- Fallback si JS está desactivado -->
    <meta http-equiv="refresh" content="3;url=<?php echo htmlspecialchars($redirect_url); ?>">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0f172a; /* Fondo dark moderno (Slate 900) */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            overflow: hidden;
            flex-direction: column;
        }

        /* Escena para la Animación 3D del Cubo */
        .scene {
            width: 100px;
            height: 100px;
            perspective: 600px;
            margin-bottom: 50px;
        }

        .cube {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            animation: rotateCube 3s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }

        .cube__face {
            position: absolute;
            width: 100px;
            height: 100px;
            border: 2px solid rgba(56, 189, 248, 0.6);
            background: rgba(14, 165, 233, 0.15);
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.3) inset;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #38bdf8;
            backdrop-filter: blur(4px);
        }

        .cube__face--front  { transform: rotateY(  0deg) translateZ(50px); }
        .cube__face--right  { transform: rotateY( 90deg) translateZ(50px); }
        .cube__face--back   { transform: rotateY(180deg) translateZ(50px); }
        .cube__face--left   { transform: rotateY(-90deg) translateZ(50px); }
        .cube__face--top    { transform: rotateX( 90deg) translateZ(50px); }
        .cube__face--bottom { transform: rotateX(-90deg) translateZ(50px); }

        @keyframes rotateCube {
            0% { transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg); }
            100% { transform: rotateX(360deg) rotateY(360deg) rotateZ(360deg); }
        }

        .loading-text {
            font-size: 1.25rem;
            letter-spacing: 5px;
            text-transform: uppercase;
            font-weight: 600;
            color: #e2e8f0;
            text-shadow: 0 0 10px rgba(56,189,248,0.5);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; text-shadow: 0 0 15px rgba(56,189,248,0.8); }
            50% { opacity: 0.5; text-shadow: 0 0 5px rgba(56,189,248,0.3); }
        }

        /* Resplandor de fondo */
        .glow {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(14,165,233,0.3) 0%, rgba(15,23,42,0) 70%);
            z-index: -1;
            filter: blur(30px);
        }
    </style>
</head>
<body>
    <div class="glow"></div>
    <div class="scene">
        <div class="cube">
            <div class="cube__face cube__face--front">🏫</div>
            <div class="cube__face cube__face--back">📚</div>
            <div class="cube__face cube__face--right">🎓</div>
            <div class="cube__face cube__face--left">✏️</div>
            <div class="cube__face cube__face--top"></div>
            <div class="cube__face cube__face--bottom"></div>
        </div>
    </div>
    <div class="loading-text">Iniciando...</div>

    <script>
        setTimeout(() => {
            window.location.href = "<?php echo $redirect_url; ?>";
        }, 2200); // 2.2 segundos de animación antes de redirigir
    </script>
</body>
</html>
