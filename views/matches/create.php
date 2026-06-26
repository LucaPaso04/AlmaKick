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
    /* Visual Formats (Radio Cards) Styles */
    .btn-check:checked + .btn-outline-primary {
        background-color: var(--bs-primary);
        color: #fff;
        border-color: var(--bs-primary);
        box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.25);
    }
    .btn-outline-primary {
        transition: all 0.2s ease-in-out;
        border-color: rgba(var(--bs-primary-rgb), 0.35);
    }
    .btn-outline-primary:hover {
        border-color: var(--bs-primary);
        transform: translateY(-2px);
    }
    .small-text {
        font-size: 0.75rem;
        opacity: 0.85;
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
                        <div id="detected-address-alert" class="alert alert-secondary d-none py-2 px-3 mt-2 rounded-3 border-0 small d-flex justify-content-between align-items-center">
                            <span id="detected-address-text"></span>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold text-nowrap" id="apply-address-btn">Usa indirizzo</button>
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

    function updateMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
    }

    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);

        updateMarker(lat, lng);
        reverseGeocode(lat, lng);
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

    // Geocoding Search Logic (nominatim)
    var searchBtn = document.getElementById('map-search-btn');
    var searchInput = document.getElementById('map-search-input');
    
    searchBtn.addEventListener('click', function() {
        var query = searchInput.value.trim();
        if (!query) return;

        searchBtn.disabled = true;
        searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Ricerca...';

        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                searchBtn.disabled = false;
                searchBtn.innerHTML = '<i class="bi bi-search me-1"></i>Cerca';
                if (data && data.length > 0) {
                    var first = data[0];
                    var lat = parseFloat(first.lat);
                    var lng = parseFloat(first.lon);

                    latInput.value = lat.toFixed(7);
                    lngInput.value = lng.toFixed(7);

                    map.setView([lat, lng], 15);
                    updateMarker(lat, lng);
                    showDetectedAddress(first.display_name);
                } else {
                    alert("Impossibile trovare la posizione cercata. Prova a cliccare direttamente sulla mappa.");
                }
            })
            .catch(function(err) {
                searchBtn.disabled = false;
                searchBtn.innerHTML = '<i class="bi bi-search me-1"></i>Cerca';
                console.error("Geocoding search error:", err);
            });
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBtn.click();
        }
    });

    // Reverse Geocoding Logic (nominatim)
    function reverseGeocode(lat, lng) {
        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.display_name) {
                    showDetectedAddress(data.display_name);
                }
            })
            .catch(function(err) {
                console.error("Reverse geocoding error:", err);
            });
    }

    function showDetectedAddress(address) {
        var alertEl = document.getElementById('detected-address-alert');
        var textEl = document.getElementById('detected-address-text');
        textEl.textContent = "📍 Indirizzo rilevato: " + address;
        alertEl.classList.remove('d-none');

        var applyBtn = document.getElementById('apply-address-btn');
        // Clean event listeners to avoid duplicates
        var newApplyBtn = applyBtn.cloneNode(true);
        applyBtn.parentNode.replaceChild(newApplyBtn, applyBtn);

        newApplyBtn.addEventListener('click', function() {
            var parts = address.split(',');
            // Extract the first few segments of the address (typically street and street number)
            var shortAddress = parts.slice(0, 3).join(',').trim();
            document.getElementById('location').value = shortAddress;
            alertEl.classList.add('d-none');
        });
    }

    // 2. Quota Preview & presets Logic
    var costInput = document.getElementById('total_cost');
    var quotaPreview = document.getElementById('quota_preview');

    function updateQuotaPreview() {
        var checkedRadio = document.querySelector('input[name="format"]:checked');
        var format = checkedRadio ? checkedRadio.value : '5vs5';
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

    document.querySelectorAll('input[name="format"]').forEach(function(radio) {
        radio.addEventListener('change', updateQuotaPreview);
    });
    costInput.addEventListener('input', updateQuotaPreview);

    // Cost preset buttons listeners
    document.querySelectorAll('.cost-preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            costInput.value = this.getAttribute('data-value');
            updateQuotaPreview();
        });
    });

    // 3. Smart defaults for Date & Time
    var dateInput = document.getElementById('date');
    var timeInput = document.getElementById('time');
    
    var now = new Date();
    
    // Set date to today
    var yyyy = now.getFullYear();
    var mm = String(now.getMonth() + 1).padStart(2, '0');
    var dd = String(now.getDate()).padStart(2, '0');
    dateInput.value = yyyy + '-' + mm + '-' + dd;
    
    // Set time to next round hour + 2 (e.g. if 18:30 -> 20:00)
    var nextHour = (now.getHours() + 2) % 24;
    var nextHourStr = String(nextHour).padStart(2, '0');
    timeInput.value = nextHourStr + ':00';

    // 4. Form Submission and validation UI
    var form = document.getElementById('createMatchForm');
    var submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            
            var firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        } else {
            // Valid: show loading feedback
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Caricamento...
            `;
            setTimeout(function() {
                submitBtn.disabled = true;
            }, 0);
        }
    }, false);
});
</script>
