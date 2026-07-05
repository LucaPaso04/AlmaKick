/* Match details Leaflet map and weather integration */
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize map
    var mapEl = document.getElementById('match-map');
    if (mapEl) {
        var lat = parseFloat(mapEl.getAttribute('data-lat'));
        var lng = parseFloat(mapEl.getAttribute('data-lng'));
        var locationName = mapEl.getAttribute('data-location') || 'Partita';

        if (!isNaN(lat) && !isNaN(lng)) {
            try {
                var map = L.map('match-map', {
                    scrollWheelZoom: false
                }).setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

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

    // Weather integrations
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
    
    // Copy link
    var copyBtn = document.getElementById('copy-link-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var urlToCopy = copyBtn.getAttribute('data-url') || window.location.href;
            navigator.clipboard.writeText(urlToCopy).then(function() {
                if (typeof window.showToast === 'function') {
                    window.showToast("Link della partita copiato negli appunti!", "success");
                } else {
                    alert("Link della partita copiato negli appunti!");
                }
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        });
    }

    // Countdown timer
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

    // Interactive stars rating
    document.querySelectorAll('.star-rating').forEach(function(ratingEl) {
        var username = ratingEl.getAttribute('data-username');
        var hiddenInput = document.getElementById('vote_val_' + username);
        var stars = ratingEl.querySelectorAll('.star-item');

        stars.forEach(function(star) {
            star.addEventListener('mouseenter', function() {
                var hoverVal = parseInt(this.getAttribute('data-value'));
                stars.forEach(function(s, idx) {
                    if (idx < hoverVal) {
                        s.classList.replace('bi-star', 'bi-star-fill');
                    } else {
                        s.classList.replace('bi-star-fill', 'bi-star');
                    }
                });
            });

            star.addEventListener('mouseleave', function() {
                var currentVal = parseInt(hiddenInput.value) || 0;
                stars.forEach(function(s, idx) {
                    if (idx < currentVal) {
                        s.classList.replace('bi-star', 'bi-star-fill');
                    } else {
                        s.classList.replace('bi-star-fill', 'bi-star');
                    }
                });
            });

            star.addEventListener('click', function() {
                var clickVal = this.getAttribute('data-value');
                hiddenInput.value = clickVal;
                stars.forEach(function(s, idx) {
                    if (idx < clickVal) {
                        s.classList.replace('bi-star', 'bi-star-fill');
                    } else {
                        s.classList.replace('bi-star-fill', 'bi-star');
                    }
                });
            });
        });
    });

    // Thumb down check
    document.querySelectorAll('.thumb-down-check').forEach(function(checkbox) {
        var label = document.querySelector('label[for="' + checkbox.id + '"]');
        if (label) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    label.classList.replace('btn-outline-danger', 'btn-danger');
                    label.querySelector('.bi').classList.replace('bi-hand-thumbs-down', 'bi-hand-thumbs-down-fill');
                } else {
                    label.classList.replace('btn-danger', 'btn-outline-danger');
                    label.querySelector('.bi').classList.replace('bi-hand-thumbs-down-fill', 'bi-hand-thumbs-down');
                }
            });
        }
    });

});

// Global ICS generator
function downloadICS(btn) {
    if (!btn) return;
    var title = btn.getAttribute("data-title") || "Partita AlmaKick";
    var location = btn.getAttribute("data-location") || "";
    var description = btn.getAttribute("data-description") || "";
    var dateStr = btn.getAttribute("data-date") || "";
    var timeStr = btn.getAttribute("data-time") || "";
    var matchId = btn.getAttribute("data-match-id") || "";
    var absoluteUrl = btn.getAttribute("data-url") || window.location.href;

    var startLocal = new Date(dateStr + 'T' + timeStr);
    var endLocal = new Date(startLocal.getTime() + 90 * 60 * 1000);
    
    function formatICSDate(date) {
        return date.toISOString().replace(/[-:]/g, "").split(".")[0] + "Z";
    }

    var icsContent = [
        "BEGIN:VCALENDAR",
        "VERSION:2.0",
        "PRODID:-//AlmaKick//NONSGML v1.0//IT",
        "BEGIN:VEVENT",
        "UID:almakick_" + matchId + "_" + Date.now() + "@almakick.it",
        "DTSTAMP:" + formatICSDate(new Date()),
        "DTSTART:" + formatICSDate(startLocal),
        "DTEND:" + formatICSDate(endLocal),
        "SUMMARY:" + title,
        "DESCRIPTION:" + description,
        "LOCATION:" + location,
        "END:VEVENT",
        "END:VCALENDAR"
    ].join("\r\n");

    var blob = new Blob([icsContent], { type: "text/calendar;charset=utf-8;" });
    var link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", "partita_almakick_" + matchId + ".ics");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    if (typeof window.showToast === 'function') {
        window.showToast("Promemoria salvato! Aggiungilo al tuo calendario.", "success");
    }
}
