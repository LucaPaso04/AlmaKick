<?php
// Determine which tab should be active by default based on search filters or tab parameter
$hasFilters = !empty($_GET['location']) || !empty($_GET['date']) || !empty($_GET['format']) || (!empty($_GET['filter']) && $_GET['filter'] !== 'all') || !empty($_GET['hide_full']);
$activeTab = $_GET['tab'] ?? ($hasFilters ? 'explore' : 'bacheca');

$userId = $_SESSION['user']['id'] ?? null;
$friendHostIds = [];
if ($userId) {
    $matchModel = new \App\Models\SoccerMatch();
    $friendHostIds = $matchModel->getFriendIds((int)$userId);
}

// Filter the matches to find only "My Matches" (where user is host or registered)
$myMatches = [];
if ($userId && is_array($matches)) {
    $myMatches = array_filter($matches, function($p) use ($userId) {
        $isHost = isset($p['host_id']) && (int)$p['host_id'] === (int)$userId;
        $isRegistered = false;
        if (isset($p['registrations']) && is_array($p['registrations'])) {
            foreach ($p['registrations'] as $reg) {
                if (isset($reg['user_id']) && (int)$reg['user_id'] === (int)$userId && in_array($reg['status'] ?? '', ['registered', 'waitlist'])) {
                    $isRegistered = true;
                    break;
                }
            }
        }
        return $isHost || $isRegistered;
    });
}

$hasPendingActions = (!empty($matchesToReport)) || (!empty($matchesToVote));
?>



<!-- HERO SECTION -->
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h1 class="h3 fw-bold mb-1">
            <?php if (isset($_SESSION['user'])): ?>
                Bentornato, <?= e(explode(' ', $_SESSION['user']['name'])[0]) ?>! ⚽
            <?php else: ?>
                Benvenuto su AlmaKick! ⚽
            <?php endif; ?>
        </h1>
        <p class="text-secondary-custom mb-0">Ecco la tua bacheca e le partite disponibili.</p>
    </div>
</div>

<!-- TABS SYSTEM Pill switcher -->
<ul class="nav nav-pills nav-fill bg-body shadow-sm rounded-4 p-2 mb-4 border" id="homeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'bacheca' ? 'active' : '' ?> rounded-pill fw-bold py-2" id="bacheca-tab" data-bs-toggle="pill"
            data-bs-target="#bacheca" type="button" role="tab" aria-controls="bacheca" aria-selected="<?= $activeTab === 'bacheca' ? 'true' : 'false' ?>">
            <i class="bi bi-speedometer2 me-2"></i>La mia Bacheca
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'explore' ? 'active' : '' ?> rounded-pill fw-bold py-2" id="explore-tab" data-bs-toggle="pill"
            data-bs-target="#explore" type="button" role="tab" aria-controls="explore" aria-selected="<?= $activeTab === 'explore' ? 'true' : 'false' ?>">
            <i class="bi bi-search me-2"></i>Trova Partite
        </button>
    </li>
</ul>

<!-- TABS CONTENT -->
<div class="tab-content" id="homeTabsContent">
    
    <!-- TAB 1: LA MIA BACHECA -->
    <div class="tab-pane fade <?= $activeTab === 'bacheca' ? 'show active' : '' ?>" id="bacheca" role="tabpanel" aria-labelledby="bacheca-tab" tabindex="0">
        
        <!-- ACTION CENTER (Notifiche urgenti / refertazione / votazione) -->
        <?php if($hasPendingActions): ?>
            <div class="mb-4">
                <h5 class="fw-bold mb-3 text-warning"><i class="bi bi-exclamation-circle-fill me-2"></i>Azioni richieste</h5>
                
                <!-- Da refertare -->
                <?php if(!empty($matchesToReport)): ?>
                    <?php foreach($matchesToReport as $mr): ?>
                    <div class="alert alert-success d-flex align-items-center justify-content-between py-3 mb-2 shadow-sm border border-success border-opacity-25 rounded-4" role="alert">
                        <div class="text-truncate me-3 text-success-emphasis">
                            <i class="bi bi-clipboard-data-fill me-2 fs-5"></i>
                            <span class="fw-bold me-2">Compila Tabellino:</span>
                            <span class="small text-body-secondary"><?= e(date('d/m/Y', strtotime($mr['date']))) ?> &bull; <?= e(strlen($mr['location']) > 25 ? substr($mr['location'], 0, 25) . '...' : $mr['location']) ?></span>
                        </div>
                        <a href="<?= url('/matches/' . $mr['id']) ?>" class="btn btn-sm btn-success rounded-pill fw-bold px-3 text-nowrap">Gestisci</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Da votare -->
                <?php if(!empty($matchesToVote)): ?>
                    <?php foreach($matchesToVote as $mv): ?>
                    <div class="alert alert-warning d-flex align-items-center justify-content-between py-3 mb-2 shadow-sm border border-warning border-opacity-25 rounded-4 text-warning-emphasis" role="alert">
                        <div class="text-truncate me-3">
                            <i class="bi bi-star-fill me-2 fs-5 text-warning"></i>
                            <span class="fw-bold me-2">Vota Compagni:</span>
                            <span class="small text-body-secondary"><?= e(date('d/m/Y', strtotime($mv['date']))) ?> &bull; <?= e(strlen($mv['location']) > 25 ? substr($mv['location'], 0, 25) . '...' : $mv['location']) ?></span>
                        </div>
                        <a href="<?= url('/matches/' . $mv['id']) ?>" class="btn btn-sm btn-warning rounded-pill fw-bold px-3 text-dark text-nowrap">Vota</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Le mie Partite in Programma -->
        <div class="mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-calendar-check text-primary me-2"></i>Le tue Prossime Partite</h5>
            
            <?php if(!$userId): ?>
                <div class="alert border shadow-sm text-center py-5 rounded-4 d-flex flex-column align-items-center justify-content-center matches-empty-state">
                    <div class="border rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3 matches-empty-icon">
                        <i class="bi bi-person-exclamation fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Accedi per vedere le tue partite</h5>
                    <p class="text-secondary-custom small mb-4 matches-empty-text-wrap">Accedi o registrati per visualizzare le partite a cui partecipi o che organizzi.</p>
                    <a href="<?= url('/login') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Accedi</a>
                </div>
            <?php elseif(empty($myMatches)): ?>
                <div class="alert border shadow-sm text-center py-5 rounded-4 d-flex flex-column align-items-center justify-content-center matches-empty-state">
                    <div class="border rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3 matches-empty-icon">
                        <i class="bi bi-calendar-x fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Nessuna partita in programma</h5>
                    <p class="text-secondary-custom small mb-4 matches-empty-text-wrap">Non sei iscritto a nessuna partita e non ne stai organizzando. Scopri le partite disponibili della community per iniziare a giocare!</p>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" onclick="switchToExploreTab()">
                        Esplora Partite
                    </button>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach($myMatches as $p): ?>
                        <?php require VIEW_PATH . '/matches/partials/match_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- TAB 2: TROVA PARTITE (ESPLORA) -->
    <div class="tab-pane fade <?= $activeTab === 'explore' ? 'show active' : '' ?>" id="explore" role="tabpanel" aria-labelledby="explore-tab" tabindex="0">
        
        <!-- MATCH DISCOVERY SECTION CON FILTRI COMPATTI -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel text-primary me-2"></i>Filtra i Risultati</h5>
                <button class="btn btn-sm btn-outline-primary d-md-none rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <i class="bi bi-funnel"></i> Filtri
                </button>
            </div>
            <div class="collapse d-md-block" id="filterCollapse">
                <form action="<?= url('/matches') ?>" method="GET" class="filter-form p-3 rounded-4 shadow-sm d-flex flex-wrap align-items-center gap-3">
                    <input type="hidden" name="tab" value="explore">
                    <!-- Ricerca Luogo -->
                    <div style="flex: 2 1 200px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="location" class="form-control border-start-0 ps-0" placeholder="Cerca città o campo..." value="<?= e($_GET['location'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <!-- Data -->
                    <div style="flex: 1 1 120px;">
                        <input type="date" name="date" class="form-control form-control-sm" value="<?= e($_GET['date'] ?? '') ?>" aria-label="Data">
                    </div>

                    <!-- Formato -->
                    <div style="flex: 1 1 120px;">
                        <select name="format" class="form-select form-select-sm">
                            <option value="">Tutti i formati</option>
                            <option value="5vs5" <?= (($_GET['format'] ?? '') == '5vs5') ? 'selected' : '' ?>>5 vs 5</option>
                            <option value="7vs7" <?= (($_GET['format'] ?? '') == '7vs7') ? 'selected' : '' ?>>7 vs 7</option>
                            <option value="8vs8" <?= (($_GET['format'] ?? '') == '8vs8') ? 'selected' : '' ?>>8 vs 8</option>
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div style="flex: 1 1 120px;">
                        <select name="filter" class="form-select form-select-sm">
                            <option value="all" <?= (($_GET['filter'] ?? 'all') == 'all') ? 'selected' : '' ?>>Tutte le partite</option>
                            <option value="friends" <?= (($_GET['filter'] ?? '') == 'friends') ? 'selected' : '' ?>>Partite degli amici</option>
                            <option value="mine" <?= (($_GET['filter'] ?? '') == 'mine') ? 'selected' : '' ?>>Le mie partite</option>
                        </select>
                    </div>

                    <!-- Solo Aperte -->
                    <div class="d-flex align-items-center py-1">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="hide_full" name="hide_full" value="1" <?= !empty($_GET['hide_full']) ? 'checked' : '' ?>>
                            <label class="form-check-label small ms-1 text-nowrap" for="hide_full">Solo aperte</label>
                        </div>
                    </div>

                    <!-- Pulsante Resetta -->
                    <div id="resetButtonContainer" class="ms-md-auto d-flex align-items-center justify-content-center">
                        <?php if($hasFilters): ?>
                            <a href="<?= url('/matches?tab=explore') ?>" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Resetta Filtri">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista Partite -->
        <div id="matchesContainer" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            <?php if(!empty($matches)): ?>
                <?php foreach($matches as $p): ?>
                    <?php require VIEW_PATH . '/matches/partials/match_card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert border shadow-sm text-center py-5 rounded-4 d-flex flex-column align-items-center justify-content-center matches-empty-state">
                        <div class="border rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3 matches-empty-icon">
                            <i class="bi bi-calendar-x fs-2"></i>
                        </div>
                        <h5 class="fw-bold">Nessuna partita trovata</h5>
                        <p class="text-body-secondary small mb-4 matches-empty-text-wrap-sm">Nessuna partita soddisfa i criteri di ricerca impostati. Prova a modificare i filtri o organizza tu una nuova partita!</p>
                        <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'super_admin'): ?>
                            <a href="<?= url('/matches/create') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Organizza Ora</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div id="paginationContainer">
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <nav aria-label="Navigazione pagine">
                    <ul class="pagination pagination-sm justify-content-center mt-4 mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $page - 1 ?>"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $page + 1 ?>"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'super_admin'): ?>
<div class="fab-container">
    <a href="<?= url('/matches/create') ?>" class="btn btn-primary fab-btn shadow-lg" title="Crea Nuova Partita" aria-label="Crea Nuova Partita">
        <i class="bi bi-plus-lg fs-2"></i>
    </a>
</div>
<?php endif; ?>

<script src="<?= url('/js/matches.js') ?>" defer></script>
