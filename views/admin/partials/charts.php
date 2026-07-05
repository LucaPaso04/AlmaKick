<?php
$pendingReports = $stats['pending_reports'];
?>

<div class="row g-4 mb-5">
    <!-- Registrations chart -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h2 class="h5 fw-bold mb-1"><span class="bi bi-graph-up text-primary me-2"></span>Andamento Registrazioni</h2>
            <p class="small text-muted mb-3">Registrazioni giornaliere degli utenti sulla piattaforma.</p>
            <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                <canvas id="regTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Roles chart -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h2 class="h5 fw-bold mb-1"><span class="bi bi-person-badge-fill text-warning me-2"></span>Ruoli Preferiti</h2>
            <p class="small text-muted mb-3">Distribuzione dei ruoli di gioco scelti dai calciatori.</p>
            <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                <canvas id="rolesDistChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Matches status chart -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h2 class="h5 fw-bold mb-1"><span class="bi bi-calendar-event text-success me-2"></span>Stato Partite</h2>
            <p class="small text-muted mb-3">Ripartizione delle partite create sulla piattaforma.</p>
            <div class="chart-container" style="position: relative; height: 260px; width: 100%;">
                <canvas id="matchesStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Trust & reliability chart -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h2 class="h5 fw-bold mb-1"><span class="bi bi-shield-check text-info me-2"></span>Affidabilità & Ban</h2>
            <p class="small text-muted mb-3">Moderazione e fasce di Trust Score degli utenti attivi.</p>
            <div class="chart-container" style="position: relative; height: 260px; width: 100%;">
                <canvas id="usersBanChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Reports status chart -->
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100 chart-card">
            <h2 class="h5 fw-bold mb-1"><span class="bi bi-flag-fill text-danger me-2"></span>Stato Segnalazioni</h2>
            <p class="small text-muted mb-3">Stato di gestione delle segnalazioni utente.</p>
            <div class="chart-container" style="position: relative; height: 260px; width: 100%;">
                <canvas id="reportsStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Dynamic data injection -->
<script>
window.adminChartsData = {
    regTrend: <?= json_encode($regTrend) ?>,
    rolesDist: <?= json_encode($rolesDist) ?>,
    trustBrackets: <?= json_encode($trustBrackets) ?>,
    stats: <?= json_encode($stats) ?>,
    resolvedReports: <?= $resolvedReports ?>,
    dismissedReports: <?= $dismissedReports ?>,
    pendingReports: <?= $pendingReports ?>
};
</script>
<script src="<?= url('/js/admin-charts.js') ?>"></script>
