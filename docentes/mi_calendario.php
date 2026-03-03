<?php
session_start();

// Validar que el usuario sea un docente
if (!isset($_SESSION['id_docente'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/conexion.php';
$id_docente = $_SESSION['id_docente'];

// Consulta SQL para obtener las materias asignadas
$sql = "SELECT m.nombre_materia, dmg.nivel, dmg.grado, dmg.grupo 
        FROM materias m
        INNER JOIN docente_materia_grupo dmg ON m.id_materia = dmg.id_materia
        WHERE dmg.id_docente = $id_docente
        ORDER BY dmg.nivel ASC, dmg.grado ASC, m.nombre_materia ASC";

$resultado = $conexion->query($sql);
$asignaciones = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $asignaciones[] = $fila;
    }
}

include '../includes/header.php';
?>

<style>
    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Ajustes FullCalendar Premium */
    #calendar-container {
        min-height: 600px;
        font-family: 'Segoe UI', system-ui, sans-serif;
    }
    .fc-theme-standard .fc-scrollgrid { border-color: rgba(0,0,0,0.1); }
    .fc .fc-toolbar-title { font-weight: 700; color: #0dcaf0; }
    .fc .fc-button-primary {
        background-color: var(--bs-info);
        border-color: var(--bs-info);
        transition: all 0.3s ease;
        border-radius: 8px;
    }
    .fc .fc-button-primary:hover {
        background-color: #0baccc;
        border-color: #0baccc;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(13, 202, 240, 0.3);
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active, 
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #087f9c;
        border-color: #087f9c;
    }
    .fc-event {
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
        padding: 3px 5px;
    }
    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
        z-index: 5 !important;
    }
    .fc-v-event .fc-event-main { color: #fff; font-weight: 500; }

    /* Compatibilidad Dark Theme */
    [data-bs-theme="dark"] .fc-theme-standard .fc-scrollgrid,
    [data-bs-theme="dark"] .fc-theme-standard td,
    [data-bs-theme="dark"] .fc-theme-standard th {
        border-color: rgba(255,255,255,0.1) !important;
    }
    [data-bs-theme="dark"] .fc-col-header-cell-cushion,
    [data-bs-theme="dark"] .fc-timegrid-axis-cushion,
    [data-bs-theme="dark"] .fc-timegrid-slot-label-cushion {
        color: #adb5bd !important;
    }
    [data-bs-theme="dark"] .fc .fc-list-empty {
        background-color: #1e1e1e;
        color: #adb5bd;
    }
    [data-bs-theme="dark"] .card { background-color: #2b2b2b; }
    [data-bs-theme="dark"] .bg-light { background-color: #1e1e1e !important; }
</style>

<!-- Librerías FullCalendar y Tippy.js (Tooltips) -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/animations/scale.css" />

<div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <h2 class="text-info m-0 fw-bold">📅 Mi Calendario Académico</h2>
</div>

<div class="card shadow-lg border-0 rounded-4 overflow-hidden animate-fade-in mb-5" style="animation-delay: 0.2s;">
    <div class="card-header bg-info text-white px-4 py-3" style="background: linear-gradient(135deg, #0dcaf0 0%, #055160 100%);">
        <h5 class="m-0 fw-bold"><i class="bi bi-calendar-week me-2"></i>Horario Semanal</h5>
    </div>
    <div class="card-body p-4 p-md-5 bg-light">
        
        <?php if (!empty($asignaciones)): ?>
            <div id='calendar-container'></div>
        <?php
else: ?>
            <div class="empty-state text-center p-5 mx-auto animate-fade-in" style="background: linear-gradient(to right, #f8f9fa, #e9ecef); border: 2px dashed #ced4da; border-radius: 12px; max-width: 600px;">
                <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;">🗓️</div>
                <h4 class="text-secondary fw-bold mb-3">Sin asignaturas agendadas</h4>
                <p class="text-muted mb-0">Actualmente no tienes materias asignadas para impartir en este ciclo escolar.</p>
            </div>
        <?php
endif; ?>

    </div>
</div>

<?php

// ---------------------------------------------------------
// GENERADOR DINÁMICO DE EVENTOS MOCK (PARA FULLCALENDAR)
// Como la DB no tiene campos de horario (Lun 8:00 - 10:00), 
// generamos eventos recurrentes (semanales) en base a las materias.
// ---------------------------------------------------------

$eventos_calendario = [];
$colores_evento = ['#0d6efd', '#198754', '#dc3545', '#fd7e14', '#6f42c1', '#20c997', '#e83e8c'];
$dias_semana = ['1', '2', '3', '4', '5']; // 1=Lun, 2=Mar, 3=Mie, 4=Jue, 5=Vie

// Hora de inicio ficticia (Empiezan a las 7:00 AM)
$hora_actual = 7;
$minuto_actual = 0;
// Duración por clase: 1 hora y 45 mins. (105 mins) para saltar rápido
$duracion_min = 105;


foreach ($asignaciones as $index => $asig) {
    // Seleccionar color random fijo por materia
    $color = $colores_evento[$index % count($colores_evento)];

    // Distribuir cada materia en 2 o 3 días aleatorios fijos por su index
    $frecuencia = ($index % 2 == 0) ? [1, 3, 5] : [2, 4]; // Lun-Mie-Vie o Mar-Jue

    $hora_str = str_pad($hora_actual, 2, '0', STR_PAD_LEFT);
    $min_str = str_pad($minuto_actual, 2, '0', STR_PAD_LEFT);
    $start_time = "{$hora_str}:{$min_str}:00";

    // Calcular hora de fin
    $minutos_totales = ($hora_actual * 60) + $minuto_actual + $duracion_min;
    $hora_fin = floor($minutos_totales / 60);
    $min_fin = $minutos_totales % 60;

    $end_time = str_pad($hora_fin, 2, '0', STR_PAD_LEFT) . ":" . str_pad($min_fin, 2, '0', STR_PAD_LEFT) . ":00";

    $eventos_calendario[] = [
        'title' => htmlspecialchars($asig['nombre_materia']),
        'startTime' => $start_time,
        'endTime' => $end_time,
        'daysOfWeek' => $frecuencia, // Se repite en estos días de la semana
        'backgroundColor' => $color,
        'borderColor' => $color,
        'extendedProps' => [
            'nivel' => htmlspecialchars($asig['nivel']),
            'grado_grupo' => htmlspecialchars($asig['grado']) . 'º ' . htmlspecialchars($asig['grupo'])
        ]
    ];

    // Mover bloque de hora para la siguiente materia
    $minuto_actual += $duracion_min + 15; // +15 min de receso
    if ($minuto_actual >= 60) {
        $hora_actual += floor($minuto_actual / 60);
        $minuto_actual = $minuto_actual % 60;
    }
    // Si pasa de las 14hrs, reiniciar al día (simulando que esto afecta ambos LMV y MJ)
    if ($hora_actual >= 14) {
        $hora_actual = 7;
        $minuto_actual = 0;
    }
}

$conexion->close();
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hasEvents = <?php echo !empty($eventos_calendario) ? 'true' : 'false'; ?>;
        
        if (hasEvents) {
            var calendarEl = document.getElementById('calendar-container');
            var rawEvents = <?php echo json_encode($eventos_calendario); ?>;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'es', // Español
                firstDay: 1,  // Lunes
                hiddenDays: [ 0, 6 ], // Ocultar Sab (6) y Dom (0)
                slotMinTime: '07:00:00', // Empieza 7am
                slotMaxTime: '15:00:00', // Termina 3pm
                allDaySlot: false, // Sin sección "todo el día"
                expandRows: true,  // Estirar para llenar contenedor
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay,listWeek'
                },
                events: rawEvents,
                
                // Función renderizada cuando el evento es inyectado
                eventDidMount: function(info) {
                    // Configurar Tooltip (Tippy.js) con efecto "Glassmorphism"
                    tippy(info.el, {
                        content: `
                            <div class="text-start" style="font-family:'Segoe UI',sans-serif;">
                                <strong class="fs-6 d-block border-bottom pb-1 mb-1">${info.event.title}</strong>
                                <span class="d-block"><i class="bi bi-diagram-3 me-1"></i> ${info.event.extendedProps.nivel}</span>
                                <span class="d-block"><i class="bi bi-people me-1"></i> Grado: ${info.event.extendedProps.grado_grupo}</span>
                                <span class="d-block mt-1 text-warning"><i class="bi bi-clock me-1"></i> ${info.timeText}</span>
                            </div>
                        `,
                        allowHTML: true,
                        animation: 'scale',
                        theme: 'light-border',
                        placement: 'top'
                    });
                }
            });

            calendar.render();

            // Refrescar tamaño al cambiar tema (FullCalendar a veces recorta grids por bordes)
            const observer = new MutationObserver(function() {
                setTimeout(()=> calendar.updateSize(), 50);
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
        }
    });
</script>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
