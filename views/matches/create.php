<?php
// views/matches/create.php
?>
<!-- Leaflet Map CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<!-- Custom Modular Stylesheet for Matches Creation Page -->
<link rel="stylesheet" href="<?= url('/css/matches-create.css') ?>">

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= url('/matches') ?>" class="btn btn-light rounded-circle me-3 shadow-sm border-0">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="h3 fw-bold mb-0">Organizza Partita</h1>
        </div>

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="<?= url('/matches') ?>" method="POST" id="createMatchForm" class="needs-validation no-spinner" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-geo-alt-fill me-2"></i>Dettagli Evento</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="date" class="form-label fw-semibold">Data</label>
                            <input type="date" class="form-control bg-body-tertiary border-0" id="date"
                                name="date" required min="<?= date('Y-m-d') ?>">
                            <div class="invalid-feedback">Inserisci una data valida (oggi o successiva).</div>
                        </div>
                        <div class="col-md-4">
                            <label for="time" class="form-label fw-semibold">Ora</label>
                            <input type="time" class="form-control bg-body-tertiary border-0" id="time"
                                name="time" required>
                            <div class="invalid-feedback">Inserisci un'ora valida per la partita.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="location" class="form-label fw-semibold">Nome Campo / Impianto</label>
                            <input type="text" class="form-control bg-body-tertiary border-0" id="location"
                                name="location" placeholder="Es. Campus CUS" required>
                            <div class="invalid-feedback">Inserisci il nome del campo o dell'impianto.</div>
                        </div>
                    </div>

                    <!-- Leaflet Map for location picking -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold"><i class="bi bi-pin-map-fill text-danger me-1"></i>Posizione sulla Mappa</label>
                        <p class="text-muted small mb-2" id="map-description">Cerca l'indirizzo dell'impianto o clicca direttamente sulla mappa per posizionare il campo.</p>
                        
                        <!-- Address Search Bar -->
                        <div class="input-group mb-2">
                            <input type="text" id="map-search-input" class="form-control bg-body-tertiary border-0" placeholder="Es. Centro Sportivo Olimpia, Milano">
                            <button class="btn btn-primary fw-bold" type="button" id="map-search-btn">
                                <i class="bi bi-search me-1"></i>Cerca
                            </button>
                        </div>

                        <div id="create-map" class="rounded-4 border shadow-sm" aria-label="Mappa interattiva" aria-describedby="map-description" role="region" tabindex="0"></div>
                        
                        <!-- Reverse Geocoding Address Alert -->
                        <div id="detected-address-alert" class="d-none mt-3 p-3 bg-body-tertiary border rounded-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-2 text-center">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <span class="bi bi-geo-alt-fill text-danger fs-4" aria-hidden="true"></span>
                                <div>
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Indirizzo Rilevato</small>
                                    <span id="detected-address-text" class="text-body fw-medium small"></span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold text-nowrap hover-scale shadow-sm mt-1" id="apply-address-btn">
                                <i class="bi bi-check2-circle me-1"></i>Usa Indirizzo
                            </button>
                        </div>

                        <input type="hidden" name="latitude" id="latitude" value="">
                        <input type="hidden" name="longitude" id="longitude" value="">
                    </div>

                    <h5 class="fw-bold mb-4 mt-5 text-primary"><i class="bi bi-people-fill me-2"></i>Formato e Costi</h5>

                    <!-- Visual Format Selection (Radio Cards) -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Formato (Totale Giocatori)</label>
                            <div class="row g-2" id="format-cards-container">
                                <div class="col-6 col-md-3">
                                    <input type="radio" class="btn-check" name="format" id="format-5vs5" value="5vs5" checked required>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3 rounded-4 d-flex flex-column align-items-center justify-content-center border-2" for="format-5vs5">
                                        <span class="fs-3 mb-1">5️⃣</span>
                                        <span class="fw-bold d-block small">Calcio a 5</span>
                                        <span class="small-text text-secondary-custom">10 Giocatori</span>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3">
                                    <input type="radio" class="btn-check" name="format" id="format-7vs7" value="7vs7" required>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3 rounded-4 d-flex flex-column align-items-center justify-content-center border-2" for="format-7vs7">
                                        <span class="fs-3 mb-1">7️⃣</span>
                                        <span class="fw-bold d-block small">Calcio a 7</span>
                                        <span class="small-text text-secondary-custom">14 Giocatori</span>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3">
                                    <input type="radio" class="btn-check" name="format" id="format-8vs8" value="8vs8" required>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3 rounded-4 d-flex flex-column align-items-center justify-content-center border-2" for="format-8vs8">
                                        <span class="fs-3 mb-1">8️⃣</span>
                                        <span class="fw-bold d-block small">Calcio a 8</span>
                                        <span class="small-text text-secondary-custom">16 Giocatori</span>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3">
                                    <input type="radio" class="btn-check" name="format" id="format-11vs11" value="11vs11" required>
                                    <label class="btn btn-outline-primary w-100 h-100 p-3 rounded-4 d-flex flex-column align-items-center justify-content-center border-2" for="format-11vs11">
                                        <span class="fs-3 mb-1">⚽</span>
                                        <span class="fw-bold d-block small">Calcio a 11</span>
                                        <span class="small-text text-secondary-custom">22 Giocatori</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label for="visibility" class="form-label fw-semibold">Visibilità</label>
                            <select class="form-select bg-body-tertiary border-0" id="visibility" name="visibility" required>
                                <option value="public" selected>Pubblica (Tutti possono iscriversi)</option>
                                <option value="private">Privata (Solo per i tuoi Amici)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="total_cost" class="form-label fw-semibold">Costo Totale Campo (€)</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text border-0 bg-primary text-white">€</span>
                            <input type="number" step="0.5" class="form-control bg-body-tertiary border-0"
                                id="total_cost" name="total_cost" placeholder="60.00" required>
                            <div class="invalid-feedback">Inserisci un costo valido per il campo.</div>
                        </div>
                        
                        <!-- Fast Cost Presets -->
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 cost-preset-btn" data-value="0">Gratis</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 cost-preset-btn" data-value="50">€50</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 cost-preset-btn" data-value="60">€60</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 cost-preset-btn" data-value="80">€80</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 cost-preset-btn" data-value="100">€100</button>
                        </div>
                        
                        <div id="quota_preview" class="text-muted small mt-2"></div>
                    </div>

                    <hr class="my-4 text-muted">

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-check2-circle me-2"></i>Conferma e Crea Partita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet Map JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Custom Modular JavaScript for Matches Creation Page -->
<script src="<?= url('/js/matches-create.js') ?>" defer></script>
