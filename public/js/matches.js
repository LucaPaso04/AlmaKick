/**
 * Matches page JavaScript logic with AJAX filtering and Pagination
 */
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

    // Helper to check if form has active filters
    function hasActiveFilters() {
        var location = filterForm.querySelector('input[name="location"]').value.trim();
        var date = filterForm.querySelector('input[name="date"]').value;
        var format = filterForm.querySelector('select[name="format"]').value;
        var filter = filterForm.querySelector('select[name="filter"]').value;
        var onlyFriends = filterForm.querySelector('input[name="only_friends"]').checked;

        return location !== "" || date !== "" || format !== "" || filter !== "all" || onlyFriends;
    }

    // Function to perform AJAX filter request
    function performFilter(pageNumber) {
        var page = (typeof pageNumber === 'number' || typeof pageNumber === 'string') ? pageNumber : 1;
        var formData = new FormData(filterForm);
        var searchParams = new URLSearchParams(formData);
        
        // Add ajax and page flag
        searchParams.set('ajax', '1');
        searchParams.set('page', page.toString());

        // Fetch filtered matches (returns JSON with html & pagination fields)
        fetch(filterForm.action + '?' + searchParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(res) {
            // Update matches list container
            matchesContainer.innerHTML = res.html;

            // Update pagination container
            if (paginationContainer) {
                paginationContainer.innerHTML = res.pagination || '';
                bindPaginationEvents();
            }

            // Update browser URL query string without reloading the page
            searchParams.delete('ajax');
            searchParams.set('tab', 'explore');
            var newUrl = filterForm.action + '?' + searchParams.toString();
            window.history.pushState(null, '', newUrl);

            // Update reset button visibility
            updateResetButton();
        })
        .catch(function(err) {
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
                    // Smooth scroll to top of list
                    matchesContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // Function to render/remove reset button
    function updateResetButton() {
        if (!resetButtonContainer) return;

        if (hasActiveFilters()) {
            if (!resetButtonContainer.querySelector('a')) {
                resetButtonContainer.innerHTML = `
                    <a href="#" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Resetta Filtri">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                `;
                // Add click listener to the newly created button
                resetButtonContainer.querySelector('a').addEventListener('click', handleReset);
            }
        } else {
            resetButtonContainer.innerHTML = '';
        }
    }

    // Function to handle reset action
    function handleReset(e) {
        if (e) e.preventDefault();
        
        // Reset all inputs in form
        filterForm.querySelector('input[name="location"]').value = '';
        filterForm.querySelector('input[name="date"]').value = '';
        filterForm.querySelector('select[name="format"]').value = '';
        filterForm.querySelector('select[name="filter"]').value = 'all';
        filterForm.querySelector('input[name="only_friends"]').checked = false;

        // Perform filter with cleared inputs (starts at page 1)
        performFilter(1);
    }

    // Bind event listeners for auto-submit (reset page to 1 on filter changes)
    var autoInputs = filterForm.querySelectorAll('select, input[type="date"], input[type="checkbox"]');
    autoInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            performFilter(1);
        });
    });

    // Debounce text input keyup/input to search as you type (reset page to 1)
    var textInput = filterForm.querySelector('input[name="location"]');
    if (textInput) {
        var debounceTimeout = null;
        textInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function() {
                performFilter(1);
            }, 300); // 300ms debounce
        });
    }

    // Add submit intercept to avoid normal form submission
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performFilter(1);
    });

    // Initialize reset button click listener if it was rendered by PHP
    var initialResetBtn = resetButtonContainer ? resetButtonContainer.querySelector('a') : null;
    if (initialResetBtn) {
        initialResetBtn.addEventListener('click', handleReset);
    }

    // Initialize pagination click listeners on first load
    bindPaginationEvents();

    // Listen for tab changes to update URL
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
