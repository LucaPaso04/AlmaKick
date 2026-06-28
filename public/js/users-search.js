/**
 * Users page JavaScript logic with AJAX live search and LocalStorage History
 */
document.addEventListener('DOMContentLoaded', function() {
    var searchForm = document.getElementById('search-form');
    var searchInput = document.getElementById('search-input');
    var resultsContainer = document.getElementById('search-results');
    var HISTORY_KEY = 'almakick_search_history';

    if (!searchForm || !resultsContainer) return;

    // Helper to get profile URL based on form action base
    function getProfileUrl(username) {
        var formAction = searchForm.action;
        var baseUrl = formAction.substring(0, formAction.lastIndexOf('/users'));
        return baseUrl + '/profile?username=' + encodeURIComponent(username);
    }

    // Load search history from localStorage
    function getHistory() {
        try {
            var data = localStorage.getItem(HISTORY_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.error('Error reading search history', e);
            return [];
        }
    }

    // Save history to localStorage
    function saveHistory(history) {
        try {
            localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
        } catch (e) {
            console.error('Error saving search history', e);
        }
    }

    // Add user to history (max 10 items)
    function addToHistory(user) {
        var history = getHistory();
        // Remove duplicate if exists
        history = history.filter(function(item) {
            return item.username !== user.username;
        });
        // Add to the top
        history.unshift(user);
        // Limit to 10
        if (history.length > 10) {
            history = history.slice(0, 10);
        }
        saveHistory(history);
    }

    // Remove single user from history
    function removeFromHistory(username) {
        var history = getHistory();
        history = history.filter(function(item) {
            return item.username !== username;
        });
        saveHistory(history);
        renderHistory();
    }

    // Clear all history
    function clearAllHistory() {
        saveHistory([]);
        renderHistory();
    }

    // Render history view
    function renderHistory() {
        var historyContainer = document.getElementById('search-history-container');
        var historyList = document.getElementById('search-history-list');
        var emptyState = document.getElementById('empty-search-state');

        if (!historyContainer || !historyList || !emptyState) return;

        var history = getHistory();

        if (history.length > 0 && searchInput.value.trim() === '') {
            historyList.innerHTML = '';
            history.forEach(function(item) {
                var profileUrl = getProfileUrl(item.username);
                var itemHtml = `
                    <div class="user-row-item d-flex align-items-center justify-content-between p-2 mb-2 rounded-4 instagram-list-item" data-username="${item.username}">
                        <a href="${profileUrl}" class="d-flex align-items-center text-decoration-none text-body flex-grow-1 min-w-0 p-2 history-link-click">
                            ${item.avatar ? 
                                `<img src="${item.avatar}" alt="Avatar" class="rounded-circle user-search-avatar-mini object-fit-cover me-3 shadow-sm">` : 
                                `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold user-search-avatar-mini me-3 shadow-sm fs-5">${item.monogram}</div>`
                            }
                            <div class="min-w-0">
                                <span class="d-block fw-bold text-truncate username-label-list">@${item.username}</span>
                                <span class="d-block text-muted small text-truncate name-label-list">${item.name}</span>
                            </div>
                        </a>
                        <button class="btn btn-link text-muted p-2 me-2 remove-history-btn" data-username="${item.username}" aria-label="Rimuovi">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                `;
                var div = document.createElement('div');
                div.innerHTML = itemHtml.trim();
                var rowElement = div.firstChild;

                // Event listener to update history order if they click it from history again
                rowElement.querySelector('.history-link-click').addEventListener('click', function() {
                    addToHistory(item);
                });

                // Event listener to remove item
                rowElement.querySelector('.remove-history-btn').addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    removeFromHistory(item.username);
                });

                historyList.appendChild(rowElement);
            });

            historyContainer.classList.remove('d-none');
            emptyState.classList.add('d-none');
        } else {
            historyContainer.classList.add('d-none');
            // Show prompt only if input is actually empty
            if (searchInput.value.trim() === '') {
                emptyState.classList.remove('d-none');
            } else {
                emptyState.classList.add('d-none');
            }
        }
    }

    // Function to perform AJAX search request
    function performSearch() {
        var q = searchInput.value.trim();
        
        if (q === '') {
            // Se vuoto, pulisci e renderizza cronologia
            resultsContainer.innerHTML = '';
            // Ripristina l'HTML iniziale del container dei risultati
            fetch(searchForm.action + '?ajax=1&q=')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                resultsContainer.innerHTML = data.html;
                // Rielementi agganciati per cancellare
                attachStaticListeners();
                renderHistory();
            });
            // Rimuovi la query string dall'indirizzo URL del browser
            window.history.pushState(null, '', searchForm.action);
            return;
        }

        var searchParams = new URLSearchParams();
        searchParams.set('q', q);
        searchParams.set('ajax', '1');

        resultsContainer.style.transition = 'opacity 0.2s ease-in-out';
        resultsContainer.style.opacity = '0.5';

        fetch(searchForm.action + '?' + searchParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(res) {
            resultsContainer.innerHTML = res.html;
            resultsContainer.style.opacity = '1';

            // Intercetta click sui risultati di ricerca per salvarli in cronologia
            var resultItems = resultsContainer.querySelectorAll('.search-result-item');
            resultItems.forEach(function(item) {
                item.querySelector('a').addEventListener('click', function() {
                    var user = {
                        username: item.getAttribute('data-username'),
                        name: item.getAttribute('data-name'),
                        avatar: item.getAttribute('data-avatar'),
                        monogram: item.getAttribute('data-monogram')
                    };
                    addToHistory(user);
                });
            });

            searchParams.delete('ajax');
            var newUrl = searchForm.action + '?' + searchParams.toString();
            window.history.pushState(null, '', newUrl);
        })
        .catch(function(err) {
            resultsContainer.style.opacity = '1';
            console.error('Search error:', err);
        });
    }

    // Bind clean events for static items (like "Cancella tutto")
    function attachStaticListeners() {
        var clearBtn = document.getElementById('clear-all-history');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearAllHistory();
            });
        }
    }

    // Debounce text input keyup/input to search as you type
    if (searchInput) {
        var debounceTimeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function() {
                performSearch();
            }, 300);
        });
    }

    // Intercept form submit
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });

    // Inizializza al caricamento pagina
    attachStaticListeners();
    renderHistory();
});


