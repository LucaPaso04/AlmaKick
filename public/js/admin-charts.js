/* Admin dashboard Chart.js visual configurations and themes observers */
(function() {
    // Read dynamic data injected into global window state
    const data = window.adminChartsData || {};
    const regTrendRaw = data.regTrend || [];
    const rolesDistRaw = data.rolesDist || [];
    const trustBrackets = data.trustBrackets || { high: 0, medium: 0, low: 0 };
    const stats = data.stats || { active_matches: 0, finished_matches: 0, cancelled_matches: 0, banned_users: 0 };
    const resolvedReports = data.resolvedReports || 0;
    const dismissedReports = data.dismissedReports || 0;
    const pendingReports = data.pendingReports || 0;

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

    // 1. Registrations Line Chart
    const regLabels = regTrendRaw.map(item => {
        const d = new Date(item.reg_date);
        return d.toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: '2-digit' });
    });
    const regData = regTrendRaw.map(item => parseInt(item.count));

    const canvasReg = document.getElementById('regTrendChart');
    let regChart = null;
    if (canvasReg) {
        const ctxReg = canvasReg.getContext('2d');
        const gradient = ctxReg.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(36, 123, 160, 0.35)');
        gradient.addColorStop(1, 'rgba(36, 123, 160, 0.00)');

        regChart = new Chart(ctxReg, {
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
    }

    // 2. Roles Distribution Doughnut Chart
    const rolesLabels = rolesDistRaw.map(item => item.preferred_role);
    const rolesData = rolesDistRaw.map(item => parseInt(item.count));
    const roleColors = {
        'Portiere': '#247ba0',
        'Difensore': '#1f487e',
        'Centrocampista': '#ffc107',
        'Attaccante': '#fb3640',
        'Non specificato': '#605f5e'
    };
    const rolesBackgrounds = rolesLabels.map(label => roleColors[label] || '#94a3b8');

    const canvasRoles = document.getElementById('rolesDistChart');
    let rolesChart = null;
    if (canvasRoles) {
        const ctxRoles = canvasRoles.getContext('2d');
        rolesChart = new Chart(ctxRoles, {
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
    }

    // 3. Matches Status Doughnut Chart
    const canvasMatches = document.getElementById('matchesStatusChart');
    let matchesChart = null;
    if (canvasMatches) {
        const ctxMatches = canvasMatches.getContext('2d');
        matchesChart = new Chart(ctxMatches, {
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
    }

    // 4. Users Trust Brackets Doughnut Chart
    const canvasUsers = document.getElementById('usersBanChart');
    let usersChart = null;
    if (canvasUsers) {
        const ctxUsers = canvasUsers.getContext('2d');
        usersChart = new Chart(ctxUsers, {
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
    }

    // 5. Reports Status Doughnut Chart
    const canvasReports = document.getElementById('reportsStatusChart');
    let reportsChart = null;
    if (canvasReports) {
        const ctxReports = canvasReports.getContext('2d');
        reportsChart = new Chart(ctxReports, {
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
    }

    function updateChartsTheme() {
        const newTheme = getThemeSettings();
        const charts = [];
        if (regChart) charts.push(regChart);
        if (rolesChart) charts.push(rolesChart);
        if (matchesChart) charts.push(matchesChart);
        if (usersChart) charts.push(usersChart);
        if (reportsChart) charts.push(reportsChart);
        
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

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === "attributes" && mutation.attributeName === "data-bs-theme") {
                updateChartsTheme();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

})();
