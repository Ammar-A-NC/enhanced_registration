(function () {
    function detectType(message, type) {
        var lower = (message || '').toLowerCase();

        if (lower.indexOf('mail-vorlage nicht gespeichert') !== -1) {
            return 'error';
        }

        if (lower.indexOf('fehlt') !== -1 || lower.indexOf('nicht gespeichert') !== -1) {
            return 'error';
        }

        return type || 'success';
    }

    function showToast(message, type) {
        type = detectType(message, type);

        var toast = document.createElement('div');
        toast.className = 'nc-admin-toast';
        toast.classList.add(type);
        toast.textContent = message;

        document.body.appendChild(toast);

        window.setTimeout(function () {
            toast.classList.add('show');
        }, 50);

        window.setTimeout(function () {
            toast.classList.remove('show');

            window.setTimeout(function () {
                if (toast && toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 250);
        }, 4500);
    }

    function initAdminToast() {
        var initial = document.getElementById('nc-admin-initial-message');

        if (!initial) {
            return;
        }

        var message = initial.getAttribute('data-message') || '';
        var type = initial.getAttribute('data-type') || 'success';

        if (message) {
            showToast(message, type);
        }

        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminToast);
    } else {
        initAdminToast();
    }
})();
