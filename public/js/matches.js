/* Matches page logic with AJAX filtering and pagination */
function switchToExploreTab() {
    var triggerEl = document.querySelector('#explore-tab');
    if (triggerEl) {
        var tab = new bootstrap.Tab(triggerEl);
        tab.show();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var filterForm = document.querySelector('.filter-form');
    var matchesContainer = document.getElementById('matchesContainer');
    var resetButtonContainer = document.getElementById('resetButtonContainer');
    var paginationContainer = document.getElementById('paginationContainer');

    if (!filterForm || !matchesContainer) return;

    // Check if form has active filters
    function hasActiveFilters() {
        var location = filterForm.querySelector('input[name="location"]').value.trim();
        var dateFromInput = filterForm.querySelector('input[name="date_from"]');
        var dateToInput = filterForm.querySelector('input[name="date_to"]');
        var dateFrom = dateFromInput ? dateFromInput.value : '';
        var dateTo = dateToInput ? dateToInput.value : '';
        var format = filterForm.querySelector('select[name="format"]').value;
        var onlyFriends = filterForm.querySelector('input[name="only_friends"]').checked;
        var excludeMyMatches = filterForm.querySelector('input[name="exclude_my_matches"]');
        var excludeChecked = excludeMyMatches ? excludeMyMatches.checked : false;

        return location !== "" || dateFrom !== "" || dateTo !== "" || format !== "" || onlyFriends || excludeChecked;
    }

    // Perform AJAX filter request
    function performFilter(pageNumber) {
        var page = (typeof pageNumber === 'number' || typeof pageNumber === 'string') ? pageNumber : 1;
        var formData = new FormData(filterForm);
        var searchParams = new URLSearchParams(formData);
        
        searchParams.set('ajax', '1');
        searchParams.set('page', page.toString());

        matchesContainer.style.transition = 'opacity 0.2s ease-in-out';
        matchesContainer.style.opacity = '0.5';

        fetch(filterForm.action + '?' + searchParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(res) {
            matchesContainer.innerHTML = res.html;
            matchesContainer.style.opacity = '1';

            if (paginationContainer) {
                paginationContainer.innerHTML = res.pagination || '';
                bindPaginationEvents();
            }

            searchParams.delete('ajax');
            searchParams.set('tab', 'explore');
            var newUrl = filterForm.action + '?' + searchParams.toString();
            window.history.pushState(null, '', newUrl);

            updateResetButton();
        })
        .catch(function(err) {
            matchesContainer.style.opacity = '1';
            console.error('Filtering error:', err);
        });
    }

    // Bind event listeners to pagination links
    function bindPaginationEvents() {
        if (!paginationContainer) return;
        var links = paginationContainer.querySelectorAll('.page-link');
        links.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var page = link.getAttribute('data-page');
                if (page && !link.parentElement.classList.contains('disabled')) {
                    performFilter(page);
                    matchesContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // Update reset button
    function updateResetButton() {
        if (!resetButtonContainer) return;

        if (hasActiveFilters()) {
            if (!resetButtonContainer.querySelector('a')) {
                resetButtonContainer.innerHTML = `
                    <a href="#" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Resetta Filtri">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                `;
                resetButtonContainer.querySelector('a').addEventListener('click', handleReset);
            }
        } else {
            resetButtonContainer.innerHTML = '';
        }
    }

    // Handle reset action
    function handleReset(e) {
        if (e) e.preventDefault();
        
        filterForm.querySelector('input[name="location"]').value = '';
        var dateFromInput = filterForm.querySelector('input[name="date_from"]');
        var dateToInput = filterForm.querySelector('input[name="date_to"]');
        if (dateFromInput) dateFromInput.value = '';
        if (dateToInput) dateToInput.value = '';
        filterForm.querySelector('select[name="format"]').value = '';
        filterForm.querySelector('input[name="only_friends"]').checked = false;
        
        var excludeMyMatches = filterForm.querySelector('input[name="exclude_my_matches"]');
        if (excludeMyMatches) excludeMyMatches.checked = false;

        performFilter(1);
    }

    // Auto-submit on change
    var autoInputs = filterForm.querySelectorAll('select, input[type="date"], input[type="checkbox"]');
    autoInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            performFilter(1);
        });
    });

    // Search input debounce
    var textInput = filterForm.querySelector('input[name="location"]');
    if (textInput) {
        var debounceTimeout = null;
        textInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function() {
                performFilter(1);
            }, 300);
        });
    }

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performFilter(1);
    });

    var initialResetBtn = resetButtonContainer ? resetButtonContainer.querySelector('a') : null;
    if (initialResetBtn) {
        initialResetBtn.addEventListener('click', handleReset);
    }

    bindPaginationEvents();

    // Tab state sync
    var tabs = document.querySelectorAll('#homeTabs button[data-bs-toggle="pill"]');
    tabs.forEach(function(tabEl) {
        tabEl.addEventListener('shown.bs.tab', function(event) {
            var targetId = event.target.id;
            var tabName = targetId === 'explore-tab' ? 'explore' : 'bacheca';
            
            var urlParams = new URLSearchParams(window.location.search);
            urlParams.set('tab', tabName);
            
            if (tabName === 'bacheca') {
                var newUrl = window.location.pathname + '?tab=bacheca';
                window.history.pushState(null, '', newUrl);
            } else {
                var newUrl = window.location.pathname + '?' + urlParams.toString();
                window.history.pushState(null, '', newUrl);
            }
        });
    });
});
