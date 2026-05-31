(function () {
    const registerUrl = '/index.php/apps/enhanced_registration/register';
    const resetUrl = '/index.php/apps/enhanced_registration/passreset';

    function goReset(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        window.location.assign(resetUrl);
        return false;
    }

    function patchLoginPage() {
        const bodyText = (document.body.innerText || '').toLowerCase();

        document.querySelectorAll('a, button').forEach(function (el) {
            const text = (el.textContent || '').trim().toLowerCase();
            const href = el.getAttribute && (el.getAttribute('href') || '');

            if (
                text.includes('passwort vergessen') ||
                text.includes('forgot password') ||
                href.includes('lostpassword') ||
                href.includes('lost-password') ||
                href.includes('reset-password') ||
                href.includes('/lostpassword')
            ) {
                if (el.tagName.toLowerCase() === 'a') {
                    el.href = resetUrl;
                }
                el.onclick = goReset;
                el.setAttribute('data-enhanced-registration-reset', '1');
            }
        });

        const loginForm = document.querySelector('form[name="login"], form[action*="/login"]');
        if (loginForm && !document.getElementById('enhanced-registration-register-link')) {
            const p = document.createElement('p');
            p.id = 'enhanced-registration-register-link';
            p.style.textAlign = 'center';
            p.style.marginTop = '22px';

            const a = document.createElement('a');
            a.href = registerUrl;
            a.textContent = 'Registrieren';
            a.className = 'button primary';

            p.appendChild(a);
            loginForm.parentNode.appendChild(p);
        }

    }

    document.addEventListener('click', function (e) {
        const el = e.target.closest('a, button');
        if (!el) return;

        const text = (el.textContent || '').trim().toLowerCase();
        const href = el.getAttribute && (el.getAttribute('href') || '');

        if (
            text.includes('passwort vergessen') ||
            text.includes('forgot password') ||
            href.includes('lostpassword') ||
            href.includes('lost-password') ||
            href.includes('reset-password') ||
            href.includes('/lostpassword')
        ) {
            goReset(e);
        }
    }, true);

    document.addEventListener('DOMContentLoaded', patchLoginPage);
    window.addEventListener('pageshow', patchLoginPage);
    window.addEventListener('popstate', patchLoginPage);
    setInterval(patchLoginPage, 300);
})();
