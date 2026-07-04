<?php
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

<!-- Inject dynamic data for Chart.js -->
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
