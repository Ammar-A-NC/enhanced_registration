(function () {
    function initAdminTabs() {
        var nav = document.querySelector('.nc-admin-tabs');
        if (!nav) {
            return;
        }

        var buttons = Array.prototype.slice.call(document.querySelectorAll('.nc-admin-tab'));
        var pages = Array.prototype.slice.call(document.querySelectorAll('.nc-admin-page'));
        var allowed = ['pending', 'users', 'audit', 'settings'];

        nav.classList.add('js-ready');

        function getInitialPage() {
            var fromHash = (window.location.hash || '').replace('#', '');
            if (allowed.indexOf(fromHash) !== -1) {
                return fromHash;
            }

            var configured = nav.getAttribute('data-initial-page') || 'pending';
            return allowed.indexOf(configured) !== -1 ? configured : 'pending';
        }

        function activate(page) {
            if (allowed.indexOf(page) === -1) {
                page = 'pending';
            }

            buttons.forEach(function (button) {
                button.classList.toggle('active', button.getAttribute('data-page') === page);
            });

            pages.forEach(function (section) {
                section.classList.toggle('active', section.getAttribute('data-page') === page);
            });

            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname + window.location.search + '#' + page);
            }
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                activate(button.getAttribute('data-page'));
            });
        });

        window.addEventListener('hashchange', function () {
            activate(getInitialPage());
        });

        activate(getInitialPage());
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminTabs);
    } else {
        initAdminTabs();
    }
})();
