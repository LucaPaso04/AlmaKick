<?php // Dashboard Amministratore ?>

{{-- =================== HEADER =================== --}}
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

{{-- =================== STATS CARDS =================== --}}
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

{{-- =================== USERS TABLE =================== --}}
<div class="card shadow border-0 rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-body-tertiary border-0 p-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Gestione Utenti</h5>
    </div>

    {{-- =================== FILTERS & SEARCH =================== --}}
    <div class="card-body border-bottom bg-body-tertiary p-3">
        <form method="GET" action="<?= url('/admin') ?>" class="row g-2">
            {{-- Search --}}
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-search text-body"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cerca utente..."
                        value="<?= e($search) ?>">
                </div>
            </div>

            {{-- Role Filter --}}
            <div class="col-md-2">
                <select name="role" class="form-select">
                    <option value="">Tutti i ruoli</option>
                    <?php foreach ($allRoles as $role): ?>
                        <option value="<?= e($role) ?>" <?= $roleFilter === $role ? 'selected' : '' ?>>
                            <?= e(ucfirst(str_replace('_', ' ', $role))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Tutti gli stati</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Attivi</option>
                    <option value="banned" <?= $statusFilter === 'banned' ? 'selected' : '' ?>>Bannati</option>
                </select>
            </div>

            {{-- Search Button --}}
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Cerca
                </button>
            </div>

            {{-- Conserva i filtri delle segnalazioni e partite --}}
            <input type="hidden" name="status_report" value="<?= e($statusReport) ?>">
            <input type="hidden" name="search_report" value="<?= e($searchReport) ?>">
            <input type="hidden" name="search_match" value="<?= e($searchMatch) ?>">
            <input type="hidden" name="status_match" value="<?= e($statusMatch) ?>">
            <input type="hidden" name="date_match" value="<?= e($dateMatch) ?>">
            <input type="hidden" name="format_match" value="<?= e($formatMatch) ?>">
        </form>
        <?php if ($search || $statusFilter || $roleFilter): ?>
            <div class="mt-2">
                <a href="<?= url('/admin') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Resetta filtri
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="ps-4">
                        <a href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['sort' => 'id', 'order' => ($sortBy === 'username' && $sortOrder === 'asc' ? 'desc' : 'asc')]))) ?>"
                            class="text-decoration-none text-dark">
                            ID <?php if($sortBy === 'username'): ?> <i
                            class="bi bi-chevron-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i> <?php endif; ?>
                        </a>
                    </th>
                    <th scope="col">Utente</th>
                    <th scope="col">Ruolo Preferito</th>
                    <th scope="col" class="text-center">
                        <a href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['sort' => 'trust_score', 'order' => ($sortBy === 'trust_score' && $sortOrder === 'asc' ? 'desc' : 'asc')]))) ?>"
                            class="text-decoration-none text-dark">
                            Trust Score <?php if($sortBy === 'trust_score'): ?> <i
                            class="bi bi-chevron-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i> <?php endif; ?>
                        </a>
                    </th>
                    <th scope="col" class="text-center">
                        <a href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['sort' => 'weather_cancels', 'order' => ($sortBy === 'weather_cancels' && $sortOrder === 'asc' ? 'desc' : 'asc')]))) ?>"
                            class="text-decoration-none text-dark">
                            <span title="Numero di partite annullate con motivo 'Meteo avverso'">
                                ⛈️ Annullate Meteo <?php if($sortBy === 'weather_cancels'): ?> <i
                                class="bi bi-chevron-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i> <?php endif; ?>
                            </span>
                        </a>
                    </th>
                    <th scope="col" class="text-center">Stato</th>
                    <th scope="col" class="text-end pe-4">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                            $is_suspect = ($u['weather_cancels'] > 3);
                            $row_class = '';
                            if ($u['is_banned']) {
                                $row_class = 'table-banned';
                            } elseif ($is_suspect) {
                                $row_class = 'table-suspicious';
                            }
                        ?>
                        <tr class="<?= $row_class ?> clickable-row" onclick="window.location.href='<?= url('/profile?username=' . urlencode($u['username'])) ?>';">
                            <td class="ps-4 text-muted">#<?= e($u['username']) ?></td>
                            <td>
                                <div class="fw-bold">
                                    <a href="<?= url('/profile?username=' . urlencode($u['username'])) ?>" class="text-decoration-none text-reset">
                                        <?= e($u['name']) ?> <?= e($u['last_name'] ?? '') ?>
                                    </a>
                                </div>
                                <div class="small text-muted"><?= e($u['email']) ?></div>
                            </td>
                            <td>
                                <span class="text-capitalize"><?= e($u['preferred_role'] ?? '—') ?></span>
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge rounded-pill fs-6 px-3 py-2 bg-<?= $u['trust_score'] >= 80 ? 'success' : ($u['trust_score'] >= 50 ? 'warning text-dark' : 'danger') ?>">
                                    <?= e($u['trust_score']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($is_suspect): ?>
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <strong><?= e($u['weather_cancels']) ?></strong>
                                    <span class="badge bg-danger ms-1 rounded-pill text-white">SOSPETTO</span>
                                <?php else: ?>
                                    <?= e($u['weather_cancels']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($u['is_banned']): ?>
                                    <span class="badge bg-danger text-white">Bannato</span>
                                <?php elseif ($u['role'] === 'super_admin'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-shield-lock me-1"></i>Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-success text-white">Attivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <?php if ($u['username'] !== $_SESSION['user']['username']): ?>
                                    <?php if ($u['is_banned']): ?>
                                        <form action="<?= url('/admin/unban') ?>" method="POST" class="d-inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                            <input type="hidden" name="user_id" value="<?= e($u['username']) ?>">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold"
                                                onclick="return confirm('Riattivare l\'utente?');"><i
                                                    class="bi bi-unlock-fill me-1"></i>Riattiva</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= url('/admin/ban') ?>" method="POST" class="d-inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                            <input type="hidden" name="user_id" value="<?= e($u['username']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold"
                                                onclick="return confirm('Bannare questo utente? Non potrà più accedere.');"><i
                                                    class="bi bi-ban me-1"></i>Banna</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Nessun utente trovato.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    {{-- =================== PAGINATION =================== --}}
    <?php if ($totalPagesUsers > 1): ?>
        <div class="card-footer bg-body-tertiary border-top p-3">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    <?php if ($pageUsers <= 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">← Precedente</span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['page' => $pageUsers - 1]))) ?>">
                                ← Precedente
                            </a>
                        </li>
                    <?php endif; ?>

                    {{-- Pagination Elements --}}
                    <?php for ($i = 1; $i <= $totalPagesUsers; $i++): ?>
                        <?php if ($i == $pageUsers): ?>
                            <li class="page-item active">
                                <span class="page-link"><?= $i ?></span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link"
                                    href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    {{-- Next Page Link --}}
                    <?php if ($pageUsers >= $totalPagesUsers): ?>
                        <li class="page-item disabled">
                            <span class="page-link">Prossima →</span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['page' => $pageUsers + 1]))) ?>">
                                Prossima →
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center mt-2 small text-body-secondary">
                Pagina <?= $pageUsers ?> di <?= $totalPagesUsers ?> (<?= $totalUsersFiltered ?> utenti totali)
            </div>
        </div>
    <?php endif; ?>
</div>

{{-- =================== REPORTS TABLE =================== --}}
<div class="card shadow border-0 rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-body-tertiary border-0 p-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-flag-fill me-2 text-danger"></i>Gestione Segnalazioni</h5>
        <?php if($stats['pending_reports'] > 0): ?>
            <span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($stats['pending_reports']) ?> Da Gestire</span>
        <?php endif; ?>
    </div>

    {{-- =================== FILTERS & SEARCH =================== --}}
    <div class="card-body border-bottom bg-body-tertiary p-3">
        <form method="GET" action="<?= url('/admin') ?>" class="row g-2">
            {{-- Search --}}
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-search text-body"></i></span>
                    <input type="text" name="search_report" class="form-control" placeholder="Cerca in segnalazioni..."
                        value="<?= e($searchReport) ?>">
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="col-md-3">
                <select name="status_report" class="form-select">
                    <option value="">Tutti gli stati</option>
                    <option value="pending" <?= $statusReport === 'pending' ? 'selected' : '' ?>>Pendenti</option>
                    <option value="resolved" <?= $statusReport === 'resolved' ? 'selected' : '' ?>>Risolte</option>
                    <option value="dismissed" <?= $statusReport === 'dismissed' ? 'selected' : '' ?>>Ignorate</option>
                </select>
            </div>

            {{-- Search Button --}}
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger w-100 fw-semibold">
                    <i class="bi bi-search me-1"></i>Filtra
                </button>
            </div>

            {{-- Conserva gli altri filtri --}}
            <input type="hidden" name="search" value="<?= e($search) ?>">
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <input type="hidden" name="role" value="<?= e($roleFilter) ?>">
            <input type="hidden" name="sort" value="<?= e($sortBy) ?>">
            <input type="hidden" name="order" value="<?= e($sortOrder) ?>">
            <input type="hidden" name="search_match" value="<?= e($searchMatch) ?>">
            <input type="hidden" name="status_match" value="<?= e($statusMatch) ?>">
            <input type="hidden" name="date_match" value="<?= e($dateMatch) ?>">
            <input type="hidden" name="format_match" value="<?= e($formatMatch) ?>">
        </form>
        <?php if ($searchReport || $statusReport !== 'pending'): ?>
            <div class="mt-2">
                <a href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['status_report' => 'pending', 'search_report' => '']))) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Resetta filtri segnalazioni
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Data</th>
                    <th>Segnalatore</th>
                    <th>Segnalato</th>
                    <th>Motivo</th>
                    <th class="w-30">Descrizione</th>
                    <th class="text-center">Stato</th>
                    <th class="text-end pe-4">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reports)): ?>
                    <?php foreach ($reports as $r): ?>
                        <tr>
                            <td class="ps-4 text-muted small"><?= $r['created_at']->format('d/m/Y H:i') ?></td>
                            <td>
                                <?php if($r['reporter']): ?>
                                    <div class="fw-bold">
                                        <a href="<?= url('/profile?username=' . urlencode($r['reporter']['id'])) ?>" class="text-decoration-none text-reset">
                                            <?= e($r['reporter']['name']) ?>
                                        </a>
                                    </div>
                                    <div class="small text-muted"><?= e($r['reporter']['email']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Eliminato</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($r['reported']): ?>
                                    <div class="fw-bold">
                                        <a href="<?= url('/profile?username=' . urlencode($r['reported']['id'])) ?>" class="text-decoration-none text-reset">
                                            <?= e($r['reported']['name']) ?>
                                        </a>
                                    </div>
                                    <div class="small text-muted">
                                        <?= e($r['reported']['email']) ?>
                                        <?php if($r['reported']['is_banned']): ?>
                                            <span class="badge bg-danger ms-1 text-white">BANNATO</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Eliminato</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-3 px-2 py-1"><?= e($r['reason']) ?></span>
                            </td>
                            <td>
                                <div class="text-wrap admin-reports-table-desc" title="<?= e($r['description']) ?>">
                                    <?= e($r['description']) ?>
                                </div>
                                <?php if($r['admin_notes']): ?>
                                    <div class="mt-1 small border-top pt-1 text-info">
                                        <i class="bi bi-chat-right-text-fill me-1"></i><strong>Nota Admin:</strong> <?= e($r['admin_notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($r['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock-fill me-1"></i>Pendente</span>
                                <?php elseif($r['status'] === 'resolved'): ?>
                                    <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Risolta</span>
                                <?php elseif($r['status'] === 'dismissed'): ?>
                                    <span class="badge bg-secondary text-white px-2 py-1"><i class="bi bi-eye-slash-fill me-1"></i>Ignorata</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <?php if($r['status'] === 'pending'): ?>
                                    <div class="d-flex justify-content-end gap-1">
                                        {{-- Bottone Risolvi --}}
                                        <button type="button" class="btn btn-sm btn-success rounded-pill fw-bold" 
                                            data-bs-toggle="modal" data-bs-target="#resolveReportModal<?= $r['id'] ?>">
                                            <i class="bi bi-check-lg me-1"></i>Risolvi
                                        </button>
                                        
                                        {{-- Bottone Ignora --}}
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold" 
                                            data-bs-toggle="modal" data-bs-target="#dismissReportModal<?= $r['id'] ?>">
                                            <i class="bi bi-slash-circle me-1"></i>Ignora
                                        </button>

                                        {{-- Bottone Banna Veloce --}}
                                        <?php if($r['reported'] && !$r['reported']['is_banned'] && $r['reported']['id'] !== $_SESSION['user']['username']): ?>
                                            <form action="<?= url('/admin/ban') ?>" method="POST" class="d-inline-block"
                                                onsubmit="return confirm('Bannare l\'utente segnalato? Questa operazione gli impedirà l\'accesso.');">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="user_id" value="<?= e($r['reported']['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">
                                                    <i class="bi bi-ban me-1"></i>Banna
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                    {{-- Modale Risolvi per questa segnalazione --}}
                                    <div class="modal fade text-start" id="resolveReportModal<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>Risolvi Segnalazione #<?= $r['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                </div>
                                                <form action="<?= url('/admin/reports/' . $r['id'] . '/resolve') ?>" method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                    <div class="modal-body py-3">
                                                        <p class="text-muted small">
                                                            Contrassegna questa segnalazione come **Risolta**. Aggiungi una nota interna per documentare l'azione intrapresa (es. "Utente ammonito" o "Nessuna violazione riscontrata").
                                                        </p>
                                                        <div class="mb-0">
                                                            <label for="admin_notes<?= $r['id'] ?>" class="form-label fw-semibold">Note dell'Amministratore (opzionale)</label>
                                                            <textarea name="admin_notes" id="admin_notes<?= $r['id'] ?>" rows="3" class="form-control rounded-3 bg-body-secondary border-secondary-subtle text-body" 
                                                                placeholder="Scrivi qui i dettagli della risoluzione..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Annulla</button>
                                                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Conferma Risoluzione</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modale Ignora per questa segnalazione --}}
                                    <div class="modal fade text-start" id="dismissReportModal<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-secondary"><i class="bi bi-eye-slash-fill me-2"></i>Ignora Segnalazione #<?= $r['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                </div>
                                                <form action="<?= url('/admin/reports/' . $r['id'] . '/dismiss') ?>" method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                    <div class="modal-body py-3">
                                                        <p class="text-muted small">
                                                            Contrassegna questa segnalazione come **Ignorata/Archiviata**. Puoi aggiungere una breve spiegazione del perché la segnalazione è stata ritenuta non fondata.
                                                        </p>
                                                        <div class="mb-0">
                                                            <label for="dismiss_notes<?= $r['id'] ?>" class="form-label fw-semibold">Note dell'Amministratore (opzionale)</label>
                                                            <textarea name="admin_notes" id="dismiss_notes<?= $r['id'] ?>" rows="3" class="form-control rounded-3 bg-body-secondary border-secondary-subtle text-body" 
                                                                placeholder="Scrivi qui il motivo dell'archiviazione..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Annulla</button>
                                                        <button type="submit" class="btn btn-secondary rounded-pill px-4 fw-bold text-white shadow-sm">Archivia Segnalazione</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Nessuna segnalazione trovata.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    {{-- =================== PAGINATION (REPORTS) =================== --}}
    <?php if ($totalPagesReports > 1): ?>
        <div class="card-footer bg-body-tertiary border-top p-3">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    <?php if ($pageReports <= 1): ?>
                        <li class="page-item disabled"><span class="page-link text-danger">← Precedente</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-danger" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['reports_page' => $pageReports - 1]))) ?>">← Precedente</a>
                        </li>
                    <?php endif; ?>

                    {{-- Pagination Elements --}}
                    <?php for ($i = 1; $i <= $totalPagesReports; $i++): ?>
                        <?php if ($i == $pageReports): ?>
                            <li class="page-item active"><span class="page-link bg-danger border-danger"><?= $i ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link text-danger" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['reports_page' => $i]))) ?>"><?= $i ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    {{-- Next Page Link --}}
                    <?php if ($pageReports >= $totalPagesReports): ?>
                        <li class="page-item disabled"><span class="page-link text-danger">Prossima →</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-danger" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['reports_page' => $pageReports + 1]))) ?>">Prossima →</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center mt-2 small text-body-secondary">
                Pagina <?= $pageReports ?> di <?= $totalPagesReports ?> (<?= $totalReportsFiltered ?> segnalazioni totali)
            </div>
        </div>
    <?php endif; ?>
</div>

{{-- =================== MATCHES TABLE =================== --}}
<div class="card shadow border-0 rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-body-tertiary border-0 p-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-calendar-event-fill me-2 text-success"></i>Gestione Partite</h5>
    </div>

    {{-- =================== FILTERS & SEARCH =================== --}}
    <div class="card-body border-bottom bg-body-tertiary p-3">
        <form method="GET" action="<?= url('/admin') ?>" class="row g-2">
            {{-- Search --}}
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-search text-body"></i></span>
                    <input type="text" name="search_match" class="form-control" placeholder="Cerca..." value="<?= e($searchMatch) ?>">
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="col-md-2">
                <select name="status_match" class="form-select">
                    <option value="">Stato</option>
                    <option value="open" <?= $statusMatch === 'open' ? 'selected' : '' ?>>Aperte</option>
                    <option value="full" <?= $statusMatch === 'full' ? 'selected' : '' ?>>Complete</option>
                    <option value="finished" <?= $statusMatch === 'finished' ? 'selected' : '' ?>>Concluse</option>
                    <option value="cancelled" <?= $statusMatch === 'cancelled' ? 'selected' : '' ?>>Annullate</option>
                </select>
            </div>

            {{-- Date Filter --}}
            <div class="col-md-3">
                <input type="date" name="date_match" class="form-control" value="<?= e($dateMatch) ?>" title="Filtra per data">
            </div>

            {{-- Format Filter --}}
            <div class="col-md-2">
                <select name="format_match" class="form-select">
                    <option value="">Formato</option>
                    <option value="5v5" <?= $formatMatch === '5v5' ? 'selected' : '' ?>>5v5</option>
                    <option value="7v7" <?= $formatMatch === '7v7' ? 'selected' : '' ?>>7v7</option>
                    <option value="8v8" <?= $formatMatch === '8v8' ? 'selected' : '' ?>>8v8</option>
                    <option value="11v11" <?= $formatMatch === '11v11' ? 'selected' : '' ?>>11v11</option>
                </select>
            </div>

            {{-- Search Button --}}
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-search me-1"></i>Cerca
                </button>
            </div>
            
            {{-- Conserva i filtri degli utenti e delle segnalazioni --}}
            <input type="hidden" name="search" value="<?= e($search) ?>">
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <input type="hidden" name="role" value="<?= e($roleFilter) ?>">
            <input type="hidden" name="sort" value="<?= e($sortBy) ?>">
            <input type="hidden" name="order" value="<?= e($sortOrder) ?>">
            <input type="hidden" name="page" value="<?= e($pageUsers) ?>">
            <input type="hidden" name="status_report" value="<?= e($statusReport) ?>">
            <input type="hidden" name="search_report" value="<?= e($searchReport) ?>">
        </form>
        <?php if ($searchMatch || $statusMatch || $dateMatch || $formatMatch): ?>
            <div class="mt-2">
                <a href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['search_match' => '', 'status_match' => '', 'date_match' => '', 'format_match' => '']))) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Resetta filtri partite
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Data/Ora</th>
                    <th>Luogo</th>
                    <th>Host</th>
                    <th class="text-center">Formato</th>
                    <th class="text-center">Stato</th>
                    <th class="text-center">Risultato</th>
                    <th class="text-end pe-4">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($matches)): ?>
                    <?php foreach ($matches as $m): ?>
                        <?php
                            $row_class = '';
                            if ($m['status'] === 'cancelled') {
                                $row_class = 'table-cancelled';
                            }
                        ?>
                        <tr class="<?= $row_class ?> clickable-row"
                            onclick="window.location.href='<?= url('/matches/' . $m['id']) ?>?from=admin';">
                            <td class="ps-4 text-muted">#<?= e($m['id']) ?></td>
                            <td><?= $m['date']->format('d/m/Y') . ' ' . substr($m['time'], 0, 5) ?></td>
                            <td class="fw-bold"><?= e(strlen($m['location']) > 20 ? substr($m['location'], 0, 17) . '...' : $m['location']) ?></td>
                            <td><?= e($m['host']['name'] ?? 'N/D') ?></td>
                            <td class="text-center"><span class="badge bg-secondary"><?= e($m['format']) ?></span></td>
                            <td class="text-center">
                                <?php if($m['status'] === 'open'): ?>
                                    <span class="badge bg-primary">Aperta</span>
                                <?php elseif($m['status'] === 'full'): ?>
                                    <span class="badge bg-success">Completa</span>
                                <?php elseif($m['status'] === 'finished'): ?>
                                    <span class="badge bg-dark">Conclusa</span>
                                <?php elseif($m['status'] === 'cancelled'): ?>
                                    <span class="badge bg-danger">Annullata</span>
                                    <?php if($m['cancellation_reason'] === 'Meteo avverso'): ?>
                                        <small class="d-block text-danger mt-1">⛈️ Meteo</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($m['result_home'] !== null): ?>
                                    <span class="fw-bold"><?= e($m['result_home']) ?> — <?= e($m['result_away']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <?php if($m['status'] === 'open' || $m['status'] === 'full'): ?>
                                    <form action="<?= url('/admin/matches/cancel') ?>" method="POST" class="d-inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill me-1"
                                            onclick="return confirm('Annullare questa partita?');"><i
                                                class="bi bi-x-lg"></i></button>
                                    </form>
                                <?php endif; ?>
                                <form action="<?= url('/admin/matches/delete') ?>" method="POST" class="d-inline-block">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="return confirm('ELIMINARE definitivamente la partita? Questa azione è irreversibile.');"><i
                                            class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Nessuna partita trovata.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    {{-- =================== PAGINATION (MATCHES) =================== --}}
    <?php if ($totalPagesMatches > 1): ?>
        <div class="card-footer bg-body-tertiary border-top p-3">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    <?php if ($pageMatches <= 1): ?>
                        <li class="page-item disabled"><span class="page-link text-success">← Precedente</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-success" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['matches_page' => $pageMatches - 1]))) ?>">← Precedente</a>
                        </li>
                    <?php endif; ?>

                    {{-- Pagination Elements --}}
                    <?php for ($i = 1; $i <= $totalPagesMatches; $i++): ?>
                        <?php if ($i == $pageMatches): ?>
                            <li class="page-item active"><span class="page-link bg-success border-success"><?= $i ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link text-success" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['matches_page' => $i]))) ?>"><?= $i ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    {{-- Next Page Link --}}
                    <?php if ($pageMatches >= $totalPagesMatches): ?>
                        <li class="page-item disabled"><span class="page-link text-success">Prossima →</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-success" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['matches_page' => $pageMatches + 1]))) ?>">Prossima →</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center mt-2 small text-body-secondary">
                Pagina <?= $pageMatches ?> di <?= $totalPagesMatches ?> (<?= $totalMatchesFiltered ?> partite totali)
            </div>
        </div>
    <?php endif; ?>
</div>

{{-- =================== TRUST HISTORY LOG =================== --}}
<div class="card shadow border-0 rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-body-tertiary border-0 p-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-info"></i>Ultimi Eventi Trust Score</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Data</th>
                    <th>Utente</th>
                    <th class="text-center">Variazione</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($trust_logs)): ?>
                    <?php foreach ($trust_logs as $log): ?>
                        <?php
                            $row_class = '';
                            if ($log['score_change'] > 0) {
                                $row_class = 'table-positive';
                            } elseif ($log['score_change'] < 0) {
                                $row_class = 'table-negative';
                            }
                        ?>
                        <tr class="<?= $row_class ?> clickable-row" onclick="window.location.href='<?= url('/profile?username=' . urlencode($log['user_id'])) ?>';">
                            <td class="ps-4 text-muted small"><?= $log['created_at']->format('d/m/Y H:i') ?></td>
                            <td class="fw-bold"><?= e($log['user']['name'] ?? 'Eliminato') ?></td>
                            <td class="text-center">
                                <span
                                    class="badge bg-<?= $log['score_change'] > 0 ? 'success' : 'danger' ?> rounded-pill fs-6 px-3">
                                    <?= $log['score_change'] > 0 ? '+' : '' ?><?= e($log['score_change']) ?>
                                </span>
                            </td>
                            <td class="small"><?= e($log['reason']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Nessun evento registrato.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    {{-- =================== PAGINATION (TRUST LOGS) =================== --}}
    <?php if ($totalPagesTrust > 1): ?>
        <div class="card-footer bg-body-tertiary border-top p-3">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    <?php if ($pageTrust <= 1): ?>
                        <li class="page-item disabled"><span class="page-link text-info">← Precedente</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-info" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['trust_page' => $pageTrust - 1]))) ?>">← Precedente</a>
                        </li>
                    <?php endif; ?>

                    {{-- Pagination Elements --}}
                    <?php for ($i = 1; $i <= $totalPagesTrust; $i++): ?>
                        <?php if ($i == $pageTrust): ?>
                            <li class="page-item active"><span class="page-link bg-info border-info text-dark"><?= $i ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link text-info" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['trust_page' => $i]))) ?>"><?= $i ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    {{-- Next Page Link --}}
                    <?php if ($pageTrust >= $totalPagesTrust): ?>
                        <li class="page-item disabled"><span class="page-link text-info">Prossima →</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-info" href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['trust_page' => $pageTrust + 1]))) ?>">Prossima →</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center mt-2 small text-body-secondary">
                Pagina <?= $pageTrust ?> di <?= $totalPagesTrust ?> (<?= $totalTrust ?> eventi)
            </div>
        </div>
    <?php endif; ?>
</div>
