<div class="row justify-content-center mb-5">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0"><i class="bi bi-person-badge-fill text-primary me-2"></i><?= $is_own_profile ? 'Il tuo Profilo' : 'Profilo di ' . e($user['name']) ?></h1>
            <div class="d-md-none d-flex gap-2">
                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                    <a href="<?= url('/admin') ?>"
                        class="btn btn-sm btn-warning rounded-pill shadow-sm px-3 fw-bold text-dark"><i
                            class="bi bi-shield-lock-fill"></i></a>
                <?php endif; ?>
                <?php if ($is_own_profile): ?>
                    <form action="<?= url('/logout') ?>" method="POST" class="m-0">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-danger rounded-pill shadow-sm px-3 fw-bold"><i
                                class="bi bi-box-arrow-right"></i> Esci</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm border rounded-4 mb-4 overflow-hidden">
            <div class="profile-banner pt-5 pb-4 px-4">
                <div class="position-absolute top-0 end-0 p-3">
                    <?php if ($user['role'] === 'super_admin'): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i
                                class="bi bi-shield-lock-fill me-1"></i>Admin</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body px-4 pb-5 text-center mt-n4">
                <div class="position-relative d-inline-block shadow-sm rounded-circle mb-3 mx-auto border border-4 border-white bg-white profile-avatar-wrap">
                    <?php if($user['avatar']): ?>
                        <img src="<?= url('/' . $user['avatar']) ?>" alt="Foto Profilo" class="w-100 h-100 rounded-circle object-fit-cover">
                    <?php else: ?>
                        <div
                            class="w-100 h-100 rounded-circle d-flex justify-content-center align-items-center bg-light text-primary">
                            <span class="fs-1 fw-bold"><?= strtoupper(substr($user['name'] ?? '', 0, 1)) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_own_profile): ?>
                        <form action="<?= url('/profile/avatar') ?>" method="POST" enctype="multipart/form-data"
                            class="position-absolute bottom-0 end-0 avatar-upload-form"
                            id="avatarForm">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <label for="avatarInput" tabindex="0" aria-label="Cambia foto profilo"
                                onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); document.getElementById('avatarInput').click(); }"
                                class="btn btn-primary rounded-circle shadow d-flex align-items-center justify-content-center border border-2 border-white avatar-upload-btn"
                                onmouseover="this.style.transform='scale(1.1)';"
                                onmouseout="this.style.transform='scale(1)';">
                                <i class="bi bi-camera-fill fs-6"></i>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" class="d-none"
                                accept="image/jpeg,image/png,image/webp,image/jpg"
                                onchange="document.getElementById('avatarForm').submit()">
                        </form>
                    <?php endif; ?>
                </div>

                <h3 class="fw-bold mb-0 profile-username d-flex align-items-center justify-content-center gap-2 flex-wrap">
                    <span><?= e($user['name']) ?> <?= e($user['last_name'] ?? '') ?></span>
                    <?php if ((int)$user['trust_score'] < 40): ?>
                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill px-2" style="font-size: 0.75rem;" title="Il trust score di questo giocatore è inferiore a 40.">
                            ⚠️ Giocatore Poco Affidabile
                        </span>
                    <?php endif; ?>
                </h3>
                <p class="text-muted mb-3 text-capitalize fw-medium">
                    <i class="bi bi-person-vcard me-1"></i><?= e($user['preferred_role'] ?? 'Ruolo non specificato') ?>
                </p>

                <!-- Social Actions for Visitor Views -->
                <?php if (!$is_own_profile): ?>
                    <div class="mb-4">
                        <?php if (!$friendship): ?>
                            <form action="<?= url('/friends/add') ?>" method="POST" class="d-inline-block">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="friend_code" value="<?= e($user['friend_code']) ?>">
                                <button type="submit" class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold">
                                    <i class="bi bi-person-plus-fill me-2"></i>Aggiungi Amico
                                </button>
                            </form>
                        <?php elseif ($friendship['status'] === 'pending'): ?>
                            <?php if ($friendship['sender_username'] === $_SESSION['user']['username']): ?>
                                <button class="btn btn-secondary rounded-pill px-4 fw-bold" disabled>
                                    <i class="bi bi-clock-history me-2"></i>Richiesta Inviata
                                </button>
                            <?php else: ?>
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="<?= url('/friends/accept/' . urlencode($user['username'])) ?>" method="POST" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-success rounded-pill shadow-sm px-4 fw-bold">
                                            <i class="bi bi-check-lg me-2"></i>Accetta
                                        </button>
                                    </form>
                                    <form action="<?= url('/friends/reject/' . urlencode($user['username'])) ?>" method="POST" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-outline-danger rounded-pill shadow-sm px-4 fw-bold">
                                            <i class="bi bi-x-lg me-2"></i>Rifiuta
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($friendship['status'] === 'accepted'): ?>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="btn btn-success rounded-pill px-4 fw-bold" style="cursor: default;">
                                    <i class="bi bi-people-fill me-2"></i>Siete Amici
                                </span>
                                <form action="<?= url('/friends/remove/' . urlencode($user['username'])) ?>" method="POST" class="m-0"
                                      onsubmit="return confirm('Sei sicuro di voler rimuovere questo amico?');">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-outline-danger rounded-pill shadow-sm px-3 fw-bold" title="Rimuovi">
                                        <i class="bi bi-person-dash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php elseif ($friendship['status'] === 'blocked'): ?>
                            <span class="btn btn-danger rounded-pill px-4 fw-bold" style="cursor: default;">
                                <i class="bi bi-slash-circle me-2"></i>Bloccato
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                $is_skill_exceptional = (float)($user['skill_rating'] ?? 0) > 4.5;
                $is_mvp_exceptional = (int)($user['mvp_count'] ?? 0) > 5;
                ?>
                <div class="row text-center mt-4 g-3">
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm">
                            <div class="icon-circle icon-primary">
                                <i class="bi bi-controller"></i>
                            </div>
                            <h4 class="fw-bold mb-0"><?= $user['matches_played'] ?? 0 ?></h4>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Presenze</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm">
                            <div class="icon-circle icon-success">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h4 class="fw-bold mb-0"><?= $matches_hosted ?? 0 ?></h4>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Organizzate</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm">
                            <div class="icon-circle icon-danger">
                                <i class="bi bi-bullseye"></i>
                            </div>
                            <h4 class="fw-bold mb-0"><?= $user['total_goals'] ?? 0 ?></h4>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Gol Totali</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm <?= $is_skill_exceptional ? 'stat-card-glow-gold' : '' ?>">
                            <div class="icon-circle icon-warning">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <h4 class="fw-bold mb-0">
                                <?= $user['skill_rating'] > 0 ? number_format($user['skill_rating'], 1) : '-' ?>
                            </h4>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Skill Media</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm <?= $is_mvp_exceptional ? 'stat-card-glow-gold' : '' ?>">
                            <div class="icon-circle icon-info">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <h4 class="fw-bold mb-0"><?= $user['mvp_count'] ?? 0 ?></h4>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">MVP 🏆</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm d-flex flex-column justify-content-center align-items-center">
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
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Trust Score</small>
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
            </div>
        </div>

        <!-- TABS NAVIGATION -->
        <ul class="nav nav-pills nav-fill bg-body shadow-sm rounded-4 p-2 mb-4 border" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold" id="activity-tab" data-bs-toggle="pill"
                    data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="true"><i
                        class="bi bi-activity me-2"></i>Panoramica</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold" id="social-tab" data-bs-toggle="pill"
                    data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false"><i
                        class="bi bi-people-fill me-2"></i>Social & Amici</button>
            </li>
            <?php if ($is_own_profile): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="settings-tab" data-bs-toggle="pill"
                        data-bs-target="#settings" type="button" role="tab" aria-controls="settings"
                        aria-selected="false"><i class="bi bi-gear-fill me-2"></i>Impostazioni</button>
                </li>
            <?php endif; ?>
        </ul>

        <!-- TABS CONTENT -->
        <div class="tab-content" id="profileTabsContent">

            <!-- TAB: PANORAMICA -->
            <div class="tab-pane fade show active" id="activity" role="tabpanel" aria-labelledby="activity-tab"
                tabindex="0">
                <?php require VIEW_PATH . '/profile/partials/badges.php'; ?>
                <div class="row g-4 mt-2 mb-2">
                    <div class="col-12">
                        <div class="card shadow-sm border rounded-4 p-4">
                            <h5 class="fw-bold mb-4"><i class="bi bi-clock-history text-primary me-2"></i>Storico Partite Giocate</h5>
                            <?php if(empty($matchHistory)): ?>
                                <div class="text-center py-4 bg-body-tertiary rounded-3">
                                    <i class="bi bi-calendar-x fs-2 text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Non ci sono partite giocate in archivio.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Data</th>
                                                <th>Ora</th>
                                                <th>Campetto</th>
                                                <th>Formato</th>
                                                <th>Risultato</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($matchHistory as $reg): ?>
                                                <?php if($reg['match']): ?>
                                                    <tr onclick="window.location.href='<?= url('/matches/' . $reg['match']['id']) ?>?from=profile';"
                                                        style="cursor: pointer;">
                                                        <td><strong><?= date('d/m/Y', strtotime($reg['match']['date'])) ?></strong></td>
                                                        <td><?= date('H:i', strtotime($reg['match']['time'])) ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                                                <span class="text-truncate" style="max-width: 200px;"><?= e($reg['match']['location']) ?></span>
                                                            </div>
                                                        </td>
                                                        <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= e($reg['match']['format']) ?></span></td>
                                                        <td>
                                                            <?php if($reg['match']['result_home'] !== null && $reg['match']['result_away'] !== null): ?>
                                                                <span class="badge bg-secondary fs-6 shadow-sm"><?= e($reg['match']['result_home']) ?> - <?= e($reg['match']['result_away']) ?></span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-muted border">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: SOCIAL E AMICI -->
            <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab" tabindex="0">
                <div class="row g-4">
                    <?php if ($is_own_profile): ?>
                        <div class="col-md-5">
                            <div class="card shadow-sm border rounded-4 h-100 p-4">
                                <h5 class="fw-bold mb-3"><i class="bi bi-person-plus-fill text-primary me-2"></i>Aggiungi Amico</h5>

                                <div class="mb-4 text-center">
                                    <small class="text-muted d-block mb-1">Il tuo Codice Amico</small>
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="bg-body-tertiary rounded-3 py-2 px-3 d-inline-block border border-primary border-opacity-25">
                                            <span class="fs-4 fw-bold text-primary tracking-wide" id="friendCodeText"><?= e($user['friend_code'] ?? '------') ?></span>
                                        </div>
                                        <?php if(!empty($user['friend_code'])): ?>
                                            <button type="button" class="btn btn-outline-primary shadow-sm"
                                                onclick="copyFriendCode(this)" title="Copia codice">
                                                <i class="bi bi-copy"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <p class="small text-muted mt-2 mb-0">Condividilo con i tuoi amici per farli unire alle tue partite private!</p>
                                </div>

                                <hr class="text-muted opacity-25">

                                <form action="<?= url('/friends/add') ?>" method="POST" class="mt-3">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <div class="mb-3">
                                        <label for="friend_code" class="form-label small fw-semibold">Inserisci Codice Amico</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-body-tertiary border-end-0"><i class="bi bi-hash"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0 text-uppercase"
                                                id="friend_code" name="friend_code" placeholder="es. A9F3K2" required>
                                            <button class="btn btn-primary fw-bold" type="submit">Aggiungi</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="<?= $is_own_profile ? 'col-md-7' : 'col-12' ?>">
                        <?php if($is_own_profile && !empty($pendingRequests)): ?>
                            <div class="card shadow-sm border border-top border-warning border-4 rounded-4 p-4 mb-4">
                                <h5 class="fw-bold mb-3 d-flex align-items-center">
                                    <i class="bi bi-person-lines-fill text-warning me-2 fs-4"></i>
                                    <span class="text-body">Richieste in Attesa (<?= count($pendingRequests) ?>)</span>
                                    <span class="badge bg-danger rounded-pill ms-2 shadow-sm" style="font-size: 0.7rem;">Nuove</span>
                                </h5>

                                <div class="list-group list-group-flush bg-transparent profile-scrollable-list">
                                    <?php foreach($pendingRequests as $richiesta): ?>
                                        <div class="list-group-item px-0 py-3 border-bottom border-light bg-transparent">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning text-dark rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold shadow-sm profile-list-avatar">
                                                        <?= strtoupper(substr($richiesta['name'], 0, 1)) ?>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0 fw-bold">
                                                            <a href="<?= url('/profile?username=' . urlencode($richiesta['username'])) ?>" class="text-decoration-none text-body"><?= e($richiesta['name']) ?></a>
                                                        </h6>
                                                        <small class="text-muted"><?= e($richiesta['preferred_role'] ?? 'Giocatore') ?></small>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2 me-2">
                                                    <form action="<?= url('/friends/accept/' . urlencode($richiesta['username'])) ?>" method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold shadow-sm px-3" title="Accetta"><i class="bi bi-check-lg"></i></button>
                                                    </form>
                                                    <form action="<?= url('/friends/reject/' . urlencode($richiesta['username'])) ?>" method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold shadow-sm px-2" title="Rifiuta"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                    <form action="<?= url('/friends/block/' . urlencode($richiesta['username'])) ?>" method="POST"
                                                        onsubmit="return confirm('Vuoi bloccare questo utente in modo permanente?');">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold shadow-sm px-2" title="Blocca"><i class="bi bi-slash-circle"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card shadow-sm border rounded-4 p-4 <?= ($is_own_profile && empty($pendingRequests) && empty($sentPendingRequests)) ? 'h-100' : '' ?>">
                            <h5 class="fw-bold mb-4"><i class="bi bi-people-fill text-success me-2"></i>Amici di <?= e($user['name']) ?></h5>

                            <?php if(empty($friends) && empty($pendingRequests) && empty($sentPendingRequests)): ?>
                                <div class="text-center py-4 bg-body-tertiary rounded-3">
                                    <i class="bi bi-emoji-frown fs-2 text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Nessun amico o richiesta in lista.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush profile-scrollable-list-large">
                                    
                                    <!-- Richieste Ricevute (Da accettare) -->
                                    <?php foreach($pendingRequests as $richiesta): ?>
                                        <div class="list-group-item px-0 py-3 border-bottom border-light">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning text-dark rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold profile-list-avatar">
                                                        <?= strtoupper(substr($richiesta['name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">
                                                            <a href="<?= url('/profile?username=' . urlencode($richiesta['username'])) ?>" class="text-decoration-none text-body"><?= e($richiesta['name']) ?></a>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?= e($richiesta['preferred_role'] ?? 'Giocatore') ?> • 
                                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 font-size-2xs">Richiesta Ricevuta</span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <form action="<?= url('/friends/accept/' . urlencode($richiesta['username'])) ?>" method="POST" class="m-0">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold shadow-sm px-3" title="Accetta"><i class="bi bi-check-lg me-1"></i>Accetta</button>
                                                    </form>
                                                    <form action="<?= url('/friends/reject/' . urlencode($richiesta['username'])) ?>" method="POST" class="m-0">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold shadow-sm px-2" title="Rifiuta"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Richieste Inviate (In attesa) -->
                                    <?php foreach($sentPendingRequests as $richiesta): ?>
                                        <div class="list-group-item px-0 py-3 border-bottom border-light">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-secondary bg-opacity-20 text-secondary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold profile-list-avatar">
                                                        <?= strtoupper(substr($richiesta['name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">
                                                            <a href="<?= url('/profile?username=' . urlencode($richiesta['username'])) ?>" class="text-decoration-none text-body"><?= e($richiesta['name']) ?></a>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?= e($richiesta['preferred_role'] ?? 'Giocatore') ?> • 
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 font-size-2xs">Richiesta Inviata (In attesa)</span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div>
                                                    <form action="<?= url('/friends/remove/' . urlencode($richiesta['username'])) ?>" method="POST" class="m-0"
                                                          onsubmit="return confirm('Sei sicuro di voler annullare questa richiesta di amicizia?');">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold px-3 shadow-sm" title="Annulla Richiesta">Annulla</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Amici Accettati -->
                                    <?php foreach($friends as $amico): ?>
                                        <div class="list-group-item px-0 py-3 border-bottom border-light">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold profile-list-avatar">
                                                        <?= strtoupper(substr($amico['name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">
                                                            <a href="<?= url('/profile?username=' . urlencode($amico['username'])) ?>" class="text-decoration-none text-body"><?= e($amico['name']) ?></a>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?= e($amico['preferred_role'] ?? 'Giocatore') ?> •
                                                            <i class="bi bi-star-fill text-warning"></i>
                                                            <?= $amico['skill_rating'] > 0 ? number_format($amico['skill_rating'], 1) : '-' ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <?php if ($is_own_profile): ?>
                                                    <form action="<?= url('/friends/remove/' . urlencode($amico['username'])) ?>" method="POST"
                                                        class="m-0"
                                                        onsubmit="return confirm('Sei sicuro di voler rimuovere questo amico?');">
                                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-2 shadow-sm" title="Rimuovi"><i class="bi bi-person-dash"></i> Rimuovi</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: IMPOSTAZIONI (OWN ONLY) -->
            <?php if ($is_own_profile): ?>
                <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab" tabindex="0">
                    <div class="row justify-content-center g-4">
                        <div class="col-12 col-lg-10">
                            <div class="card shadow-sm border rounded-4 overflow-hidden">
                                <div class="card-header bg-primary bg-opacity-10 border-bottom-0 p-4">
                                    <h5 class="fw-bold text-primary mb-0"><i class="bi bi-gear-fill me-2"></i>Gestione Account</h5>
                                </div>

                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <!-- Profilo Base -->
                                        <div class="list-group-item p-4 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="fw-bold mb-0 text-uppercase text-muted settings-section-header">Informazioni Personali</h6>
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm"
                                                    data-bs-toggle="modal" data-bs-target="#settingsModal" onclick="switchSettingsTab('modal-info-tab')">
                                                    <i class="bi bi-pencil-square me-1"></i>Modifica
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-12 col-sm-4">
                                                    <span class="d-block fw-semibold text-body mb-1">Nome</span>
                                                    <span class="text-muted"><?= e($user['name']) ?> <?= e($user['last_name'] ?? '') ?></span>
                                                </div>
                                                <div class="col-12 col-sm-4">
                                                    <span class="d-block fw-semibold text-body mb-1">Telefono</span>
                                                    <span class="text-muted"><?= $user['phone'] ? e($user['phone']) : '<em>Non inserito</em>' ?></span>
                                                </div>
                                                <div class="col-12 col-sm-4">
                                                    <span class="d-block fw-semibold text-body mb-1">Ruolo</span>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary"><?= e($user['preferred_role'] ?? 'Jolly') ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sicurezza -->
                                        <div class="list-group-item p-4 border-bottom">
                                            <h6 class="fw-bold mb-4 text-uppercase text-muted settings-section-header">Sicurezza e Accesso</h6>

                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center py-2 mb-3 border-bottom border-light">
                                                <div class="mb-3 mb-md-0">
                                                    <span class="d-block fw-semibold text-body mb-1">Indirizzo Email</span>
                                                    <span class="text-muted"><?= e($user['email']) ?></span>
                                                </div>
                                                <button class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm"
                                                    data-bs-toggle="modal" type="button" data-bs-target="#settingsModal" onclick="switchSettingsTab('modal-email-tab')">
                                                    <i class="bi bi-envelope me-2"></i>Cambia Email
                                                </button>
                                            </div>

                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center py-2">
                                                <div class="mb-3 mb-md-0">
                                                    <span class="d-block fw-semibold text-body mb-1">Password</span>
                                                    <span class="text-muted">************</span>
                                                </div>
                                                <button class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm"
                                                    data-bs-toggle="modal" type="button" data-bs-target="#settingsModal" onclick="switchSettingsTab('modal-pwd-tab')">
                                                    <i class="bi bi-key me-2"></i>Cambia Password
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Footer Info -->
                                        <div class="list-group-item p-4 bg-body-tertiary text-center">
                                            <span class="text-muted small"><i class="bi bi-calendar-check me-1"></i>Iscritto a AlmaKick dal
                                                <strong><?= date('d/m/Y', strtotime($user['created_at'])) ?></strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($is_own_profile): ?>
    <!-- Modal Gestione Account (Schede Unificate) -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="settingsModalLabel">Impostazioni Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                
                <div class="px-3 pt-3">
                    <ul class="nav nav-tabs border-bottom-0 gap-1 bg-light p-1 rounded-3" id="settingsModalTabs" role="tablist" style="font-size: 0.85rem;">
                        <li class="nav-item flex-grow-1 text-center" role="presentation">
                            <button class="nav-link active rounded-2 border-0 w-100 fw-semibold" id="modal-info-tab" data-bs-toggle="tab" data-bs-target="#modal-info-pane" type="button" role="tab" aria-controls="modal-info-pane" aria-selected="true">
                                <i class="bi bi-person me-1"></i>Profilo
                            </button>
                        </li>
                        <li class="nav-item flex-grow-1 text-center" role="presentation">
                            <button class="nav-link rounded-2 border-0 w-100 fw-semibold" id="modal-email-tab" data-bs-toggle="tab" data-bs-target="#modal-email-pane" type="button" role="tab" aria-controls="modal-email-pane" aria-selected="false">
                                <i class="bi bi-envelope me-1"></i>Email
                            </button>
                        </li>
                        <li class="nav-item flex-grow-1 text-center" role="presentation">
                            <button class="nav-link rounded-2 border-0 w-100 fw-semibold" id="modal-pwd-tab" data-bs-toggle="tab" data-bs-target="#modal-pwd-pane" type="button" role="tab" aria-controls="modal-pwd-pane" aria-selected="false">
                                <i class="bi bi-key me-1"></i>Password
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="settingsModalTabsContent">
                    <!-- Tab: Modifica Info -->
                    <div class="tab-pane fade show active" id="modal-info-pane" role="tabpanel" aria-labelledby="modal-info-tab">
                        <form action="<?= url('/profile/info') ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <div class="modal-body pt-3">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Nome</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= e($user['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="last_name" class="form-label fw-semibold">Cognome</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-semibold">Numero di Telefono</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="es. 3331234567">
                                </div>
                                <div class="mb-3">
                                    <label for="preferred_role" class="form-label fw-semibold">Ruolo Preferito</label>
                                    <select class="form-select" id="preferred_role" name="preferred_role">
                                        <option value="Jolly" <?= $user['preferred_role'] === 'Jolly' ? 'selected' : '' ?>>Jolly</option>
                                        <option value="Portiere" <?= $user['preferred_role'] === 'Portiere' ? 'selected' : '' ?>>Portiere</option>
                                        <option value="Difensore" <?= $user['preferred_role'] === 'Difensore' ? 'selected' : '' ?>>Difensore</option>
                                        <option value="Centrocampista" <?= $user['preferred_role'] === 'Centrocampista' ? 'selected' : '' ?>>Centrocampista</option>
                                        <option value="Attaccante" <?= $user['preferred_role'] === 'Attaccante' ? 'selected' : '' ?>>Attaccante</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0">
                                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Salva Modifiche</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Cambia Email -->
                    <div class="tab-pane fade" id="modal-email-pane" role="tabpanel" aria-labelledby="modal-email-tab">
                        <form action="<?= url('/profile/info') ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <div class="modal-body pt-3">
                                <p class="text-muted small">Per motivi di sicurezza, ti chiediamo di confermare la tua password attuale.</p>
                                <div class="mb-3">
                                    <label for="new_email" class="form-label fw-semibold">Nuova Email</label>
                                    <input type="email" class="form-control" id="new_email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="current_password_email" class="form-label fw-semibold">Password Attuale</label>
                                    <input type="password" class="form-control" id="current_password_email" name="current_password" required>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0">
                                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Aggiorna Email</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Cambia Password -->
                    <div class="tab-pane fade" id="modal-pwd-pane" role="tabpanel" aria-labelledby="modal-pwd-tab">
                        <form action="<?= url('/profile/info') ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <div class="modal-body pt-3">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-semibold">Password Attuale</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label fw-semibold">Nuova Password</label>
                                    <input type="password" class="form-control" id="new_password" name="password" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label fw-semibold">Conferma Nuova Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="6">
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0">
                                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Aggiorna Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    function copyFriendCode(btn) {
        const textEl = document.getElementById('friendCodeText');
        if (!textEl) return;
        
        navigator.clipboard.writeText(textEl.innerText).then(() => {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i>';
            btn.classList.replace('btn-outline-primary', 'btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.replace('btn-success', 'btn-outline-primary');
            }, 2000);
        }).catch(err => {
            console.error("Errore durante la copia negli appunti: ", err);
        });
    }

    function switchSettingsTab(tabId) {
        const tabEl = document.getElementById(tabId);
        if (tabEl) {
            const tab = new bootstrap.Tab(tabEl);
            tab.show();
        }
    }
</script>
