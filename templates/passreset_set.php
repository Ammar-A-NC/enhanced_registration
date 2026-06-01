<?php
$urls = $_['urls'] ?? []; include __DIR__ . '/_i18n_start.php'; ?>
<?php script('enhanced_registration', 'password-toggle'); ?>
<?php style('enhanced_registration', 'enhanced'); ?>
<?php script('enhanced_registration', 'password-strength'); ?>

<div class="guest-box">
    <h2>Neues Passwort setzen</h2>

    <?php if (!empty($_['message'])): ?>
        <p class="nc-error"><?= htmlspecialchars($_['message']) ?></p>
    <?php endif; ?>

    <form method="post" action="<?php p($urls['passreset_set'] ?? ''); ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_['token']) ?>">

        <p style="display:flex;gap:6px;">
            <input type="password" id="password" name="password" placeholder="Neues Passwort" required style="flex:1;padding:12px;">
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
        <div id="rule-upper" data-text="Mindestens ein Großbuchstabe"></div>
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

    <div id="rule-match" data-text="Passwörter stimmen überein"></div>
</div>

        <p>
            <button type="submit" class="button primary" style="width:100%;">Passwort ändern</button>
        </p>
    </form>

    <p style="text-align:center;margin-top:18px;">
        <a href="/login" class="button">Zurück zum Login</a>
    </p>
</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
