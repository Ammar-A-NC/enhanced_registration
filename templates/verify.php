<?php
$urls = $_['urls'] ?? []; include __DIR__ . '/_i18n_start.php'; ?>
<?php
style('core', 'guest');
$code = $_['code'] ?? '';
?>

<div class="guest-box login-box" style="max-width:360px;margin:auto;">
    <h2 style="text-align:center;">Konto erstellen</h2>

    <form method="post" action="<?php p($urls['details_submit'] ?? ''); ?>">
        <input type="hidden" name="code" value="<?php p($code); ?>">

        <input type="text" name="username" placeholder="Anmeldename" required autofocus>
        <input type="text" name="displayname" placeholder="Vollständiger Name" required>
        <input type="text" name="phone" placeholder="Telefonnummer">
        <input type="password" name="password" placeholder="Passwort" required>

        <button type="submit" class="primary" style="width:100%;margin-top:12px;">
            Antrag absenden
        </button>
    </form>

    <p style="text-align:center;margin-top:18px;">
        Zurück zur Anmeldung
    </p>
</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
