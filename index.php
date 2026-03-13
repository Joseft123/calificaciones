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
            position: relative; /* Para que las coordenadas absolutas se basen en el body */
        }

        /* Canvas de partículas de fondo */
        #particles-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.7;
            pointer-events: none;
        }

        /* Escena para la Animación 3D del Cubo */
        .scene {
            width: 100px;
            height: 100px;
            perspective: 600px;
            margin-bottom: 40px;
            z-index: 10;
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

        /* Contenedor de Textos y Barra */
        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 10;
            width: 250px;
            text-align: center;
        }

        .loading-text {
            font-size: 0.95rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
            color: #e2e8f0;
            text-shadow: 0 0 10px rgba(56,189,248,0.5);
            margin-bottom: 12px;
            min-height: 20px;
            transition: opacity 0.2s ease-in-out;
        }

        /* Barra de Progreso */
        .progress-wrapper {
            width: 100%;
            height: 6px;
            background: rgba(15, 23, 42, 0.8);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.5) inset, 0 0 5px rgba(56,189,248,0.1);
            border: 1px solid rgba(56, 189, 248, 0.2);
            position: relative;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #0284c7, #38bdf8);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(56,189,248,0.8);
            /* Transición suave para que no brinque bruscamente */
            transition: width 0.1s linear;
        }

        .progress-text {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #94a3b8;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }

        /* Resplandor de fondo */
        .glow {
            position: absolute;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(14,165,233,0.3) 0%, rgba(15,23,42,0) 70%);
            z-index: 1;
            filter: blur(40px);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .fade-out {
            opacity: 0 !important;
            transition: opacity 0.5s ease-out;
        }
    </style>
</head>
<body>
    <!-- Canvas de partículas al fondo -->
    <canvas id="particles-canvas"></canvas>
    
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

    <!-- Contenedor cargando textos y progreso -->
    <div class="loading-container">
        <div class="loading-text" id="dynamic-text">Iniciando...</div>
        <div class="progress-wrapper">
            <div class="progress-bar" id="progress-bar"></div>
        </div>
        <div class="progress-text" id="progress-text">0%</div>
    </div>

    <script>
        // --- 1. Textos de Carga Dinámicos ---
        const phrases = [
            "Conectando a base de datos...",
            "Cargando perfiles...",
            "Preparando entorno escolar...",
            "Un momento por favor...",
            "Iniciando..."
        ];
        
        const dynamicText = document.getElementById('dynamic-text');
        let phraseIndex = 0;
        
        // Cambiar el texto cada 400ms aproximadamente
        const textInterval = setInterval(() => {
            dynamicText.style.opacity = 0;
            setTimeout(() => {
                phraseIndex = (phraseIndex + 1) % phrases.length;
                dynamicText.innerText = phrases[phraseIndex];
                dynamicText.style.opacity = 1;
            }, 100);
        }, 400);

        // --- 2. Barra de Progreso y Porcentaje ---
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        let progress = 0;
        
        // La animación entera dura unos 2200ms
        const totalDuration = 2000; 
        const tickRate = 20; // ms
        const increment = 100 / (totalDuration / tickRate);

        const progressInterval = setInterval(() => {
            progress += increment;
            if (progress >= 100) {
                progress = 100;
                clearInterval(progressInterval);
                clearInterval(textInterval);
                dynamicText.innerText = "¡Listo!";
                dynamicText.style.opacity = 1;
            }
            let currentInt = Math.floor(progress);
            progressBar.style.width = currentInt + "%";
            progressText.innerText = currentInt + "%";
        }, tickRate);


        // --- 3. Efecto de Partículas de Fondo ---
        const canvas = document.getElementById('particles-canvas');
        const ctx = canvas.getContext('2d');
        
        // Igualar tamaño al de la ventana
        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas(); // Llenar al principio

        const particlesArray = [];
        const numberOfParticles = 50; // Cantidad de "estrellas" o puntos

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2; // tamaño de 0 a 2 px
                // Velocidades muy sutiles
                this.speedX = (Math.random() * 0.6) - 0.3;
                this.speedY = (Math.random() * 0.6) - 0.3;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                
                // Rebotar en los bordes
                if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
            }
            draw() {
                ctx.fillStyle = 'rgba(56, 189, 248, 0.4)'; // Color celeste semitransparente
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.closePath();
                ctx.fill();
            }
        }

        // Crear partículas
        for (let i = 0; i < numberOfParticles; i++) {
            particlesArray.push(new Particle());
        }

        // Bucle de animación
        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height); // limpiar
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
                particlesArray[i].draw();
            }
            requestAnimationFrame(animateParticles);
        }
        animateParticles();


        // --- Fades y Redirección Final ---
        setTimeout(() => {
            // Empezar a desvanecer todo
            document.querySelector('.scene').classList.add('fade-out');
            document.querySelector('.loading-container').classList.add('fade-out');
            document.querySelector('.glow').classList.add('fade-out');
            canvas.classList.add('fade-out');
        }, 1800); 

        // Redirigir según el temporizador configurado
        setTimeout(() => {
            window.location.href = "<?php echo $redirect_url; ?>";
        }, 2200);

    </script>
</body>
</html>
