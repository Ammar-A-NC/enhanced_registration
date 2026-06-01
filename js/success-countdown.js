document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('nc-countdown');

    if (!el) {
        return;
    }

    let seconds = parseInt(el.dataset.seconds || el.textContent || '10', 10);
    const redirectUrl = el.dataset.redirectUrl || '';

    if (!Number.isFinite(seconds) || seconds < 0) {
        seconds = 10;
    }

    el.textContent = String(seconds);

    const timer = window.setInterval(function () {
        seconds -= 1;
        el.textContent = String(Math.max(seconds, 0));

        if (seconds <= 0) {
            window.clearInterval(timer);

            if (redirectUrl !== '') {
                window.location.href = redirectUrl;
            }
        }
    }, 1000);
});
