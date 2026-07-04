/* Match report drag & drop and score validation */
document.addEventListener('DOMContentLoaded', function() {
    var homeZone = document.getElementById('team-home-zone');
    var awayZone = document.getElementById('team-away-zone');
    var sourceContainer = document.getElementById('players-source-container');

    if (!homeZone || !awayZone || !sourceContainer) return;

    // Distribute players
    var draggables = sourceContainer.querySelectorAll('.player-card-draggable');
    draggables.forEach(function(card) {
        var startingTeam = card.getAttribute('data-starting-team');
        if (startingTeam === 'away') {
            awayZone.appendChild(card);
        } else {
            homeZone.appendChild(card);
        }
    });

    // Recompute metrics
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

    // Drag & Drop events
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

                var teamInput = document.getElementById('team_input_' + regId);
                if (teamInput) {
                    teamInput.value = teamName;
                }

                card.querySelectorAll('input[type="number"]').forEach(function(input) {
                    input.setAttribute('data-team', teamName);
                });

                updateCounts();
                validateForm();
            }
        });
    });

    // Score validation logic
    var form = document.querySelector('form');
    var resultHomeInput = document.getElementById('result_home');
    var resultAwayInput = document.getElementById('result_away');
    var validationAlert = document.getElementById('validation-alert');
    var validationAlertMsg = document.getElementById('validation-alert-msg');
    var submitBtn = form ? form.querySelector('button[type="submit"]') : null;

    function validateForm(e) {
        if (!resultHomeInput || !resultAwayInput || !validationAlert || !validationAlertMsg) return true;

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

    if (form) {
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
    }

    if (resultHomeInput) resultHomeInput.addEventListener('input', function() { validateForm(); });
    if (resultAwayInput) resultAwayInput.addEventListener('input', function() { validateForm(); });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('player-goals-input') || e.target.classList.contains('guest-goals-input')) {
            validateForm();
        }
    });
});
