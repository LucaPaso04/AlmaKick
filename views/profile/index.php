<div class="row justify-content-center mb-5">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0"><span
                    class="bi bi-person-badge-fill text-primary me-2"></span><?= $is_own_profile ? 'Il tuo Profilo' : 'Profilo di ' . e($user['name']) ?>
            </h1>
            <div class="d-md-none d-flex gap-2">
                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                    <a href="<?= url('/admin') ?>"
                        class="btn btn-sm btn-warning rounded-pill shadow-sm px-3 fw-bold text-dark"
                        aria-label="Dashboard Admin" title="Dashboard Admin">
                        <span class="bi bi-shield-lock-fill"></span>
                    </a>
                <?php endif; ?>
                <?php if ($is_own_profile): ?>
                    <form action="<?= url('/logout') ?>" method="POST" class="m-0">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-danger rounded-pill shadow-sm px-3 fw-bold"><span
                                class="bi bi-box-arrow-right"></span> Esci</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm border rounded-4 mb-4 overflow-hidden">
            <div class="profile-banner pt-5 pb-4 px-4">
                <div class="position-absolute top-0 end-0 p-3">
                    <?php if ($user['role'] === 'super_admin'): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><span
                                class="bi bi-shield-lock-fill me-1"></span>Admin</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body px-4 pb-5 text-center mt-n4">
                <div
                    class="position-relative d-inline-block shadow-sm rounded-circle mb-3 mx-auto border border-4 border-white bg-white profile-avatar-wrap">
                    <?php if ($user['avatar']): ?>
                        <img src="<?= url('/' . $user['avatar']) ?>" alt="Foto Profilo"
                            class="w-100 h-100 rounded-circle object-fit-cover">
                    <?php else: ?>
                        <div
                            class="w-100 h-100 rounded-circle d-flex justify-content-center align-items-center bg-light text-primary">
                            <span class="fs-1 fw-bold"><?= strtoupper(substr($user['name'] ?? '', 0, 1)) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_own_profile): ?>
                        <form action="<?= url('/profile/avatar') ?>" method="POST" enctype="multipart/form-data"
                            class="position-absolute bottom-0 end-0 avatar-upload-form" id="avatarForm">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <label for="avatarInput" tabindex="0"
                                class="btn btn-primary rounded-circle shadow d-flex align-items-center justify-content-center border border-2 border-white avatar-upload-btn">
                                <span class="bi bi-camera-fill fs-6"></span>
                                <span class="visually-hidden">Cambia foto profilo</span>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" class="d-none"
                                accept="image/jpeg,image/png,image/webp,image/jpg">
                        </form>
                    <?php endif; ?>
                </div>

                <h2
                    class="h3 fw-bold mb-0 profile-username d-flex align-items-center justify-content-center gap-2 flex-wrap">
                    <span><?= e($user['name']) ?> <?= e($user['last_name'] ?? '') ?></span>
                    <?php if ((int) $user['trust_score'] < 40): ?>
                        <span
                            class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill px-2 font-size-xs"
                            title="Il trust score di questo giocatore è inferiore a 40.">
                            ⚠️ Giocatore Poco Affidabile
                        </span>
                    <?php endif; ?>
                </h2>
                <p class="text-muted mb-3 text-capitalize fw-medium">
                    <span
                        class="bi bi-person-vcard me-1"></span><?= e($user['preferred_role'] ?? 'Ruolo non specificato') ?>
                </p>

                <!-- Social Actions -->
                <?php if (!$is_own_profile): ?>
                    <div class="mb-4">
                        <?php if (!$friendship): ?>
                            <form action="<?= url('/friends/add') ?>" method="POST" class="d-inline-block">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="friend_code" value="<?= e($user['friend_code']) ?>">
                                <button type="submit" class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold">
                                    <span class="bi bi-person-plus-fill me-2"></span>Aggiungi Amico
                                </button>
                            </form>
                        <?php elseif ($friendship['status'] === 'pending'): ?>
                            <?php if ($friendship['sender_username'] === $_SESSION['user']['username']): ?>
                                <button class="btn btn-secondary rounded-pill px-4 fw-bold" disabled>
                                    <span class="bi bi-clock-history me-2"></span>Richiesta Inviata
                                </button>
                            <?php else: ?>
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="<?= url('/friends/accept/' . urlencode($user['username'])) ?>" method="POST"
                                        class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-success rounded-pill shadow-sm px-4 fw-bold">
                                            <span class="bi bi-check-lg me-2"></span>Accetta
                                        </button>
                                    </form>
                                    <form action="<?= url('/friends/reject/' . urlencode($user['username'])) ?>" method="POST"
                                        class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-outline-danger rounded-pill shadow-sm px-4 fw-bold">
                                            <span class="bi bi-x-lg me-2"></span>Rifiuta
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($friendship['status'] === 'accepted'): ?>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="btn btn-success rounded-pill px-4 fw-bold cursor-default">
                                    <span class="bi bi-people-fill me-2"></span>Siete Amici
                                </span>
                                <form action="<?= url('/friends/remove/' . urlencode($user['username'])) ?>" method="POST"
                                    class="m-0" onsubmit="return confirm('Sei sicuro di voler rimuovere questo amico?');">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-outline-danger rounded-pill shadow-sm px-3 fw-bold"
                                        title="Rimuovi">
                                        <span class="bi bi-person-dash"></span>
                                    </button>
                                </form>
                            </div>
                        <?php elseif ($friendship['status'] === 'blocked'): ?>
                            <span class="btn btn-danger rounded-pill px-4 fw-bold cursor-default">
                                <span class="bi bi-slash-circle me-2"></span>Bloccato
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                $is_skill_exceptional = (float) ($user['skill_rating'] ?? 0) > 4.5;
                $is_mvp_exceptional = (int) ($user['mvp_count'] ?? 0) > 5;
                ?>
                <div class="row text-center mt-4 g-3">
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm">
                            <div class="icon-circle icon-primary">
                                <span class="bi bi-controller"></span>
                            </div>
                            <div class="h4 fw-bold mb-0"><?= $user['matches_played'] ?? 0 ?></div>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Presenze</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm">
                            <div class="icon-circle icon-success">
                                <span class="bi bi-calendar-check"></span>
                            </div>
                            <div class="h4 fw-bold mb-0"><?= $matches_hosted ?? 0 ?></div>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Organizzate</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-card rounded-4 p-3 h-100 shadow-sm">
                            <div class="icon-circle icon-danger">
                                <span class="bi bi-bullseye"></span>
                            </div>
                            <div class="h4 fw-bold mb-0"><?= $user['total_goals'] ?? 0 ?></div>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Gol Totali</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div
                            class="stat-card rounded-4 p-3 h-100 shadow-sm <?= $is_skill_exceptional ? 'stat-card-glow-gold' : '' ?>">
                            <div class="icon-circle icon-warning">
                                <span class="bi bi-star-fill"></span>
                            </div>
                            <div class="h4 fw-bold mb-0">
                                <?= $user['skill_rating'] > 0 ? number_format($user['skill_rating'], 1) : '-' ?>
                            </div>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">Skill Media</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div
                            class="stat-card rounded-4 p-3 h-100 shadow-sm <?= $is_mvp_exceptional ? 'stat-card-glow-gold' : '' ?>">
                            <div class="icon-circle icon-info">
                                <span class="bi bi-award-fill"></span>
                            </div>
                            <div class="h4 fw-bold mb-0"><?= $user['mvp_count'] ?? 0 ?></div>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label">MVP 🏆</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div
                            class="stat-card rounded-4 p-3 h-100 shadow-sm d-flex flex-column justify-content-center align-items-center">
                            <?php
                            $ts = (int) $trust_score;
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
                            <div
                                class="trust-circle-container <?= $glow_class ?> mb-2 position-relative shadow-sm bg-body rounded-circle size-60">
                                <svg width="60" height="60" viewBox="0 0 60 60" class="trust-circle-svg">
                                    <circle cx="30" cy="30" r="25" fill="transparent" stroke="rgba(120, 120, 120, 0.15)"
                                        stroke-width="4.5" />
                                    <circle cx="30" cy="30" r="25" fill="transparent" stroke="<?= $stroke_color ?>"
                                        stroke-width="4.5" stroke-dasharray="<?= $circumference ?>"
                                        stroke-dashoffset="<?= $dashoffset ?>" stroke-linecap="round"
                                        class="trust-circle-progress" />
                                </svg>
                                <div
                                    class="position-absolute top-50 start-50 translate-middle fw-bold text-center trust-circle-text">
                                    <?= $ts ?>%
                                </div>
                            </div>
                            <small class="text-muted text-uppercase fw-semibold stat-card-label font-size-xs">Trust
                                Score</small>
                        </div>
                    </div>
                </div>

                <!-- Performance trend -->
                <div class="mt-4 p-3 bg-body border rounded-4 shadow-sm text-start">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold text-muted text-uppercase tracking-wide font-size-xs"><span
                                class="bi bi-graph-up text-primary me-1"></span>Trend Prestazioni (Ultime 5
                            partite)</span>
                        <span
                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 fw-bold font-size-xs">Stato
                            di Forma</span>
                    </div>
                    <?php if (empty($trend_votes)): ?>
                        <div class="text-center py-2 text-muted small">
                            <span class="bi bi-info-circle me-1"></span>Non ci sono valutazioni sufficienti per calcolare il
                            trend.
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
                            <div class="performance-chart-wrapper">
                                <svg viewBox="0 0 <?= $width ?> <?= $height ?>" class="w-100 performance-chart-svg">
                                    <defs>
                                        <linearGradient id="sparklineGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="var(--bs-primary)" stop-opacity="0.25" />
                                            <stop offset="100%" stop-color="var(--bs-primary)" stop-opacity="0.0" />
                                        </linearGradient>
                                    </defs>

                                    <!-- Grid lines and labels -->
                                    <line x1="<?= $padding_x - 5 ?>" y1="<?= $y_3_0 ?>" x2="<?= $width - 15 ?>"
                                        y2="<?= $y_3_0 ?>" stroke="rgba(120, 120, 120, 0.15)" stroke-width="1"
                                        stroke-dasharray="3,3" />
                                    <line x1="<?= $padding_x - 5 ?>" y1="<?= $y_4_5 ?>" x2="<?= $width - 15 ?>"
                                        y2="<?= $y_4_5 ?>" stroke="rgba(120, 120, 120, 0.15)" stroke-width="1"
                                        stroke-dasharray="3,3" />
                                    <text x="<?= $padding_x - 8 ?>" y="<?= $y_3_0 + 3 ?>" text-anchor="end" font-size="8"
                                        font-weight="bold" fill="var(--bs-secondary)" opacity="0.6">3.0</text>
                                    <text x="<?= $padding_x - 8 ?>" y="<?= $y_4_5 + 3 ?>" text-anchor="end" font-size="8"
                                        font-weight="bold" fill="var(--bs-secondary)" opacity="0.6">4.5</text>

                                    <!-- Area fill -->
                                    <path d="<?= $fill_d ?>" fill="url(#sparklineGrad)" />
                                    <!-- Line path -->
                                    <path d="<?= $path_d ?>" fill="none" stroke="var(--bs-primary)" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round" />

                                    <!-- Value markers & Text -->
                                    <?php foreach ($points_array as $pt): ?>
                                        <circle cx="<?= $pt['x'] ?>" cy="<?= $pt['y'] ?>" r="3.5" fill="#ffffff"
                                            stroke="var(--bs-primary)" stroke-width="2" />
                                        <text x="<?= $pt['x'] ?>" y="<?= $pt['y'] - 8 ?>" text-anchor="middle" font-size="8.5"
                                            font-weight="bold" fill="var(--bs-emphasis-color)"
                                            class="performance-chart-text"><?= number_format($pt['val'], 1) ?></text>
                                    <?php endforeach; ?>
                                </svg>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabs navigation -->
        <ul class="nav nav-pills nav-fill bg-body shadow-sm rounded-4 p-2 mb-4 border" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold" id="activity-tab" data-bs-toggle="pill"
                    data-bs-target="#activity" type="button" role="tab" aria-controls="activity"
                    aria-selected="true"><span class="bi bi-activity me-2"></span>Panoramica</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold" id="social-tab" data-bs-toggle="pill"
                    data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false"><span
                        class="bi bi-people-fill me-2"></span>Social & Amici</button>
            </li>
            <?php if ($is_own_profile): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="settings-tab" data-bs-toggle="pill"
                        data-bs-target="#settings" type="button" role="tab" aria-controls="settings"
                        aria-selected="false"><span class="bi bi-gear-fill me-2"></span>Impostazioni</button>
                </li>
            <?php endif; ?>
        </ul>

        <!-- Tabs content -->
        <div class="tab-content" id="profileTabsContent">

            <!-- Tab: Overview -->
            <div class="tab-pane fade show active" id="activity" role="tabpanel" aria-labelledby="activity-tab"
                tabindex="0">
                <?php require VIEW_PATH . '/profile/partials/badges.php'; ?>
                <?php require VIEW_PATH . '/profile/partials/history_tab.php'; ?>

            </div>

            <!-- Tab: Social & Friends -->
            <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab" tabindex="0">
                <?php require VIEW_PATH . '/profile/partials/social_tab.php'; ?>

            </div>

            <!-- Tab: Settings -->
            <?php if ($is_own_profile): ?>
                <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab" tabindex="0">
                    <?php require VIEW_PATH . '/profile/partials/info_tab.php'; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= url('/js/profile.js') ?>"></script>