<div class="row justify-content-center my-4">
    <div class="col-12 col-lg-9">
        <div class="glass-panel p-4 p-md-5">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center border-bottom pb-4 mb-4 gap-3">
                <div>
                    <span class="match-format px-3 py-2 fs-6"><?= e($match['format']) ?></span>
                    <h1 class="h2 fw-bold mt-3 mb-0"><?= e($match['location']) ?></h1>
                </div>
                <div class="text-sm-end">
                    <span class="status-badge status-<?= e(strtolower($match['status'])) ?>" style="font-size: 0.9rem;"><?= strtoupper(e($match['status'])) ?></span>
                    <p class="small text-secondary-custom mb-0 mt-2">Organizzato da: <?= e($match['host_name']) ?></p>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <h3 class="h5 text-accent mb-3">Dettagli Partita</h3>
                    <ul class="list-unstyled text-secondary-custom d-flex flex-column gap-2 mb-0">
                        <li><strong>Data:</strong> <?= e($match['date']) ?></li>
                        <li><strong>Ora:</strong> <?= e($match['time']) ?></li>
                        <li><strong>Costo Campo:</strong> <?= e($match['total_cost'] ?? '0.00') ?> €</li>
                        <li><strong>Visibilità:</strong> <?= e($match['visibility']) ?></li>
                    </ul>
                </div>
                <div class="col-12 col-md-6">
                    <h3 class="h5 text-accent mb-3">Giocatori Iscritti</h3>
                    <p class="fs-4 fw-bold mb-3 text-secondary-custom">0 / <?= e($match['max_players']) ?></p>
                    <div>
                        <?php if (isset($_SESSION['user'])): ?>
                            <button class="btn btn-primary">Partecipa alla Partita</button>
                        <?php else: ?>
                            <a href="<?= url('/login') ?>" class="btn btn-outline text-decoration-none">Accedi per partecipare</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="border-top pt-4 text-center">
                <a href="<?= url('/matches') ?>" class="text-secondary-custom text-decoration-none">&larr; Torna alle partite disponibili</a>
            </div>
        </div>
    </div>
</div>
