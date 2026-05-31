(function () {
    'use strict';

    const registerUrl = '/index.php/apps/enhanced_registration/register';
    const resetUrl = '/index.php/apps/enhanced_registration/passreset';
    const registerLinkId = 'enhanced-registration-register-link';

    function looksLikeLoginPage() {
        return Boolean(
            document.querySelector('form[name="login"]') ||
            document.querySelector('form#login') ||
            document.querySelector('#login form') ||
            document.querySelector('form[action*="/login"]')
        );
    }

    function patchPasswordResetLinks(root) {
        const scope = root && root.querySelectorAll ? root : document;

        scope.querySelectorAll('a').forEach((el) => {
            if (el.dataset.enhancedRegistrationReset === '1') {
                return;
            }

            const text = (el.textContent || '').toLowerCase();
            const href = (el.getAttribute('href') || '').toLowerCase();

            const isResetLink =
                text.includes('forgot password') ||
                text.includes('passwort vergessen') ||
                text.includes('password reset') ||
                text.includes('passwort zurücksetzen') ||
                href.includes('lostpassword') ||
                href.includes('lost-password') ||
                href.includes('reset-password') ||
                href.includes('/lostpassword');

            if (!isResetLink) {
                return;
            }

            el.setAttribute('href', resetUrl);
            el.dataset.enhancedRegistrationReset = '1';
        });
    }

    function addRegisterLink() {
        if (document.getElementById(registerLinkId)) {
            return;
        }

        const loginForm =
            document.querySelector('form[name="login"]') ||
            document.querySelector('form#login') ||
            document.querySelector('#login form') ||
            document.querySelector('form[action*="/login"]');

        if (!loginForm) {
            return;
        }

        const wrapper = document.createElement('p');
        wrapper.id = registerLinkId;
        wrapper.style.textAlign = 'center';
        wrapper.style.marginTop = '14px';

        const link = document.createElement('a');
        link.href = registerUrl;
        link.textContent = 'Registrieren';
        link.className = 'button';

        wrapper.appendChild(link);
        loginForm.insertAdjacentElement('afterend', wrapper);
    }

    function applyLoginLinks(root) {
        if (!looksLikeLoginPage()) {
            return;
        }

        patchPasswordResetLinks(root || document);
        addRegisterLink();
    }

    let scheduled = false;

    function scheduleApply(root) {
        if (scheduled) {
            return;
        }

        scheduled = true;

        window.requestAnimationFrame(() => {
            scheduled = false;
            applyLoginLinks(root || document);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => applyLoginLinks(document), { once: true });
    } else {
        applyLoginLinks(document);
    }

    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node && node.nodeType === Node.ELEMENT_NODE) {
                    scheduleApply(node);
                    return;
                }
            }
        }
    });

    observer.observe(document.documentElement, {
        childList: true,
        subtree: true,
    });

    window.setTimeout(() => observer.disconnect(), 15000);
})();
