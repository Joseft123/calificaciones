document.addEventListener('DOMContentLoaded', function () {
    // Requires window.chartLabels and window.chartData to be set before including this script
    if (typeof window.chartLabels === 'undefined' || typeof window.chartData === 'undefined') {
        return;
    }

    const getTextColor = () => {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#adb5bd' : '#495057';
    };

    const ctxEl = document.getElementById('nivelesChart');
    if (ctxEl) {
        const ctx = ctxEl.getContext('2d');

        const bgColors = [
            'rgba(25, 135, 84, 0.8)',  // Success
            'rgba(13, 110, 253, 0.8)', // Primary
            'rgba(255, 193, 7, 0.8)',  // Warning
            'rgba(13, 202, 240, 0.8)'  // Info
        ];
        const borderColors = [
            'rgb(25, 135, 84)',
            'rgb(13, 110, 253)',
            'rgb(255, 193, 7)',
            'rgb(13, 202, 240)'
        ];

        let nivelesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: window.chartLabels.length > 0 ? window.chartLabels : ['Sin datos'],
                datasets: [{
                    data: window.chartData.length > 0 ? window.chartData : [1],
                    backgroundColor: window.chartData.length > 0 ? bgColors : ['rgba(200, 200, 200, 0.2)'],
                    borderColor: window.chartData.length > 0 ? borderColors : ['rgba(200, 200, 200, 0.5)'],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: getTextColor(),
                            padding: 20,
                            font: { size: 14, family: "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif" },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 14, weight: 'bold' },
                        displayColors: false
                    }
                }
            }
        });

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.attributeName === "data-bs-theme") {
                    nivelesChart.options.plugins.legend.labels.color = getTextColor();
                    nivelesChart.update();
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });
    }

    // Gráfica de Promedios por Nivel (Bar Chart)
    const ctxPromediosEl = document.getElementById('promediosChart');
    if (ctxPromediosEl && typeof window.promediosData !== 'undefined') {
        const ctxPromedios = ctxPromediosEl.getContext('2d');

        // Colores para las barras
        const barBgColors = [
            'rgba(25, 135, 84, 0.7)',  // Success
            'rgba(13, 110, 253, 0.7)', // Primary
            'rgba(255, 193, 7, 0.7)',  // Warning
            'rgba(13, 202, 240, 0.7)', // Info
            'rgba(111, 66, 193, 0.7)'  // Purple
        ];

        let promediosChart = new Chart(ctxPromedios, {
            type: 'bar',
            data: {
                labels: window.promediosLabels.length > 0 ? window.promediosLabels : ['Sin datos'],
                datasets: [{
                    label: 'Promedio General',
                    data: window.promediosData.length > 0 ? window.promediosData : [0],
                    backgroundColor: window.promediosData.length > 0 ? barBgColors : ['rgba(200, 200, 200, 0.2)'],
                    borderRadius: 6,
                    borderWidth: 0,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        grid: {
                            color: 'rgba(150, 150, 150, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: getTextColor(),
                            font: { family: "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif" }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: getTextColor(),
                            font: { family: "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", weight: 'bold' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // No necesitamos leyenda para una sola métrica
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 14, weight: 'bold' },
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return 'Promedio: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });

        // Actualizar colores si cambia el tema
        const observerPromedios = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.attributeName === "data-bs-theme") {
                    promediosChart.options.scales.x.ticks.color = getTextColor();
                    promediosChart.options.scales.y.ticks.color = getTextColor();
                    promediosChart.update();
                }
            });
        });
        observerPromedios.observe(document.documentElement, { attributes: true });
    }

    // Animated Number Counters
    const counters = document.querySelectorAll('.counter');
    const MathSpeed = 200;

    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const inc = target / MathSpeed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 15);
            } else {
                counter.innerText = target;
            }
        };
        setTimeout(updateCount, 400);
    });
});
