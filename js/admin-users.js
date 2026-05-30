document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('nc-user-search');
    const cards = document.querySelectorAll('.nc-user-card');

    if (!input || cards.length === 0) {
        return;
    }

    input.addEventListener('input', function () {
        const q = input.value.toLowerCase().trim();

        cards.forEach(function (card) {
            const haystack = (card.dataset.search || '').toLowerCase();
            card.style.display = haystack.includes(q) ? '' : 'none';
        });
    });
});
