<?php
// views/matches/create.php
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #create-map {
        height: 350px;
        width: 100%;
        margin-top: 10px;
        z-index: 1;
    }
    html[data-bs-theme="dark"] #create-map .leaflet-layer,
    html[data-bs-theme="dark"] #create-map .leaflet-control-attribution {
        filter: invert(1) hue-rotate(180deg) brightness(0.9) contrast(0.9);
    }
</style>

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
                <form action="<?= url('/matches') ?>" method="POST" id="createMatchForm" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-geo-alt-fill me-2"></i>Dettagli Evento</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="date" class="form-label fw-semibold">Data</label>
                            <input type="date" class="form-control bg-body-tertiary border-0" id="date"
                                name="date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="time" class="form-label fw-semibold">Ora</label>
                            <input type="time" class="form-control bg-body-tertiary border-0" id="time"
                                name="time" required>
                        </div>
                        <div class="col-md-4">
                            <label for="location" class="form-label fw-semibold">Nome Campo / Impianto</label>
                            <input type="text" class="form-control bg-body-tertiary border-0" id="location"
                                name="location" placeholder="Es. Campus CUS" required>
                        </div>
                    </div>

                    <!-- Leaflet Map for location picking -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold"><i class="bi bi-pin-map-fill text-danger me-1"></i>Posizione sulla Mappa</label>
                        <p class="text-muted small mb-2" id="map-description">Clicca sulla mappa per posizionare il marcatore del campo. Servirà ai giocatori per trovarlo!</p>
                        <div id="create-map" class="rounded-4 border shadow-sm" aria-label="Mappa interattiva" aria-describedby="map-description" role="region" tabindex="0"></div>
                        <input type="hidden" name="latitude" id="latitude" value="">
                        <input type="hidden" name="longitude" id="longitude" value="">
                    </div>

                    <h5 class="fw-bold mb-4 mt-5 text-primary"><i class="bi bi-people-fill me-2"></i>Formato e Costi</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="format" class="form-label fw-semibold">Formato (Totale Giocatori)</label>
                            <select class="form-select bg-body-tertiary border-0" id="format" name="format" required>
                                <option value="5vs5" selected>Calcio a 5 (10 Giocatori)</option>
                                <option value="7vs7">Calcio a 7 (14 Giocatori)</option>
                                <option value="8vs8">Calcio a 8 (16 Giocatori)</option>
                                <option value="11vs11">Calcio a 11 (22 Giocatori)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="visibility" class="form-label fw-semibold">Visibilità</label>
                            <select class="form-select bg-body-tertiary border-0" id="visibility" name="visibility" required>
                                <option value="public" selected>Pubblica (Tutti possono iscriversi)</option>
                                <option value="private">Privata (Solo per i tuoi Amici)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="total_cost" class="form-label fw-semibold">Costo Totale Campo (€)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-primary text-white">€</span>
                            <input type="number" step="0.5" class="form-control bg-body-tertiary border-0"
                                id="total_cost" name="total_cost" placeholder="60.00" required>
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Leaflet Map
    var defaultLat = 44.4949; // Default center (Bologna/Emilia-Romagna region context)
    var defaultLng = 11.3426;
    
    var map = L.map('create-map', {
        scrollWheelZoom: false
    }).setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker;

    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');

    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });

    // Try to get user location for map center
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var userLat = position.coords.latitude;
            var userLng = position.coords.longitude;
            map.setView([userLat, userLng], 14);
        }, function(error) {
            console.log("Geolocation error or declined:", error);
        });
    }

    // 2. Quota Preview Logic
    var formatSelect = document.getElementById('format');
    var costInput = document.getElementById('total_cost');
    var quotaPreview = document.getElementById('quota_preview');

    function updateQuotaPreview() {
        var format = formatSelect.value;
        var cost = parseFloat(costInput.value) || 0;
        
        var maxPlayers = 10;
        if (format === '7vs7') {
            maxPlayers = 14;
        } else if (format === '8vs8') {
            maxPlayers = 16;
        } else if (format === '11vs11') {
            maxPlayers = 22;
        }

        if (cost > 0) {
            var quota = (cost / maxPlayers).toFixed(2);
            quotaPreview.textContent = 'Quota stimata per giocatore: €' + quota + ' (divisa per ' + maxPlayers + ' giocatori)';
        } else {
            quotaPreview.textContent = '';
        }
    }

    formatSelect.addEventListener('change', updateQuotaPreview);
    costInput.addEventListener('input', updateQuotaPreview);
});
</script>
