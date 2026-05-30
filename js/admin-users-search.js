(function () {
    function initEnhancedRegistrationUserSearch() {
        var input = document.getElementById('nc-user-search');
        var cards = document.querySelectorAll('.nc-user-card');

        if (!input || !cards.length) {
            return;
        }

        function filterUsers() {
            var q = (input.value || '').toLowerCase().trim();

            cards.forEach(function (card) {
                var haystack = (card.getAttribute('data-search') || '').toLowerCase();
                card.style.display = haystack.indexOf(q) !== -1 ? '' : 'none';
            });
        }

        input.addEventListener('input', filterUsers);
        input.addEventListener('keyup', filterUsers);
        filterUsers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEnhancedRegistrationUserSearch);
    } else {
        initEnhancedRegistrationUserSearch();
    }

    setTimeout(initEnhancedRegistrationUserSearch, 500);
})();
