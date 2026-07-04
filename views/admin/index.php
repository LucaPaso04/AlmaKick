<?php // Dashboard Amministratore ?>

<?php // =================== HEADER =================== ?>
<div class="admin-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h1 class="fw-bolder mb-1 text-white admin-header-subtitle">
            <i class="bi bi-shield-lock-fill text-warning me-2"></i>Dashboard Amministratore
        </h1>
        <p class="text-white-50 mb-0">Supervisiona utenti, partite, Trust Score e moderazione sulla piattaforma.</p>
    </div>
    <div class="d-none d-md-block">
        <i class="bi bi-graph-up-arrow text-white-50 admin-header-decor"></i>
    </div>
</div>

<?php // =================== TABS =================== ?>
<ul class="nav nav-pills nav-fill admin-tabs mb-4 p-1 rounded-4 shadow-sm border border-secondary-subtle" id="adminDashboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill fw-bold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-section" type="button" role="tab" aria-controls="overview-section" aria-selected="true">
            📊 Panoramica
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill fw-bold" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-section" type="button" role="tab" aria-controls="users-section" aria-selected="false">
            👥 Gestione Utenti
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill fw-bold" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-section" type="button" role="tab" aria-controls="reports-section" aria-selected="false">
            🚩 Segnalazioni
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill fw-bold" id="matches-tab" data-bs-toggle="tab" data-bs-target="#matches-section" type="button" role="tab" aria-controls="matches-section" aria-selected="false">
            ⚽ Partite
        </button>
    </li>
</ul>

<div class="tab-content" id="adminDashboardTabsContent">
    <!-- Tab 1: Panoramica -->
    <div class="tab-pane fade show active" id="overview-section" role="tabpanel" aria-labelledby="overview-tab">
        <?php // =================== STATS CARDS =================== ?>
        <div class="stats-grid mb-5">
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-primary">
            <i class="bi bi-people-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['total_users']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Utenti Totali</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-danger">
            <i class="bi bi-person-x-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['banned_users']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Bannati</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-success">
            <i class="bi bi-calendar-event-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['total_matches']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Partite Totali</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-info">
            <i class="bi bi-play-circle-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['active_matches']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Attive</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-dark">
            <i class="bi bi-flag-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['finished_matches']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Concluse</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-warning text-dark">
            <i class="bi bi-x-circle-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['cancelled_matches']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Annullate</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100 <?= $stats['pending_reports'] > 0 ? 'border border-danger border-opacity-50' : '' ?>">
        <div class="icon-circle <?= $stats['pending_reports'] > 0 ? 'icon-danger pulse-danger' : 'icon-secondary' ?> <?= $stats['pending_reports'] > 0 ? 'pending-reports-active' : 'pending-reports-inactive' ?>">
            <i class="bi bi-flag-fill"></i>
        </div>
        <h3 class="fw-bold mb-0"><?= e($stats['pending_reports']) ?></h3>
        <small class="text-muted fw-semibold admin-stat-card-label">Segnalazioni</small>
    </div>
</div>

        <?php require VIEW_PATH . '/admin/partials/charts.php'; ?>
    </div>

    <!-- Tab 2: Gestione Utenti -->
    <div class="tab-pane fade" id="users-section" role="tabpanel" aria-labelledby="users-tab">
<?php require VIEW_PATH . '/admin/partials/users_table.php'; ?>

    </div>

    <!-- Tab 3: Segnalazioni -->
    <div class="tab-pane fade" id="reports-section" role="tabpanel" aria-labelledby="reports-tab">
<?php require VIEW_PATH . '/admin/partials/reports_table.php'; ?>

    </div>

    <!-- Tab 4: Partite -->
    <div class="tab-pane fade" id="matches-section" role="tabpanel" aria-labelledby="matches-tab">
<?php require VIEW_PATH . '/admin/partials/matches_table.php'; ?>

    </div>


</div>
