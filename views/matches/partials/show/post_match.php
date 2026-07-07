<?php


if ($match['status'] === 'finished'):
    $matchDateTime = strtotime($match['date'] . ' ' . $match['time']);
    $isWithin24Hours = (time() < $matchDateTime + 24 * 3600);

    // Find current user registration
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

    <?php // Host controls ?>
    <?php if ($is_host): ?>
        <div class="card shadow-sm border-0 mb-4 rounded-4 border-start border-4 border-success">
            <div class="card-body p-4">
                <h2 class="fw-bold fs-5 mb-4"><span class="bi bi-clipboard-data-fill me-2 text-success"></span>Pannello Host Post-Partita</h2>
                
                <div class="d-flex flex-column gap-4">
                    <!-- Scoreboard section -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <h3 class="h6 fw-bold mb-1"><span class="bi bi-file-earmark-spreadsheet me-2 text-success"></span>Tabellino e Gol</h3>
                            <p class="text-muted small mb-0">Inserisci o modifica il punteggio finale e i marcatori della partita.</p>
                        </div>
                        <div style="min-width: 220px;">
                            <?php if(($match['result_home'] === null) || $isWithin24Hours): ?>
                                <a href="<?= url('/matches/' . $match['id'] . '/report') ?>" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm py-2">
                                    <span class="bi bi-pencil-square me-2"></span><?= ($match['result_home'] !== null) ? 'Modifica Tabellino' : 'Compila Tabellino' ?>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 rounded-pill fw-bold shadow-sm py-2" disabled title="Tempo scaduto (24h)">
                                    <span class="bi bi-lock-fill me-2"></span>Tabellino Chiuso
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- MVP section -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="flex-grow-1">
                            <h3 class="h6 fw-bold mb-1"><span class="bi bi-trophy-fill me-2 text-warning"></span>Gestione MVP</h3>
                            <?php if(!$match['mvp_assigned'] && !$match['mvp_deadline']): ?>
                                <p class="text-muted small mb-0">Imposta una scadenza per consentire ai giocatori di votare l'MVP della partita.</p>
                            <?php elseif(!$match['mvp_assigned'] && $match['mvp_deadline']): ?>
                                <p class="text-muted small mb-0">Le votazioni sono in corso. L'MVP verrà calcolato e assegnato automaticamente alla scadenza.</p>
                            <?php else: ?>
                                <p class="text-muted small mb-0">Le votazioni si sono concluse e l'MVP è stato assegnato.</p>
                            <?php endif; ?>
                        </div>
                        <div style="min-width: 240px;">
                            <?php if(!$match['mvp_assigned'] && !$match['mvp_deadline']): ?>
                                <form action="<?= url('/matches/' . $match['id'] . '/set-mvp-deadline?from=' . urlencode($from)) ?>" method="POST" class="d-flex gap-2 align-items-center mb-0">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <div class="flex-grow-1">
                                        <label for="mvp_deadline_input" class="visually-hidden">Scadenza votazioni MVP</label>
                                        <input type="datetime-local" id="mvp_deadline_input" name="mvp_deadline" class="form-control form-control-sm rounded-pill border-warning" value="<?= date('Y-m-d\TH:i', time() + 24 * 3600) ?>" required min="<?= date('Y-m-d\TH:i', time() + 300) ?>">
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-sm rounded-pill fw-bold shadow-sm text-dark px-3 py-1.5">
                                        Imposta
                                    </button>
                                </form>
                            <?php elseif(!$match['mvp_assigned'] && $match['mvp_deadline']): ?>
                                <div class="text-center py-2 px-3 rounded-4 bg-warning bg-opacity-25 border border-warning shadow-sm">
                                    <small class="fw-bold d-block text-dark mb-1">Scadenza Voti:</small>
                                    <span class="badge bg-warning text-dark fs-6"><?= date('d/m/Y H:i', strtotime($match['mvp_deadline'])) ?></span>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 rounded-pill fw-bold shadow-sm py-2" disabled>
                                    <span class="bi bi-check-circle-fill me-2"></span>MVP Assegnato 🏆
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php // Voting section ?>
    <?php if ($my_reg): 
        $isMvpDeadlinePassed = !empty($match['mvp_deadline']) && (time() > strtotime($match['mvp_deadline']));
        $isVotingOpen = empty($match['mvp_assigned']) && !$isMvpDeadlinePassed;
    ?>
        <div class="card shadow-sm border-0 mb-4 rounded-4">
            <div class="card-body p-4">
                <h2 class="fw-bold mb-1 fs-5"><span class="bi bi-star-fill text-warning me-2"></span>Vota i tuoi Compagni</h2>
                
                <?php if ($isVotingOpen): ?>
                    <p class="text-muted small mb-4">Assegna una valutazione per la Skill di ogni giocatore cliccando sulle stelle. Usa il pollice in giù in caso di comportamenti antisportivi.</p>
 
                    <form action="<?= url('/matches/' . $match['id'] . '/vote?from=' . urlencode($from)) ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        
                        <?php
                        $hasSomeoneToVote = false;
                        foreach ($registrations as $reg):
                            if ($reg['status'] === 'registered' && $reg['username'] !== $_SESSION['user']['username']):
                                // Find existing vote
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
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">
                                                <span class="bi bi-check-lg me-1"></span>Votato: <?= $existing_vote['skill_vote'] ?>⭐
                                            </span>
                                        <?php else: 
                                            $hasSomeoneToVote = true;
                                            ?>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="hidden" name="votes[<?= e($reg['username']) ?>][evaluated_username]" value="<?= e($reg['username']) ?>">
                                                
                                                <!-- Star rating -->
                                                <input type="hidden" name="votes[<?= e($reg['username']) ?>][skill_vote]" id="vote_val_<?= e($reg['username']) ?>" value="">
                                                <div class="star-rating d-flex gap-1" data-username="<?= e($reg['username']) ?>">
                                                     <?php for ($i = 1; $i <= 5; $i++): ?>
                                                         <span class="bi bi-star text-warning fs-5 star-item" data-value="<?= $i ?>" style="cursor: pointer;"></span>
                                                     <?php endfor; ?>
                                                </div>

                                                <!-- Thumb down checkbox -->
                                                <input type="checkbox" name="votes[<?= e($reg['username']) ?>][thumb_down]" value="1" id="td_<?= e($reg['username']) ?>" class="d-none thumb-down-check">
                                                <label class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center p-0 thumb-down-btn" for="td_<?= e($reg['username']) ?>" style="width: 32px; height: 32px;" title="Segnala comportamento grave">
                                                    <span class="bi bi-hand-thumbs-down"></span>
                                                    <span class="visually-hidden">Segnala comportamento grave di <?= e($reg['name']) ?></span>
                                                </label>
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
                                    <span class="bi bi-send-fill me-2"></span>Invia Voti
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="text-muted mb-2"><span class="bi bi-lock-fill fs-2 text-secondary"></span></div>
                        <p class="text-secondary mb-0 fw-semibold">Le votazioni per questa partita sono chiuse.</p>
                        <small class="text-muted">La scadenza per inserire i voti è terminata o l'MVP è già stato proclamato.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
