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

        <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
            <div class="bg-primary pt-5 pb-3 px-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3">
                    <?php if ($user['role'] === 'super_admin'): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-shield-lock-fill me-1"></i>Admin</span>
                    <?php endif; ?>
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

                <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
                    <?php if($is_friend): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2 fs-6 shadow-sm d-flex align-items-center"><i class="bi bi-check-circle-fill me-1"></i> Amici</span>
                        <form action="<?= url('/friends/remove/' . urlencode($user['username'])) ?>" method="POST" onsubmit="return confirm('Sei sicuro di voler rimuovere questo amico?');">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="btn btn-outline-danger rounded-pill px-3 fw-bold shadow-sm"><i class="bi bi-person-dash me-1"></i> Rimuovi</button>
                        </form>
                    <?php elseif($sent_request): ?>
                        <button disabled class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm opacity-75"><i class="bi bi-hourglass-split me-1"></i> In attesa di conferma</button>
                    <?php elseif($received_request): ?>
                        <div class="d-flex gap-2 align-items-center">
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
                        <form action="<?= url('/friends/add') ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="friend_code" value="<?= e($user['friend_code']) ?>">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-person-plus-fill me-1"></i> Aggiungi Amico</button>
                        </form>
                    <?php endif; ?>

                    <form action="<?= url('/friends/block/' . urlencode($user['username'])) ?>" method="POST" onsubmit="return confirm('Vuoi bloccare questo utente in modo permanente? Non potrà più vedere il tuo profilo né contattarti.');">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-outline-secondary rounded-pill px-3 fw-bold shadow-sm"><i class="bi bi-slash-circle me-1"></i> Blocca</button>
                    </form>

                    <button type="button" class="btn btn-outline-danger rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#reportUserModal">
                        <i class="bi bi-flag-fill me-1"></i> Segnala
                    </button>
                </div>

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
                        <div class="p-3 bg-body-tertiary rounded-4 h-100">
                            <i class="bi bi-star-fill fs-3 text-warning mb-2"></i>
                            <h4 class="fw-bold mb-0"><?= $user['skill_rating'] > 0 ? number_format($user['skill_rating'], 1) : '-' ?></h4>
                            <small class="text-muted">Skill Media</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-body-tertiary rounded-4 h-100">
                            <i class="bi bi-award-fill fs-3 text-info mb-2"></i>
                            <h4 class="fw-bold mb-0"><?= $user['mvp_count'] ?? 0 ?></h4>
                            <small class="text-muted">MVP 🏆</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-body-tertiary rounded-4 h-100">
                            <i class="bi bi-shield-check fs-3 text-<?= $trust_score >= 80 ? 'success' : ($trust_score >= 50 ? 'warning' : 'danger') ?> mb-2"></i>
                            <h4 class="fw-bold mb-0"><?= $trust_score ?>%</h4>
                            <small class="text-muted">Trust Score</small>
                        </div>
                    </div>
                </div>
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
