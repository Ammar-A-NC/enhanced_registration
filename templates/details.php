<?php
$urls = $_['urls'] ?? []; include __DIR__ . '/_i18n_start.php'; ?>
<?php style("core","guest"); ?>
<?php style('enhanced_registration', 'enhanced'); ?>
<?php script('enhanced_registration', 'password-toggle'); ?>
<?php script('enhanced_registration', 'password-strength'); ?>

<div class="guest-box login-box">
    <h2>Konto erstellen</h2>

    <?php if (!empty($_["message"])): ?>
        <p class="nc-error"><?php p($_["message"]); ?></p>
    <?php endif; ?>

    <form method="post" action="<?php p($urls['details_submit'] ?? ''); ?>">
        <input type="hidden" name="token" value="<?php p($_["token"] ?? ""); ?>">
        <input type="hidden" name="email" value="<?php p($_["email"] ?? ""); ?>">

        <input type="text" name="username" placeholder="Anmeldename" value="<?php p($_["username"] ?? ""); ?>" required>
        <input type="text" name="displayname" placeholder="Vollständiger Name" value="<?php p($_["displayname"] ?? ""); ?>" required>
        <input type="text" name="phone" placeholder="Telefonnummer" value="<?php p($_["phone"] ?? ""); ?>">

        <p style="display:flex;gap:6px;">
            <input type="password" id="password" name="password" placeholder="Passwort" required style="flex:1;padding:12px;">
            <button type="button" class="button" data-password-toggle="password" aria-label="Passwort anzeigen">👁</button>
        </p>

        <p style="display:flex;gap:6px;">
            <input type="password" id="password_confirm" name="password_confirm" placeholder="Passwort wiederholen" required style="flex:1;padding:12px;">
            <button type="button" class="button" data-password-toggle="password_confirm" aria-label="Passwort anzeigen">👁</button>
        </p>
<div
    id="password-rules"
    data-min-length="<?php p($_['password_min_length'] ?? '12'); ?>"
    data-require-upper="<?php p($_['password_require_uppercase'] ?? '1'); ?>"
    data-require-lower="<?php p($_['password_require_lowercase'] ?? '1'); ?>"
    data-require-number="<?php p($_['password_require_number'] ?? '1'); ?>"
    data-require-special="<?php p($_['password_require_special'] ?? '1'); ?>"
    style="font-size:13px;margin:8px 0 14px;"
>
    <div id="rule-length" data-text="Mindestens <?php p($_['password_min_length'] ?? '12'); ?> Zeichen"></div>

    <?php if (($_['password_require_uppercase'] ?? '1') === '1'): ?>
        <div id="rule-upper" data-text="Mindestens ein Grossbuchstabe"></div>
    <?php endif; ?>

    <?php if (($_['password_require_lowercase'] ?? '1') === '1'): ?>
        <div id="rule-lower" data-text="Mindestens ein Kleinbuchstabe"></div>
    <?php endif; ?>

    <?php if (($_['password_require_number'] ?? '1') === '1'): ?>
        <div id="rule-number" data-text="Mindestens eine Zahl"></div>
    <?php endif; ?>

    <?php if (($_['password_require_special'] ?? '1') === '1'): ?>
        <div id="rule-special" data-text="Mindestens ein Sonderzeichen"></div>
    <?php endif; ?>

    <div id="rule-match" data-text="Passwoerter stimmen ueberein"></div>
</div>

        <button type="submit" class="primary">Antrag absenden</button>
    </form>

    <p style="text-align:center;margin-top:18px;">
        <a href="/login" class="button">Zurück zum Login</a>
    </p>
</div>

<style>
input {
    display:block;
    width:100%;
    margin-bottom:12px;
}
button {
    width:100%;
}
p button.button {
    width:auto;
    min-width:48px;
}
</style>
<?php include __DIR__ . '/_i18n_end.php'; ?>
