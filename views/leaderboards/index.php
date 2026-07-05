<div class="container-fluid py-2">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <header class="leaderboard-hero text-white text-center py-5 px-3 shadow-lg">
                <h1 class="display-5 fw-bolder mb-2" tabindex="0">
                    <span class="bi bi-trophy-fill text-warning me-2" aria-hidden="true"></span>AlmaKick Leaderboards
                </h1>
                <p class="lead text-white-50 mb-0 fw-medium" tabindex="0">I migliori giocatori di AlmaKick. Gioca, segna e scala le classifiche del campus!</p>
            </header>
        </div>
    </div>

    <!-- Tabs navigation -->
    <nav aria-label="Navigazione classifiche">
        <ul class="nav nav-pills nav-fill bg-body shadow-sm rounded-pill p-2 mb-4 border" id="leaderboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold" id="global-tab" data-bs-toggle="pill" data-bs-target="#global" type="button" role="tab" aria-controls="global" aria-selected="true" aria-label="Mostra Classifica Globale">
                    <span class="bi bi-globe-americas me-2" aria-hidden="true"></span>Classifica Globale
                </button>
            </li>
            <?php if (isset($_SESSION['user'])): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold" id="friends-tab" data-bs-toggle="pill" data-bs-target="#friends" type="button" role="tab" aria-controls="friends" aria-selected="false" aria-label="Mostra Classifica Amici">
                    <span class="bi bi-people-fill me-2" aria-hidden="true"></span>Tra i tuoi Amici
                </button>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Tabs content -->
    <div class="tab-content" id="leaderboardTabsContent" aria-live="polite">
        
        <!-- Tab: Global -->
        <div class="tab-pane fade show active" id="global" role="tabpanel" aria-labelledby="global-tab" tabindex="0">
            <?php 
            $scorers = $topScorers;
            $mvps = $topMVPs;
            $rated = $topRated;
            require VIEW_PATH . '/leaderboards/partials/columns.php'; 
            ?>
        </div>

        <?php if (isset($_SESSION['user'])): ?>
        <!-- Tab: Friends -->
        <div class="tab-pane fade" id="friends" role="tabpanel" aria-labelledby="friends-tab" tabindex="0">
            <?php if ($friendsCount === 0): ?>
                <div class="alert alert-info text-center border-0 shadow-sm rounded-4 py-5 mb-4 d-flex flex-column align-items-center">
                    <div class="bg-body rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3 leaderboards-empty-icon">
                        <span class="bi bi-person-plus fs-1 text-info" aria-hidden="true"></span>
                    </div>
                    <h5 class="fw-bold alert-heading">Non hai ancora aggiunto amici!</h5>
                    <p class="mb-0">Aggiungi amici usando il loro Codice Amico per confrontare le tue statistiche con le loro.</p>
                </div>
            <?php else: ?>
                <?php 
                $scorers = $friendsScorers;
                $mvps = $friendsMVPs;
                $rated = $friendsRated;
                require VIEW_PATH . '/leaderboards/partials/columns.php'; 
                ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>
