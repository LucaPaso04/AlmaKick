<div class="row justify-content-center">
    <div class="col-12 col-lg-9">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h1 class="fw-bolder mb-1"><span class="bi bi-people-fill text-primary me-2"></span>Ricerca Giocatori</h1>
                <p class="text-muted mb-0">Trova nuovi compagni di squadra, consulta i loro profili e invia richieste di
                    amicizia.</p>
            </div>
        </div>

        <!-- Search panel -->
        <div class="card search-panel border-0 mb-4 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/users') ?>" method="GET" id="search-form" class="no-spinner">
                    <div class="row g-3">

                        <!-- Input -->
                        <div class="col-12">
                            <label for="search-input"
                                class="form-label small fw-bold text-muted text-uppercase tracking-wider">Cerca per nome
                                o username</label>
                            <div class="input-group search-input-group">
                                <span class="input-group-text"><span class="bi bi-search"></span></span>
                                <input type="text" id="search-input" name="q" class="form-control search-control py-3"
                                    placeholder="Digita il nome o l'username di un giocatore..."
                                    value="<?= e($q ?? '') ?>" autocomplete="off">
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div id="search-results">
            <?php require VIEW_PATH . '/users/partials/results.php'; ?>
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="<?= url('/js/users-search.js') ?>"></script>