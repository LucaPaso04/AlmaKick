<?php
// views/matches/partials/show/hero_banner.php
?>
<div class="card border-0 mb-4 rounded-4 overflow-hidden shadow-sm position-relative match-hero-banner">
    <div class="card-body p-4 p-md-5 text-white position-relative z-1">
        <div class="d-flex align-items-center mb-4 flex-wrap gap-2">
            <?php
            $backUrl = url('/');
            if (!empty($from)) {
                // Se $from contiene parametri di query o è un percorso relativo locale
                if (strpos($from, '/') === 0 || strpos($from, 'matches') === 0 || strpos($from, 'admin') === 0 || strpos($from, 'profile') === 0) {
                    $backUrl = url($from);
                } elseif ($from === 'admin') {
                    $backUrl = url('/admin');
                } elseif ($from === 'profile') {
                    $backUrl = url('/profile');
                } elseif ($from === 'matches') {
                    $backUrl = url('/matches');
                } elseif ($from === 'explore') {
                    $backUrl = url('/matches?tab=explore');
                }
            }
            ?>
            <a href="<?= $backUrl ?>"
                class="btn btn-light rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center hover-scale match-show-avatar-small"
                aria-label="Torna indietro">
                <span class="bi bi-arrow-left" aria-hidden="true"></span>
            </a>

            <span class="badge bg-light text-dark bg-opacity-75 fs-6 rounded-pill backdrop-blur shadow-sm" role="img"
                aria-label="Data e ora della partita">
                <span class="bi bi-calendar-event me-1" aria-hidden="true"></span>
                <?= date('d/m/Y H:i', strtotime($match['date'] . ' ' . $match['time'])) ?>
            </span>

            <?php if ($match['status'] === 'cancelled'): ?>
                <span class="badge bg-danger rounded-pill fs-6 text-uppercase shadow-sm" role="img"
                    aria-label="Stato partita: Annullata">Annullata</span>
            <?php elseif ($match['status'] === 'finished'): ?>
                <span class="badge bg-dark rounded-pill fs-6 shadow-sm" role="img" aria-label="Stato partita: Conclusa">🏁
                    Conclusa</span>
            <?php elseif ($match['status'] === 'full'): ?>
                <span class="badge bg-success rounded-pill fs-6 shadow-sm" role="img"
                    aria-label="Stato partita: Completa">Completa</span>
            <?php else: ?>
                <span class="badge bg-white text-primary rounded-pill fs-6 text-uppercase fw-bold shadow-sm" role="img"
                    aria-label="Formato partita"><?= e($match['format']) ?></span>
            <?php endif; ?>
        </div>

        <h1 class="display-5 fw-bolder mb-2 text-shadow" id="match-location-title">
            <?= e($match['location']) ?>
        </h1>
        <p class="fs-5 mb-0 opacity-75">
            <span class="bi bi-person-circle me-2" aria-hidden="true"></span>Organizzata da
            <a href="<?= url('/profile?username=' . urlencode($match['host_username'])) ?>"
                class="text-white text-decoration-underline fw-bold rounded px-1 focus-ring"
                aria-label="Profilo dell'organizzatore <?= e($match['host_name']) ?>">
                <?= e($match['host_name']) ?> (@<?= e($match['host_username']) ?>)
            </a>
        </p>
    </div>
    <!-- Decorative circle -->
    <div class="position-absolute rounded-circle match-hero-circle" aria-hidden="true"></div>
</div>