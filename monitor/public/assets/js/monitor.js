/**
 * XMOTOS Monitor - JavaScript del panel de observabilidad
 *
 * Contiene:
 *  1. Toggle sidebar (móvil)
 *  2. Auto-refresh con countdown
 *  3. Gráfico de operaciones (Chart.js)
 */
console.log('[MONITOR] monitor.js cargado');

document.addEventListener('DOMContentLoaded', function () {
    console.log('[MONITOR] DOM listo');

    // =====================================================
    // 1. Toggle sidebar en móvil
    // =====================================================
    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar   = document.getElementById('monitorSidebar');

    if (btnToggle && sidebar) {
        btnToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }

    // =====================================================
    // 2. Auto-refresh con countdown
    // =====================================================
    const countdownEl = document.getElementById('countdown');

    if (countdownEl) {
        let segundos = 10;
        let pausado  = false;

        // Restaurar scroll si venimos de un refresh
        window.addEventListener('load', function () {
            const scrollY = sessionStorage.getItem('monitor_scrollY');
            if (scrollY) {
                window.scrollTo(0, parseInt(scrollY, 10));
                sessionStorage.removeItem('monitor_scrollY');
            }
        });

        // Función global para el botón "Refrescar ahora"
        window.refrescarAhora = function () {
            sessionStorage.setItem('monitor_scrollY', window.scrollY);
            location.reload();
        };

        // Countdown
        setInterval(function () {
            if (pausado) return;
            segundos--;
            countdownEl.textContent = segundos;
            if (segundos <= 0) {
                window.refrescarAhora();
            }
        }, 1000);

        // Pausar si la pestaña no está visible
        document.addEventListener('visibilitychange', function () {
            pausado = document.hidden;
        });
    }

    // =====================================================
    // 3. Gráfico de operaciones (Chart.js)
    // =====================================================
    const canvas = document.getElementById('graficoOperaciones');

    if (canvas && typeof Chart !== 'undefined') {
        // Leer los datos desde el atributo data-ops del canvas
        let datos = [];
        try {
            datos = JSON.parse(canvas.dataset.ops || '[]');
        } catch (e) {
            console.error('[MONITOR] Error parseando data-ops:', e);
        }

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: Array.from({ length: 24 }, (_, i) =>
                    i.toString().padStart(2, '0') + ':00'
                ),
                datasets: [{
                    label: 'Operaciones',
                    data: datos,
                    backgroundColor: '#B8FF24',
                    borderColor: '#4A7A00',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleColor: '#B8FF24',
                        bodyColor: '#FFFFFF',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#64748B' },
                        grid: { color: '#E2E8F0' }
                    },
                    x: {
                        ticks: { color: '#64748B', font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });

        console.log('[MONITOR] Gráfico renderizado con', datos.length, 'puntos');
    }
});