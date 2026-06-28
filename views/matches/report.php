<style>
.team-dropzone {
    transition: background-color 0.2s, border-color 0.2s;
}
.team-dropzone.dragover-home {
    background-color: rgba(220, 53, 69, 0.1) !important;
    border-color: #dc3545 !important;
}
.team-dropzone.dragover-away {
    background-color: rgba(13, 110, 253, 0.1) !important;
    border-color: #0d6efd !important;
}
.player-card-draggable {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.player-card-draggable:hover {
    transform: translateY(-2px);
    box-shadow: 0 .25rem .75rem rgba(0,0,0,.08) !important;
}
.player-card-draggable.dragging {
    opacity: 0.4;
    cursor: grabbing;
}
</style>

<div class="row justify-content-center my-4">
    <div class="col-12 col-lg-10">
        <!-- Header Section -->
        <div class="d-flex align-items-center mb-4">
            <a href="<?= url('/matches/' . $match['id']) ?>" class="btn btn-light rounded-circle me-3 shadow-sm border-0">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="h3 fw-bold mb-0">📋 Tabellino Post-Partita</h1>
        </div>

        <!-- Real-time Validation Alert -->
        <div id="validation-alert" class="alert alert-danger d-none rounded-4 mb-4 shadow-sm border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3" aria-hidden="true"></i>
                <div id="validation-alert-msg"></div>
            </div>
        </div>

        <form action="<?= url('/matches/' . $match['id'] . '/report') ?>" method="POST" class="no-spinner">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">

            <!-- Punteggio Finale Card -->
            <div class="card shadow-sm border-0 mb-4 rounded-4 bg-body">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-center"><i class="bi bi-trophy-fill text-warning me-2" aria-hidden="true"></i>Punteggio Finale</h5>
                    <div class="row align-items-center justify-content-center g-3">
                        <div class="col-5 text-center">
                            <label for="result_home" class="form-label fw-bold text-danger fs-5">🔴 Home</label>
                            <input type="number" id="result_home" name="result_home" class="form-control form-control-lg text-center fw-bold fs-3 rounded-3"
                                value="<?= e(isset($oldInput['result_home']) ? $oldInput['result_home'] : ($match['result_home'] ?? 0)) ?>" min="0" required>
                        </div>
                        <div class="col-2 text-center">
                            <span class="fs-2 fw-bold text-muted" aria-hidden="true">vs</span>
                        </div>
                        <div class="col-5 text-center">
                            <label for="result_away" class="form-label fw-bold text-primary fs-5">🔵 Away</label>
                            <input type="number" id="result_away" name="result_away" class="form-control form-control-lg text-center fw-bold fs-3 rounded-3"
                                value="<?= e(isset($oldInput['result_away']) ? $oldInput['result_away'] : ($match['result_away'] ?? 0)) ?>" min="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne Squadre con Drag & Drop -->
            <div class="row g-4 mb-4">
                <!-- Colonna Home (Red) -->
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 rounded-4 h-100 border-top border-4 border-danger bg-body">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-2 text-danger d-flex align-items-center justify-content-between">
                                <span>🔴 Squadra Home</span>
                                <span class="badge bg-danger rounded-pill fs-7" id="home-count">0 iscritti (0 gioc.)</span>
                            </h5>
                            <p class="text-muted small mb-3">Trascina qui i giocatori che hanno giocato in questa squadra.</p>
                            
                            <!-- Dropzone Home -->
                            <div id="team-home-zone" class="team-dropzone p-3 rounded-4 border border-2 border-dashed bg-light bg-opacity-25" style="min-height: 300px; border-color: rgba(220,53,69,0.15);">
                                <!-- Giocatori inseriti via JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonna Away (Blue) -->
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 rounded-4 h-100 border-top border-4 border-primary bg-body">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-2 text-primary d-flex align-items-center justify-content-between">
                                <span>🔵 Squadra Away</span>
                                <span class="badge bg-primary rounded-pill fs-7" id="away-count">0 iscritti (0 gioc.)</span>
                            </h5>
                            <p class="text-muted small mb-3">Trascina qui i giocatori che hanno giocato in questa squadra.</p>
                            
                            <!-- Dropzone Away -->
                            <div id="team-away-zone" class="team-dropzone p-3 rounded-4 border border-2 border-dashed bg-light bg-opacity-25" style="min-height: 300px; border-color: rgba(13,110,253,0.15);">
                                <!-- Giocatori inseriti via JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Giocatori Disponibili da Agganciare alle Colonne -->
            <div id="players-source-container" class="d-none">
                <?php
                // Unisce tutti i giocatori registrati (inclusi quelli non assegnati)
                $allPlayers = array_merge($home_team, $away_team, $unassigned);
                foreach ($allPlayers as $reg):
                    $startingTeam = $reg['team'];
                    // Di base se nullo lo assegniamo a home per non avere giocatori "orfani"
                    if (empty($startingTeam)) {
                        $startingTeam = 'home';
                    }
                    $avatarMonogram = strtoupper(substr($reg['name'], 0, 1));
                ?>
                    <!-- Elemento Giocatore Trascinabile -->
                    <div class="player-card-draggable card mb-3 border-0 bg-body-secondary rounded-4 shadow-sm p-3 position-relative" 
                         draggable="true" 
                         data-reg-id="<?= $reg['id'] ?>"
                         data-starting-team="<?= $startingTeam ?>"
                         style="cursor: grab;">
                        
                        <!-- Input Nascosto per inviare la squadra sul DB -->
                        <input type="hidden" name="teams[<?= $reg['id'] ?>]" id="team_input_<?= $reg['id'] ?>" value="<?= $startingTeam ?>">

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle text-muted me-3 fs-5" aria-hidden="true">
                                    <i class="bi bi-grip-vertical"></i>
                                </div>
                                <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold shadow-sm" style="width: 38px; height: 38px;" aria-hidden="true">
                                    <?= e($avatarMonogram) ?>
                                </div>
                                <div>
                                    <span class="fw-bold d-block"><?= e($reg['name']) ?></span>
                                    <small class="text-muted"><i class="bi bi-person-vcard me-1" aria-hidden="true"></i><?= e($reg['preferred_role'] ?? 'N/D') ?></small>
                                </div>
                            </div>

                            <!-- Sezione Gol (Giocatore + eventuale Ospite) -->
                            <div class="d-flex align-items-center gap-3">
                                <!-- Gol del Giocatore -->
                                <div class="text-end">
                                    <label for="goals_<?= $reg['id'] ?>" class="small text-muted d-block mb-1">Gol:</label>
                                    <input type="number" id="goals_<?= $reg['id'] ?>" name="goals[<?= $reg['id'] ?>]" 
                                           data-reg-id="<?= $reg['id'] ?>"
                                           data-team="<?= $startingTeam ?>" 
                                           class="form-control text-center fw-bold rounded-3 player-goals-input" 
                                           value="<?= e(isset($oldInput['goals'][$reg['id']]) ? $oldInput['goals'][$reg['id']] : ($reg['goals_scored'] ?? 0)) ?>" 
                                           min="0" style="width: 70px;">
                                </div>

                                <!-- Gol dell'Ospite (se has_guest = 1) -->
                                <?php if ($reg['has_guest']): ?>
                                    <div class="text-end border-start ps-3">
                                        <label for="guest_goals_<?= $reg['id'] ?>" class="small text-info d-block mb-1">+1 Ospite:</label>
                                        <input type="number" id="guest_goals_<?= $reg['id'] ?>" name="guest_goals[<?= $reg['id'] ?>]" 
                                               data-reg-id="<?= $reg['id'] ?>"
                                               data-team="<?= $startingTeam ?>" 
                                               class="form-control text-center fw-bold text-info border-info rounded-3 guest-goals-input" 
                                               value="<?= e(isset($oldInput['guest_goals'][$reg['id']]) ? $oldInput['guest_goals'][$reg['id']] : 0) ?>" 
                                               min="0" style="width: 70px;" title="Gol segnati dall'ospite">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm mb-5 mt-3 py-2.5">
                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>Salva Tabellino e Chiudi
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var homeZone = document.getElementById('team-home-zone');
    var awayZone = document.getElementById('team-away-zone');
    var sourceContainer = document.getElementById('players-source-container');

    // Distribuzione iniziale dei giocatori nelle due colonne
    var draggables = sourceContainer.querySelectorAll('.player-card-draggable');
    draggables.forEach(function(card) {
        var startingTeam = card.getAttribute('data-starting-team');
        if (startingTeam === 'away') {
            awayZone.appendChild(card);
        } else {
            homeZone.appendChild(card);
        }
    });

    // Ricalcola il conteggio degli iscritti e il peso complessivo (giocatori + ospiti)
    function updateCounts() {
        var homeCount = homeZone.querySelectorAll('.player-card-draggable').length;
        var awayCount = awayZone.querySelectorAll('.player-card-draggable').length;

        var homeWeight = 0;
        homeZone.querySelectorAll('.player-card-draggable').forEach(function(card) {
            homeWeight += 1 + (card.querySelector('.guest-goals-input') ? 1 : 0);
        });

        var awayWeight = 0;
        awayZone.querySelectorAll('.player-card-draggable').forEach(function(card) {
            awayWeight += 1 + (card.querySelector('.guest-goals-input') ? 1 : 0);
        });

        document.getElementById('home-count').textContent = homeCount + ' iscritti (' + homeWeight + ' gioc.)';
        document.getElementById('away-count').textContent = awayCount + ' iscritti (' + awayWeight + ' gioc.)';
    }
    updateCounts();

    // Eventi Drag & Drop
    draggables.forEach(function(card) {
        card.addEventListener('dragstart', function(e) {
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.getAttribute('data-reg-id'));
        });

        card.addEventListener('dragend', function() {
            card.classList.remove('dragging');
        });
    });

    [homeZone, awayZone].forEach(function(zone) {
        var teamName = zone.id === 'team-home-zone' ? 'home' : 'away';

        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        zone.addEventListener('dragenter', function(e) {
            e.preventDefault();
            zone.classList.add(teamName === 'home' ? 'dragover-home' : 'dragover-away');
        });

        zone.addEventListener('dragleave', function() {
            zone.classList.remove(teamName === 'home' ? 'dragover-home' : 'dragover-away');
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            zone.classList.remove(teamName === 'home' ? 'dragover-home' : 'dragover-away');

            var regId = e.dataTransfer.getData('text/plain');
            var card = document.querySelector('.player-card-draggable[data-reg-id="' + regId + '"]');
            if (card) {
                zone.appendChild(card);

                // Aggiorna l'input hidden per inviare la nuova squadra al server
                var teamInput = document.getElementById('team_input_' + regId);
                if (teamInput) {
                    teamInput.value = teamName;
                }

                // Aggiorna l'attributo data-team per i calcoli JS in tempo reale
                card.querySelectorAll('input[type="number"]').forEach(function(input) {
                    input.setAttribute('data-team', teamName);
                });

                updateCounts();
                validateForm();
            }
        });
    });

    // Validazione dei Gol in tempo reale
    var form = document.querySelector('form');
    var resultHomeInput = document.getElementById('result_home');
    var resultAwayInput = document.getElementById('result_away');
    var validationAlert = document.getElementById('validation-alert');
    var validationAlertMsg = document.getElementById('validation-alert-msg');
    var submitBtn = form.querySelector('button[type="submit"]');

    function validateForm(e) {
        var resultHome = parseInt(resultHomeInput.value) || 0;
        var resultAway = parseInt(resultAwayInput.value) || 0;

        var sumHome = 0;
        var sumAway = 0;

        document.querySelectorAll('input[data-team="home"]').forEach(function(input) {
            if (input.classList.contains('player-goals-input') || input.classList.contains('guest-goals-input')) {
                sumHome += parseInt(input.value) || 0;
            }
        });

        document.querySelectorAll('input[data-team="away"]').forEach(function(input) {
            if (input.classList.contains('player-goals-input') || input.classList.contains('guest-goals-input')) {
                sumAway += parseInt(input.value) || 0;
            }
        });

        var errors = [];
        if (sumHome !== resultHome) {
            errors.push('La somma dei gol dei singoli giocatori e ospiti <strong>Home</strong> (' + sumHome + ') non corrisponde al risultato finale inserito (' + resultHome + ').');
        }
        if (sumAway !== resultAway) {
            errors.push('La somma dei gol dei singoli giocatori e ospiti <strong>Away</strong> (' + sumAway + ') non corrisponde al risultato finale inserito (' + resultAway + ').');
        }

        if (errors.length > 0) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            validationAlertMsg.innerHTML = errors.join('<br>');
            validationAlert.classList.remove('d-none');
            if (e) {
                validationAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        } else {
            validationAlert.classList.add('d-none');
            return true;
        }
    }

    form.addEventListener('submit', function(e) {
        var isValid = validateForm(e);
        if (isValid && submitBtn) {
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Salvataggio in corso...
            `;
            setTimeout(function() {
                submitBtn.disabled = true;
            }, 0);
        }
    });

    resultHomeInput.addEventListener('input', function() { validateForm(); });
    resultAwayInput.addEventListener('input', function() { validateForm(); });

    // Ascolto dinamico degli input gol (anche dopo spostamenti drag & drop)
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('player-goals-input') || e.target.classList.contains('guest-goals-input')) {
            validateForm();
        }
    });
});
</script>
