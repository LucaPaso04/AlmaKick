<?php
// views/matches/partials/show/actions.php

$my_registration = null;
if (isset($_SESSION['user']['username'])) {
    $currentUser = $_SESSION['user']['username'];
    foreach ($registrations as $reg) {
        if ($reg['username'] === $currentUser && in_array($reg['status'], ['registered', 'waitlist'])) {
            $my_registration = $reg;
            break;
        }
    }
}

$matchStart = strtotime($match['date'] . ' ' . $match['time']);
$canClose = time() >= ($matchStart + 3600);

$timeDiff = $matchStart - time();
$occupied = 0;
foreach ($registrations as $r) {
    if ($r['status'] === 'registered') {
        $occupied += 1 + (int)$r['has_guest'];
    }
}
$canCancelNoPenaltyPlayers = ($timeDiff <= 3600 && $occupied < $match['max_players']);
?>

<?php if ($match['status'] === 'open' || $match['status'] === 'full'): ?>
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body p-4 text-center">
            <?php if ($is_registered && $my_registration): ?>
                
                <?php if ($my_registration['status'] === 'waitlist'): ?>
                    <?php if (!empty($my_registration['offer_expires_at']) && strtotime($my_registration['offer_expires_at']) > time()): ?>
                        <?php 
                        $secondsLeft = strtotime($my_registration['offer_expires_at']) - time();
                        $minutesLeft = ceil($secondsLeft / 60);
                        ?>
                        <div class="alert alert-warning border border-warning border-opacity-25 shadow-sm rounded-4 p-4 mb-3 position-relative overflow-hidden lastminute-offer-card" style="border-left: 5px solid #ffb300 !important;">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center p-2 me-3 shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="bi bi-lightning-charge-fill fs-5 animate-pulse"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bolder text-warning mb-0 fs-5 text-start">⚡ Posto Disponibile Last-Minute!</h5>
                                    <small class="text-warning-emphasis opacity-75 d-block text-start">Si è liberato un posto attivo in squadra.</small>
                                </div>
                            </div>
                            <p class="small mb-3 text-start text-warning-emphasis opacity-90">Un giocatore si è ritirato. Poiché la partita inizia a breve, ti offriamo l'opportunità di scendere in campo! Devi decidere entro il tempo limite indicato sotto.</p>
                            
                            <div class="bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-pill py-2 px-3 d-inline-flex align-items-center gap-2 mb-3 shadow-sm" id="offer-timer" data-expires="<?= strtotime($my_registration['offer_expires_at']) ?>">
                                <i class="bi bi-alarm-fill text-warning"></i>
                                <span class="fw-bold text-warning-emphasis font-monospace" style="font-size: 0.95rem; letter-spacing: 0.5px;">Tempo rimasto: <?= $minutesLeft ?>m</span>
                            </div>
                            
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <form action="<?= url('/matches/' . $match['id'] . '/accept-offer?from=' . urlencode($from)) ?>" method="POST" class="m-0">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm hover-scale transition-all">
                                        <i class="bi bi-check-circle-fill me-1"></i> Accetta Posto
                                    </button>
                                </form>
                                <form action="<?= url('/matches/' . $match['id'] . '/reject-offer?from=' . urlencode($from)) ?>" method="POST" class="m-0"
                                    onsubmit="return confirm('Sei sicuro di voler rifiutare? Rifiutando verrai rimosso dalla panchina.');">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-outline-danger rounded-pill px-4 py-2 shadow-sm hover-scale transition-all">
                                        <i class="bi bi-x-circle me-1"></i> Rifiuta
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const timerEl = document.getElementById("offer-timer");
                            if (timerEl) {
                                const expires = parseInt(timerEl.getAttribute("data-expires"), 10) * 1000;
                                const span = timerEl.querySelector("span");
                                function updateTimer() {
                                    const now = new Date().getTime();
                                    const distance = expires - now;
                                    if (distance < 0) {
                                        span.innerText = "Tempo scaduto!";
                                        span.classList.add("text-danger");
                                        setTimeout(() => { window.location.reload(); }, 1500);
                                        return;
                                    }
                                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                    span.innerText = "Tempo rimasto: " + minutes + "m " + seconds + "s";
                                    setTimeout(updateTimer, 1000);
                                }
                                updateTimer();
                            }
                        });
                        </script>
                    <?php else: ?>
                        <p class="text-warning fw-bold mb-3"><span class="bi bi-hourglass-split me-2"></span>Sei in Panchina (Lista d'attesa)!</p>
                        <small class="d-block mb-3 text-muted">Subentrerai automaticamente in caso di ritiri.</small>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-success fw-bold mb-3"><span class="bi bi-check-circle-fill me-2"></span>Sei Iscritto!</p>
                    <?php if($my_registration['has_guest']): ?>
                        <div class="badge bg-warning text-dark mb-3 px-3 py-2">Stai portando un Ospite (+1)</div>
                        <p class="text-muted small">Dovrai pagare la quota doppia al campo (€<?= number_format($current_quote * 2, 2) ?>)</p>
                    <?php else: ?>
                        <p class="text-muted small">Dovrai pagare la singola quota al campo (€<?= number_format($current_quote, 2) ?>)</p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!$is_host): ?>
                    <form action="<?= url('/matches/' . $match['id'] . '/leave?from=' . urlencode($from)) ?>" method="POST"
                        onsubmit="return confirm('Sei sicuro di volerti ritirare? Potresti perdere Trust Score se mancano meno di 24h.');">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-outline-danger shadow-sm rounded-pill px-4 mt-2">Ritirati dalla partita</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <?php if (!isset($_SESSION['user'])): ?>
                    <p class="text-muted mb-3">Accedi per partecipare a questa partita.</p>
                    <a href="<?= url('/login') ?>" class="btn btn-primary btn-lg shadow-sm rounded-pill px-5 fw-bold">Accedi</a>
                <?php else: ?>
                    <?php if ($match['status'] === 'open'): ?>
                        <p class="text-muted mb-3">Mancano ancora <strong><?= $available_seats ?></strong> posti. Unisciti!</p>
                    <?php else: ?>
                        <p class="text-danger fw-bold mb-3">La partita è completa, ma puoi metterti in panchina.</p>
                    <?php endif; ?>

                    <form action="<?= url('/matches/' . $match['id'] . '/join?from=' . urlencode($from)) ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="form-check d-flex justify-content-center mb-3">
                            <div>
                                <input class="form-check-input" type="checkbox" value="1" id="has_guest" name="has_guest">
                                <label class="form-check-label fw-bold ms-2" for="has_guest">Porto un amico Ospite (+1)</label>
                                <div class="small text-muted text-start ms-4">Pagherai quota doppia per coprilo.</div>
                            </div>
                        </div>
                        <button type="submit"
                            class="btn btn-primary btn-lg shadow-sm rounded-pill px-5 fw-bold w-100 w-md-auto">
                            <span class="bi bi-person-plus-fill me-2"></span><?= $match['status'] === 'open' ? 'Partecipa Ora' : 'Mettiti in Panchina' ?>
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php // Host Controls ?>
    <?php if ($is_host): ?>
        <div class="card shadow-sm border-0 mb-4 rounded-4 border-start border-4 border-warning">
            <div class="card-body p-4">
                <h2 class="fw-bold mb-3 fs-5"><span class="bi bi-gear-fill me-2 text-warning"></span>Gestione Organizzatore</h2>
                <div class="row g-3">
                    <?php // Generate Teams ?>
                    <div class="col-12 col-md-6">
                        <form action="<?= url('/matches/' . $match['id'] . '/generate-teams?from=' . urlencode($from)) ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm">
                                <span class="bi bi-shuffle me-2"></span>Genera Squadre Equilibrate
                            </button>
                        </form>
                    </div>
                    <?php // Close Match ?>
                    <div class="col-12 col-md-6">
                        <?php if ($canClose): ?>
                            <form action="<?= url('/matches/' . $match['id'] . '/close?from=' . urlencode($from)) ?>" method="POST"
                                onsubmit="return confirm('Vuoi concludere la partita? Non sarà più possibile iscriversi.');">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="btn btn-dark w-100 rounded-pill fw-bold shadow-sm">
                                    <span class="bi bi-flag-fill me-2"></span>Termina Partita
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="btn btn-dark w-100 rounded-pill fw-bold shadow-sm opacity-50" disabled
                                title="Potrai terminare la partita un'ora dopo il fischio d'inizio (dal <?= date('d/m \a\l\l\e H:i', $matchStart + 3600) ?>)">
                                <span class="bi bi-flag-fill me-2"></span>Termina Partita
                            </button>
                            <small class="text-muted d-block text-center mt-1" style="font-size: 0.75rem;">Disponibile dal <?= date('d/m \a\l\l\e H:i', $matchStart + 3600) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-3 border-danger opacity-25">
                <p class="small text-danger mb-2">Se annulli per un motivo non legato al meteo a meno di 24h, perderai 40 Trust Score.</p>
                <form action="<?= url('/matches/' . $match['id'] . '/cancel?from=' . urlencode($from)) ?>" method="POST"
                    onsubmit="return confirm('Sei DAVVERO sicuro di voler annullare l\'intera partita?');">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="motivo_meteo" name="motivo_meteo">
                        <label class="form-check-label fw-bold" for="motivo_meteo">Annulla per maltempo (Nessuna penalità)</label>
                    </div>
                    <?php if ($canCancelNoPenaltyPlayers): ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="motivo_giocatori" name="motivo_giocatori">
                            <label class="form-check-label fw-bold text-success" for="motivo_giocatori">Annulla per giocatori insufficienti (Nessuna penalità)</label>
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-outline-danger shadow-sm rounded-pill px-4">
                        <span class="bi bi-x-octagon me-2"></span>Annulla Partita
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
