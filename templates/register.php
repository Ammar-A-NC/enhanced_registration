<?php include __DIR__ . '/_i18n_start.php'; ?>
<?php
style('core', 'guest');
?>

<div class="guest-box login-box" style="max-width:420px;margin:auto;padding-bottom:30px;">
    <h2 style="text-align:center;">Konto erstellen</h2>

    <p style="text-align:center;">
        Geben Sie zuerst Ihre E-Mail-Adresse ein.
    </p>

    <form method="post" action="/index.php/apps/enhanced_registration/register">
        <p>
            <input
                type="email"
                name="email"
                placeholder="E-Mail"
                required
                autofocus
                style="width:100%;">
        </p>

        <p>
            <button
                type="submit"
                class="primary"
                style="width:100%;">
                Bestätigungscode anfordern
            </button>
        </p>
    </form>

    <p style="text-align:center;margin-top:18px;">
        <a href="/login">Zurück zur Anmeldung</a>
    </p>
</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
