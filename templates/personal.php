<?php
script('enhanced_registration', 'password-toggle');

$status = (string)($_['status'] ?? '');
$message = (string)($_['message'] ?? '');

$messages = [
    'not_logged_in' => 'Sie müssen angemeldet sein, um Ihr Passwort zu ändern.',
    'current_password_required' => 'Bitte geben Sie Ihr aktuelles Passwort ein.',
    'current_password_invalid' => 'Das aktuelle Passwort ist nicht korrekt.',
    'password_mismatch' => 'Die neuen Passwörter stimmen nicht überein.',
    'password_policy' => 'Das neue Passwort erfüllt die Passwortregeln nicht.',
    'password_change_failed' => 'Das Passwort konnte aktuell nicht geändert werden. Bitte versuchen Sie es später erneut oder kontaktieren Sie einen Administrator.',
    'password_changed' => 'Ihr LDAP-Passwort wurde erfolgreich geändert.',
];

$messageText = $messages[$message] ?? '';
?>

<div class="section">
    <h2>Enhanced Registration</h2>

    <p>
        Hier können Sie Ihr LDAP-Passwort ändern. Die Änderung wird direkt an LLDAP weitergegeben.
    </p>

    <?php if ($messageText !== ''): ?>
        <div class="<?php p($status === 'success' ? 'success' : 'warning'); ?>" style="margin: 12px 0;">
            <?php p($messageText); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php p($_['personal_password_url']); ?>" style="max-width: 520px;">
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken'] ?? ''); ?>">
        <input type="hidden" name="return_url" value="<?php p($_['return_url'] ?? '/settings/user/enhanced_registration'); ?>">

        <p>
            <label for="enhanced-registration-current-password">Aktuelles Passwort</label><br>
            <span style="display:flex;gap:8px;align-items:center;">
                <input type="password" id="enhanced-registration-current-password" name="current_password" autocomplete="current-password" required style="flex:1;">
                <button type="button" class="button" data-password-toggle="enhanced-registration-current-password" aria-label="Passwort anzeigen">👁</button>
            </span>
        </p>

        <p>
            <label for="enhanced-registration-password">Neues Passwort</label><br>
            <span style="display:flex;gap:8px;align-items:center;">
                <input type="password" id="enhanced-registration-password" name="password" autocomplete="new-password" required style="flex:1;">
                <button type="button" class="button" data-password-toggle="enhanced-registration-password" aria-label="Passwort anzeigen">👁</button>
            </span>
        </p>

        <p>
            <label for="enhanced-registration-password-confirm">Neues Passwort wiederholen</label><br>
            <span style="display:flex;gap:8px;align-items:center;">
                <input type="password" id="enhanced-registration-password-confirm" name="password_confirm" autocomplete="new-password" required style="flex:1;">
                <button type="button" class="button" data-password-toggle="enhanced-registration-password-confirm" aria-label="Passwort anzeigen">👁</button>
            </span>
        </p>

        <p class="settings-hint">
            Mindestlänge: <?php p($_['password_min_length'] ?? '12'); ?> Zeichen.
            <?php if (($_['password_require_uppercase'] ?? '1') === '1'): ?> Großbuchstaben erforderlich.<?php endif; ?>
            <?php if (($_['password_require_lowercase'] ?? '1') === '1'): ?> Kleinbuchstaben erforderlich.<?php endif; ?>
            <?php if (($_['password_require_number'] ?? '1') === '1'): ?> Zahl erforderlich.<?php endif; ?>
            <?php if (($_['password_require_special'] ?? '1') === '1'): ?> Sonderzeichen erforderlich.<?php endif; ?>
        </p>

        <p>
            <button type="submit" class="button primary">LDAP-Passwort ändern</button>
        </p>
    </form>
</div>
