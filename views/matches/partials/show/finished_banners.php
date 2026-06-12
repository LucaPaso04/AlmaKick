<?php
// views/matches/partials/show/finished_banners.php
?>

<?php if ($match['status'] === 'cancelled'): ?>
    <div class="alert alert-danger mb-4 border-0 rounded-4 shadow-sm d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-octagon-fill fs-3 me-3" aria-hidden="true"></i>
        <div>
            <strong class="d-block mb-1">Partita Annullata</strong>
            <?= e($match['cancellation_reason'] ?: "Nessun motivo specificato.") ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($match['status'] === 'finished' && $match['result_home'] !== null): ?>
    <div class="mb-4 p-4 rounded-4 text-center shadow-sm" style="background: linear-gradient(135deg, #1a1a2e, #16213e); border: 1px solid rgba(255,255,255,0.1);" aria-label="Risultato Finale: Home <?= $match['result_home'] ?> a Away <?= $match['result_away'] ?>" tabindex="0">
        <small class="text-muted text-uppercase fw-bold d-block mb-3 tracking-wide" aria-hidden="true">Risultato Finale</small>
        <div class="d-flex justify-content-center align-items-center gap-4" aria-hidden="true">
            <div class="text-center">
                <span class="badge bg-danger fs-6 mb-2 px-3 shadow-sm rounded-pill">Home</span>
                <div class="display-4 fw-bolder text-white lh-1"><?= $match['result_home'] ?></div>
            </div>
            <span class="fs-1 fw-bold text-white opacity-25">—</span>
            <div class="text-center">
                <span class="badge bg-primary fs-6 mb-2 px-3 shadow-sm rounded-pill">Away</span>
                <div class="display-4 fw-bolder text-white lh-1"><?= $match['result_away'] ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($match['status'] === 'finished' && $match['mvp_assigned'] && $mvp): ?>
    <?php
    $mvpAvatarUrl = null;
    if ($mvp['avatar']) {
        if (strpos($mvp['avatar'], 'http://') === 0 || strpos($mvp['avatar'], 'https://') === 0) {
            $mvpAvatarUrl = $mvp['avatar'];
        } elseif (strpos($mvp['avatar'], 'uploads/') === 0) {
            $mvpAvatarUrl = url('/' . $mvp['avatar']);
        } else {
            $mvpAvatarUrl = url('/uploads/' . ltrim($mvp['avatar'], '/'));
        }
    }
    ?>
    <div class="mb-4 p-4 rounded-4 text-center border border-warning position-relative overflow-hidden shadow-sm hover-scale transition-all mvp-custom-card">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle, rgba(255,193,7,0.15) 0%, transparent 70%); pointer-events: none;" aria-hidden="true"></div>
        <div class="position-relative z-1 d-flex flex-column align-items-center">
            <div class="mb-2 position-relative">
                <?php if($mvpAvatarUrl): ?>
                    <img src="<?= htmlspecialchars($mvpAvatarUrl) ?>" alt="Avatar MVP" class="rounded-circle object-fit-cover border border-3 border-warning shadow mvp-avatar">
                <?php else: ?>
                    <div class="rounded-circle bg-warning text-dark d-flex justify-content-center align-items-center fw-bold border border-3 border-white shadow mvp-avatar" aria-hidden="true">
                        <?= strtoupper(substr($mvp['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center mvp-badge-wrap" aria-hidden="true">
                    <i class="bi bi-trophy-fill text-warning fs-6"></i>
                </div>
            </div>
            <small class="mvp-title text-uppercase fw-bolder d-block mb-1 tracking-wide" aria-hidden="true">MVP della Partita</small>
            <h2 class="fw-bolder mb-0 fs-3">
                <a href="<?= url('/profile?username=' . urlencode($mvp['username'])) ?>" class="text-decoration-none mvp-name-link stretched-link rounded px-1 focus-ring" aria-label="Profilo MVP: <?= e($mvp['name']) ?>">
                    <?= e($mvp['name']) ?>
                </a>
            </h2>
            <small class="mvp-desc fw-medium mt-1">Eletto dai voti dei giocatori</small>
        </div>
    </div>
<?php endif; ?>
