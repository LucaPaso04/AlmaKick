<div id="users-section-card" class="card shadow border-0 rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-body-tertiary border-0 p-3">
        <h2 class="h5 fw-bold mb-0"><span class="bi bi-people-fill me-2 text-primary"></span>Gestione Utenti</h2>
    </div>

    <?php // Filters & Search ?>
    <div class="card-body border-bottom bg-body-tertiary p-3">
        <form method="GET" action="<?= url('/admin') ?>#users-section" class="row g-2">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary"><span class="bi bi-search text-body"></span></span>
                    <label for="search-user-input" class="visually-hidden">Cerca utente</label>
                    <input type="text" id="search-user-input" name="search" class="form-control" placeholder="Cerca utente..."
                        value="<?= e($search) ?>">
                </div>
            </div>

            <div class="col-md-3">
                <label for="filter-role-select" class="visually-hidden">Filtra per ruolo</label>
                <select id="filter-role-select" name="role" class="form-select">
                    <option value="">Tutti i ruoli</option>
                    <?php foreach ($allRoles as $role): ?>
                        <option value="<?= e($role) ?>" <?= $roleFilter === $role ? 'selected' : '' ?>>
                            <?= e(ucfirst(str_replace('_', ' ', $role))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="filter-status-select" class="visually-hidden">Filtra per stato</label>
                <select id="filter-status-select" name="status" class="form-select">
                    <option value="">Tutti gli stati</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Attivi</option>
                    <option value="banned" <?= $statusFilter === 'banned' ? 'selected' : '' ?>>Bannati</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="filter-problematic-select" class="visually-hidden">Filtra per segnalazioni</label>
                <select id="filter-problematic-select" name="problematic" class="form-select">
                    <option value="">Tutti i profili</option>
                    <option value="low_trust" <?= $problematicFilter === 'low_trust' ? 'selected' : '' ?>>Trust Score &lt; 40</option>
                    <option value="suspicious_weather" <?= $problematicFilter === 'suspicious_weather' ? 'selected' : '' ?>>Annullamenti Meteo Sospetti (>=3)</option>
                </select>
            </div>
        </form>
        <?php if ($search || $statusFilter || $roleFilter || $problematicFilter): ?>
            <div class="mt-2">
                <a href="<?= url('/admin') ?>#users-section" class="btn btn-sm btn-outline-secondary">
                    <span class="bi bi-x-circle me-1"></span>Resetta filtri
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="ps-4">
                        <a href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['sort' => 'id', 'order' => ($sortBy === 'username' && $sortOrder === 'asc' ? 'desc' : 'asc')]))) ?>#users-section"
                            class="text-decoration-none text-dark">
                            Username <?php if($sortBy === 'username'): ?> <span
                            class="bi bi-chevron-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></span> <?php endif; ?>
                        </a>
                    </th>
                    <th scope="col">Utente</th>
                    <th scope="col">Ruolo Preferito</th>
                    <th scope="col" class="text-center">
                        <a href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['sort' => 'trust_score', 'order' => ($sortBy === 'trust_score' && $sortOrder === 'asc' ? 'desc' : 'asc')]))) ?>#users-section"
                            class="text-decoration-none text-dark">
                            Trust Score <?php if($sortBy === 'trust_score'): ?> <span
                            class="bi bi-chevron-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></span> <?php endif; ?>
                        </a>
                    </th>
                    <th scope="col" class="text-center">
                        <a href="<?= url('/admin?' . http_build_query(array_merge($_GET, ['sort' => 'weather_cancels', 'order' => ($sortBy === 'weather_cancels' && $sortOrder === 'asc' ? 'desc' : 'asc')]))) ?>#users-section"
                            class="text-decoration-none text-dark">
                            <span title="Numero di partite annullate con motivo 'Meteo avverso'">
                                ⛈️ Annullate Meteo <?php if($sortBy === 'weather_cancels'): ?> <span
                                class="bi bi-chevron-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></span> <?php endif; ?>
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
                            $is_low_trust = ($u['trust_score'] < 25);
                            $row_class = '';
                            if ($u['is_banned']) {
                                $row_class = 'table-banned';
                            } elseif ($is_low_trust) {
                                $row_class = 'table-banned'; // Highlight red
                            } elseif ($is_suspect) {
                                $row_class = 'table-suspicious';
                            }
                        ?>
                        <tr class="<?= $row_class ?> clickable-row" onclick="window.location.href='<?= url('/profile?username=' . urlencode($u['username'])) ?>';">
                            <td class="ps-4 text-muted">@<?= e($u['username']) ?></td>
                            <td>
                                <div class="fw-bold">
                                    <a href="<?= url('/profile?username=' . urlencode($u['username'])) ?>" class="text-decoration-none text-reset">
                                        <?= e($u['name']) ?> <?= e($u['last_name'] ?? '') ?>
                                    </a>
                                    <?php if ($is_low_trust && !$u['is_banned']): ?>
                                        <span class="badge bg-danger rounded-pill ms-1 text-white" style="font-size: 0.7rem;" title="Questo utente ha un trust score inferiore a 25 ed è segnalato all'amministratore per un eventuale ban.">⚠️ SEGNALATO</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted"><?= e($u['email']) ?></div>
                            </td>
                            <td>
                                <span class="text-capitalize"><?= e($u['preferred_role'] ?? '—') ?></span>
                            </td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <span
                                    class="badge rounded-pill fs-6 px-3 py-2 bg-<?= $u['trust_score'] >= 80 ? 'success' : ($u['trust_score'] >= 50 ? 'warning text-dark' : 'danger') ?>">
                                    <?= e($u['trust_score']) ?>
                                </span>
                                <button type="button" class="btn btn-link text-info p-0 ms-1" style="font-size: 1.15rem; vertical-align: middle;" 
                                    data-bs-toggle="modal" data-bs-target="#trustHistoryModal<?= e($u['username']) ?>" title="Vedi storico variazioni">
                                    <span class="bi bi-clock-history"></span>
                                </button>
                            </td>
                            <td class="text-center">
                                <?php if ($is_suspect): ?>
                                    <span class="bi bi-exclamation-triangle-fill me-1"></span>
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
                                    <span class="badge bg-warning text-dark"><span class="bi bi-shield-lock me-1"></span>Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-success text-white">Attivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <?php if ($u['username'] !== $_SESSION['user']['username']): ?>
                                    <?php if ($u['is_banned']): ?>
                                        <form action="<?= url('/admin/unban') ?>#users-section" method="POST" class="d-inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                            <input type="hidden" name="user_id" value="<?= e($u['username']) ?>">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold"
                                                onclick="return confirm('Riattivare l\'utente?');"><span
                                                    class="bi bi-unlock-fill me-1"></span>Riattiva</button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill fw-bold me-1" 
                                            data-bs-toggle="modal" data-bs-target="#editTrustModal<?= e($u['username']) ?>">
                                            <span class="bi bi-shield-shaded me-1"></span>Trust
                                        </button>
                                        <form action="<?= url('/admin/ban') ?>#users-section" method="POST" class="d-inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                            <input type="hidden" name="user_id" value="<?= e($u['username']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold"
                                                onclick="return confirm('Bannare questo utente? Non potrà più accedere.');"><span
                                                    class="bi bi-ban me-1"></span>Banna</button>
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

    <?php // Pagination ?>
    <?php if ($totalPagesUsers > 1): ?>
        <div class="card-footer bg-body-tertiary border-top p-3">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    <?php if ($pageUsers <= 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">← Precedente</span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= url('/admin?' . http_build_query(['page' => $pageUsers - 1, 'search' => $search, 'role' => $roleFilter, 'status' => $statusFilter, 'problematic' => $problematicFilter])) ?>#users-section">
                                ← Precedente
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPagesUsers; $i++): ?>
                        <?php if ($i == $pageUsers): ?>
                            <li class="page-item active">
                                <span class="page-link"><?= $i ?></span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link"
                                    href="<?= url('/admin?' . http_build_query(['page' => $i, 'search' => $search, 'role' => $roleFilter, 'status' => $statusFilter, 'problematic' => $problematicFilter])) ?>#users-section">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pageUsers >= $totalPagesUsers): ?>
                        <li class="page-item disabled">
                            <span class="page-link">Prossima →</span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link"
                                href="<?= url('/admin?' . http_build_query(['page' => $pageUsers + 1, 'search' => $search, 'role' => $roleFilter, 'status' => $statusFilter, 'problematic' => $problematicFilter])) ?>#users-section">
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

<!-- Modals section (moved outside the table to fix nested headers table accessibility warning) -->
<?php if (!empty($users)): ?>
    <?php foreach ($users as $u): ?>
        <!-- Trust history modal -->
        <div class="modal fade text-start" id="trustHistoryModal<?= e($u['username']) ?>" tabindex="-1" role="dialog" aria-modal="true" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h2 class="h5 modal-title fw-bold text-info"><span class="bi bi-clock-history me-2"></span>Storico Trust: @<?= e($u['username']) ?></h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                    </div>
                    <div class="modal-body py-3">
                        <?php if (!empty($u['trust_history'])): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th class="text-center">Variazione</th>
                                            <th>Motivazione</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($u['trust_history'] as $log): ?>
                                            <tr>
                                                <td class="text-muted small"><?= $log['created_at']->format('d/m/Y H:i') ?></td>
                                                <td class="text-center">
                                                    <?php if ($log['score_change'] > 0): ?>
                                                        <span class="badge bg-success-subtle text-success-emphasis rounded-pill fw-bold">
                                                            +<?= $log['score_change'] ?>
                                                        </span>
                                                    <?php elseif ($log['score_change'] < 0): ?>
                                                        <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill fw-bold">
                                                            <?= $log['score_change'] ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill fw-bold">
                                                            0
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= e($log['reason']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <span class="bi bi-info-circle fs-3 mb-2 d-block"></span>
                                Nessun record di variazione registrato per questo utente.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($u['username'] !== $_SESSION['user']['username'] && !$u['is_banned']): ?>
            <!-- Edit trust score modal -->
            <div class="modal fade text-start" id="editTrustModal<?= e($u['username']) ?>" tabindex="-1" role="dialog" aria-modal="true" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
                        <div class="modal-header border-bottom-0 pb-0">
                            <h2 class="h5 modal-title fw-bold text-info"><span class="bi bi-shield-shaded me-2"></span>Gestisci Trust Score</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                        </div>
                        <form action="<?= url('/admin/users/update-trust') ?>" method="POST" class="action-form">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="username" value="<?= e($u['username']) ?>">
                            <div class="modal-body py-3">
                                <div class="mb-3">
                                     <label for="trust_score_input<?= e($u['username']) ?>" class="form-label fw-semibold">Nuovo Punteggio Trust (0 - 100)</label>
                                    <input type="number" name="trust_score" id="trust_score_input<?= e($u['username']) ?>" class="form-control rounded-3 bg-body-secondary border-secondary-subtle text-body" 
                                        min="0" max="100" value="<?= e($u['trust_score']) ?>" required>
                                </div>
                                <div class="mb-0">
                                    <label for="trust_reason_input<?= e($u['username']) ?>" class="form-label fw-semibold">Motivazione della Modifica</label>
                                    <textarea name="reason" id="trust_reason_input<?= e($u['username']) ?>" rows="3" class="form-control rounded-3 bg-body-secondary border-secondary-subtle text-body" 
                                        placeholder="Es. Ripristino dopo contestazione meteo o errore di segnalazione..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-info rounded-pill px-4 fw-bold text-white shadow-sm">Salva Modifiche</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
