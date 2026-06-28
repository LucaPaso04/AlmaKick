<?php if (empty($q)): ?>
    <!-- Contenitore Cronologia Ricerche (gestito via client-side localStorage) -->
    <div id="search-history-container" class="d-none mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 border-secondary border-opacity-10">
            <h6 class="fw-bold text-muted text-uppercase tracking-wider mb-0 fs-7">Ricerche recenti</h6>
            <button id="clear-all-history" class="btn btn-link btn-sm text-secondary text-decoration-none p-0 small">Cancella tutto</button>
        </div>
        <div id="search-history-list" class="d-flex flex-column"></div>
    </div>

    <!-- Stato Vuoto Iniziale -->
    <div id="empty-search-state" class="text-center py-5">
        <div class="card bg-dark bg-opacity-25 border border-secondary border-opacity-10 rounded-4 p-5 shadow-sm">
            <i class="bi bi-search fs-1 text-primary opacity-75 mb-3 d-block"></i>
            <h4 class="fw-bold mb-1">Cerca Giocatori su AlmaKick</h4>
            <p class="text-muted mb-0">Digita un nome o username nella barra sopra per scoprirne il profilo.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Risultati Ricerca Attiva in formato Lista (Stile Instagram) -->
    <div class="d-flex flex-column" id="search-results-list">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <?php
                    $avatarUrl = null;
                    if (!empty($user['avatar'])) {
                        if (strpos($user['avatar'], 'http://') === 0 || strpos($user['avatar'], 'https://') === 0) {
                            $avatarUrl = $user['avatar'];
                        } elseif (strpos($user['avatar'], 'uploads/') === 0) {
                            $avatarUrl = url('/' . $user['avatar']);
                        } else {
                            $avatarUrl = url('/uploads/' . ltrim($user['avatar'], '/'));
                        }
                    }
                    $monogram = strtoupper(substr($user['name'] ?? $user['username'], 0, 2));
                    $fullName = e($user['name']) . ' ' . e($user['last_name'] ?? '');
                ?>
                <div class="user-row-item d-flex align-items-center justify-content-between p-2 mb-2 rounded-4 instagram-list-item search-result-item" 
                     data-username="<?= e($user['username']) ?>"
                     data-name="<?= e($fullName) ?>"
                     data-avatar="<?= e($avatarUrl ?? '') ?>"
                     data-monogram="<?= e($monogram) ?>">
                    
                    <a href="<?= url('/profile?username=' . urlencode($user['username'])) ?>" class="d-flex align-items-center text-decoration-none text-body flex-grow-1 min-w-0 p-2">
                        <?php if ($avatarUrl): ?>
                            <img src="<?= e($avatarUrl) ?>" alt="Avatar" class="rounded-circle user-search-avatar-mini object-fit-cover me-3 shadow-sm">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold user-search-avatar-mini me-3 shadow-sm fs-5">
                                <?= $monogram ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="min-w-0">
                            <span class="d-block fw-bold text-truncate username-label-list">@<?= e($user['username']) ?></span>
                            <span class="d-block text-muted small text-truncate name-label-list"><?= $fullName ?></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="card bg-dark bg-opacity-25 border border-secondary border-opacity-10 rounded-4 p-5 shadow-sm">
                    <i class="bi bi-emoji-frown fs-1 text-muted opacity-50 mb-3 d-block"></i>
                    <h4 class="fw-bold mb-1">Nessun giocatore trovato</h4>
                    <p class="text-muted mb-0">Prova a modificare i termini di ricerca.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>


