/**
 * AlmaKick Match Details JavaScript Integration
 * Manages Leaflet Map mounting and dynamic weather forecast loading from OpenWeatherMap.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Initialize Leaflet Map
    var mapEl = document.getElementById('match-map');
    if (mapEl) {
        var lat = parseFloat(mapEl.getAttribute('data-lat'));
        var lng = parseFloat(mapEl.getAttribute('data-lng'));
        var locationName = mapEl.getAttribute('data-location') || 'Partita';

        if (!isNaN(lat) && !isNaN(lng)) {
            try {
                // Initialize map centered at location
                var map = L.map('match-map', {
                    scrollWheelZoom: false
                }).setView([lat, lng], 15);

                // Add OpenStreetMap tiles (always white/light as requested)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Add Marker
                var marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`<strong>${locationName}</strong>`).openPopup();
                
            } catch (e) {
                console.error('Error loading Leaflet map:', e);
                mapEl.innerHTML = '<div class="p-4 text-center text-muted"><i class="bi bi-exclamation-triangle-fill text-warning fs-3 mb-2 d-block"></i>Impossibile caricare la mappa.</div>';
            }
        } else {
            mapEl.style.display = 'none';
        }
    }

    // 2. Fetch Weather Info
    var weatherEl = document.getElementById('weather-display');
    if (weatherEl) {
        var lat = weatherEl.getAttribute('data-lat');
        var lng = weatherEl.getAttribute('data-lng');
        var apiKey = weatherEl.getAttribute('data-api-key');
        var status = weatherEl.getAttribute('data-status');
        var dateStr = weatherEl.getAttribute('data-date');
        var timeStr = weatherEl.getAttribute('data-time');

        var weatherIconEl = document.getElementById('weather-icon');

        if (status === 'finished') {
            weatherEl.textContent = '🏁 Conclusa';
            if (weatherIconEl) {
                weatherIconEl.className = 'bi bi-calendar-check-fill fs-3 text-success';
                var iconWrap = weatherIconEl.closest('.rounded-circle');
                if (iconWrap) iconWrap.className = 'rounded-circle bg-success bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3 icon-wrap-50';
            }
        } else if (status === 'cancelled') {
            weatherEl.textContent = '❌ Annullata';
            if (weatherIconEl) {
                weatherIconEl.className = 'bi bi-calendar-x-fill fs-3 text-danger';
                var iconWrap = weatherIconEl.closest('.rounded-circle');
                if (iconWrap) iconWrap.className = 'rounded-circle bg-danger bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3 icon-wrap-50';
            }
        } else if (!apiKey || apiKey.trim() === '') {
            // Se non c'è una chiave API configurata, mostra un messaggio pulito di default
            weatherEl.textContent = 'Meteo N/D';
            weatherEl.title = 'Configura OPENWEATHER_KEY in config.php per abilitare le previsioni meteo.';
        } else if (lat && lng) {
            var forecastUrl = `https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lng}&units=metric&lang=it&appid=${apiKey}`;
            
            fetch(forecastUrl)
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('API response error ' + response.status);
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (data.list && data.list.length > 0) {
                        // Trova la slot oraria più vicina all'orario della partita
                        var matchTime = new Date(dateStr + 'T' + timeStr).getTime();
                        var closest = data.list[0];
                        var minDiff = Math.abs(new Date(closest.dt * 1000).getTime() - matchTime);

                        for (var i = 1; i < data.list.length; i++) {
                            var diff = Math.abs(new Date(data.list[i].dt * 1000).getTime() - matchTime);
                            if (diff < minDiff) {
                                minDiff = diff;
                                closest = data.list[i];
                            }
                        }

                        var temp = Math.round(closest.main.temp);
                        var desc = closest.weather[0].description;
                        var iconCode = closest.weather[0].icon;

                        // Mappa l'icona OpenWeather ad un'icona Bootstrap corrispondente
                        if (weatherIconEl && iconCode) {
                            var iconClass = 'bi-cloud-sun-fill';
                            switch(iconCode) {
                                case '01d': iconClass = 'bi-sun-fill'; break;
                                case '01n': iconClass = 'bi-moon-stars-fill'; break;
                                case '02d': iconClass = 'bi-cloud-sun-fill'; break;
                                case '02n': iconClass = 'bi-cloud-moon-fill'; break;
                                case '03d': 
                                case '03n': iconClass = 'bi-cloud-fill'; break;
                                case '04d':
                                case '04n': iconClass = 'bi-clouds-fill'; break;
                                case '09d':
                                case '09n': iconClass = 'bi-cloud-drizzle-fill'; break;
                                case '10d':
                                case '10n': iconClass = 'bi-cloud-rain-heavy-fill'; break;
                                case '11d':
                                case '11n': iconClass = 'bi-cloud-lightning-rain-fill'; break;
                                case '13d':
                                case '13n': iconClass = 'bi-snowflake'; break;
                                case '50d':
                                case '50n': iconClass = 'bi-cloud-fog2-fill'; break;
                            }
                            weatherIconEl.className = `bi ${iconClass} fs-3 text-warning`;
                        }

                        // Capitalize description first letter
                        desc = desc.charAt(0).toUpperCase() + desc.slice(1);
                        weatherEl.innerHTML = `<span class="fs-4 fw-bold">${temp}°C</span><br><span class="small fw-normal text-muted d-block mt-1 text-wrap">${desc}</span>`;
                    } else {
                        weatherEl.textContent = 'Meteo N/D';
                    }
                })
                .catch(function(err) {
                    console.error('Weather load error:', err);
                    weatherEl.textContent = 'Meteo N/D';
                });
        } else {
            weatherEl.textContent = 'Meteo N/D';
        }
    }
});
