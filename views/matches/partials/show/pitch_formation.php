<?php
// views/matches/partials/show/pitch_formation.php

$home_team = array_filter($registrations, function($r) {
    return $r['team'] === 'home' && $r['status'] === 'registered';
});
$away_team = array_filter($registrations, function($r) {
    return $r['team'] === 'away' && $r['status'] === 'registered';
});
$teams_generated = count($home_team) > 0 || count($away_team) > 0;

$groupPlayersByRole = function($team) {
    $lines = ['GK' => [], 'DEF' => [], 'MID' => [], 'ATT' => []];
    foreach($team as $reg) {
        $r = strtolower($reg['preferred_role'] ?? '');
        if (str_contains($r, 'portiere') || str_contains($r, 'goalkeeper')) {
            $lines['GK'][] = $reg;
        } elseif (str_contains($r, 'difensor') || str_contains($r, 'terzino') || str_contains($r, 'defender')) {
            $lines['DEF'][] = $reg;
        } elseif (str_contains($r, 'attaccant') || str_contains($r, 'punta') || str_contains($r, 'ala') || str_contains($r, 'avanti') || str_contains($r, 'striker')) {
            $lines['ATT'][] = $reg;
        } else {
            $lines['MID'][] = $reg;
        }
    }
    return $lines;
};

$home_lines = $groupPlayersByRole($home_team);
$away_lines = $groupPlayersByRole($away_team);

$home_order = ['GK', 'DEF', 'MID', 'ATT'];
$away_order = ['ATT', 'MID', 'DEF', 'GK'];
?>

<?php if($teams_generated): ?>
<div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden" role="region" aria-label="Visualizzazione Formazioni in Campo">
    <div class="card-body p-0">
        <h2 class="fw-bold text-center py-3 mb-0 bg-body-tertiary border-bottom fs-5"><i class="bi bi-people-fill me-2 text-primary" aria-hidden="true"></i>Formazioni in Campo</h2>
        
        <div class="pitch-container position-relative overflow-hidden">
            <?php // PITCH GRAPHICS ?>
            <!-- Center Line -->
            <div class="pitch-center-line"></div>
            <!-- Center Circle -->
            <div class="pitch-center-circle"></div>
            <!-- Center Dot -->
            <div class="pitch-center-dot"></div>

            <!-- Home Penalty Area -->
            <div class="pitch-penalty-home"></div>
            <!-- Home Goal Area -->
            <div class="pitch-goal-home"></div>
            <!-- Home Penalty Dot -->
            <div class="pitch-penalty-dot-home"></div>

            <!-- Away Penalty Area -->
            <div class="pitch-penalty-away"></div>
            <!-- Away Goal Area -->
            <div class="pitch-goal-away"></div>
            <!-- Away Penalty Dot -->
            <div class="pitch-penalty-dot-away"></div>

            <div class="row h-100 g-0 position-relative pitch-grid">
                <?php // HOME (Red) ?>
                <div class="col-6 d-flex flex-row p-2 py-4">
                    <div class="position-absolute top-0 start-0 m-2 mt-3 z-3">
                        <span class="badge bg-danger shadow-sm px-3 py-2 fs-6 rounded-pill border border-white fw-bold">🔴 Home</span>
                    </div>
                    <?php foreach($home_order as $roleKey): ?>
                        <?php if(count($home_lines[$roleKey]) > 0): ?>
                            <div class="d-flex flex-column justify-content-around align-items-center h-100 pitch-role-col">
                                <?php foreach($home_lines[$roleKey] as $reg): ?>
                                    <?php
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
                                    $displayName = (strlen($reg['name']) > 8) ? substr($reg['name'], 0, 8) . '.' : $reg['name'];
                                    ?>
                                    <div class="text-center position-relative player-avatar my-2 hover-scale transition-all">
                                        <a href="<?= url('/profile?username=' . urlencode($reg['username'])) ?>" class="text-decoration-none focus-ring rounded-circle d-block mx-auto" aria-label="Profilo di <?= e($reg['name']) ?>, ruolo <?= $roleKey ?>">
                                            <div class="rounded-circle mx-auto d-flex justify-content-center align-items-center fw-bold text-white shadow match-show-avatar"
                                                 style="background: #dc3545; <?= $regAvatarUrl ? 'background-image: url(' . htmlspecialchars($regAvatarUrl) . '); background-size: cover; background-position: center;' : '' ?>">
                                                <?php if(!$regAvatarUrl): ?>
                                                    <?= strtoupper(substr($reg['name'], 0, 2)) ?>
                                                <?php endif; ?>
                                                <?php if($match['status'] === 'finished' && $reg['goals_scored'] > 0): ?>
                                                    <span class="position-absolute bg-dark text-white rounded-circle border border-white d-flex align-items-center justify-content-center shadow-sm pitch-player-goals" role="img" aria-label="<?= $reg['goals_scored'] ?> gol segnati">
                                                        ⚽<span class="sr-only"><?= $reg['goals_scored'] ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                        <div class="mt-1 d-flex flex-column align-items-center pe-none">
                                            <span class="badge bg-dark bg-opacity-75 text-white shadow-sm px-2 py-1 backdrop-blur pitch-player-label">
                                                <?= e($displayName) ?>
                                            </span>
                                            <small class="text-white-50 mt-1 d-block fw-bold pitch-player-role">
                                                <?= $roleKey ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php // AWAY (Blue) ?>
                <div class="col-6 d-flex flex-row p-2 py-4">
                    <div class="position-absolute top-0 end-0 m-2 mt-3 z-3">
                        <span class="badge bg-primary shadow-sm px-3 py-2 fs-6 rounded-pill border border-white fw-bold">🔵 Away</span>
                    </div>
                    <?php foreach($away_order as $roleKey): ?>
                        <?php if(count($away_lines[$roleKey]) > 0): ?>
                            <div class="d-flex flex-column justify-content-around align-items-center h-100 pitch-role-col">
                                <?php foreach($away_lines[$roleKey] as $reg): ?>
                                    <?php
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
                                    $displayName = (strlen($reg['name']) > 8) ? substr($reg['name'], 0, 8) . '.' : $reg['name'];
                                    ?>
                                    <div class="text-center position-relative player-avatar my-2 hover-scale transition-all">
                                        <a href="<?= url('/profile?username=' . urlencode($reg['username'])) ?>" class="text-decoration-none focus-ring rounded-circle d-block mx-auto" aria-label="Profilo di <?= e($reg['name']) ?>, ruolo <?= $roleKey ?>">
                                            <div class="rounded-circle mx-auto d-flex justify-content-center align-items-center fw-bold text-white shadow match-show-avatar"
                                                 style="background: #0d6efd; <?= $regAvatarUrl ? 'background-image: url(' . htmlspecialchars($regAvatarUrl) . '); background-size: cover; background-position: center;' : '' ?>">
                                                <?php if(!$regAvatarUrl): ?>
                                                    <?= strtoupper(substr($reg['name'], 0, 2)) ?>
                                                <?php endif; ?>
                                                <?php if($match['status'] === 'finished' && $reg['goals_scored'] > 0): ?>
                                                    <span class="position-absolute bg-dark text-white rounded-circle border border-white d-flex align-items-center justify-content-center shadow-sm pitch-player-goals" role="img" aria-label="<?= $reg['goals_scored'] ?> gol segnati">
                                                        ⚽<span class="sr-only"><?= $reg['goals_scored'] ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                        <div class="mt-1 d-flex flex-column align-items-center pe-none">
                                            <span class="badge bg-dark bg-opacity-75 text-white shadow-sm px-2 py-1 backdrop-blur pitch-player-label">
                                                <?= e($displayName) ?>
                                            </span>
                                            <small class="text-white-50 mt-1 d-block fw-bold pitch-player-role">
                                                <?= $roleKey ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
