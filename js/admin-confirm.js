document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm-delete-user]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm-delete-user');

            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
