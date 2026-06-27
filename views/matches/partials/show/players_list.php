<?php
// views/matches/partials/show/players_list.php

$activePlayers = [];
$benchPlayers = [];
foreach ($registrations as $reg) {
    if ($reg['status'] === 'cancelled') continue;
    if ($reg['status'] === 'waitlist') {
        $benchPlayers[] = $reg;
    } else {
        $activePlayers[] = $reg;
    }
}
?>

<!-- Match Enrollment Progress Bar -->
<div class="card shadow-sm border-0 mb-4 rounded-4 bg-body">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold fs-6"><i class="bi bi-people-fill text-primary me-2"></i>Posti Occupati</span>
            <span class="fw-bold fs-6"><span class="text-primary"><?= $occupied_seats ?></span> / <?= $match['max_players'] ?></span>
        </div>
        <div class="progress rounded-pill shadow-sm bg-body-tertiary" style="height: 10px;">
            <?php 
                $percent = min(100, ($occupied_seats / $match['max_players']) * 100);
                $progressColor = $percent >= 100 ? 'bg-danger' : ($percent >= 80 ? 'bg-warning' : 'bg-success');
            ?>
            <div class="progress-bar <?= $progressColor ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="<?= $occupied_seats ?>" aria-valuemin="0" aria-valuemax="<?= $match['max_players'] ?>"></div>
        </div>
        <?php if($occupied_seats >= $match['max_players']): ?>
            <div class="text-danger small mt-2 d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-1"></i> Posti principali completati. I prossimi iscritti andranno in lista d'attesa (panchina).</div>
        <?php else: ?>
            <div class="text-muted small mt-2 d-flex align-items-center"><i class="bi bi-info-circle me-1"></i> Rimangono ancora <?= ($match['max_players'] - $occupied_seats) ?> posti disponibili per scendere in campo!</div>
        <?php endif; ?>
    </div>
</div>

<!-- Titolari (Registered Active) -->
<h2 class="fw-bolder mb-3 mt-4 px-2 fs-5"><span class="bi bi-check-circle-fill text-success me-2"></span>Giocatori Iscritti (<?= count($activePlayers) ?>)</h2>
<div class="list-group shadow-sm rounded-4 border-0 mb-4" role="list" aria-label="Lista dei giocatori iscritti">
    <?php if (!empty($activePlayers)): ?>
        <?php foreach ($activePlayers as $reg): 
            $regAvatarUrl = null;
            if ($reg['avatar']) {
                if (strpos($reg['avatar'], 'http://') === 0 || strpos($reg['avatar'], 'https://') === 0) {
                    $regAvatarUrl = $reg['avatar'];
                } elseif (strpos($reg['avatar'], 'uploads/') === 0) {
                    $regAvatarUrl = url('/' . $reg['avatar']);
                } else {
                    $regAvatarUrl = url('/uploads/' . ltrim($reg['avatar'], '/'));
                }
            }
            $isPlayerHost = ($reg['username'] === $match['host_username']);
            ?>
            <div class="list-group-item border-0 border-bottom d-flex justify-content-between align-items-center py-3 bg-body hover-scale transition-all" role="listitem">
                <div class="d-flex align-items-center">
                    <div class="bg-<?= $reg['team'] === 'home' ? 'danger' : ($reg['team'] === 'away' ? 'primary' : 'secondary') ?> text-white rounded-circle d-flex justify-content-center align-items-center me-3 fs-5 fw-bold shadow-sm border border-2 border-white match-show-avatar"
                        style="<?= $regAvatarUrl ? 'background-image: url(' . htmlspecialchars($regAvatarUrl) . '); background-size: cover; background-position: center;' : '' ?>" aria-hidden="true">
                        <?php if(!$regAvatarUrl): ?>
                            <?= strtoupper(substr($reg['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold d-flex align-items-center gap-2 flex-wrap fs-6">
                            <a href="<?= url('/profile?username=' . urlencode($reg['username'])) ?>" class="text-decoration-none text-body focus-ring rounded" aria-label="Profilo di <?= e($reg['name']) ?>">
                                <?= e($reg['name']) ?> (@<?= e($reg['username']) ?>)
                            </a>
                            <?php if($reg['has_guest']): ?>
                                <span class="badge bg-info text-dark shadow-sm d-inline-flex align-items-center"><i class="bi bi-person-plus-fill me-1"></i>+1 Ospite</span>
                            <?php endif; ?>
                            <?php if($isPlayerHost): ?>
                                <span class="badge bg-secondary shadow-sm"><span class="bi bi-star-fill text-warning me-1" aria-hidden="true"></span>Host</span>
                            <?php endif; ?>
                            <?php if($reg['team']): ?>
                                <span class="badge bg-<?= $reg['team'] === 'home' ? 'danger' : 'primary' ?> shadow-sm"><?= ucfirst($reg['team']) ?></span>
                            <?php endif; ?>
                            <?php if($match['status'] === 'finished' && $reg['goals_scored'] > 0): ?>
                                <span class="badge bg-success shadow-sm" role="img" aria-label="Ha segnato <?= $reg['goals_scored'] ?> gol">⚽ <?= $reg['goals_scored'] ?></span>
                            <?php endif; ?>
                        </h3>
                        <small class="text-muted d-flex align-items-center gap-1 mt-1">
                            <span class="bi bi-person-badge" aria-hidden="true"></span> 
                            <?= e($reg['preferred_role'] ?: 'Ruolo non specificato') ?>
                        </small>
                    </div>
                </div>
                <div class="text-end d-flex flex-column align-items-end">
                    <span class="badge bg-<?= $reg['trust_score'] >= 80 ? 'success' : ($reg['trust_score'] >= 50 ? 'warning' : 'danger') ?> rounded-pill fs-6 px-3 py-1 shadow-sm mb-1" role="img" aria-label="Trust Score: <?= $reg['trust_score'] ?>">
                        <span class="bi bi-shield-check me-1" aria-hidden="true"></span><?= $reg['trust_score'] ?>
                    </span>
                    <small class="text-muted fw-bold">
                        €<?= number_format($current_quote * ($reg['has_guest'] ? 2 : 1), 2) ?>
                    </small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="list-group-item text-center text-muted py-4 border-0 rounded-4">
            Nessun titolare iscritto ancora.
        </div>
    <?php endif; ?>
</div>

<!-- Panchinari (Waitlist) -->
<h2 class="fw-bolder mb-3 mt-4 px-2 fs-5 text-warning"><span class="bi bi-hourglass-split me-2"></span>In Panchina (Lista d'Attesa) (<?= count($benchPlayers) ?>)</h2>
<div class="list-group shadow-sm rounded-4 border-0 mb-5" role="list" aria-label="Lista dei giocatori in panchina">
    <?php if (!empty($benchPlayers)): ?>
        <?php foreach ($benchPlayers as $reg): 
            $regAvatarUrl = null;
            if ($reg['avatar']) {
                if (strpos($reg['avatar'], 'http://') === 0 || strpos($reg['avatar'], 'https://') === 0) {
                    $regAvatarUrl = $reg['avatar'];
                } elseif (strpos($reg['avatar'], 'uploads/') === 0) {
                    $regAvatarUrl = url('/' . $reg['avatar']);
                } else {
                    $regAvatarUrl = url('/uploads/' . ltrim($reg['avatar'], '/'));
                }
            }
            ?>
            <div class="list-group-item border-0 border-bottom d-flex justify-content-between align-items-center py-3 bg-body opacity-75 hover-scale transition-all" role="listitem">
                <div class="d-flex align-items-center">
                    <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3 fs-5 fw-bold shadow-sm border border-2 border-white match-show-avatar"
                        style="<?= $regAvatarUrl ? 'background-image: url(' . htmlspecialchars($regAvatarUrl) . '); background-size: cover; background-position: center;' : '' ?>" aria-hidden="true">
                        <?php if(!$regAvatarUrl): ?>
                            <?= strtoupper(substr($reg['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold d-flex align-items-center gap-2 flex-wrap fs-6 text-warning">
                            <a href="<?= url('/profile?username=' . urlencode($reg['username'])) ?>" class="text-decoration-none text-warning focus-ring rounded" aria-label="Profilo di <?= e($reg['name']) ?>">
                                <?= e($reg['name']) ?> (@<?= e($reg['username']) ?>)
                            </a>
                            <?php if($reg['has_guest']): ?>
                                <span class="badge bg-info text-dark shadow-sm d-inline-flex align-items-center"><i class="bi bi-person-plus-fill me-1"></i>+1 Ospite</span>
                            <?php endif; ?>
                            <span class="badge bg-warning text-dark shadow-sm">In Attesa</span>
                        </h3>
                        <small class="text-muted d-flex align-items-center gap-1 mt-1">
                            <span class="bi bi-person-badge" aria-hidden="true"></span> 
                            <?= e($reg['preferred_role'] ?: 'Ruolo non specificato') ?>
                        </small>
                    </div>
                </div>
                <div class="text-end d-flex flex-column align-items-end">
                    <span class="badge bg-<?= $reg['trust_score'] >= 80 ? 'success' : ($reg['trust_score'] >= 50 ? 'warning' : 'danger') ?> rounded-pill fs-6 px-3 py-1 shadow-sm mb-1" role="img" aria-label="Trust Score: <?= $reg['trust_score'] ?>">
                        <span class="bi bi-shield-check me-1" aria-hidden="true"></span><?= $reg['trust_score'] ?>
                    </span>
                    <small class="text-muted fw-bold">
                        €<?= number_format($current_quote * ($reg['has_guest'] ? 2 : 1), 2) ?>
                    </small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="list-group-item text-center text-muted py-4 border-0 rounded-4 bg-body bg-opacity-50">
            <span class="small">La panchina è vuota. Nessun giocatore è in lista d'attesa.</span>
        </div>
    <?php endif; ?>
</div>
