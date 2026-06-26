<div class="row justify-content-center my-4">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= url('/matches/' . $match['id']) ?>" class="btn btn-light rounded-circle me-3 shadow-sm border-0">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="h3 fw-bold mb-0">📋 Tabellino Post-Partita</h1>
        </div>

        <!-- Real-time Validation Alert -->
        <div id="validation-alert" class="alert alert-danger d-none rounded-4 mb-4 shadow-sm border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div id="validation-alert-msg"></div>
            </div>
        </div>

        <form action="<?= url('/matches/' . $match['id'] . '/report') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">

            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-center"><i class="bi bi-trophy-fill text-warning me-2"></i>Risultato Finale</h5>
                    <div class="row align-items-center justify-content-center g-3">
                        <div class="col-5 text-center">
                            <label class="form-label fw-bold text-danger fs-5">🔴 Home</label>
                            <input type="number" name="result_home" class="form-control form-control-lg text-center fw-bold fs-3 rounded-3"
                                value="<?= e(isset($oldInput['result_home']) ? $oldInput['result_home'] : ($match['result_home'] ?? 0)) ?>" min="0" required>
                        </div>
                        <div class="col-2 text-center">
                            <span class="fs-2 fw-bold text-muted">vs</span>
                        </div>
                        <div class="col-5 text-center">
                            <label class="form-label fw-bold text-primary fs-5">🔵 Away</label>
                            <input type="number" name="result_away" class="form-control form-control-lg text-center fw-bold fs-3 rounded-3"
                                value="<?= e(isset($oldInput['result_away']) ? $oldInput['result_away'] : ($match['result_away'] ?? 0)) ?>" min="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(count($home_team) > 0): ?>
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><span class="badge bg-danger me-2">Home</span> Gol Individuali</h5>
                    <?php foreach($home_team as $reg): ?>
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger text-white rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold"
                                    style="width: 38px; height: 38px;">
                                    <?= e(strtoupper(substr($reg['name'], 0, 1))) ?>
                                </div>
                                <div>
                                    <span class="fw-bold"><?= e($reg['name']) ?></span>
                                    <small class="text-muted d-block"><?= e($reg['preferred_role'] ?? 'N/D') ?></small>
                                </div>
                            </div>
                            <div style="width: 80px;">
                                <input type="number" name="goals[<?= e($reg['id']) ?>]" data-team="home" class="form-control text-center fw-bold rounded-3"
                                    value="<?= e(isset($oldInput['goals'][$reg['id']]) ? $oldInput['goals'][$reg['id']] : ($reg['goals_scored'] ?? 0)) ?>" min="0">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if(count($away_team) > 0): ?>
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><span class="badge bg-primary me-2">Away</span> Gol Individuali</h5>
                    <?php foreach($away_team as $reg): ?>
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold"
                                    style="width: 38px; height: 38px;">
                                    <?= e(strtoupper(substr($reg['name'], 0, 1))) ?>
                                </div>
                                <div>
                                    <span class="fw-bold"><?= e($reg['name']) ?></span>
                                    <small class="text-muted d-block"><?= e($reg['preferred_role'] ?? 'N/D') ?></small>
                                </div>
                            </div>
                            <div style="width: 80px;">
                                <input type="number" name="goals[<?= e($reg['id']) ?>]" data-team="away" class="form-control text-center fw-bold rounded-3"
                                    value="<?= e(isset($oldInput['goals'][$reg['id']]) ? $oldInput['goals'][$reg['id']] : ($reg['goals_scored'] ?? 0)) ?>" min="0">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if(count($unassigned) > 0): ?>
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><span class="badge bg-secondary me-2">Non assegnati</span> Gol Individuali</h5>
                    <?php foreach($unassigned as $reg): ?>
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold"
                                    style="width: 38px; height: 38px;">
                                    <?= e(strtoupper(substr($reg['name'], 0, 1))) ?>
                                </div>
                                <div>
                                    <span class="fw-bold"><?= e($reg['name']) ?></span>
                                    <small class="text-muted d-block"><?= e($reg['preferred_role'] ?? 'N/D') ?></small>
                                </div>
                            </div>
                            <div style="width: 80px;">
                                <input type="number" name="goals[<?= e($reg['id']) ?>]" data-team="unassigned" class="form-control text-center fw-bold rounded-3"
                                    value="<?= e(isset($oldInput['goals'][$reg['id']]) ? $oldInput['goals'][$reg['id']] : ($reg['goals_scored'] ?? 0)) ?>" min="0">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm mb-5">
                <i class="bi bi-check-circle-fill me-2"></i>Salva Tabellino e Chiudi
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form');
    var resultHomeInput = document.querySelector('input[name="result_home"]');
    var resultAwayInput = document.querySelector('input[name="result_away"]');
    var validationAlert = document.getElementById('validation-alert');
    var validationAlertMsg = document.getElementById('validation-alert-msg');
    var submitBtn = form.querySelector('button[type="submit"]');
    
    function validateForm(e) {
        var resultHome = parseInt(resultHomeInput.value) || 0;
        var resultAway = parseInt(resultAwayInput.value) || 0;
        
        var sumHome = 0;
        var sumAway = 0;
        
        document.querySelectorAll('input[data-team="home"]').forEach(function(input) {
            sumHome += parseInt(input.value) || 0;
        });
        
        document.querySelectorAll('input[data-team="away"]').forEach(function(input) {
            sumAway += parseInt(input.value) || 0;
        });
        
        var errors = [];
        if (sumHome !== resultHome) {
            errors.push('La somma dei gol dei singoli giocatori <strong>Home</strong> (' + sumHome + ') non corrisponde al risultato finale inserito (' + resultHome + ').');
        }
        if (sumAway !== resultAway) {
            errors.push('La somma dei gol dei singoli giocatori <strong>Away</strong> (' + sumAway + ') non corrisponde al risultato finale inserito (' + resultAway + ').');
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
    
    // Attiva controllo dinamico al variare dei valori
    resultHomeInput.addEventListener('input', function() { validateForm(); });
    resultAwayInput.addEventListener('input', function() { validateForm(); });
    
    document.querySelectorAll('input[data-team="home"], input[data-team="away"]').forEach(function(input) {
        input.addEventListener('input', function() { validateForm(); });
    });
});
</script>
