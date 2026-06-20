<?php
// views/matches/partials/show/info_grid.php
?>
<div class="row g-3 mb-4 text-center">
    <div class="col-6 col-md-3">
        <div class="p-4 bg-body rounded-4 shadow-sm border-0 h-100 d-flex flex-column justify-content-center hover-scale transition-all" tabindex="0" role="region" aria-label="Quota base per persona: <?= number_format($current_quote, 2) ?> euro">
            <div class="rounded-circle bg-success bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3 icon-wrap-50" aria-hidden="true">
                <span class="bi bi-cash-stack fs-3 text-success"></span>
            </div>
            <span class="fw-bolder d-block fs-4 mb-1">€<?= number_format($current_quote, 2) ?></span>
            <small class="text-muted fw-medium text-uppercase tracking-wide" style="font-size: 0.75rem;">Quota / Persona</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-4 bg-body rounded-4 shadow-sm border-0 h-100 d-flex flex-column justify-content-center hover-scale transition-all" tabindex="0" role="region" aria-label="Posti occupati: <?= $occupied_seats ?> su <?= $match['max_players'] ?>">
            <div class="rounded-circle bg-primary bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3 icon-wrap-50" aria-hidden="true">
                <span class="bi bi-people-fill fs-3 text-primary"></span>
            </div>
            <span class="fw-bolder d-block fs-4 mb-1"><?= $occupied_seats ?> / <?= $match['max_players'] ?></span>
            <small class="text-muted fw-medium text-uppercase tracking-wide" style="font-size: 0.75rem;">Posti Occupati</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-4 bg-body rounded-4 shadow-sm border-0 h-100 d-flex flex-column justify-content-center hover-scale transition-all" tabindex="0" role="region" aria-label="Meteo previsto">
            <div class="rounded-circle bg-warning bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3 icon-wrap-50" aria-hidden="true">
                <span class="bi bi-cloud-sun-fill fs-3 text-warning"></span>
            </div>
            <span class="fw-bolder d-block fs-5 mb-1 lh-sm" id="weather-display"
                  data-lat="<?= e($match['latitude'] ?? '') ?>"
                  data-lng="<?= e($match['longitude'] ?? '') ?>"
                  data-api-key="<?= defined('OPENWEATHER_KEY') ? e(OPENWEATHER_KEY) : '' ?>"
                  data-date="<?= e($match['date']) ?>"
                  data-time="<?= e($match['time']) ?>"
                  data-status="<?= e($match['status']) ?>"><?= e($weather) ?></span>
            <small class="text-muted fw-medium text-uppercase tracking-wide mt-auto" style="font-size: 0.75rem;">Meteo</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-4 bg-body rounded-4 shadow-sm border-0 h-100 d-flex flex-column justify-content-center hover-scale transition-all" tabindex="0" role="region" aria-label="Visibilità: <?= $match['visibility'] === 'public' ? 'Pubblica' : 'Privata' ?>">
            <div class="rounded-circle bg-secondary bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3 icon-wrap-50" aria-hidden="true">
                <span class="bi bi-lock-fill fs-3 text-secondary"></span>
            </div>
            <span class="fw-bolder d-block fs-4 mb-1 text-capitalize"><?= $match['visibility'] === 'public' ? 'Pubblica' : 'Privata' ?></span>
            <small class="text-muted fw-medium text-uppercase tracking-wide" style="font-size: 0.75rem;">Visibilità</small>
        </div>
    </div>
</div>
