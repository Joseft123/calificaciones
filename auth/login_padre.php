<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Padres - Sistema Escolar</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="manifest" href="/calificaciones/manifest.json">
    <meta name="theme-color" content="#198754">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css"> <!-- Para soporte de Dark Mode y estilos globales -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/calificaciones/sw.js');
            });
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        /* Si el modo oscuro global está activo, ajustamos el fondo */
        [data-bs-theme="dark"] body {
            background: #0f172a;
        }

        .login-card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        [data-bs-theme="dark"] .login-card {
            background: rgba(30, 41, 59, 0.95);
        }

        .btn-login {
            background: #198754;
            border: none;
            border-radius: 0.5rem;
            padding: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #146c43;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
            color: white;
        }

        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        .back-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: white;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">

                <div class="text-center mb-4">
                    <a href="../index.php" class="back-link"><i class="bi bi-arrow-left me-1"></i>Regresar al inicio</a>
                </div>

                <div class="card login-card animate-fade-in" style="animation-duration: 0.5s;">
                    <div class="card-body p-5">

                        <div class="text-center mb-4">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 70px; height: 70px; font-size: 2rem;">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <h3 class="fw-bold mb-1">Portal de Padres</h3>
                            <p class="text-muted small">Familias y Tutores</p>
                        </div>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                                <i class="bi bi-x-circle-fill me-1"></i>
                                <?php
                                if ($_GET['error'] == 'credenciales')
                                    echo "Correo o contraseña incorrectos.";
                                elseif ($_GET['error'] == 'vacio')
                                    echo "Por favor, complete todos los campos.";
                                else
                                    echo "Error de autenticación.";
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'logout'): ?>
                            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                <i class="bi bi-check-circle-fill me-1"></i> Sesión terminada exitosamente.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="autenticar_padre.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="correo" class="form-label fw-medium text-body-secondary small">Correo
                                    Electrónico</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-end-0 text-muted"><i
                                            class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="correo"
                                        name="correo" required placeholder="correo@ejemplo.com">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password"
                                    class="form-label fw-medium text-body-secondary small">Contraseña</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-end-0 text-muted"><i
                                            class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0 border-end-0"
                                        id="password" name="password" required placeholder="••••••••">
                                    <button class="btn border border-start-0 rounded-end text-muted shadow-none"
                                        type="button" onclick="togglePassword()"><i class="bi bi-eye-fill"></i></button>
                                </div>
                            </div>

                            <div class="d-grid mb-3 mt-4">
                                <button type="submit" class="btn btn-login text-white shadow-sm">Ingresar al
                                    Portal</button>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-sm btn-outline-light rounded-pill px-3" id="btnThemeToggle">
                        <span id="themeIcon">🌙</span> <span class="ms-1">Modo Visual</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        // Mostrar/ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = event.currentTarget.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            }
        }

        // Bootstrap validation script
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>

</body>

</html>