<?php
$avatarUrl = null;
if (!empty($user['avatar'])) {
    if (strpos($user['avatar'], 'http://') === 0 || strpos($user['avatar'], 'https://') === 0) {
        $avatarUrl = $user['avatar'];
    } elseif (strpos($user['avatar'], 'uploads/') === 0) {
        $avatarUrl = url('/' . $user['avatar']);
    } else {
        $avatarUrl = url('/uploads/' . ltrim($user['avatar'], '/'));
    }
}

$backUrl = url('/');
if (!empty($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== $_SERVER['REQUEST_URI']) {
    $backUrl = $_SERVER['HTTP_REFERER'];
}
?>
<div class="row justify-content-center mb-5">
    <div class="col-12 col-md-10 col-lg-8">
        <a href="<?= $backUrl ?>" class="btn btn-link text-decoration-none text-body mb-3 px-0">
            <i class="bi bi-arrow-left me-1"></i> Indietro
        </a>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="bg-primary pt-5 pb-3 px-4 position-relative rounded-top-4">
                <div class="position-absolute top-0 end-0 p-3 d-flex align-items-center gap-2" style="z-index: 1000;">
                    <?php if ($user['role'] === 'super_admin'): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-shield-lock-fill me-1"></i>Admin</span>
                    <?php endif; ?>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-white p-0 border-0 fs-4" type="button" id="profileActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="outline: none; box-shadow: none; line-height: 1;">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 fade-down" aria-labelledby="profileActionsDropdown">
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#reportUserModal">
                                    <i class="bi bi-flag-fill"></i> Segnala Utente
                                </button>
                            </li>
                            <li>
                                <form action="<?= url('/friends/block/' . urlencode($user['username'])) ?>" method="POST" onsubmit="return confirm('Vuoi bloccare questo utente in modo permanente? Non potrà più vedere il tuo profilo né contattarti.');" class="m-0">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 text-body fw-semibold bg-transparent border-0 w-100 text-start">
                                        <i class="bi bi-slash-circle text-secondary"></i> Blocca Utente
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body px-4 pb-5 text-center mt-n4">
                <div class="position-relative d-inline-block shadow-sm rounded-circle mb-3 mx-auto border bg-white" style="width: 100px; height: 100px; margin-top: -50px; z-index:10;">
                    <?php if($avatarUrl): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Foto Profilo" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                    <?php else: ?>
                        <div class="w-100 h-100 rounded-circle d-flex justify-content-center align-items-center bg-white text-primary">
                            <span class="fs-1 fw-bold"><?= strtoupper(substr($user['name'] ?? '', 0, 1)) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <h3 class="fw-bold mb-0">
                    <?= e($user['name']) ?>
                </h3>
                <p class="text-muted mb-3 text-capitalize">
                    <?= e($user['preferred_role'] ?? 'Ruolo non specificato') ?>
                </p>

                <div class="d-flex justify-content-center flex-wrap gap-2 mb-3">
                    <?php if($is_friend): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2 fs-6 shadow-sm d-flex align-items-center"><i class="bi bi-check-circle-fill me-1"></i> Amici</span>
                        <form action="<?= url('/friends/remove/' . urlencode($user['username'])) ?>" method="POST" onsubmit="return confirm('Sei sicuro di voler rimuovere questo amico?');" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="btn btn-outline-danger rounded-pill px-3 fw-bold shadow-sm"><i class="bi bi-person-dash me-1"></i> Rimuovi</button>
                        </form>
                        <button type="button" class="btn btn-primary rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#compareStatsModal">
                            <i class="bi bi-arrow-left-right me-1"></i> Confronta con me
                        </button>
                    <?php elseif($sent_request): ?>
                        <button disabled class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm opacity-75"><i class="bi bi-hourglass-split me-1"></i> In attesa di conferma</button>
                    <?php elseif($received_request): ?>
                        <div class="d-flex gap-2 align-items-center flex-wrap justify-content-center">
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fs-6 shadow-sm d-flex align-items-center"><i class="bi bi-person-exclamation me-1"></i> Ti ha inviato una richiesta</span>
                            <form action="<?= url('/friends/accept/' . urlencode($user['username'])) ?>" method="POST" class="m-0">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="btn btn-success rounded-pill px-3 fw-bold shadow-sm"><i class="bi bi-check-lg me-1"></i> Accetta</button>
                            </form>
                            <form action="<?= url('/friends/reject/' . urlencode($user['username'])) ?>" method="POST" class="m-0">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="btn btn-outline-danger rounded-pill px-3 fw-bold shadow-sm"><i class="bi bi-x-lg me-1"></i> Rifiuta</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form action="<?= url('/friends/add') ?>" method="POST" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="friend_code" value="<?= e($user['friend_code']) ?>">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-person-plus-fill me-1"></i> Aggiungi Amico</button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Social Context: Mutual Friends & Matches Played Together -->
                <div class="profile-social-context-card mb-4 text-start">
                    <div class="row align-items-center g-3">
                        <!-- Mutual Friends -->
                        <div class="col-12 col-md-6 border-end-md">
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($mutual_friends)): ?>
                                    <div class="avatar-stack">
                                        <?php 
                                        $max_avatars = 3;
                                        $shown_friends = array_slice($mutual_friends, 0, $max_avatars);
                                        foreach ($shown_friends as $idx => $f): 
                                            $fAvatarUrl = null;
                                            if (!empty($f['avatar'])) {
                                                if (strpos($f['avatar'], 'http://') === 0 || strpos($f['avatar'], 'https://') === 0) {
                                                    $fAvatarUrl = $f['avatar'];
                                                } elseif (strpos($f['avatar'], 'uploads/') === 0) {
                                                    $fAvatarUrl = url('/' . $f['avatar']);
                                                } else {
                                                    $fAvatarUrl = url('/uploads/' . ltrim($f['avatar'], '/'));
                                                }
                                            }
                                        ?>
                                            <div class="avatar-stack-item shadow-sm" style="z-index: <?= 10 - $idx ?>;">
                                                <?php if ($fAvatarUrl): ?>
                                                    <img src="<?= htmlspecialchars($fAvatarUrl) ?>" alt="<?= e($f['name']) ?>" class="w-100 h-100 object-fit-cover">
                                                <?php else: ?>
                                                    <div class="w-100 h-100 d-flex justify-content-center align-items-center bg-primary text-white fw-bold" style="font-size: 0.75rem;">
                                                        <?= strtoupper(substr($f['name'] ?? '', 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="small text-body">
                                        <span class="fw-bold"><?= count($mutual_friends) ?> <?= count($mutual_friends) === 1 ? 'amico' : 'amici' ?> in comune</span>:
                                        <?php 
                                        $names = array_map(function($f) {
                                            return e($f['name'] . ' ' . $f['last_name']);
                                        }, $mutual_friends);
                                        
                                        if (count($names) <= 2) {
                                            echo implode(' e ', $names);
                                        } else {
                                            echo $names[0] . ', ' . $names[1] . ' e altri ' . (count($names) - 2);
                                        }
                                        ?>
                                    </div>
                                <?php else: ?>
                                    <div class="rounded-circle bg-body-secondary d-flex justify-content-center align-items-center text-muted" style="width: 32px; height: 32px;">
                                        <i class="bi bi-people-fill text-muted" style="font-size: 0.9rem;"></i>
                                    </div>
                                    <span class="text-muted small">Nessun amico in comune</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Matches Played Together -->
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex justify-content-center align-items-center text-primary shadow-sm" style="width: 32px; height: 32px;">
                                    <i class="bi bi-controller" style="font-size: 1rem;"></i>
                                </div>
                                <div class="small text-body">
                                    <?php if ($matches_played_together > 0): ?>
                                        Avete giocato insieme <span class="fw-bold text-primary"><?= $matches_played_together ?> <?= $matches_played_together === 1 ? 'partita' : 'partite' ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Non avete ancora giocato partite insieme</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($is_friend): ?>
                    <?php
                    $is_skill_exceptional = (float)($user['skill_rating'] ?? 0) > 4.5;
                    $is_mvp_exceptional = (int)($user['mvp_count'] ?? 0) > 5;
                    ?>
                    <div class="row text-center mt-4 g-3">
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-4 h-100">
                                <i class="bi bi-controller fs-3 text-primary mb-2"></i>
                                <h4 class="fw-bold mb-0"><?= $matches_played ?? 0 ?></h4>
                                <small class="text-muted">Presenze</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-4 h-100">
                               <i class="bi bi-people-fill fs-3 text-success mb-2"></i>
                                <h4 class="fw-bold mb-0"><?= $friends_count ?? 0 ?></h4>
                                <small class="text-muted">Amici</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-4 h-100">
                                <i class="bi bi-bullseye fs-3 text-danger mb-2"></i>
                                <h4 class="fw-bold mb-0"><?= $user['total_goals'] ?? 0 ?></h4>
                                <small class="text-muted">Gol Totali</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-4 h-100 <?= $is_skill_exceptional ? 'stat-card-glow-gold' : '' ?>">
                                <i class="bi bi-star-fill fs-3 text-warning mb-2"></i>
                                <h4 class="fw-bold mb-0"><?= $user['skill_rating'] > 0 ? number_format($user['skill_rating'], 1) : '-' ?></h4>
                                <small class="text-muted">Skill Media</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-4 h-100 <?= $is_mvp_exceptional ? 'stat-card-glow-gold' : '' ?>">
                                <i class="bi bi-award-fill fs-3 text-info mb-2"></i>
                                <h4 class="fw-bold mb-0"><?= $user['mvp_count'] ?? 0 ?></h4>
                                <small class="text-muted">MVP 🏆</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-4 h-100 d-flex flex-column justify-content-center align-items-center">
                                <?php
                                $ts = (int)$trust_score;
                                $circumference = 157;
                                $dashoffset = $circumference * (1 - $ts / 100);
    
                                if ($ts === 100) {
                                    $stroke_color = '#00ff66';
                                    $glow_class = 'trust-glow-green';
                                } elseif ($ts >= 70) {
                                    $stroke_color = '#ff9f0a';
                                    $glow_class = 'trust-glow-orange';
                                } else {
                                    $stroke_color = '#ff453a';
                                    $glow_class = 'trust-glow-red';
                                }
                                ?>
                                <div class="trust-circle-container <?= $glow_class ?> mb-2 position-relative shadow-sm bg-body rounded-circle" style="width: 60px; height: 60px;">
                                    <svg width="60" height="60" viewBox="0 0 60 60" style="transform: rotate(-90deg);">
                                        <circle cx="30" cy="30" r="25" fill="transparent" stroke="rgba(120, 120, 120, 0.15)" stroke-width="4.5" />
                                        <circle cx="30" cy="30" r="25" fill="transparent" 
                                                stroke="<?= $stroke_color ?>" stroke-width="4.5" 
                                                stroke-dasharray="<?= $circumference ?>" 
                                                stroke-dashoffset="<?= $dashoffset ?>" 
                                                stroke-linecap="round"
                                                style="transition: stroke-dashoffset 0.5s ease-in-out;" />
                                    </svg>
                                    <div class="position-absolute top-50 start-50 translate-middle fw-bold text-center" style="font-size: 0.85rem; color: var(--bs-body-color);">
                                        <?= $ts ?>%
                                    </div>
                                </div>
                                <small class="text-muted text-uppercase fw-semibold stat-card-label" style="font-size: 0.75rem;">Trust Score</small>
                            </div>
                        </div>
                    </div>
    
                    <!-- Trend/Sparkline Widget -->
                    <div class="mt-4 p-3 bg-body border rounded-4 shadow-sm text-start">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold text-muted text-uppercase tracking-wide" style="font-size: 0.75rem;"><i class="bi bi-graph-up text-primary me-1"></i>Trend Prestazioni (Ultime 5 partite)</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 fw-bold" style="font-size: 0.75rem;">Stato di Forma</span>
                        </div>
                        <?php if (empty($trend_votes)): ?>
                            <div class="text-center py-2 text-muted small">
                                <i class="bi bi-info-circle me-1"></i>Non ci sono valutazioni sufficienti per calcolare il trend.
                            </div>
                        <?php else: ?>
                            <?php
                            $width = 300;
                            $height = 80;
                            $padding_x = 35; // margin for left labels
                            $padding_y = 15;
                            
                            $count = count($trend_votes);
                            $min_val = 1.0;
                            $max_val = 5.0;
                            
                            $step_x = $count > 1 ? ($width - $padding_x - 20) / ($count - 1) : 0;
                            
                            $points_array = [];
                            $points = [];
                            foreach ($trend_votes as $i => $val) {
                                $x = $padding_x + $i * $step_x;
                                // Y goes from top to bottom, so high ratings (5.0) are at the top (lower y values)
                                $y = $padding_y + (1 - ($val - $min_val) / ($max_val - $min_val)) * ($height - 2 * $padding_y);
                                $points[] = "$x,$y";
                                $points_array[] = ['x' => $x, 'y' => $y, 'val' => $val];
                            }
                            
                            $path_d = "M " . implode(" L ", $points);
                            $fill_d = $path_d . " L " . ($padding_x + ($count - 1) * $step_x) . "," . ($height - $padding_y) . " L " . $padding_x . "," . ($height - $padding_y) . " Z";
                            
                            // Reference lines: 3.0 and 4.5
                            $y_3_0 = $padding_y + (1 - (3.0 - $min_val) / ($max_val - $min_val)) * ($height - 2 * $padding_y);
                            $y_4_5 = $padding_y + (1 - (4.5 - $min_val) / ($max_val - $min_val)) * ($height - 2 * $padding_y);
                            ?>
                            <div class="d-flex align-items-center justify-content-center py-2">
                                <div style="width: 100%; max-width: 320px;">
                                    <svg viewBox="0 0 <?= $width ?> <?= $height ?>" class="w-100" style="height: 80px; overflow: visible;">
                                        <defs>
                                            <linearGradient id="sparklineGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="var(--bs-primary)" stop-opacity="0.25" />
                                                <stop offset="100%" stop-color="var(--bs-primary)" stop-opacity="0.0" />
                                            </linearGradient>
                                        </defs>
                                        
                                        <!-- Grid lines and labels -->
                                        <line x1="<?= $padding_x - 5 ?>" y1="<?= $y_3_0 ?>" x2="<?= $width - 15 ?>" y2="<?= $y_3_0 ?>" stroke="rgba(120, 120, 120, 0.15)" stroke-width="1" stroke-dasharray="3,3" />
                                        <line x1="<?= $padding_x - 5 ?>" y1="<?= $y_4_5 ?>" x2="<?= $width - 15 ?>" y2="<?= $y_4_5 ?>" stroke="rgba(120, 120, 120, 0.15)" stroke-width="1" stroke-dasharray="3,3" />
                                        <text x="<?= $padding_x - 8 ?>" y="<?= $y_3_0 + 3 ?>" text-anchor="end" font-size="8" font-weight="bold" fill="var(--bs-secondary)" opacity="0.6">3.0</text>
                                        <text x="<?= $padding_x - 8 ?>" y="<?= $y_4_5 + 3 ?>" text-anchor="end" font-size="8" font-weight="bold" fill="var(--bs-secondary)" opacity="0.6">4.5</text>
                                        
                                        <!-- Area fill -->
                                        <path d="<?= $fill_d ?>" fill="url(#sparklineGrad)" />
                                        <!-- Line path -->
                                        <path d="<?= $path_d ?>" fill="none" stroke="var(--bs-primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                        
                                        <!-- Value markers & Text -->
                                        <?php foreach ($points_array as $pt): ?>
                                            <circle cx="<?= $pt['x'] ?>" cy="<?= $pt['y'] ?>" r="3.5" fill="#ffffff" stroke="var(--bs-primary)" stroke-width="2" />
                                            <text x="<?= $pt['x'] ?>" y="<?= $pt['y'] - 8 ?>" text-anchor="middle" font-size="8.5" font-weight="bold" fill="var(--bs-emphasis-color)" style="paint-order: stroke; stroke: var(--bs-body-bg); stroke-width: 3px; stroke-linejoin: round;"><?= number_format($pt['val'], 1) ?></text>
                                        <?php endforeach; ?>
                                    </svg>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Locked Stats Card -->
                    <div class="mt-4 p-4 bg-body-tertiary border border-secondary-subtle rounded-4 text-center shadow-sm">
                        <div class="d-inline-flex p-3 bg-primary bg-opacity-10 text-primary rounded-circle mb-3">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Statistiche Private</h5>
                        <p class="text-muted small mb-0 px-md-4">
                            Le statistiche dettagliate, lo stato di forma ed il trend delle prestazioni di <strong><?= e($user['name']) ?></strong> sono visibili solo ai suoi amici. Invia una richiesta di amicizia per sbloccarle!
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Modale di Segnalazione -->
<div class="modal fade" id="reportUserModal" tabindex="-1" aria-labelledby="reportUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="reportUserModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Segnala Utente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <form action="<?= url('/reports') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="reported_username" value="<?= e($user['username']) ?>">
                <?php if (isset($_GET['match_id'])): ?>
                    <input type="hidden" name="match_id" value="<?= e($_GET['match_id']) ?>">
                <?php endif; ?>
                <div class="modal-body py-3">
                    <p class="text-muted small mb-3">
                        Stai segnalando l'utente <strong><?= e($user['name']) ?></strong> agli amministratori. Fornisci un motivo dettagliato per aiutarci nella verifica.
                    </p>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">Motivo della segnalazione</label>
                        <select name="reason" id="reason" class="form-select rounded-3 bg-body-secondary border-secondary-subtle text-body" required>
                            <option value="" disabled selected>Seleziona un motivo...</option>
                            <option value="Comportamento Antisportivo / Violento">Comportamento Antisportivo / Violento</option>
                            <option value="Linguaggio Offensivo o Inappropriato">Linguaggio Offensivo o Inappropriato</option>
                            <option value="Assenza Ingiustificata alla Partita">Assenza Ingiustificata alla Partita</option>
                            <option value="Profilo Falso o Inappropriato">Profilo Falso o Inappropriato</option>
                            <option value="Altro">Altro (specifica sotto)</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="description" class="form-label fw-semibold">Dettagli / Descrizione</label>
                        <textarea name="description" id="description" rows="4" class="form-control rounded-3 bg-body-secondary border-secondary-subtle text-body" 
                            placeholder="Descrivi cosa è accaduto in dettaglio (minimo 10 caratteri)..." required minlength="10" maxlength="1000"></textarea>
                        <div class="form-text text-muted small">Massimo 1000 caratteri.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 fw-bold" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-1"></i> Invia Segnalazione
                    </button>
                 </div>
            </form>
        </div>
    </div>
</div>

<?php if ($is_friend): ?>
<!-- Modale Confronto Statistiche -->
<div class="modal fade" id="compareStatsModal" tabindex="-1" aria-labelledby="compareStatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="compareStatsModalLabel">
                    <i class="bi bi-arrow-left-right text-primary me-2"></i>Confronta Statistiche
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            
            <div class="modal-body py-4">
                <!-- Players head to head header -->
                <div class="row align-items-center mb-4 text-center">
                    <!-- Left: Me -->
                    <div class="col-5">
                        <div class="position-relative d-inline-block shadow-sm rounded-circle mb-2 border bg-white" style="width: 70px; height: 70px;">
                            <?php 
                            $meAvatarUrl = null;
                            if (!empty($me['avatar'])) {
                                if (strpos($me['avatar'], 'http://') === 0 || strpos($me['avatar'], 'https://') === 0) {
                                    $meAvatarUrl = $me['avatar'];
                                } elseif (strpos($me['avatar'], 'uploads/') === 0) {
                                    $meAvatarUrl = url('/' . $me['avatar']);
                                } else {
                                    $meAvatarUrl = url('/uploads/' . ltrim($me['avatar'], '/'));
                                }
                            }
                            if ($meAvatarUrl): 
                            ?>
                                <img src="<?= htmlspecialchars($meAvatarUrl) ?>" alt="Tu" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="w-100 h-100 rounded-circle d-flex justify-content-center align-items-center bg-primary text-white">
                                    <span class="fs-4 fw-bold"><?= strtoupper(substr($me['name'] ?? '', 0, 1)) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h6 class="fw-bold mb-0 text-truncate text-primary">Tu</h6>
                        <small class="text-muted d-block text-truncate text-lowercase">@<?= e($me['username']) ?></small>
                    </div>
                    
                    <!-- Center: VS -->
                    <div class="col-2">
                        <span class="badge bg-secondary-subtle text-secondary border fs-6 px-3 py-2 rounded-circle shadow-sm">VS</span>
                    </div>
                    
                    <!-- Right: User -->
                    <div class="col-5">
                        <div class="position-relative d-inline-block shadow-sm rounded-circle mb-2 border bg-white" style="width: 70px; height: 70px;">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= e($user['name']) ?>" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="w-100 h-100 rounded-circle d-flex justify-content-center align-items-center bg-success text-white">
                                    <span class="fs-4 fw-bold"><?= strtoupper(substr($user['name'] ?? '', 0, 1)) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h6 class="fw-bold mb-0 text-truncate text-success"><?= e($user['name']) ?></h6>
                        <small class="text-muted d-block text-truncate text-lowercase">@<?= e($user['username']) ?></small>
                    </div>
                </div>
                
                <hr class="border-secondary-subtle mb-4">
                
                <!-- Stat Comparisons -->
                <?php
                // Array of stats to compare
                $stats_to_compare = [
                    ['label' => 'Presenze', 'val_me' => (int)($me['matches_played'] ?? 0), 'val_user' => (int)($user['matches_played'] ?? 0)],
                    ['label' => 'Gol Totali', 'val_me' => (int)($me['total_goals'] ?? 0), 'val_user' => (int)($user['total_goals'] ?? 0)],
                    ['label' => 'Skill Rating', 'val_me' => (float)($me['skill_rating'] ?? 0.0), 'val_user' => (float)($user['skill_rating'] ?? 0.0), 'is_float' => true],
                    ['label' => 'Titoli MVP 🏆', 'val_me' => (int)($me['mvp_count'] ?? 0), 'val_user' => (int)($user['mvp_count'] ?? 0)],
                    ['label' => 'Trust Score', 'val_me' => (int)($me['trust_score'] ?? 0), 'val_user' => (int)($user['trust_score'] ?? 0)]
                ];
                
                foreach ($stats_to_compare as $s):
                    $val_me = $s['val_me'];
                    $val_user = $s['val_user'];
                    $label = $s['label'];
                    $is_float = $s['is_float'] ?? false;
                    
                    // Format values
                    $display_me = $is_float ? number_format($val_me, 1) : $val_me;
                    $display_user = $is_float ? number_format($val_user, 1) : $val_user;
                    
                    // Math for progress bars
                    if ($val_me == 0 && $val_user == 0) {
                        $pct_me = 50;
                        $pct_user = 50;
                    } else {
                        $total = $val_me + $val_user;
                        $pct_me = ($val_me / $total) * 100;
                        $pct_user = ($val_user / $total) * 100;
                    }
                ?>
                    <div class="mb-4 px-md-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-primary" style="font-size: 1.1rem;"><?= $display_me ?></span>
                            <span class="text-muted small text-uppercase fw-bold"><?= $label ?></span>
                            <span class="fw-bold text-success" style="font-size: 1.1rem;"><?= $display_user ?></span>
                        </div>
                        <div class="progress rounded-pill bg-body-secondary shadow-sm" style="height: 10px; overflow: hidden;">
                            <div class="progress-bar bg-primary transition-all" role="progressbar" style="width: <?= $pct_me ?>%" aria-valuenow="<?= $pct_me ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-success transition-all" role="progressbar" style="width: <?= $pct_user ?>%" aria-valuenow="<?= $pct_user ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="modal-footer border-top-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
