<?php // Admin Dashboard ?>

<?php // Header ?>
<div class="admin-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h1 class="fw-bolder mb-1 text-white admin-header-subtitle">
            <span class="bi bi-shield-lock-fill text-warning me-2"></span>Dashboard Amministratore
        </h1>
        <p class="text-white-50 mb-0">Supervisiona utenti, partite, Trust Score e moderazione sulla piattaforma.</p>
    </div>
    <div class="d-none d-md-block">
        <span class="bi bi-graph-up-arrow text-white-50 admin-header-decor"></span>
    </div>
</div>

<?php // Tabs ?>
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
    <!-- Tab: Overview -->
    <div class="tab-pane fade show active" id="overview-section" role="tabpanel" aria-labelledby="overview-tab">
        <?php // Stats cards ?>
        <div class="stats-grid mb-5">
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-primary">
            <span class="bi bi-people-fill"></span>
        </div>
        <div class="h3 fw-bold mb-0"><?= e($stats['total_users']) ?></div>
        <small class="text-muted fw-semibold admin-stat-card-label">Utenti Totali</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-danger">
            <span class="bi bi-person-x-fill"></span>
        </div>
        <div class="h3 fw-bold mb-0"><?= e($stats['banned_users']) ?></div>
        <small class="text-muted fw-semibold admin-stat-card-label">Bannati</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-success">
            <span class="bi bi-calendar-event-fill"></span>
        </div>
        <div class="h3 fw-bold mb-0"><?= e($stats['total_matches']) ?></div>
        <small class="text-muted fw-semibold admin-stat-card-label">Partite Totali</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-info">
            <span class="bi bi-play-circle-fill"></span>
        </div>
        <div class="h3 fw-bold mb-0"><?= e($stats['active_matches']) ?></div>
        <small class="text-muted fw-semibold admin-stat-card-label">Attive</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-dark">
            <span class="bi bi-flag-fill"></span>
        </div>
        <div class="h3 fw-bold mb-0"><?= e($stats['finished_matches']) ?></div>
        <small class="text-muted fw-semibold admin-stat-card-label">Concluse</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100">
        <div class="icon-circle icon-warning text-dark">
            <span class="bi bi-x-circle-fill"></span>
        </div>
        <div class="h3 fw-bold mb-0"><?= e($stats['cancelled_matches']) ?></div>
        <small class="text-muted fw-semibold admin-stat-card-label">Annullate</small>
    </div>
    <div class="card admin-stat-card border-0 shadow-sm rounded-4 text-center h-100 <?= $stats['pending_reports'] > 0 ? 'border border-danger border-opacity-50' : '' ?>">
        <div class="icon-circle <?= $stats['pending_reports'] > 0 ? 'icon-danger pulse-danger' : 'icon-secondary' ?> <?= $stats['pending_reports'] > 0 ? 'pending-reports-active' : 'pending-reports-inactive' ?>">
            <span class="bi bi-flag-fill"></span>
        </div>
        <div class="h3 fw-bold mb-0"><?= e($stats['pending_reports']) ?></div>
        <small class="text-muted fw-semibold admin-stat-card-label">Segnalazioni</small>
    </div>
</div>

        <?php require VIEW_PATH . '/admin/partials/charts.php'; ?>
    </div>

    <!-- Tab: Users -->
    <div class="tab-pane fade" id="users-section" role="tabpanel" aria-labelledby="users-tab">
<?php require VIEW_PATH . '/admin/partials/users_table.php'; ?>

    </div>

    <!-- Tab: Reports -->
    <div class="tab-pane fade" id="reports-section" role="tabpanel" aria-labelledby="reports-tab">
<?php require VIEW_PATH . '/admin/partials/reports_table.php'; ?>

    </div>

    <!-- Tab: Matches -->
    <div class="tab-pane fade" id="matches-section" role="tabpanel" aria-labelledby="matches-tab">
<?php require VIEW_PATH . '/admin/partials/matches_table.php'; ?>

    </div>


</div>
