/* Map picker and dynamic settings for match creation */
document.addEventListener('DOMContentLoaded', function() {
    var defaultLat = 44.4949;
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

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var userLat = position.coords.latitude;
            var userLng = position.coords.longitude;
            map.setView([userLat, userLng], 14);
        }, function(error) {
            console.log("Geolocation error or declined:", error);
        });
    }

    // Geocoding query
    var searchBtn = document.getElementById('map-search-btn');
    var searchInput = document.getElementById('map-search-input');
    
    if (searchBtn && searchInput) {
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
    }

    // Reverse geocoding query
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
        if (!alertEl || !textEl) return;

        textEl.textContent = address;
        alertEl.classList.remove('d-none');

        var applyBtn = document.getElementById('apply-address-btn');
        if (applyBtn) {
            var newApplyBtn = applyBtn.cloneNode(true);
            applyBtn.parentNode.replaceChild(newApplyBtn, applyBtn);

            newApplyBtn.addEventListener('click', function() {
                var parts = address.split(',');
                var shortAddress = parts.slice(0, 3).join(',').trim();
                var locInput = document.getElementById('location');
                if (locInput) locInput.value = shortAddress;
                alertEl.classList.add('d-none');
            });
        }
    }

    // Quota calculator and presets logic
    var costInput = document.getElementById('total_cost');
    var quotaPreview = document.getElementById('quota_preview');

    if (costInput) {
        var lastCost = localStorage.getItem('last_match_cost');
        if (lastCost !== null) {
            costInput.value = lastCost;
        }
    }

    function updateQuotaPreview() {
        if (!costInput || !quotaPreview) return;
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
    if (costInput) {
        costInput.addEventListener('input', updateQuotaPreview);
    }

    document.querySelectorAll('.cost-preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (costInput) {
                costInput.value = this.getAttribute('data-value');
                updateQuotaPreview();
            }
        });
    });

    updateQuotaPreview();

    // Default dates config
    var dateInput = document.getElementById('date');
    var timeInput = document.getElementById('time');
    
    if (dateInput && timeInput && !dateInput.value && !timeInput.value) {
        var now = new Date();
        var yyyy = now.getFullYear();
        var mm = String(now.getMonth() + 1).padStart(2, '0');
        var dd = String(now.getDate()).padStart(2, '0');
        dateInput.value = yyyy + '-' + mm + '-' + dd;
        
        var nextHour = (now.getHours() + 2) % 24;
        var nextHourStr = String(nextHour).padStart(2, '0');
        timeInput.value = nextHourStr + ':00';
    }

    // Form submission validation
    var form = document.getElementById('createMatchForm');
    if (form) {
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
                if (costInput) {
                    localStorage.setItem('last_match_cost', costInput.value);
                }
                if (submitBtn) {
                    submitBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Caricamento...
                    `;
                    setTimeout(function() {
                        submitBtn.disabled = true;
                    }, 0);
                }
            }
        }, false);
    }
});
