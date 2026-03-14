<?php
/**
 * footer.php - Cierre universal de etiquetas y carga de scripts
 */
?>
    </div> <!-- / .contenedor-principal -->

    <!-- Bootstrap 5 JS Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Si se requiere inicializar algo globalmente después de cargar Bootstrap -->
    <script>
        // Inicializar tooltips y popovers si existen
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>
</html>
