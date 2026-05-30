document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('nc-countdown');
    if (!el) return;

    let seconds = parseInt(el.dataset.seconds || '30', 10);

    const timer = setInterval(function () {
        seconds -= 1;
        el.textContent = String(seconds);

        if (seconds <= 0) {
            clearInterval(timer);
        }
    }, 1000);
});
