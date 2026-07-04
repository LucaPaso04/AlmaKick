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
                    <span class="badge bg-danger rounded-pill ms-2 shadow-sm font-size-2xs">Nuove</span>
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
                    
                    <!-- Received requests -->
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

                    <!-- Sent requests -->
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

                    <!-- Friends list -->
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
