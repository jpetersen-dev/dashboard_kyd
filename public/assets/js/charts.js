document.addEventListener('DOMContentLoaded', () => {

    // --- LÓGICA DEL SELECTOR DE CAMPAÑA Y MODAL ---
    const campaignSelector = document.getElementById('campaignSelector');
    if (campaignSelector) {
        campaignSelector.addEventListener('change', (e) => {
            const newCampaignId = e.target.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('campaign_id', newCampaignId);
            currentUrl.searchParams.delete('period'); // Reset period on campaign change
            window.location.href = currentUrl.href;
        });
    }
    
    // --- Lógica del Modal ---
    const openModalBtn = document.getElementById('openModalBtn');
    const openModalBtnAlt = document.getElementById('openModalBtn-alt');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const modal = document.getElementById('campaignModal');
    const form = document.getElementById('createCampaignForm');

    const openModal = () => modal?.classList.remove('hidden');
    const closeModal = () => {
        if (modal) {
            modal.classList.add('hidden');
            form?.reset();
        }
    };
    
    openModalBtn?.addEventListener('click', openModal);
    openModalBtnAlt?.addEventListener('click', openModal);
    closeModalBtn?.addEventListener('click', closeModal);

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const campaignNameInput = document.getElementById('campaign_name');
            const feedbackDiv = document.getElementById('form-feedback');
            const submitButton = form.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.textContent = 'Creando...';

            try {
                const response = await fetch('/campaigns/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nombre_campana: campaignNameInput.value.trim() }),
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message);
                feedbackDiv.className = 'text-sm mb-4 text-green-400';
                feedbackDiv.textContent = '¡Campaña creada con éxito!';
                setTimeout(() => window.location.reload(), 1500);
            } catch (error) {
                feedbackDiv.className = 'text-sm mb-4 text-red-400';
                feedbackDiv.textContent = `Error: ${error.message}`;
                submitButton.disabled = false;
                submitButton.textContent = 'Crear Campaña';
            }
        });
    }
    
    // --- LÓGICA PARA RENDERIZAR GRÁFICOS ---
    if (typeof chartData === 'undefined' || !chartData) {
        return; // No hay datos para los gráficos
    }

    const isDarkMode = () => document.documentElement.classList.contains('dark');
    const getThemeColors = () => ({
        textColor: isDarkMode() ? '#94a3b8' : '#64748b',
        gridColor: isDarkMode() ? '#334155' : '#e2e8f0'
    });
    
    let colors = getThemeColors();

    // --- Gráfico de Serie de Tiempo (LÓGICA AJAX) ---
    const timeSeriesCtx = document.getElementById('timeSeriesChart')?.getContext('2d');
    let timeSeriesChart; // Guardar la instancia del gráfico

    if (timeSeriesCtx) {
        // Función para actualizar los datos y etiquetas del gráfico
        const updateChartData = (chart, period, newData) => {
            let labels, opens, clicks;
            if (period === 'today') {
                labels = Array.from({ length: 24 }, (_, i) => `${String(i).padStart(2, '0')}:00`);
                opens = Array(24).fill(0);
                clicks = Array(24).fill(0);
                newData.forEach(item => {
                    const hour = parseInt(item.hora);
                    opens[hour] = parseInt(item.aperturas);
                    clicks[hour] = parseInt(item.clics);
                });
            } else {
                labels = newData.map(d => new Date(d.fecha + 'T00:00:00').toLocaleDateString('es-CL', { day: 'numeric', month: 'short' }));
                opens = newData.map(d => parseInt(d.aperturas));
                clicks = newData.map(d => parseInt(d.clics));
            }
            chart.data.labels = labels;
            chart.data.datasets[0].data = opens;
            chart.data.datasets[1].data = clicks;
            chart.update();
        };

        // Crear el gráfico inicial
        timeSeriesChart = new Chart(timeSeriesCtx, {
            type: 'line',
            data: { 
                labels: [], 
                datasets: [
                    { label: 'Aperturas', data: [], borderColor: '#38bdf8', backgroundColor: '#38bdf820', tension: 0.3, fill: true },
                    { label: 'Clics', data: [], borderColor: '#34d399', backgroundColor: '#34d39920', tension: 0.3, fill: true }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { type: 'category', ticks: { color: colors.textColor, maxRotation: 0, autoSkip: true, maxTicksLimit: 15 }},
                    y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } }
                },
                plugins: { legend: { labels: { color: colors.textColor } } }
            }
        });

        // Cargar los datos iniciales
        updateChartData(timeSeriesChart, chartData.selectedPeriod, chartData.interactionsOverTime);

        // --- Lógica para el cambio de período con AJAX ---
        const periodSelector = document.getElementById('period-selector');
        const spinner = document.getElementById('chart-spinner');

        periodSelector.addEventListener('click', async (e) => {
            if (e.target.tagName !== 'BUTTON') return;

            const newPeriod = e.target.dataset.period;
            const currentCampaignId = chartData.selectedCampaignId;

            spinner.classList.remove('hidden');
            periodSelector.querySelectorAll('button').forEach(btn => btn.classList.remove('active-period'));
            e.target.classList.add('active-period');

            try {
                const response = await fetch(`/api/interactions/${currentCampaignId}/${newPeriod}`);
                if (!response.ok) throw new Error('Error al cargar los datos.');
                const newData = await response.json();
                
                const url = new URL(window.location);
                url.searchParams.set('period', newPeriod);
                window.history.pushState({}, '', url);

                updateChartData(timeSeriesChart, newPeriod, newData);

            } catch (error) {
                console.error('Error al actualizar el gráfico:', error);
            } finally {
                spinner.classList.add('hidden');
            }
        });
    }
    
    // --- Gráfico de Interés por Rubro ---
    const industryCtx = document.getElementById('industryChart')?.getContext('2d');
    if (industryCtx && chartData.interestByIndustry) {
        const labels = chartData.interestByIndustry.map(item => item.rubro);
        const data = chartData.interestByIndustry.map(item => item.total);
        new Chart(industryCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: 'Interacciones', data: data, backgroundColor: '#0ea5e9', borderRadius: 4 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { display: false }, ticks: { color: colors.textColor } }, x: { grid: { color: colors.gridColor }, ticks: { color: colors.textColor } } } }
        });
    }

    // --- GRÁFICO MODIFICADO: Interés por Comuna ---
    const communeCtx = document.getElementById('communeChart')?.getContext('2d');
    if (communeCtx && chartData.interestByCommune) {
        const labels = chartData.interestByCommune.map(item => item.comuna);
        const data = chartData.interestByCommune.map(item => item.total);
        new Chart(communeCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: 'Interacciones', data: data, backgroundColor: '#8b5cf6', borderRadius: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: colors.gridColor }, ticks: { color: colors.textColor } }, x: { grid: { display: false }, ticks: { color: colors.textColor } } } }
        });
    }

    // --- GRÁFICO MODIFICADO: Salud de la Base de Datos (CON COLORES CORREGIDOS) ---
    const contactStatusCtx = document.getElementById('contactStatusChart')?.getContext('2d');
    if (contactStatusCtx && chartData.contactStatus) {
        const labels = chartData.contactStatus.map(item => item.estado_suscripcion);
        const data = chartData.contactStatus.map(item => item.total);

        // 1. Definir el mapa de colores deseado
        const statusColorMap = {
            'activo': '#22c55e',       // Verde para activos
            'activos': '#22c55e',      // Verde (alias por si acaso)
            'baja': '#ef4444',         // Rojo para bajas
            'bajas': '#ef4444',        // Rojo (alias por si acaso)
            'rebotado_duro': '#f59e0b', // Ámbar para rebotes
            'default': '#64748b'       // Gris/Slate para cualquier otro estado (ej: 'sin_confirmar')
        };

        // 2. Mapear las etiquetas (labels) a los colores
        const backgroundColors = labels.map(label => 
            statusColorMap[label.toLowerCase()] || statusColorMap['default']
        );

        new Chart(contactStatusCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ 
                    data: data, 
                    backgroundColor: backgroundColors, // <-- Usar el array dinámico
                    borderWidth: 0 
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { color: colors.textColor, boxWidth: 12, padding: 20 } } } }
        });
    }

    // --- Gráfico de Mapa de Calor ---
    const heatmapCtx = document.getElementById('heatmapChart')?.getContext('2d');
    if (heatmapCtx && chartData.interactionHeatmap) {
        const heatmapData = chartData.interactionHeatmap.map(item => ({
            x: parseInt(item.hora_dia),
            y: parseInt(item.dia_semana),
            v: parseInt(item.interacciones)
        }));

        const daysOfWeek = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        
        new Chart(heatmapCtx, {
            type: 'matrix',
            data: {
                datasets: [{
                    label: 'Interacciones',
                    data: heatmapData,
                    backgroundColor: (ctx) => {
                        if (!ctx.raw) return isDarkMode() ? '#1e293b' : '#f8fafc';
                        const value = ctx.raw.v;
                        if (value === 0) return isDarkMode() ? '#1e293b' : '#f8fafc';
                        const maxValue = Math.max(...heatmapData.map(d => d.v).filter(v => v > 0));
                        const alpha = maxValue > 0 ? Math.min(0.1 + (value / maxValue) * 0.9, 1) : 0.1;
                        return `rgba(34, 197, 94, ${alpha})`;
                    },
                    borderColor: colors.gridColor,
                    borderWidth: 1.5,
                    width: ({chart}) => (chart.chartArea || {}).width / 24,
                    height: ({chart}) => (chart.chartArea || {}).height / 7,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: {
                    callbacks: {
                        title: (items) => `${daysOfWeek[items[0].raw.y]}, ${String(items[0].raw.x).padStart(2,'0')}:00h`,
                        label: (item) => `Interacciones: ${item.raw.v}`
                    }
                }},
                scales: {
                    x: { type: 'linear', offset: false, position: 'bottom', min: -0.5, max: 23.5, grid: { display: false }, ticks: { color: colors.textColor, stepSize: 2, callback: (val) => `${String(val).padStart(2, '0')}` }},
                    y: { type: 'linear', offset: false, min: -0.5, max: 6.5, reverse: true, grid: { display: false }, ticks: { color: colors.textColor, callback: (val) => daysOfWeek[val] }}
                }
            }
        });
    }
});
