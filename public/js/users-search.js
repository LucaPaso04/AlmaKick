/**
 * Users page JavaScript logic with AJAX live search
 */
document.addEventListener('DOMContentLoaded', function() {
    var searchForm = document.getElementById('search-form');
    var searchInput = document.getElementById('search-input');
    var resultsContainer = document.getElementById('search-results');

    if (!searchForm || !resultsContainer) return;

    // Function to perform AJAX search request
    function performSearch() {
        var q = searchInput.value.trim();
        
        var searchParams = new URLSearchParams();
        searchParams.set('q', q);
        searchParams.set('ajax', '1');

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

    // Debounce text input keyup/input to search as you type
    if (searchInput) {
        var debounceTimeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function() {
                performSearch();
            }, 300); // 300ms debounce
        });
    }

    // Intercept form submit
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
});

