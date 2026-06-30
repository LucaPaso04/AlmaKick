<?php
$db = \App\Database::getInstance()->getConnection();
$resolvedReports = (int) $db->query("SELECT COUNT(*) FROM reports WHERE status = 'resolved'")->fetchColumn();
$dismissedReports = (int) $db->query("SELECT COUNT(*) FROM reports WHERE status = 'dismissed'")->fetchColumn();
$pendingReports = $stats['pending_reports'];
?>

<div class="row g-4 mb-5">
    <!-- Card 1: Andamento Registrazioni nel Tempo (Line Chart) -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h5 class="fw-bold mb-1"><i class="bi bi-graph-up text-primary me-2"></i>Andamento Registrazioni</h5>
            <p class="small text-muted mb-3">Registrazioni giornaliere degli utenti sulla piattaforma.</p>
            <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                <canvas id="regTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Card 2: Distribuzione Ruoli Preferiti (Doughnut Chart) -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h5 class="fw-bold mb-1"><i class="bi bi-person-badge-fill text-warning me-2"></i>Ruoli Preferiti</h5>
            <p class="small text-muted mb-3">Distribuzione dei ruoli di gioco scelti dai calciatori.</p>
            <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                <canvas id="rolesDistChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Card 3: Stato Partite (Doughnut Chart) -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h5 class="fw-bold mb-1"><i class="bi bi-calendar-event text-success me-2"></i>Stato Partite</h5>
            <p class="small text-muted mb-3">Ripartizione delle partite create sulla piattaforma.</p>
            <div class="chart-container" style="position: relative; height: 260px; width: 100%;">
                <canvas id="matchesStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Card 4: Stato Account e Trust (Doughnut Chart) -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h5 class="fw-bold mb-1"><i class="bi bi-shield-check text-info me-2"></i>Affidabilità & Ban</h5>
            <p class="small text-muted mb-3">Moderazione e fasce di Trust Score degli utenti attivi.</p>
            <div class="chart-container" style="position: relative; height: 260px; width: 100%;">
                <canvas id="usersBanChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Card 5: Risoluzione Segnalazioni (Doughnut Chart) -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h5 class="fw-bold mb-1"><i class="bi bi-flag-fill text-danger me-2"></i>Stato Segnalazioni</h5>
            <p class="small text-muted mb-3">Stato di gestione delle segnalazioni utente.</p>
            <div class="chart-container" style="position: relative; height: 260px; width: 100%;">
                <canvas id="reportsStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function() {
    // Iniezione sicura dei dati da PHP
    const regTrendRaw = <?= json_encode($regTrend) ?>;
    const rolesDistRaw = <?= json_encode($rolesDist) ?>;
    const trustBrackets = <?= json_encode($trustBrackets) ?>;
    const stats = <?= json_encode($stats) ?>;
    const resolvedReports = <?= $resolvedReports ?>;
    const dismissedReports = <?= $dismissedReports ?>;
    const pendingReports = <?= $pendingReports ?>;

    // Helper per determinare il tema corrente
    function getThemeSettings() {
        const isLight = document.documentElement.getAttribute('data-bs-theme') === 'light';
        return {
            textColor: isLight ? '#475569' : '#94a3b8',
            gridColor: isLight ? 'rgba(0, 0, 0, 0.06)' : 'rgba(255, 255, 255, 0.06)',
            tooltipBg: isLight ? 'rgba(255, 255, 255, 0.95)' : 'rgba(15, 23, 42, 0.95)',
            tooltipColor: isLight ? '#0f172a' : '#f8fafc',
            tooltipBorder: isLight ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)',
        };
    }

    const theme = getThemeSettings();

    // 1. Chart Registrazioni nel tempo
    const regLabels = regTrendRaw.map(item => {
        const d = new Date(item.reg_date);
        return d.toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: '2-digit' });
    });
    const regData = regTrendRaw.map(item => parseInt(item.count));

    const ctxReg = document.getElementById('regTrendChart').getContext('2d');
    
    // Creazione gradiente per l'andamento delle registrazioni
    const gradient = ctxReg.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(36, 123, 160, 0.35)');
    gradient.addColorStop(1, 'rgba(36, 123, 160, 0.00)');

    const regChart = new Chart(ctxReg, {
        type: 'line',
        data: {
            labels: regLabels.length > 0 ? regLabels : ['Nessun dato'],
            datasets: [{
                label: 'Registrazioni',
                data: regData.length > 0 ? regData : [0],
                borderColor: '#247ba0',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#247ba0',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: theme.tooltipBg,
                    titleColor: theme.tooltipColor,
                    bodyColor: theme.tooltipColor,
                    borderColor: theme.tooltipBorder,
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: theme.textColor, font: { family: 'Outfit, sans-serif' } }
                },
                y: {
                    grid: { color: theme.gridColor },
                    ticks: { 
                        color: theme.textColor, 
                        font: { family: 'Outfit, sans-serif' },
                        precision: 0 
                    }
                }
            }
        }
    });

    // 2. Chart Ruoli Preferiti (Doughnut)
    const rolesLabels = rolesDistRaw.map(item => item.preferred_role);
    const rolesData = rolesDistRaw.map(item => parseInt(item.count));
    const roleColors = {
        'Portiere': '#247ba0', // Cerulean
        'Difensore': '#1f487e', // Steel Azure
        'Centrocampista': '#ffc107', // Amber/Yellow
        'Attaccante': '#fb3640', // Strawberry Red
        'Non specificato': '#605f5e' // Charcoal
    };
    const rolesBackgrounds = rolesLabels.map(label => roleColors[label] || '#94a3b8');

    const ctxRoles = document.getElementById('rolesDistChart').getContext('2d');
    const rolesChart = new Chart(ctxRoles, {
        type: 'doughnut',
        data: {
            labels: rolesLabels.length > 0 ? rolesLabels : ['Nessun dato'],
            datasets: [{
                data: rolesData.length > 0 ? rolesData : [0],
                backgroundColor: rolesBackgrounds.length > 0 ? rolesBackgrounds : ['#605f5e'],
                borderWidth: 0,
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
                        color: theme.textColor,
                        font: { family: 'Outfit, sans-serif', size: 12 },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: theme.tooltipBg,
                    titleColor: theme.tooltipColor,
                    bodyColor: theme.tooltipColor,
                    borderColor: theme.tooltipBorder,
                    borderWidth: 1,
                    padding: 10
                }
            }
        }
    });

    // 3. Chart Stato Partite (Doughnut)
    const ctxMatches = document.getElementById('matchesStatusChart').getContext('2d');
    const matchesChart = new Chart(ctxMatches, {
        type: 'doughnut',
        data: {
            labels: ['Attive', 'Concluse', 'Annullate'],
            datasets: [{
                data: [stats.active_matches, stats.finished_matches, stats.cancelled_matches],
                backgroundColor: ['#247ba0', '#1d3461', '#fb3640'],
                borderWidth: 0,
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
                        color: theme.textColor,
                        font: { family: 'Outfit, sans-serif', size: 12 },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: theme.tooltipBg,
                    titleColor: theme.tooltipColor,
                    bodyColor: theme.tooltipColor,
                    borderColor: theme.tooltipBorder,
                    borderWidth: 1,
                    padding: 10
                }
            }
        }
    });

    // 4. Chart Stato Utenti / Ban & Trust (Doughnut)
    const ctxUsers = document.getElementById('usersBanChart').getContext('2d');
    const usersChart = new Chart(ctxUsers, {
        type: 'doughnut',
        data: {
            labels: ['Trust Alto (>=80)', 'Trust Medio (50-79)', 'Trust Basso (<50)', 'Bannati'],
            datasets: [{
                data: [trustBrackets.high, trustBrackets.medium, trustBrackets.low, stats.banned_users],
                backgroundColor: ['#198754', '#ffc107', '#fd7e14', '#fb3640'],
                borderWidth: 0,
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
                        color: theme.textColor,
                        font: { family: 'Outfit, sans-serif', size: 12 },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: theme.tooltipBg,
                    titleColor: theme.tooltipColor,
                    bodyColor: theme.tooltipColor,
                    borderColor: theme.tooltipBorder,
                    borderWidth: 1,
                    padding: 10
                }
            }
        }
    });

    // 5. Chart Stato Segnalazioni (Doughnut)
    const ctxReports = document.getElementById('reportsStatusChart').getContext('2d');
    const reportsChart = new Chart(ctxReports, {
        type: 'doughnut',
        data: {
            labels: ['Pendenti', 'Risolte', 'Ignorate'],
            datasets: [{
                data: [pendingReports, resolvedReports, dismissedReports],
                backgroundColor: ['#ffc107', '#198754', '#605f5e'],
                borderWidth: 0,
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
                        color: theme.textColor,
                        font: { family: 'Outfit, sans-serif', size: 12 },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: theme.tooltipBg,
                    titleColor: theme.tooltipColor,
                    bodyColor: theme.tooltipColor,
                    borderColor: theme.tooltipBorder,
                    borderWidth: 1,
                    padding: 10
                }
            }
        }
    });

    // Funzione per aggiornare i colori in base al tema corrente
    function updateChartsTheme() {
        const newTheme = getThemeSettings();
        
        const charts = [regChart, rolesChart, matchesChart, usersChart, reportsChart];
        
        charts.forEach(chart => {
            if (chart.options.scales) {
                if (chart.options.scales.x) {
                    chart.options.scales.x.ticks.color = newTheme.textColor;
                }
                if (chart.options.scales.y) {
                    chart.options.scales.y.grid.color = newTheme.gridColor;
                    chart.options.scales.y.ticks.color = newTheme.textColor;
                }
            }
            if (chart.options.plugins && chart.options.plugins.legend) {
                chart.options.plugins.legend.labels.color = newTheme.textColor;
            }
            if (chart.options.plugins && chart.options.plugins.tooltip) {
                chart.options.plugins.tooltip.backgroundColor = newTheme.tooltipBg;
                chart.options.plugins.tooltip.titleColor = newTheme.tooltipColor;
                chart.options.plugins.tooltip.bodyColor = newTheme.tooltipColor;
                chart.options.plugins.tooltip.borderColor = newTheme.tooltipBorder;
            }
            chart.update();
        });
    }

    // Osservatore MutationObserver per reagire al cambio di tema (data-bs-theme su html)
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === "attributes" && mutation.attributeName === "data-bs-theme") {
                updateChartsTheme();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

})();
</script>
