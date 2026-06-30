<?php
// views/matches/partials/show/pitch_formation.php

$home_team_expanded = [];
foreach ($registrations as $r) {
    if ($r['team'] === 'home' && $r['status'] === 'registered') {
        $home_team_expanded[] = $r;
        if ($r['has_guest']) {
            $home_team_expanded[] = [
                'username' => 'guest_' . $r['username'],
                'name' => 'Ospite di ' . (explode(' ', $r['name'])[0]),
                'avatar' => null,
                'preferred_role' => 'MID',
                'skill_rating' => 3.0,
                'goals_scored' => 0,
                'is_guest_dummy' => true
            ];
        }
    }
}

$away_team_expanded = [];
foreach ($registrations as $r) {
    if ($r['team'] === 'away' && $r['status'] === 'registered') {
        $away_team_expanded[] = $r;
        if ($r['has_guest']) {
            $away_team_expanded[] = [
                'username' => 'guest_' . $r['username'],
                'name' => 'Ospite di ' . (explode(' ', $r['name'])[0]),
                'avatar' => null,
                'preferred_role' => 'MID',
                'skill_rating' => 3.0,
                'goals_scored' => 0,
                'is_guest_dummy' => true
            ];
        }
    }
}
$teams_generated = count($home_team_expanded) > 0 || count($away_team_expanded) > 0;

$roleEmojiMap = [
    'GK' => '🧤',
    'DEF' => '🛡️',
    'MID' => '🛡️⚔️',
    'ATT' => '⚔️'
];

$getCanonicalRole = function($preferredRole) {
    $r = strtolower($preferredRole ?? '');
    if (str_contains($r, 'portiere') || str_contains($r, 'goalkeeper')) {
        return 'GK';
    } elseif (str_contains($r, 'difensor') || str_contains($r, 'terzino') || str_contains($r, 'defender')) {
        return 'DEF';
    } elseif (str_contains($r, 'attaccant') || str_contains($r, 'punta') || str_contains($r, 'ala') || str_contains($r, 'avanti') || str_contains($r, 'striker')) {
        return 'ATT';
    } else {
        return 'MID';
    }
};

$assignPlayersToFormation = function($team) use ($getCanonicalRole) {
    $N = count($team);
    if ($N === 0) {
        return ['GK' => [], 'DEF' => [], 'MID' => [], 'ATT' => []];
    }

    // Predefined realistic schemas based on number of players N in the team
    $schemas = [
        1 => [
            ['GK' => 1, 'DEF' => 0, 'MID' => 0, 'ATT' => 0]
        ],
        2 => [
            ['GK' => 1, 'DEF' => 1, 'MID' => 0, 'ATT' => 0],
            ['GK' => 1, 'DEF' => 0, 'MID' => 1, 'ATT' => 0]
        ],
        3 => [
            ['GK' => 1, 'DEF' => 1, 'MID' => 1, 'ATT' => 0],
            ['GK' => 1, 'DEF' => 1, 'MID' => 0, 'ATT' => 1]
        ],
        4 => [
            ['GK' => 1, 'DEF' => 1, 'MID' => 1, 'ATT' => 1],
            ['GK' => 1, 'DEF' => 2, 'MID' => 1, 'ATT' => 0],
            ['GK' => 1, 'DEF' => 1, 'MID' => 2, 'ATT' => 0]
        ],
        5 => [
            ['GK' => 1, 'DEF' => 1, 'MID' => 2, 'ATT' => 1], // 1-2-1
            ['GK' => 1, 'DEF' => 2, 'MID' => 1, 'ATT' => 1], // 2-1-1
            ['GK' => 1, 'DEF' => 2, 'MID' => 2, 'ATT' => 0], // 2-2-0
            ['GK' => 1, 'DEF' => 1, 'MID' => 3, 'ATT' => 0]  // 1-3-0
        ],
        6 => [
            ['GK' => 1, 'DEF' => 2, 'MID' => 2, 'ATT' => 1],
            ['GK' => 1, 'DEF' => 2, 'MID' => 1, 'ATT' => 2],
            ['GK' => 1, 'DEF' => 3, 'MID' => 2, 'ATT' => 0]
        ],
        7 => [
            ['GK' => 1, 'DEF' => 2, 'MID' => 3, 'ATT' => 1], // 2-3-1
            ['GK' => 1, 'DEF' => 3, 'MID' => 2, 'ATT' => 1], // 3-2-1
            ['GK' => 1, 'DEF' => 3, 'MID' => 1, 'ATT' => 2], // 3-1-2
            ['GK' => 1, 'DEF' => 2, 'MID' => 2, 'ATT' => 2]  // 2-2-2
        ],
        8 => [
            ['GK' => 1, 'DEF' => 3, 'MID' => 3, 'ATT' => 1], // 3-3-1
            ['GK' => 1, 'DEF' => 3, 'MID' => 2, 'ATT' => 2], // 3-2-2
            ['GK' => 1, 'DEF' => 4, 'MID' => 2, 'ATT' => 1], // 4-2-1
            ['GK' => 1, 'DEF' => 2, 'MID' => 4, 'ATT' => 1]  // 2-4-1
        ],
        9 => [
            ['GK' => 1, 'DEF' => 3, 'MID' => 3, 'ATT' => 2],
            ['GK' => 1, 'DEF' => 4, 'MID' => 3, 'ATT' => 1],
            ['GK' => 1, 'DEF' => 3, 'MID' => 4, 'ATT' => 1]
        ],
        10 => [
            ['GK' => 1, 'DEF' => 4, 'MID' => 3, 'ATT' => 2],
            ['GK' => 1, 'DEF' => 4, 'MID' => 4, 'ATT' => 1],
            ['GK' => 1, 'DEF' => 3, 'MID' => 4, 'ATT' => 2]
        ],
        11 => [
            ['GK' => 1, 'DEF' => 4, 'MID' => 4, 'ATT' => 2], // 4-4-2
            ['GK' => 1, 'DEF' => 4, 'MID' => 3, 'ATT' => 3], // 4-3-3
            ['GK' => 1, 'DEF' => 3, 'MID' => 5, 'ATT' => 2]  // 3-5-2
        ]
    ];

    // Sort players by skill_rating descending so strongest get priority
    usort($team, function($a, $b) {
        $srA = isset($a['skill_rating']) ? (float)$a['skill_rating'] : 0.0;
        $srB = isset($b['skill_rating']) ? (float)$b['skill_rating'] : 0.0;
        return $srB <=> $srA;
    });

    // Determine candidate schemas
    if (isset($schemas[$N])) {
        $candidates = $schemas[$N];
    } else {
        // Fallback for larger teams
        $gk = 1;
        $def = (int)floor(($N - 1) * 0.35);
        $att = (int)max(1, floor(($N - 1) * 0.25));
        $mid = $N - 1 - $def - $att;
        $candidates = [['GK' => $gk, 'DEF' => $def, 'MID' => $mid, 'ATT' => $att]];
    }

    $bestAssignments = [];
    $bestScore = -1;

    foreach ($candidates as $schema) {
        $assignments = ['GK' => [], 'DEF' => [], 'MID' => [], 'ATT' => []];
        $slotsLeft = $schema;
        $score = 0;
        $unassigned = [];

        // Pass 1: Place players in their preferred role if available
        foreach ($team as $player) {
            $pref = $getCanonicalRole($player['preferred_role'] ?? '');
            if (isset($slotsLeft[$pref]) && $slotsLeft[$pref] > 0) {
                $assignments[$pref][] = $player;
                $slotsLeft[$pref]--;
                $score += 10; // Positive score weight for matching preference
            } else {
                $unassigned[] = $player;
            }
        }

        // Pass 2: Autocomplete remaining empty slots
        foreach ($unassigned as $player) {
            foreach (['GK', 'DEF', 'MID', 'ATT'] as $role) {
                if (isset($slotsLeft[$role]) && $slotsLeft[$role] > 0) {
                    $assignments[$role][] = $player;
                    $slotsLeft[$role]--;
                    break;
                }
            }
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestAssignments = $assignments;
        }
    }

    return $bestAssignments;
};

$home_lines = $assignPlayersToFormation($home_team_expanded);
$away_lines = $assignPlayersToFormation($away_team_expanded);

$home_order = ['GK', 'DEF', 'MID', 'ATT'];
$away_order = ['ATT', 'MID', 'DEF', 'GK'];

// Selezione casuale del pattern d'erba per il campo
$pitch_patterns = ['pattern-circles', 'pattern-vertical', 'pattern-horizontal', 'pattern-diagonal', 'pattern-checkerboard'];
$random_pitch_pattern = $pitch_patterns[array_rand($pitch_patterns)];
?>

<?php if($teams_generated && ($match['status'] === 'full' || $match['status'] === 'finished') && $match['result_home'] === null): ?>
<div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden" role="region" aria-label="Visualizzazione Formazioni in Campo">
    <div class="card-body p-0">
        <h2 class="fw-bold text-center py-3 mb-0 bg-body-tertiary border-bottom fs-5"><span class="bi bi-people-fill me-2 text-primary" aria-hidden="true"></span>Formazioni in Campo</h2>
        
        <div class="pitch-container position-relative overflow-hidden <?= $random_pitch_pattern ?>">
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
                                        <?php if(isset($reg['is_guest_dummy'])): ?>
                                            <div class="rounded-circle mx-auto d-flex justify-content-center align-items-center fw-bold text-white shadow match-show-avatar"
                                                 style="background: #dc3545; cursor: default;">
                                                <i class="bi bi-person-plus-fill fs-5" aria-hidden="true"></i>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= url('/profile?username=' . urlencode($reg['username']) . '&match_id=' . $match['id']) ?>" class="text-decoration-none focus-ring rounded-circle d-block mx-auto" aria-label="Profilo di <?= e($reg['name']) ?>, ruolo <?= $roleKey ?>">
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
                                        <?php endif; ?>
                                        <div class="mt-1 d-flex flex-column align-items-center pe-none">
                                            <span class="badge bg-dark bg-opacity-75 text-white shadow-sm px-2 py-1 backdrop-blur pitch-player-label">
                                                <?= e($displayName) ?>
                                            </span>
                                            <small class="text-white-50 mt-1 d-block fw-bold pitch-player-role" style="font-size: 0.8rem;">
                                                <?= $roleEmojiMap[$roleKey] ?? $roleKey ?>
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
                                        <?php if(isset($reg['is_guest_dummy'])): ?>
                                            <div class="rounded-circle mx-auto d-flex justify-content-center align-items-center fw-bold text-white shadow match-show-avatar"
                                                 style="background: #0d6efd; cursor: default;">
                                                <i class="bi bi-person-plus-fill fs-5" aria-hidden="true"></i>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= url('/profile?username=' . urlencode($reg['username']) . '&match_id=' . $match['id']) ?>" class="text-decoration-none focus-ring rounded-circle d-block mx-auto" aria-label="Profilo di <?= e($reg['name']) ?>, ruolo <?= $roleKey ?>">
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
                                        <?php endif; ?>
                                        <div class="mt-1 d-flex flex-column align-items-center pe-none">
                                            <span class="badge bg-dark bg-opacity-75 text-white shadow-sm px-2 py-1 backdrop-blur pitch-player-label">
                                                <?= e($displayName) ?>
                                            </span>
                                            <small class="text-white-50 mt-1 d-block fw-bold pitch-player-role" style="font-size: 0.8rem;">
                                                <?= $roleEmojiMap[$roleKey] ?? $roleKey ?>
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
