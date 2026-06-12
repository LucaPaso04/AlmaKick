<?php
// views/matches/partials/show/post_match.php

if ($match['status'] === 'finished'):
    $matchDateTime = strtotime($match['date'] . ' ' . $match['time']);
    $isWithin24Hours = (time() < $matchDateTime + 24 * 3600);

    // Trova iscrizione dell'utente corrente per verificare se è un partecipante attivo
    $my_reg = null;
    if (isset($_SESSION['user']['username'])) {
        $currentUser = $_SESSION['user']['username'];
        foreach ($registrations as $reg) {
            if ($reg['username'] === $currentUser && $reg['status'] === 'registered') {
                $my_reg = $reg;
                break;
            }
        }
    }
    ?>

    <?php // Host: Report + MVP ?>
    <?php if ($is_host): ?>
        <div class="card shadow-sm border-0 mb-4 rounded-4 border-start border-4 border-success">
            <div class="card-body p-4">
                <h2 class="fw-bold fs-5"><i class="bi bi-clipboard-data-fill me-2 text-success"></i>Pannello Host Post-Partita</h2>
                <div class="row g-3 mt-2">
                    <div class="col-12 col-md-6">
                        <div class="h-100 d-flex flex-column justify-content-center">
                            <?php if($isWithin24Hours): ?>
                                <a href="<?= url('/matches/' . $match['id'] . '/report') ?>" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm py-3">
                                    <i class="bi bi-pencil-square me-2"></i><?= ($match['result_home'] !== null) ? 'Modifica Tabellino' : 'Compila Tabellino' ?>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 rounded-pill fw-bold shadow-sm py-3" disabled title="Tempo scaduto (24h)">
                                    <i class="bi bi-lock-fill me-2"></i>Tabellino Chiuso
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="h-100 d-flex flex-column justify-content-center">
                            <?php if(!$match['mvp_assigned'] && !$match['mvp_deadline']): ?>
                                <form action="<?= url('/matches/' . $match['id'] . '/set-mvp-deadline?from=' . urlencode($from)) ?>" method="POST" class="d-flex flex-column gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <label class="form-label small fw-bold mb-0">Scadenza Voti (Assegnaz. MVP):</label>
                                    <input type="datetime-local" name="mvp_deadline" class="form-control form-control-sm rounded-pill border-warning" value="<?= date('Y-m-d\TH:i', time() + 24 * 3600) ?>" required min="<?= date('Y-m-d\TH:i', time() + 300) ?>">
                                    <button type="submit" class="btn btn-warning w-100 rounded-pill fw-bold shadow-sm text-dark">
                                        <i class="bi bi-clock-history me-2"></i>Imposta Scadenza
                                    </button>
                                </form>
                            <?php elseif(!$match['mvp_assigned'] && $match['mvp_deadline']): ?>
                                <div class="text-center p-3 rounded-4 bg-warning bg-opacity-25 border border-warning shadow-sm">
                                    <small class="fw-bold d-block text-dark mb-1">Scadenza Voti impostata al:</small>
                                    <span class="badge bg-warning text-dark fs-6"><?= date('d/m/Y H:i', strtotime($match['mvp_deadline'])) ?></span>
                                    <small class="d-block mt-2 text-muted" style="font-size: 0.75rem;">L'MVP verrà assegnato in automatico allo scadere.</small>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 rounded-pill fw-bold shadow-sm py-3" disabled>
                                    <i class="bi bi-check-circle-fill me-2"></i>MVP Assegnato 🏆
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php // Voting Section (All participants) ?>
    <?php if ($my_reg): ?>
        <div class="card shadow-sm border-0 mb-4 rounded-4">
            <div class="card-body p-4">
                <h2 class="fw-bold mb-3 fs-5"><i class="bi bi-star-fill text-warning me-2"></i>Vota i tuoi Compagni</h2>
                <p class="text-muted small mb-4">Assegna da 1 a 5 stelle per la Skill di ogni giocatore. Il Pollice in giù è una segnalazione seria.</p>

                <form action="<?= url('/matches/' . $match['id'] . '/vote?from=' . urlencode($from)) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <?php
                    $hasSomeoneToVote = false;
                    foreach ($registrations as $reg):
                        if ($reg['status'] === 'registered' && $reg['username'] !== $_SESSION['user']['username']):
                            // Cerca se esiste già un voto dell'utente loggato per questo giocatore
                            $existing_vote = null;
                            foreach ($evaluations as $eval) {
                                if ($eval['evaluated_username'] === $reg['username']) {
                                    $existing_vote = $eval;
                                    break;
                                }
                            }

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
                            <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="bg-<?= $reg['team'] === 'home' ? 'danger' : ($reg['team'] === 'away' ? 'primary' : 'secondary') ?> text-white rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold match-show-avatar"
                                         style="<?= $regAvatarUrl ? 'background-image: url(' . htmlspecialchars($regAvatarUrl) . '); background-size: cover; background-position: center;' : '' ?>">
                                        <?php if(!$regAvatarUrl): ?>
                                            <?= strtoupper(substr($reg['name'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="fw-bold"><a href="<?= url('/profile?username=' . urlencode($reg['username'])) ?>" class="text-decoration-none text-body"><?= e($reg['name']) ?></a></span>
                                        <?php if($reg['goals_scored'] > 0): ?>
                                            <span class="badge bg-success ms-1">⚽ <?= $reg['goals_scored'] ?></span>
                                        <?php endif; ?>
                                        <small class="text-muted d-block"><?= e($reg['preferred_role'] ?: 'N/D') ?></small>
                                    </div>
                                </div>

                                <div>
                                    <?php if($existing_vote): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                            <i class="bi bi-check-lg me-1"></i>Votato: <?= $existing_vote['skill_vote'] ?>⭐
                                        </span>
                                    <?php else: 
                                        $hasSomeoneToVote = true;
                                        ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="votes[<?= e($reg['username']) ?>][evaluated_username]" value="<?= e($reg['username']) ?>">
                                            <select name="votes[<?= e($reg['username']) ?>][skill_vote]" class="form-select form-select-sm rounded-pill border-primary" style="width: 75px;">
                                                <option value="">⭐</option>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <option value="<?= $i ?>"><?= $i ?>⭐</option>
                                                <?php endfor; ?>
                                            </select>
                                            <div class="form-check form-check-inline mb-0" title="Segnala comportamento grave">
                                                <input class="form-check-input" type="checkbox" name="votes[<?= e($reg['username']) ?>][thumb_down]" value="1" id="td_<?= e($reg['username']) ?>">
                                                <label class="form-check-label" for="td_<?= e($reg['username']) ?>">👎</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php 
                        endif;
                    endforeach; 
                    ?>

                    <?php if($hasSomeoneToVote): ?>
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm py-2">
                                <i class="bi bi-send-fill me-2"></i>Invia Voti
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
