<div class="row g-4">
    <!-- Colonna 1: La Scarpa d'Oro (Gol) -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-lg border-0 rounded-4 h-100 overflow-hidden card-danger">
            <header class="card-header-gradient-danger text-white p-4 text-center position-relative">
                <i class="bi bi-bullseye fs-1 mb-2 opacity-75" aria-hidden="true"></i>
                <h3 class="fw-bolder mb-0">La Scarpa d'Oro</h3>
                <small class="opacity-75 fw-medium text-uppercase tracking-wide">Migliori Marcatori</small>
            </header>
            <div class="card-body p-0 bg-body">
                <ul class="list-group list-group-flush mb-0" aria-label="Classifica Migliori Marcatori">
                    <?php if (!empty($scorers)): ?>
                        <?php foreach ($scorers as $index => $user): ?>
                            <?php 
                            $isMe = isset($_SESSION['user']) && $user['username'] === $_SESSION['user']['username'];
                            $avatarUrl = null;
                            if ($user['avatar']) {
                                if (strpos($user['avatar'], 'http://') === 0 || strpos($user['avatar'], 'https://') === 0) {
                                    $avatarUrl = $user['avatar'];
                                } elseif (strpos($user['avatar'], 'uploads/') === 0) {
                                    $avatarUrl = url('/' . $user['avatar']);
                                } else {
                                    $avatarUrl = url('/uploads/' . ltrim($user['avatar'], '/'));
                                }
                            }
                            ?>
                            <?php
                            $podiumClass = '';
                            if ($index === 0) $podiumClass = 'podium-gold';
                            elseif ($index === 1) $podiumClass = 'podium-silver';
                            elseif ($index === 2) $podiumClass = 'podium-bronze';
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3 leaderboard-item <?= $podiumClass ?> <?= $isMe ? 'bg-danger bg-opacity-10' : '' ?>">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 rank-badge rank-<?= $index < 3 ? $index + 1 : 'other' ?>" aria-label="Posizione <?= $index + 1 ?>">
                                        <?php if ($index < 3): ?>
                                            <i class="bi bi-award-fill" aria-hidden="true"></i>
                                        <?php else: ?>
                                            <?= $index + 1 ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="me-3 user-avatar <?= $isMe ? 'border-danger text-danger' : 'border-secondary text-secondary' ?> bg-light">
                                        <?php if ($avatarUrl): ?>
                                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= e($user['name']) ?>" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                                        <?php else: ?>
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">
                                            <a href="<?= url('/profile?username=' . urlencode($user['username'])) ?>" class="text-decoration-none <?= $isMe ? 'text-danger' : 'text-body' ?> leaderboard-link stretched-link"><?= e($user['name'] . ' ' . $user['last_name']) ?></a>
                                            <?php if ($isMe): ?> <span class="badge bg-danger ms-1 small">Tu</span> <?php endif; ?>
                                        </h6>
                                        <small class="text-muted"><i class="bi bi-person-vcard me-1" aria-hidden="true"></i><?= e(getRoleBadge($user['preferred_role'])) ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-danger rounded-pill fs-6 shadow-sm z-2" aria-label="<?= (int)$user['total_goals'] ?> gol"><?= (int)$user['total_goals'] ?> <i class="bi bi-record-circle" aria-hidden="true"></i></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item p-4 text-center text-muted bg-transparent border-0">
                            <i class="bi bi-emoji-frown fs-2 d-block mb-2 opacity-50" aria-hidden="true"></i>
                            Nessun dato disponibile in questa classifica.
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Colonna 2: Hall of Fame (MVP) -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-lg border-0 rounded-4 h-100 overflow-hidden card-warning">
            <header class="card-header-gradient-warning text-white p-4 text-center position-relative">
                <i class="bi bi-star-fill fs-1 mb-2 opacity-75 text-white" aria-hidden="true"></i>
                <h3 class="fw-bolder mb-0 text-white">Hall of Fame</h3>
                <small class="opacity-75 text-white fw-medium text-uppercase tracking-wide">Pluripremiati MVP</small>
            </header>
            <div class="card-body p-0 bg-body">
                <ul class="list-group list-group-flush mb-0" aria-label="Classifica Pluripremiati MVP">
                    <?php if (!empty($mvps)): ?>
                        <?php foreach ($mvps as $index => $user): ?>
                            <?php 
                            $isMe = isset($_SESSION['user']) && $user['username'] === $_SESSION['user']['username'];
                            $avatarUrl = null;
                            if ($user['avatar']) {
                                if (strpos($user['avatar'], 'http://') === 0 || strpos($user['avatar'], 'https://') === 0) {
                                    $avatarUrl = $user['avatar'];
                                } elseif (strpos($user['avatar'], 'uploads/') === 0) {
                                    $avatarUrl = url('/' . $user['avatar']);
                                } else {
                                    $avatarUrl = url('/uploads/' . ltrim($user['avatar'], '/'));
                                }
                            }
                            ?>
                            <?php
                            $podiumClass = '';
                            if ($index === 0) $podiumClass = 'podium-gold';
                            elseif ($index === 1) $podiumClass = 'podium-silver';
                            elseif ($index === 2) $podiumClass = 'podium-bronze';
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3 leaderboard-item <?= $podiumClass ?> <?= $isMe ? 'bg-warning bg-opacity-10' : '' ?>">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 rank-badge rank-<?= $index < 3 ? $index + 1 : 'other' ?>" aria-label="Posizione <?= $index + 1 ?>">
                                        <?php if ($index < 3): ?>
                                            <i class="bi bi-award-fill" aria-hidden="true"></i>
                                        <?php else: ?>
                                            <?= $index + 1 ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="me-3 user-avatar <?= $isMe ? 'border-warning text-warning' : 'border-secondary text-secondary' ?> bg-light">
                                        <?php if ($avatarUrl): ?>
                                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= e($user['name']) ?>" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                                        <?php else: ?>
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">
                                            <a href="<?= url('/profile?username=' . urlencode($user['username'])) ?>" class="text-decoration-none <?= $isMe ? 'text-warning text-darken' : 'text-body' ?> leaderboard-link stretched-link"><?= e($user['name'] . ' ' . $user['last_name']) ?></a>
                                            <?php if ($isMe): ?> <span class="badge bg-warning text-dark ms-1 small">Tu</span> <?php endif; ?>
                                        </h6>
                                        <small class="text-muted"><i class="bi bi-person-vcard me-1" aria-hidden="true"></i><?= e(getRoleBadge($user['preferred_role'])) ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark rounded-pill fs-6 shadow-sm z-2" aria-label="<?= (int)$user['mvp_count'] ?> titoli MVP"><?= (int)$user['mvp_count'] ?> <i class="bi bi-trophy" aria-hidden="true"></i></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item p-4 text-center text-muted bg-transparent border-0">
                            <i class="bi bi-emoji-frown fs-2 d-block mb-2 opacity-50" aria-hidden="true"></i>
                            Nessun dato disponibile in questa classifica.
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Colonna 3: Top Players (Skill) -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-lg border-0 rounded-4 h-100 overflow-hidden card-info">
            <header class="card-header-gradient-info text-white p-4 text-center position-relative">
                <i class="bi bi-patch-check-fill fs-1 mb-2 opacity-75" aria-hidden="true"></i>
                <h3 class="fw-bolder mb-0">Top Players</h3>
                <small class="opacity-75 fw-medium text-uppercase tracking-wide">Miglior Skill Rating</small>
            </header>
            <div class="card-body p-0 bg-body">
                <ul class="list-group list-group-flush mb-0" aria-label="Classifica Miglior Skill Rating">
                    <?php if (!empty($rated)): ?>
                        <?php foreach ($rated as $index => $user): ?>
                            <?php 
                            $isMe = isset($_SESSION['user']) && $user['username'] === $_SESSION['user']['username'];
                            $avatarUrl = null;
                            if ($user['avatar']) {
                                if (strpos($user['avatar'], 'http://') === 0 || strpos($user['avatar'], 'https://') === 0) {
                                    $avatarUrl = $user['avatar'];
                                } elseif (strpos($user['avatar'], 'uploads/') === 0) {
                                    $avatarUrl = url('/' . $user['avatar']);
                                } else {
                                    $avatarUrl = url('/uploads/' . ltrim($user['avatar'], '/'));
                                }
                            }
                            ?>
                            <?php
                            $podiumClass = '';
                            if ($index === 0) $podiumClass = 'podium-gold';
                            elseif ($index === 1) $podiumClass = 'podium-silver';
                            elseif ($index === 2) $podiumClass = 'podium-bronze';
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3 leaderboard-item <?= $podiumClass ?> <?= $isMe ? 'bg-info bg-opacity-10' : '' ?>">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 rank-badge rank-<?= $index < 3 ? $index + 1 : 'other' ?>" aria-label="Posizione <?= $index + 1 ?>">
                                        <?php if ($index < 3): ?>
                                            <i class="bi bi-award-fill" aria-hidden="true"></i>
                                        <?php else: ?>
                                            <?= $index + 1 ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="me-3 user-avatar <?= $isMe ? 'border-info text-info' : 'border-secondary text-secondary' ?> bg-light">
                                        <?php if ($avatarUrl): ?>
                                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= e($user['name']) ?>" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                                        <?php else: ?>
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">
                                            <a href="<?= url('/profile?username=' . urlencode($user['username'])) ?>" class="text-decoration-none <?= $isMe ? 'text-info text-darken' : 'text-body' ?> leaderboard-link stretched-link"><?= e($user['name'] . ' ' . $user['last_name']) ?></a>
                                            <?php if ($isMe): ?> <span class="badge bg-info ms-1 small text-white">Tu</span> <?php endif; ?>
                                        </h6>
                                        <small class="text-muted"><i class="bi bi-person-vcard me-1" aria-hidden="true"></i><?= e(getRoleBadge($user['preferred_role'])) ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded-pill fs-6 shadow-sm text-white z-2" aria-label="Valutazione <?= number_format($user['skill_rating'], 1) ?> stelle"><?= number_format($user['skill_rating'], 1) ?> <i class="bi bi-star-fill" aria-hidden="true"></i></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item p-4 text-center text-muted bg-transparent border-0">
                            <i class="bi bi-info-circle fs-2 d-block mb-2 opacity-50" aria-hidden="true"></i>
                            Dati insufficienti. Le statistiche compaiono dopo 3 partite giocate!
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
