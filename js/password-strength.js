document.addEventListener('DOMContentLoaded', function () {
    const password = document.getElementById('password');
    const confirm = document.getElementById('password_confirm');
    const box = document.getElementById('password-rules');

    if (!password || !box) return;

    const minLength = parseInt(box.dataset.minLength || '12', 10);
    const requireUpper = (box.dataset.requireUpper || '1') === '1';
    const requireLower = (box.dataset.requireLower || '1') === '1';
    const requireNumber = (box.dataset.requireNumber || '1') === '1';
    const requireSpecial = (box.dataset.requireSpecial || '1') === '1';

    function setRule(id, ok) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = (ok ? '✅ ' : '❌ ') + el.dataset.text;
    }

    function update() {
        const value = password.value;

        setRule('rule-length', value.length >= minLength);

        if (requireUpper) {
            setRule('rule-upper', /[A-Z]/.test(value));
        }

        if (requireLower) {
            setRule('rule-lower', /[a-z]/.test(value));
        }

        if (requireNumber) {
            setRule('rule-number', /[0-9]/.test(value));
        }

        if (requireSpecial) {
            setRule('rule-special', /[\W_]/.test(value));
        }

        if (confirm) {
            setRule('rule-match', value !== '' && value === confirm.value);
        }
    }

    password.addEventListener('input', update);
    if (confirm) confirm.addEventListener('input', update);

    update();
});
