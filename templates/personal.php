<?php
script('enhanced_registration', 'password-toggle');

$language = strtolower((string)($_['ui_language'] ?? 'auto'));
$isEnglish = str_starts_with($language, 'en');

$status = (string)($_['status'] ?? '');
$message = (string)($_['message'] ?? '');

$texts = $isEnglish ? [
    'title' => 'Change password',
    'intro' => 'Here you can change your LDAP password. The change is written directly to LLDAP.',
    'current_password' => 'Current password',
    'new_password' => 'New password',
    'repeat_password' => 'Repeat new password',
    'submit' => 'Change LDAP password',
    'show_password' => 'Show password',
    'min_length' => 'Minimum length',
    'characters' => 'characters',
    'uppercase' => 'uppercase letter required.',
    'lowercase' => 'lowercase letter required.',
    'number' => 'number required.',
    'special' => 'special character required.',
] : [
    'title' => 'Passwort ändern',
    'intro' => 'Hier können Sie Ihr LDAP-Passwort ändern. Die Änderung wird direkt an LLDAP weitergegeben.',
    'current_password' => 'Aktuelles Passwort',
    'new_password' => 'Neues Passwort',
    'repeat_password' => 'Neues Passwort wiederholen',
    'submit' => 'LDAP-Passwort ändern',
    'show_password' => 'Passwort anzeigen',
    'min_length' => 'Mindestlänge',
    'characters' => 'Zeichen',
    'uppercase' => 'Großbuchstaben erforderlich.',
    'lowercase' => 'Kleinbuchstaben erforderlich.',
    'number' => 'Zahl erforderlich.',
    'special' => 'Sonderzeichen erforderlich.',
];

$messages = $isEnglish ? [
    'not_logged_in' => 'You must be logged in to change your password.',
    'current_password_required' => 'Please enter your current password.',
    'current_password_invalid' => 'The current password is not correct.',
    'password_mismatch' => 'The new passwords do not match.',
    'password_policy' => 'The new password does not meet the password rules.',
    'password_change_failed' => 'The password could not be changed right now. Please try again later or contact an administrator.',
    'password_changed' => 'Your LDAP password has been changed successfully.',
] : [
    'not_logged_in' => 'Sie müssen angemeldet sein, um Ihr Passwort zu ändern.',
    'current_password_required' => 'Bitte geben Sie Ihr aktuelles Passwort ein.',
    'current_password_invalid' => 'Das aktuelle Passwort ist nicht korrekt.',
    'password_mismatch' => 'Die neuen Passwörter stimmen nicht überein.',
    'password_policy' => 'Das neue Passwort erfüllt die Passwortregeln nicht.',
    'password_change_failed' => 'Das Passwort konnte aktuell nicht geändert werden. Bitte versuchen Sie es später erneut oder kontaktieren Sie einen Administrator.',
    'password_changed' => 'Ihr LDAP-Passwort wurde erfolgreich geändert.',
];

$messageText = $messages[$message] ?? '';
$isSuccess = $status === 'success';
?>

<div class="section">
    <h2><?php p($texts['title']); ?></h2>

    <p><?php p($texts['intro']); ?></p>

    <?php if ($messageText !== ''): ?>
        <div
            class="<?php p($isSuccess ? 'success' : 'warning'); ?>"
            style="margin: 16px 0; padding: 12px 14px; border-radius: 8px; font-weight: 600;"
            role="status"
            aria-live="polite"
        >
            <?php p($isSuccess ? '✅ ' : '⚠️ '); ?><?php p($messageText); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php p($_['personal_password_url']); ?>" style="max-width: 520px;">
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken'] ?? ''); ?>">
        <input type="hidden" name="return_url" value="<?php p($_['return_url'] ?? '/settings/user/enhanced_registration'); ?>">

        <p>
            <label for="enhanced-registration-current-password"><?php p($texts['current_password']); ?></label><br>
            <span style="display:flex;gap:8px;align-items:center;">
                <input type="password" id="enhanced-registration-current-password" name="current_password" autocomplete="current-password" required style="flex:1;">
                <button type="button" class="button" data-password-toggle="enhanced-registration-current-password" aria-label="<?php p($texts['show_password']); ?>">👁</button>
            </span>
        </p>

        <p>
            <label for="enhanced-registration-password"><?php p($texts['new_password']); ?></label><br>
            <span style="display:flex;gap:8px;align-items:center;">
                <input type="password" id="enhanced-registration-password" name="password" autocomplete="new-password" required style="flex:1;">
                <button type="button" class="button" data-password-toggle="enhanced-registration-password" aria-label="<?php p($texts['show_password']); ?>">👁</button>
            </span>
        </p>

        <p>
            <label for="enhanced-registration-password-confirm"><?php p($texts['repeat_password']); ?></label><br>
            <span style="display:flex;gap:8px;align-items:center;">
                <input type="password" id="enhanced-registration-password-confirm" name="password_confirm" autocomplete="new-password" required style="flex:1;">
                <button type="button" class="button" data-password-toggle="enhanced-registration-password-confirm" aria-label="<?php p($texts['show_password']); ?>">👁</button>
            </span>
        </p>

        <p class="settings-hint">
            <?php p($texts['min_length']); ?>: <?php p($_['password_min_length'] ?? '12'); ?> <?php p($texts['characters']); ?>.
            <?php if (($_['password_require_uppercase'] ?? '1') === '1'): ?> <?php p($texts['uppercase']); ?><?php endif; ?>
            <?php if (($_['password_require_lowercase'] ?? '1') === '1'): ?> <?php p($texts['lowercase']); ?><?php endif; ?>
            <?php if (($_['password_require_number'] ?? '1') === '1'): ?> <?php p($texts['number']); ?><?php endif; ?>
            <?php if (($_['password_require_special'] ?? '1') === '1'): ?> <?php p($texts['special']); ?><?php endif; ?>
        </p>

        <p>
            <button type="submit" class="button primary"><?php p($texts['submit']); ?></button>
        </p>
    </form>
</div>
