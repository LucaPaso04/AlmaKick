/**
 * Users page JavaScript logic with AJAX live search and Pagination
 */
document.addEventListener('DOMContentLoaded', function() {
    var searchForm = document.getElementById('search-form');
    var searchInput = document.getElementById('search-input');
    var resultsContainer = document.getElementById('search-results');

    if (!searchForm || !resultsContainer) return;

    // Function to perform AJAX search request
    function performSearch(pageNumber) {
        var page = (typeof pageNumber === 'number' || typeof pageNumber === 'string') ? pageNumber : 1;
        var q = searchInput.value.trim();
        
        var searchParams = new URLSearchParams();
        searchParams.set('q', q);
        searchParams.set('ajax', '1');
        searchParams.set('page', page.toString());

        // Visual loading feedback: dim results container
        resultsContainer.style.transition = 'opacity 0.2s ease-in-out';
        resultsContainer.style.opacity = '0.5';

        // Fetch filtered users (returns JSON with html fields)
        fetch(searchForm.action + '?' + searchParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(res) {
            // Update results container with smooth fade-in
            resultsContainer.innerHTML = res.html;
            resultsContainer.style.opacity = '1';

            // Bind pagination click events for the new elements
            bindPaginationEvents();

            // Update browser URL query string without reloading the page
            searchParams.delete('ajax');
            var newUrl = searchForm.action + '?' + searchParams.toString();
            window.history.pushState(null, '', newUrl);
        })
        .catch(function(err) {
            resultsContainer.style.opacity = '1';
            console.error('Search error:', err);
        });
    }

    // Bind event listeners to pagination links
    function bindPaginationEvents() {
        var paginationContainer = document.getElementById('paginationContainer');
        if (!paginationContainer) return;
        
        var links = paginationContainer.querySelectorAll('.page-link');
        links.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var page = link.getAttribute('data-page');
                if (page && !link.parentElement.classList.contains('disabled') && !link.parentElement.classList.contains('active')) {
                    performSearch(page);
                    // Smooth scroll to top of search area
                    searchForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // Debounce text input keyup/input to search as you type
    if (searchInput) {
        var debounceTimeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function() {
                performSearch(1);
            }, 300); // 300ms debounce
        });
    }

    // Intercept form submit
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch(1);
    });

    // Initialize pagination click listeners on first load
    bindPaginationEvents();
});
