<h2 class="fw-bold mb-4">Partite Disponibili</h2>

<?php if (empty($matches)): ?>
    <div class="glass-panel p-5 text-center mt-4 text-secondary-custom">
        Nessuna partita programmata al momento. 
        <?php if (isset($_SESSION['user'])): ?>
            <a href="<?= url('/matches/create') ?>" class="text-accent text-decoration-none">Creane una tu!</a>
        <?php else: ?>
            <a href="<?= url('/login') ?>" class="text-accent text-decoration-none">Accedi per crearne una.</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row g-4 mt-2">
        <?php foreach ($matches as $match): ?>
            <div class="col-12 col-md-6 col-lg-4 d-flex">
                <div class="glass-panel match-card w-100 d-flex flex-column p-4">
                    <div class="match-header d-flex justify-content-between align-items-center mb-3">
                        <span class="match-format"><?= e($match['format']) ?></span>
                        <span class="status-badge status-<?= e(strtolower($match['status'])) ?>"><?= e($match['status']) ?></span>
                    </div>
                    <div class="mb-4">
                        <h3 class="h5 mb-2"><?= e($match['location']) ?></h3>
                        <p class="small text-secondary-custom mb-0">
                            Data: <?= e($match['date']) ?> ore <?= e($match['time']) ?>
                        </p>
                    </div>
                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-secondary-custom">Host: <?= e($match['host_name']) ?></span>
                        <a href="<?= url('/matches/' . e($match['id'])) ?>" class="btn btn-outline py-1 px-3 btn-sm">Dettagli</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
