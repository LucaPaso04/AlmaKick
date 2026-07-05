<?php
// Determine which tab should be active by default based on search filters or tab parameter
$hasFilters = !empty($_GET['location']) || !empty($_GET['date_from']) || !empty($_GET['date_to']) || !empty($_GET['format']) || (!empty($_GET['filter']) && $_GET['filter'] !== 'all') || !empty($_GET['only_friends']) || !empty($_GET['exclude_my_matches']);
$activeTab = $_GET['tab'] ?? ($hasFilters ? 'explore' : 'bacheca');

$username = $_SESSION['user']['username'] ?? null;
$friendHostUsernames = [];
if ($username) {
    $matchModel = new \App\Models\SoccerMatch();
    $friendHostUsernames = $matchModel->getFriendUsernames($username);
}

// $myMatches is loaded cleanly and fully from the controller to avoid issues with pagination or search filters
?>



<!-- Hero Section -->
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

<!-- Tabs switcher -->
<ul class="nav nav-pills nav-fill bg-body shadow-sm rounded-4 p-2 mb-4 border" id="homeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'bacheca' ? 'active' : '' ?> rounded-pill fw-bold py-2"
            id="bacheca-tab" data-bs-toggle="pill" data-bs-target="#bacheca" type="button" role="tab"
            aria-controls="bacheca" aria-selected="<?= $activeTab === 'bacheca' ? 'true' : 'false' ?>">
            <span class="bi bi-speedometer2 me-2"></span>La mia Bacheca
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'explore' ? 'active' : '' ?> rounded-pill fw-bold py-2"
            id="explore-tab" data-bs-toggle="pill" data-bs-target="#explore" type="button" role="tab"
            aria-controls="explore" aria-selected="<?= $activeTab === 'explore' ? 'true' : 'false' ?>">
            <span class="bi bi-search me-2"></span>Trova Partite
        </button>
    </li>
</ul>

<!-- Tabs content -->
<div class="tab-content" id="homeTabsContent">

    <!-- Tab: My Board -->
    <div class="tab-pane fade <?= $activeTab === 'bacheca' ? 'show active' : '' ?>" id="bacheca" role="tabpanel"
        aria-labelledby="bacheca-tab" tabindex="0">

        <!-- My Upcoming Matches -->
        <div class="mb-4">
            <h2 class="h5 fw-bold mb-3"><span class="bi bi-calendar-check text-primary me-2"></span>Le tue Prossime Partite</h2>

            <?php if (!$username): ?>
                <div
                    class="alert border shadow-sm text-center py-5 rounded-4 d-flex flex-column align-items-center justify-content-center matches-empty-state">
                    <div
                        class="border rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3 matches-empty-icon">
                        <span class="bi bi-person-exclamation fs-2"></span>
                    </div>
                    <h2 class="h5 fw-bold mb-2">Accedi per vedere le tue partite</h2>
                    <p class="text-secondary-custom small mb-4 matches-empty-text-wrap">Accedi o registrati per visualizzare
                        le partite a cui partecipi o che organizzi.</p>
                    <a href="<?= url('/login') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Accedi</a>
                </div>
            <?php elseif (empty($myMatches)): ?>
                <div
                    class="alert border shadow-sm text-center py-5 rounded-4 d-flex flex-column align-items-center justify-content-center matches-empty-state">
                    <div
                        class="border rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3 matches-empty-icon">
                        <span class="bi bi-calendar-x fs-2"></span>
                    </div>
                    <h2 class="h5 fw-bold mb-2">Nessuna partita in programma</h2>
                    <p class="text-secondary-custom small mb-4 matches-empty-text-wrap">Non sei iscritto a nessuna partita e
                        non ne stai organizzando. Scopri le partite disponibili della community per iniziare a giocare!</p>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold"
                        onclick="switchToExploreTab()">
                        Esplora Partite
                    </button>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($myMatches as $p): ?>
                        <?php require VIEW_PATH . '/matches/partials/match_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Tab: Find Matches -->
    <div class="tab-pane fade <?= $activeTab === 'explore' ? 'show active' : '' ?>" id="explore" role="tabpanel"
        aria-labelledby="explore-tab" tabindex="0">

        <!-- Filters section -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold mb-0"><span class="bi bi-funnel text-primary me-2"></span>Filtra i Risultati</h2>
                <button class="btn btn-sm btn-outline-primary d-md-none rounded-pill" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false"
                    aria-controls="filterCollapse">
                    <span class="bi bi-funnel"></span> Filtri
                </button>
            </div>
            <div class="collapse d-md-block" id="filterCollapse">
                <form action="<?= url('/matches') ?>" method="GET"
                    class="filter-form p-3 rounded-4 shadow-sm d-flex flex-wrap align-items-center gap-3">
                    <input type="hidden" name="tab" value="explore">
                    <!-- Location search -->
                    <div style="flex: 2 1 200px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0"><span class="bi bi-search text-muted"></span></span>
                            <label for="filter-location" class="visually-hidden">Cerca città o campo</label>
                            <input type="text" id="filter-location" name="location" class="form-control border-start-0 ps-2"
                                placeholder="Cerca città o campo..." value="<?= e($_GET['location'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Date from -->
                    <div style="flex: 1 1 140px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0 bg-transparent text-muted small">Dal</span>
                            <input type="date" id="filter-date-from" name="date_from" class="form-control border-start-0 ps-1"
                                value="<?= e($_GET['date_from'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Date to -->
                    <div style="flex: 1 1 140px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0 bg-transparent text-muted small">Al</span>
                            <input type="date" id="filter-date-to" name="date_to" class="form-control border-start-0 ps-1"
                                value="<?= e($_GET['date_to'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Format -->
                    <div style="flex: 1 1 120px;">
                        <label for="filter-format" class="visually-hidden">Filtra per formato</label>
                        <select id="filter-format" name="format" class="form-select form-select-sm">
                            <option value="">Tutti i formati</option>
                            <option value="5vs5" <?= (($_GET['format'] ?? '') == '5vs5') ? 'selected' : '' ?>>5 vs 5
                            </option>
                            <option value="7vs7" <?= (($_GET['format'] ?? '') == '7vs7') ? 'selected' : '' ?>>7 vs 7
                            </option>
                            <option value="8vs8" <?= (($_GET['format'] ?? '') == '8vs8') ? 'selected' : '' ?>>8 vs 8
                            </option>
                            <option value="11vs11" <?= (($_GET['format'] ?? '') == '11vs11') ? 'selected' : '' ?>>11 vs 11
                            </option>
                        </select>
                    </div>


                    <!-- Friends matches -->
                    <div class="d-flex align-items-center py-1">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="only_friends"
                                name="only_friends" value="1" <?= !empty($_GET['only_friends']) ? 'checked' : '' ?>>
                            <label class="form-check-label small ms-1 text-nowrap" for="only_friends">Partite di amici</label>
                        </div>
                    </div>

                    <?php if ($username): ?>
                    <!-- Hide my matches -->
                    <div class="d-flex align-items-center py-1">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="exclude_my_matches"
                                name="exclude_my_matches" value="1" <?= !empty($_GET['exclude_my_matches']) ? 'checked' : '' ?>>
                            <label class="form-check-label small ms-1 text-nowrap" for="exclude_my_matches">Nascondi iscritte</label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Reset button -->
                    <div id="resetButtonContainer" class="ms-md-auto d-flex align-items-center justify-content-center">
                        <?php if ($hasFilters): ?>
                            <a href="<?= url('/matches?tab=explore') ?>"
                                class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; padding: 0;" title="Resetta Filtri">
                                <span class="bi bi-arrow-counterclockwise"></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Matches list -->
        <div id="matchesContainer" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            <?php if (!empty($matches)): ?>
                <?php foreach ($matches as $p): ?>
                    <?php $fromTab = 'explore'; ?>
                    <?php require VIEW_PATH . '/matches/partials/match_card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div
                        class="alert border shadow-sm text-center py-5 rounded-4 d-flex flex-column align-items-center justify-content-center matches-empty-state">
                        <div
                            class="border rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3 matches-empty-icon">
                            <span class="bi bi-calendar-x fs-2"></span>
                        </div>
                        <h2 class="h5 fw-bold">Nessuna partita trovata</h2>
                        <p class="text-body-secondary small mb-4 matches-empty-text-wrap-sm">Nessuna partita soddisfa i
                            criteri di ricerca impostati. Prova a modificare i filtri o organizza tu una nuova partita!</p>
                            <a href="<?= url('/matches/create') ?>"
                                class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Organizza Ora</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div id="paginationContainer">
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <nav aria-label="Navigazione pagine">
                    <ul class="pagination pagination-sm justify-content-center mt-4 mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $page - 1 ?>" aria-label="Pagina precedente"><span
                                    class="bi bi-chevron-left"></span></a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $page + 1 ?>" aria-label="Pagina successiva"><span
                                    class="bi bi-chevron-right"></span></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

    </div>

</div>

    <div class="fab-container">
        <a href="<?= url('/matches/create') ?>" class="btn btn-primary fab-btn shadow-lg" title="Crea Nuova Partita"
            aria-label="Crea Nuova Partita">
            <span class="bi bi-plus-lg fs-2"></span>
        </a>
    </div>

<script src="<?= url('/js/matches.js') ?>" defer></script>