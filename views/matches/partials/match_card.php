<?php
$isPrivate = ($p['visibility'] ?? 'public') === 'private';
$isHost = isset($_SESSION['user']['username']) && $_SESSION['user']['username'] === $p['host_username'];
$isFriend = isset($friendHostUsernames) && in_array($p['host_username'], $friendHostUsernames);
$isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'super_admin';
$canJoin = !$isPrivate || $isHost || $isFriend;
$canClick = $canJoin || $isAdmin;

$formatLower = strtolower($p['format'] ?? '');
if (str_contains($formatLower, '5v5') || str_contains($formatLower, '5vs5')) {
    $gradient = 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)';
    $bgClass = 'bg-success bg-opacity-10 text-success-emphasis';
} elseif (str_contains($formatLower, '7v7') || str_contains($formatLower, '7vs7') || str_contains($formatLower, '8v8') || str_contains($formatLower, '8vs8')) {
    $gradient = 'linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%)';
    $bgClass = 'bg-info bg-opacity-10 text-info-emphasis';
} elseif (str_contains($formatLower, '11v11') || str_contains($formatLower, '11vs11')) {
    $gradient = 'linear-gradient(135deg, #3A1C71 0%, #D76D77 50%, #FFAF7B 100%)';
    $bgClass = 'bg-danger bg-opacity-10 text-danger-emphasis';
} else {
    $gradient = 'linear-gradient(135deg, #e94057 0%, #f27121 100%)';
    $bgClass = 'bg-warning bg-opacity-10 text-warning-emphasis';
}

$pct = min(100, max(0, round((($p['posti_occupati'] ?? 0) / $p['max_players']) * 100)));
$barClass = 'bg-primary';
if ($pct >= 100) {
    $barClass = 'bg-success';
} elseif (($p['posti_occupati'] ?? 0) == $p['max_players'] - 1) {
    $barClass = 'bg-danger progress-bar-striped progress-bar-animated';
} elseif ($pct >= 80) {
    $barClass = 'bg-warning';
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/matches';
// Rimuovi eventuale parametro ajax=1 per evitare che al ritorno il server restituisca un JSON
$requestUri = str_replace(['?ajax=1&', '&ajax=1&', '&ajax=1', '?ajax=1'], ['?', '&', '', ''], $requestUri);
if (defined('BASE_URL') && BASE_URL !== '' && strpos($requestUri, BASE_URL) === 0) {
    $requestUri = substr($requestUri, strlen(BASE_URL));
}
?>

<div class="col">
    <div class="card h-100 card-partita rounded-4 shadow-sm bg-body overflow-visible mt-3 position-relative d-flex flex-column <?= !empty($p['is_urgent']) ? 'card-partita-urgent' : '' ?>"
     <?= $canClick ? 'onclick="window.location.href=\'' . url('/matches/' . $p['id']) . '?from=' . urlencode($requestUri) . '\';"' : '' ?> 
     style="<?= $canClick ? 'cursor: pointer;' : 'cursor: default;' ?>">
        <div class="match-header rounded-top-4" style="background: <?= $gradient ?>; height: 8px;"></div>

        <!-- Host Avatar Overlapping -->
        <div class="position-absolute top-0 start-50 translate-middle-x" style="margin-top: -12px;">
            <a href="#" title="Organizzatore: <?= e($p['host_name'] ?? '') ?>" onclick="event.stopPropagation();">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['host_name'] ?? '') ?>&background=random&color=fff&size=64"
                    alt="<?= e($p['host_name'] ?? '') ?>" class="rounded-circle border border-3 border-white shadow-sm"
                    style="width: 48px; height: 48px; object-fit: cover;">
            </a>
        </div>

        <div class="card-body position-relative pt-4 mt-2 d-flex flex-column h-100">

            <!-- Badges Superiori -->
            <div class="d-flex justify-content-between align-items-start mb-3 gap-1 flex-wrap">
                <div class="d-flex gap-1 align-items-center">
                    <span class="badge bg-body-secondary text-body border rounded-pill px-2 py-1 shadow-sm">
                        <span class="bi bi-calendar-event me-1 text-primary"></span>
                        <?= e(date('d/m H:i', strtotime($p['date'] . ' ' . $p['time']))) ?>
                    </span>
                    <?php if (($p['visibility'] ?? 'public') === 'public'): ?>
                        <span class="badge bg-body-secondary text-secondary border rounded-pill px-2 py-1 shadow-sm"
                            title="Partita Pubblica">
                            <span class="bi bi-globe"></span>
                        </span>
                    <?php else: ?>
                        <span
                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1 shadow-sm"
                            title="Partita Privata (Solo Amici)">
                            <span class="bi bi-lock-fill"></span>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (($p['status'] ?? '') === 'full' || ($p['posti_occupati'] ?? 0) >= $p['max_players']): ?>
                    <span class="badge bg-success rounded-pill shadow-sm py-1 px-2"><span
                            class="bi bi-check-circle-fill me-1"></span>Completa</span>
                <?php elseif (!empty($p['is_urgent'])): ?>
                    <span
                        class="badge bg-danger rounded-pill shadow-sm py-1 px-2 animate__animated animate__pulse animate__infinite">🔥
                        Urgente: Mancano <?= ($p['max_players'] - $p['posti_occupati']) ?>!</span>
                <?php else: ?>
                    <span
                        class="badge <?= $bgClass ?> rounded-pill shadow-sm py-1 px-2 border border-opacity-25"><?= strtoupper(e($p['format'] ?? '')) ?></span>
                <?php endif; ?>
            </div>

            <!-- Etichette Personali -->
            <div class="mb-2 text-center" style="min-height: 24px;">
                <?php if (isset($_SESSION['user']['username']) && $p['host_username'] === $_SESSION['user']['username']): ?>
                    <span
                        class="badge bg-success bg-opacity-25 text-success-emphasis rounded-pill border border-success border-opacity-50 px-2">
                        <span class="bi bi-person-badge-fill me-1"></span> La tua partita
                    </span>
                <?php elseif (isset($friendHostUsernames) && in_array($p['host_username'], $friendHostUsernames)): ?>
                    <span
                        class="badge bg-warning bg-opacity-25 text-warning-emphasis rounded-pill border border-warning border-opacity-50 px-2">
                        <span class="bi bi-star-fill me-1"></span> Di un amico
                    </span>
                <?php endif; ?>
            </div>

            <h3
                class="card-title h5 fw-bold text-center text-white mb-1 d-flex align-items-center justify-content-center gap-1">
                <span><?= e($p['location'] ?? '') ?></span>
                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($p['location'] ?? '') ?>"
                    target="_blank" rel="noopener noreferrer" class="text-primary fs-6" title="Apri su Google Maps"
                    onclick="event.stopPropagation();">
                    <span class="bi bi-geo-alt-fill"></span>
                </a>
            </h3>
            <p class="text-center text-muted small mb-2"><span class="bi bi-person-fill text-secondary"></span> Org:
                @<?= e($p['host_username'] ?? '') ?></p>

            <!-- Barra di Progresso Iscritti -->
            <div class="my-2 px-1">
                <div class="progress rounded-pill shadow-sm" style="height: 6px;"
                    title="Iscritti: <?= $p['posti_occupati'] ?? 0 ?>/<?= $p['max_players'] ?> (<?= $pct ?>%)">
                    <div class="progress-bar <?= $barClass ?>" role="progressbar" style="width: <?= $pct ?>%"
                        aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-auto pt-2">
                <div>
                    <small class="text-body-secondary d-block mb-1"><span class="bi bi-people-fill me-1"></span>Posti</small>
                    <div class="fw-bold fs-5 text-body">
                        <span
                            class="<?= ($p['posti_occupati'] ?? 0) >= $p['max_players'] ? 'text-success' : 'text-primary' ?>">
                            <?= $p['posti_occupati'] ?? 0 ?>
                        </span>
                        <span class="text-body-secondary fs-6 fw-normal">/ <?= $p['max_players'] ?></span>
                    </div>
                </div>
                <div class="text-end">
                    <small class="text-body-secondary d-block mb-1"><span class="bi bi-cash-stack me-1"></span>Quota</small>
                    <div class="fw-bold text-body fs-5">
                        € <?= number_format($p['total_cost'] / max(1, $p['max_players']), 2) ?>
                    </div>
                </div>
            </div>

            <!-- Azioni Card -->
            <div class="mt-3 pt-3 border-top border-opacity-10 d-block w-100" onclick="event.stopPropagation();">
                <?php if (($p['status'] ?? '') === 'cancelled'): ?>
                    <div
                        class="w-100 text-center py-2 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill fw-bold small">
                        <span class="bi bi-x-circle-fill me-1"></span> Annullata
                    </div>
                <?php elseif (($p['status'] ?? '') === 'finished'): ?>
                    <div
                        class="w-100 text-center py-2 bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill fw-bold small">
                        <span class="bi bi-calendar-check-fill me-1"></span> Conclusa
                    </div>
                <?php else: ?>
                    <?php
                    $userRegStatus = $p['user_registration_status'] ?? null;
                    ?>

                    <?php if (!$canJoin): ?>
                        <div class="w-100 text-center py-2 bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill fw-bold small"
                            style="font-size: 0.75rem;">
                            <span class="bi bi-lock-fill me-1"></span> Privata (Solo Amici)
                        </div>
                    <?php elseif ($userRegStatus): ?>
                        <?php if ($userRegStatus === 'registered'): ?>
                            <div class="w-100 text-center py-2 bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill fw-bold small"
                                style="font-size: 0.75rem;">
                                <span class="bi bi-check-circle-fill me-1"></span> Iscritto
                            </div>
                        <?php else: ?>
                            <div class="w-100 text-center py-2 bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill fw-bold small"
                                style="font-size: 0.75rem;">
                                <span class="bi bi-hourglass-split me-1"></span> In panchina
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (($p['posti_occupati'] ?? 0) >= $p['max_players'] || ($p['status'] ?? '') === 'full'): ?>
                            <form action="<?= url('/matches/' . $p['id'] . '/join?from=' . urlencode($requestUri)) ?>" method="POST" class="m-0 w-100">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit"
                                    class="btn btn-sm btn-outline-warning rounded-pill w-100 py-2 shadow-sm fw-bold">
                                    <span class="bi bi-hourglass-split me-1"></span> Piena (Entra in Panchina)
                                </button>
                            </form>
                        <?php else: ?>
                            <form action="<?= url('/matches/' . $p['id'] . '/join?from=' . urlencode($requestUri)) ?>" method="POST" class="m-0 w-100">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit"
                                    class="btn btn-sm btn-primary rounded-pill w-100 py-2 shadow-sm fw-bold">Unisciti</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>