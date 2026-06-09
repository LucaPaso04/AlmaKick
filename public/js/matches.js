/**
 * Matches page JavaScript logic
 */
function switchToExploreTab() {
    var triggerEl = document.querySelector('#explore-tab');
    if (triggerEl) {
        var tab = new bootstrap.Tab(triggerEl);
        tab.show();
    }
}

// Auto-submit filter form on input change
document.addEventListener('DOMContentLoaded', function() {
    var filterForm = document.querySelector('.filter-form');
    if (filterForm) {
        // Auto-submit select, date and checkbox inputs on change
        var autoSubmitInputs = filterForm.querySelectorAll('select, input[type="date"], input[type="checkbox"]');
        autoSubmitInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });

        // Debounce text input keyup/input to search as you type
        var textInput = filterForm.querySelector('input[name="location"]');
        if (textInput) {
            var debounceTimeout = null;
            textInput.addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(function() {
                    filterForm.submit();
                }, 400); // 400ms debounce
            });
        }
    }
});
