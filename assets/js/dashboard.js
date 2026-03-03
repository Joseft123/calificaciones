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
