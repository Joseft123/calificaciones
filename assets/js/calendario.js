document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.calendarEvents === 'undefined' || window.calendarEvents.length === 0) {
        return;
    }

    var calendarEl = document.getElementById('calendar-container');
    if (!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'es', // Español
        firstDay: 1,  // Lunes
        hiddenDays: [0, 6], // Ocultar Sab (6) y Dom (0)
        slotMinTime: '07:00:00', // Empieza 7am
        slotMaxTime: '15:00:00', // Termina 3pm
        allDaySlot: false, // Sin sección "todo el día"
        expandRows: true,  // Estirar para llenar contenedor
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay,listWeek'
        },
        events: window.calendarEvents,

        // Función renderizada cuando el evento es inyectado
        eventDidMount: function (info) {
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
    const observer = new MutationObserver(function () {
        setTimeout(() => calendar.updateSize(), 50);
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
});
