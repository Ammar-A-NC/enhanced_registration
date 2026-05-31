<?php include __DIR__ . '/_i18n_start.php'; ?>
<style>
.nc-reg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-top:20px}
.nc-reg-card{background:#fff;border-radius:18px;padding:22px;box-shadow:0 4px 18px rgba(0,0,0,.08);border:1px solid #ddd}
.nc-reg-user{font-size:22px;font-weight:700;margin-bottom:6px}
.nc-reg-name{color:#666;margin-bottom:4px}
.nc-reg-mail{color:#888;font-size:14px;margin-bottom:18px;word-break:break-word}
.nc-reg-select{width:100%;margin-bottom:16px}
.nc-reg-actions{display:flex;gap:12px;align-items:center}
.nc-btn-approve{background:#16a34a!important;color:white!important;border:none}
.nc-btn-blacklist{background:#dc2626!important;color:white!important;border:none}
.nc-settings{background:#fff;border:1px solid #ddd;border-radius:18px;padding:22px;margin:20px 0;max-width:980px}
.nc-settings-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px;margin-top:16px}
.nc-settings-card{border:1px solid #e1e1e1;border-radius:16px;padding:18px;background:#fafafa}
.nc-settings-card h4{margin-top:0;margin-bottom:8px}
.nc-settings label{display:block;font-weight:600;margin-top:12px}
.nc-settings input,.nc-settings select,.nc-settings textarea{width:100%;max-width:620px;margin-top:5px}
.nc-settings textarea{min-height:120px;font-family:monospace;resize:vertical}
.nc-group-list label{display:block;margin:6px 0}
.nc-muted{color:#777;font-size:13px}
.nc-setup-status{background:#fff;border:1px solid #ddd;border-radius:18px;padding:18px 22px;margin:18px 0 22px;max-width:980px}
.nc-setup-status.ok{border-color:#b9e4c6;background:#f6fff8}
.nc-setup-status.warn{border-color:#ffd69a;background:#fffaf2}
.nc-setup-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px;margin-top:12px}
.nc-setup-item{display:flex;gap:8px;align-items:flex-start;padding:10px 12px;border-radius:12px;background:rgba(255,255,255,.75);border:1px solid #eee}
.nc-setup-item.ok{color:#136b2e;border-color:#d7efd9}
.nc-setup-item.fail{color:#991b1b;border-color:#f5b5b5;background:#fff7f7}
.nc-setup-icon{font-weight:800;min-width:20px}
.nc-test-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;align-items:flex-end}
.nc-test-actions form{display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;margin:0}
.nc-test-actions input{min-width:240px}
.nc-admin-message{padding:12px 16px;border-radius:12px;margin:14px 0 18px;font-weight:600}
.nc-admin-message.success{background:#e8f7ee;color:#136b2e;border:1px solid #b9e4c6}
.nc-admin-message.warning{background:#fff4e5;color:#8a4b00;border:1px solid #ffd69a}
.nc-admin-message.error{background:#fde8e8;color:#991b1b;border:1px solid #f5b5b5}
.nc-admin-toast{position:fixed;top:18px;right:18px;z-index:99999;min-width:260px;max-width:420px;padding:14px 18px;border-radius:14px;font-weight:700;box-shadow:0 8px 30px rgba(0,0,0,.18);opacity:0;transform:translateY(-10px);pointer-events:none;transition:opacity .2s ease,transform .2s ease}
.nc-admin-toast.show{opacity:1;transform:translateY(0)}
.nc-admin-toast.success{background:#e8f7ee!important;color:#136b2e!important;border:1px solid #b9e4c6!important}
.nc-admin-toast.warning{background:#fff4e5!important;color:#8a4b00!important;border:1px solid #ffd69a!important}
.nc-admin-toast.error{background:#fde8e8!important;color:#991b1b!important;border:1px solid #f5b5b5!important}

.nc-admin-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0 22px}
.nc-admin-tab{border:1px solid #d0d0d0;background:#fff;border-radius:999px;padding:9px 15px;font-weight:700;cursor:pointer}
.nc-admin-tab.active{background:#00679e;color:#fff;border-color:#00679e}
.nc-admin-page{display:none}
.nc-admin-page.active{display:block}
.nc-admin-tabs:not(.js-ready) ~ .nc-admin-page[data-page="pending"]{display:block}
.nc-audit-table{width:100%;border-collapse:collapse;margin-top:14px}
.nc-audit-table th,.nc-audit-table td{border-bottom:1px solid #e5e5e5;padding:8px 10px;text-align:left;vertical-align:top}
.nc-audit-table code{white-space:pre-wrap;word-break:break-word}
.nc-audit-actions{margin:14px 0 18px}
</style>
<?php script('enhanced_registration', 'admin-users-search'); ?>
<?php script('enhanced_registration', 'admin-toast-v2'); ?>
<?php script('enhanced_registration', 'admin-tabs-v3'); ?>

<?php
$settings = $_['settings'] ?? [];
$groups = $_['groups'] ?? [];
$requestToken = $_['requesttoken'] ?? '';

$msg = $_GET['msg'] ?? '';
$messages = [
    'settings_saved' => 'Einstellungen gespeichert.',
    'user_groups_saved' => 'Benutzer-Berechtigungen gespeichert.',
    'user_deleted' => 'Benutzer wurde aus LLDAP gelöscht.',
    'user_delete_failed' => 'Benutzer konnte nicht gelöscht werden. Bitte Logs prüfen.',
    'user_delete_blocked' => 'Geschützter Benutzer wurde nicht gelöscht.',
    'approved' => 'Registrierungsantrag freigegeben.',
    'blacklisted' => 'Registrierungsantrag abgelehnt und zur Blacklist hinzugefügt.',
    'mail_template_invalid' => 'Mail-Vorlage nicht gespeichert: Ein Pflicht-Platzhalter fehlt oder wurde verändert.',
    'lldap_test_ok' => 'LLDAP-Verbindung erfolgreich getestet.',
    'lldap_test_failed' => 'LLDAP-Test fehlgeschlagen. Bitte Logs prüfen.',
    'bridge_test_ok' => 'Bridge erfolgreich getestet.',
    'bridge_test_failed' => 'Bridge-Test fehlgeschlagen. Bitte Logs prüfen.',
    'test_mail_ok' => 'Test-Mail wurde gesendet.',
    'test_mail_failed' => 'Test-Mail konnte nicht gesendet werden. Bitte Logs prüfen.',
    'test_mail_invalid' => 'Bitte eine gültige Test-Mail-Adresse eingeben.',
    'audit_cleared' => 'Audit-Log wurde geleert.',
    'audit_settings_saved' => 'Audit-Einstellungen gespeichert.',
];

$messageTypes = [
    'settings_saved' => 'success',
    'user_groups_saved' => 'success',
    'user_deleted' => 'warning',
    'user_delete_failed' => 'error',
    'user_delete_blocked' => 'error',
    'approved' => 'success',
    'blacklisted' => 'warning',
    'mail_template_invalid' => 'error',
    'lldap_test_ok' => 'success',
    'lldap_test_failed' => 'error',
    'bridge_test_ok' => 'success',
    'bridge_test_failed' => 'error',
    'test_mail_ok' => 'success',
    'test_mail_failed' => 'error',
    'test_mail_invalid' => 'error',
    'audit_cleared' => 'success',
    'audit_settings_saved' => 'success',
];

$defaultApprovalGroupIds = array_values(array_filter(array_map('trim', explode(',', (string)($settings['default_approval_group_ids'] ?? '')))));

$protectedUserIds = array_filter(array_map('trim', explode(',', (string)($settings['protected_user_ids'] ?? 'admin'))));
$protectedUserIds[] = (string)($settings['lldap_admin_user'] ?? '');
$protectedUserIds = array_values(array_unique(array_filter($protectedUserIds)));

$pendingGroupId = (string)($settings['lldap_pending_group_id'] ?? '');
$blacklistGroupId = (string)($settings['lldap_blacklist_group_id'] ?? '');

$protectedGroupNames = array_filter(array_map('trim', explode(',', strtolower((string)($settings['protected_group_names'] ?? 'pending-users,blacklist')))));
$protectedGroupPrefixes = array_filter(array_map('trim', explode(',', strtolower((string)($settings['protected_group_prefixes'] ?? 'lldap_')))));

$assignableGroups = array_filter($groups, function ($group) use ($pendingGroupId, $blacklistGroupId, $protectedGroupNames, $protectedGroupPrefixes) {
    $id = (string)($group['id'] ?? '');


    $name = strtolower((string)($group['displayName'] ?? ''));

    if ($id === $pendingGroupId || $id === $blacklistGroupId) {
        return false;
    }

    if (in_array($name, $protectedGroupNames, true)) {
        return false;
    }

    foreach ($protectedGroupPrefixes as $prefix) {
        if ($prefix !== '' && strpos($name, $prefix) === 0) {
            return false;
        }
    }

    return true;
});

$setupChecks = [];

$passwordWriter = (string)($settings['password_writer'] ?? 'direct_ldap');

if (!in_array($passwordWriter, ['direct_ldap', 'direct_ldap_with_bridge_fallback', 'bridge_legacy'], true)) {
    $passwordWriter = 'direct_ldap';
}


$setupChecks[] = [
    'label' => 'Branding gesetzt',
    'ok' => trim((string)($settings['brand_name'] ?? '')) !== '',
];

$setupChecks[] = [
    'label' => 'LLDAP GraphQL URL gesetzt',
    'ok' => trim((string)($settings['lldap_url'] ?? '')) !== '',
];

$setupChecks[] = [
    'label' => 'LLDAP Admin User gesetzt',
    'ok' => trim((string)($settings['lldap_admin_user'] ?? '')) !== '',
];

$setupChecks[] = [
    'label' => 'LLDAP Admin-Passwort gespeichert',
    'ok' => (($settings['has_lldap_admin_password'] ?? '0') === '1'),
];

$setupChecks[] = [
    'label' => 'Pending-Gruppe ausgewählt',
    'ok' => trim((string)($settings['lldap_pending_group_id'] ?? '')) !== '',
];

$setupChecks[] = [
    'label' => 'Blacklist-Gruppe ausgewählt',
    'ok' => trim((string)($settings['lldap_blacklist_group_id'] ?? '')) !== '',
];

if ($passwordWriter !== 'bridge_legacy') {
    $setupChecks[] = [
        'label' => 'PHP LDAP Modul verfügbar',
        'ok' => (($settings['has_php_ldap'] ?? '0') === '1'),
    ];

    $setupChecks[] = [
        'label' => 'LDAP Passwortänderung verfügbar',
        'ok' => (($settings['has_ldap_exop_passwd'] ?? '0') === '1'),
    ];

    $setupChecks[] = [
        'label' => 'LLDAP LDAP URL gesetzt',
        'ok' => trim((string)($settings['lldap_ldap_url'] ?? '')) !== '',
    ];

    $setupChecks[] = [
        'label' => 'LLDAP Base DN gesetzt',
        'ok' => trim((string)($settings['lldap_base_dn'] ?? '')) !== '',
    ];

    $setupChecks[] = [
        'label' => 'LLDAP User-DN Template gesetzt',
        'ok' => trim((string)($settings['lldap_user_dn_template'] ?? '')) !== '',
    ];
}

if ($passwordWriter === 'bridge_legacy' || $passwordWriter === 'direct_ldap_with_bridge_fallback') {
    $setupChecks[] = [
        'label' => 'Legacy Bridge URL gesetzt',
        'ok' => trim((string)($settings['bridge_url'] ?? '')) !== '',
    ];

    $setupChecks[] = [
        'label' => 'Legacy Bridge Secret gespeichert',
        'ok' => (($settings['has_bridge_secret'] ?? '0') === '1'),
    ];
}

$setupChecks[] = [
    'label' => 'Login URL gesetzt',
    'ok' => trim((string)($settings['login_url'] ?? '')) !== '',
];

$mailTemplateRequired = [
    'mail_confirm_subject' => ['{brand}'],
    'mail_confirm_body' => ['{code}', '{link}'],
    'mail_approved_subject' => ['{brand}'],
    'mail_approved_body' => ['{displayName}', '{loginUrl}'],
    'mail_rejected_subject' => ['{brand}'],
    'mail_rejected_body' => ['{displayName}'],
    'mail_password_reset_subject' => ['{brand}'],
    'mail_password_reset_body' => ['{code}', '{link}'],
];

$mailTemplatesOk = true;

foreach ($mailTemplateRequired as $field => $requiredPlaceholders) {
    $value = (string)($settings[$field] ?? '');

    foreach ($requiredPlaceholders as $placeholder) {
        if (strpos($value, $placeholder) === false) {
            $mailTemplatesOk = false;
            break 2;
        }
    }
}

$setupChecks[] = [
    'label' => 'Mail-Vorlagen vollständig',
    'ok' => $mailTemplatesOk,
];

$setupChecks[] = [
    'label' => 'Rate-Limit aktiviert',
    'ok' => (($settings['rate_limit_enabled'] ?? '1') === '1'),
];

$setupOk = true;

foreach ($setupChecks as $setupCheck) {
    if (empty($setupCheck['ok'])) {
        $setupOk = false;
        break;
    }
}


$initialAdminPage = 'pending';

if (in_array($msg, ['settings_saved', 'mail_template_invalid', 'lldap_test_ok', 'lldap_test_failed', 'bridge_test_ok', 'bridge_test_failed', 'test_mail_ok', 'test_mail_failed', 'test_mail_invalid'], true)) {
    $initialAdminPage = 'settings';
} elseif (in_array($msg, ['audit_cleared', 'audit_settings_saved'], true)) {
    $initialAdminPage = 'audit';
} elseif (in_array($msg, ['user_groups_saved', 'user_deleted', 'user_delete_failed', 'user_delete_blocked'], true)) {
    $initialAdminPage = 'users';
} elseif (in_array($msg, ['approved', 'blacklisted'], true)) {
    $initialAdminPage = 'pending';
}

$auditEvents = json_decode((string)($settings['audit_events'] ?? '[]'), true);

if (!is_array($auditEvents)) {
    $auditEvents = [];
}
?>

<div class="section">
    <h2><?php p($settings['brand_name'] ?? 'Enhanced Registration'); ?></h2>

    <?php if (!empty($_['lldap_load_error'])): ?>
        <div class="nc-admin-message warning">
            <?php p($_['lldap_load_error']); ?>
        </div>
    <?php endif; ?>


    <div class="nc-admin-tabs" data-initial-page="<?php p($initialAdminPage); ?>">
        <button type="button" class="nc-admin-tab <?php echo $initialAdminPage === 'pending' ? 'active' : ''; ?>" data-page="pending">Ausstehende Registrierungen</button>
        <button type="button" class="nc-admin-tab <?php echo $initialAdminPage === 'users' ? 'active' : ''; ?>" data-page="users">Benutzer & Berechtigungen</button>
        <button type="button" class="nc-admin-tab <?php echo $initialAdminPage === 'audit' ? 'active' : ''; ?>" data-page="audit">Audit-Log</button>
        <button type="button" class="nc-admin-tab <?php echo $initialAdminPage === 'settings' ? 'active' : ''; ?>" data-page="settings">Einstellungen</button>
    </div>


    <div class="nc-admin-page <?php echo $initialAdminPage === 'settings' ? 'active' : ''; ?>" data-page="settings">
    <div class="nc-setup-status <?php echo $setupOk ? 'ok' : 'warn'; ?>">
        <h3>Setup-Status</h3>
        <p class="nc-muted">
            Diese Übersicht zeigt, ob die wichtigsten Einstellungen gesetzt sind. Direct LDAP ist der empfohlene Password Writer. Die Bridge ist nur noch Legacy/Fallback.
        </p>
        <p class="nc-admin-message warning">
            Wichtig: Die Pending-Gruppe darf im Nextcloud LDAP-Loginfilter nicht zur Anmeldung berechtigt sein.
            Ausstehende Benutzer sollen erst nach Admin-Freigabe durch ihre Zielgruppen loginfähig werden.
        </p>

        <div class="nc-setup-grid">
            <?php foreach ($setupChecks as $setupCheck): ?>
                <div class="nc-setup-item <?php echo !empty($setupCheck['ok']) ? 'ok' : 'fail'; ?>">
                    <span class="nc-setup-icon"><?php echo !empty($setupCheck['ok']) ? '✓' : '!'; ?></span>
                    <span><?php p($setupCheck['label']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="nc-test-actions">
            <form method="POST" action="/index.php/apps/enhanced_registration/admin/settings">
            <input type="hidden" name="default_approval_group_ids" value="">
                <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                <input type="hidden" name="save_type" value="test_lldap">
                <button type="submit" class="button">LLDAP testen</button>
            </form>

            <form method="POST" action="/index.php/apps/enhanced_registration/admin/settings">
                <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                <input type="hidden" name="save_type" value="test_bridge">
                <button type="submit" class="button">Bridge testen</button>
            </form>

            <form method="POST" action="/index.php/apps/enhanced_registration/admin/settings">
                <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                <input type="hidden" name="save_type" value="test_mail">
                <label>
                    Test-Mail-Empfänger
                    <input type="email" name="test_email" value="<?php p($settings['test_mail_recipient'] ?? ''); ?>" placeholder="admin@example.com" required>
                </label>
                <button type="submit" class="button">Test-Mail senden</button>
            </form>
        </div>
    </div>


    <?php if (!empty($msg) && isset($messages[$msg])): ?>
        <div id="nc-admin-initial-message" data-message="<?php p($messages[$msg]); ?>" data-type="<?php p($messageTypes[$msg] ?? 'success'); ?>" style="display:none;"></div>
    <?php endif; ?>

    <div class="nc-settings">
        <h3>Einstellungen</h3>
        <p class="nc-muted">Diese Werte werden gespeichert. Geheime Werte werden nicht im Klartext angezeigt.</p>

        <form method="POST" action="/index.php/apps/enhanced_registration/admin/settings">
                    <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
            <div class="nc-settings-grid">
                <div class="nc-settings-card">
                    <h4>Verbindung</h4>

                    <label>Branding / Anzeigename</label>
                    <input type="text" name="brand_name" value="<?php p($settings['brand_name'] ?? 'Enhanced Registration'); ?>" placeholder="Enhanced Registration">

                    <label>Sprache / Language</label>
                    <select name="ui_language">
                        <?php $uiLanguage = (string)($settings['ui_language'] ?? 'auto'); ?>
                        <option value="auto" <?php if ($uiLanguage === 'auto') { echo 'selected'; } ?>>Automatisch / Auto</option>
                        <option value="de" <?php if ($uiLanguage === 'de') { echo 'selected'; } ?>>Deutsch</option>
                        <option value="en" <?php if ($uiLanguage === 'en') { echo 'selected'; } ?>>English</option>
                    </select>
                    <p class="nc-muted">Legt die Sprache für sichtbare App-Texte fest. Bei „Automatisch“ wird die Browser-Sprache genutzt. Mail-Vorlagen bleiben separat editierbar.</p>

                    <label>LLDAP GraphQL URL</label>
                    <input type="text" name="lldap_url" value="<?php p($settings['lldap_url'] ?? ''); ?>" placeholder="http://lldap:30325/api/graphql">

                    <label>LLDAP Admin User</label>
                    <input type="text" name="lldap_admin_user" value="<?php p($settings['lldap_admin_user'] ?? ''); ?>" placeholder="Admin">
                </div>

                <div class="nc-settings-card">
                    <h4>Geheimnisse</h4>

                    <label>LLDAP Admin-Passwort</label>
                    <input type="password" name="lldap_admin_password" value="" placeholder="Leer lassen = unverändert">
                    <p class="nc-muted">
                        Status: <?php echo (($settings['has_lldap_admin_password'] ?? '0') === '1') ? 'gespeichert' : 'nicht gesetzt'; ?>.
                        Leer lassen, um den bestehenden Wert beizubehalten.
                    </p>

                    <label>Bridge-Secret</label>
                    <input type="password" name="bridge_secret" value="" placeholder="Leer lassen = unverändert">
                    <p class="nc-muted">
                        Status: <?php echo (($settings['has_bridge_secret'] ?? '0') === '1') ? 'gespeichert' : 'nicht gesetzt'; ?>.
                        Leer lassen, um den bestehenden Wert beizubehalten.
                    </p>
                </div>

                <div class="nc-settings-card">
                    <h4>Password Writer & Weiterleitung</h4>

                    <label>Password Writer</label>
                    <select name="password_writer">
                        <option value="direct_ldap" <?php if (($settings['password_writer'] ?? 'direct_ldap') === 'direct_ldap') { echo 'selected'; } ?>>
                            Direct LDAP password writer empfohlen
                        </option>
                        <option value="direct_ldap_with_bridge_fallback" <?php if (($settings['password_writer'] ?? 'direct_ldap') === 'direct_ldap_with_bridge_fallback') { echo 'selected'; } ?>>
                            Direct LDAP mit Legacy-Bridge-Fallback
                        </option>
                        <option value="bridge_legacy" <?php if (($settings['password_writer'] ?? 'direct_ldap') === 'bridge_legacy') { echo 'selected'; } ?>>
                            Legacy Bridge only
                        </option>
                    </select>
                    <p class="nc-muted">
                        Direct LDAP nutzt PHP-LDAP und ldap_exop_passwd direkt aus der Nextcloud-App.
                        Die Bridge bleibt nur noch als Legacy-Fallback erhalten.
                    </p>

                    <label>PHP LDAP Status</label>
                    <p class="nc-muted">
                        ldap_connect:
                        <?php echo (($settings['has_php_ldap'] ?? '0') === '1') ? 'verfügbar' : 'fehlt'; ?> ·
                        ldap_exop_passwd:
                        <?php echo (($settings['has_ldap_exop_passwd'] ?? '0') === '1') ? 'verfügbar' : 'fehlt'; ?>
                    </p>

                    <label>LLDAP LDAP URL</label>
                    <input type="text" name="lldap_ldap_url" value="<?php p($settings['lldap_ldap_url'] ?? ''); ?>" placeholder="ldap://lldap:3890">
                    <p class="nc-muted">
                        Das ist die LDAP-Schnittstelle von LLDAP, nicht die GraphQL-URL.
                    </p>

                    <label>LLDAP Base DN</label>
                    <input type="text" name="lldap_base_dn" value="<?php p($settings['lldap_base_dn'] ?? ''); ?>" placeholder="dc=example,dc=com">
                    <p class="nc-muted">
                        Beispiel: <code>dc=example,dc=com</code>. Muss zu Ihrer LLDAP-Konfiguration passen.
                    </p>

                    <label>LLDAP Admin DN optional</label>
                    <input type="text" name="lldap_admin_dn" value="<?php p($settings['lldap_admin_dn'] ?? ''); ?>" placeholder="uid=admin,ou=people,dc=example,dc=com">
                    <p class="nc-muted">
                        Leer lassen, wenn der Admin-DN automatisch aus LLDAP Admin User + Base DN gebaut werden soll.
                    </p>

                    <label>LLDAP User-DN Template</label>
                    <input type="text" name="lldap_user_dn_template" value="<?php p($settings['lldap_user_dn_template'] ?? 'uid={uid},ou=people,{base}'); ?>" placeholder="uid={uid},ou=people,{base}">
                    <p class="nc-muted">
                        Platzhalter: <code>{uid}</code> für die User-ID, <code>{base}</code> für die Base DN.
                    </p>

                    <hr>
                    <h4>Legacy Bridge / Fallback</h4>
                    <p class="nc-muted">
                        Nur nötig, wenn der Password Writer auf Bridge oder Fallback gestellt ist.
                    </p>

                    <label>Bridge URL</label>
                    <input type="text" name="bridge_url" value="<?php p($settings['bridge_url'] ?? ''); ?>" placeholder="http://your-bridge-host:18081">

                    <label>Login URL</label>
                    <input type="text" name="login_url" value="<?php p($settings['login_url'] ?? '/login'); ?>">

                    <label>Redirect nach erfolgreicher Registrierung</label>
                    <input type="text" name="registration_success_redirect_url" value="<?php p($settings['registration_success_redirect_url'] ?? ''); ?>" placeholder="Leer = Login URL">

                    <label>Redirect nach erfolgreichem Passwortreset</label>
                    <input type="text" name="password_reset_success_redirect_url" value="<?php p($settings['password_reset_success_redirect_url'] ?? ''); ?>" placeholder="Leer = Login URL">
                </div>

                <div class="nc-settings-card" style="grid-column:1/-1;">
                    <h4>Mail-Vorlagen</h4>
                    <p class="nc-muted">
                        Platzhalter: {brand}, {code}, {link}, {displayName}, {userId}, {groups}, {loginUrl}
                    </p>
                    <p class="nc-admin-message warning">
                        Hinweis: Pflicht-Platzhalter bitte nicht entfernen oder verändern. Beim Speichern werden sie geprüft.
                    </p>

                    <label>Betreff: Konto bestätigen</label>
                    <input type="text" name="mail_confirm_subject" value="<?php p($settings['mail_confirm_subject'] ?? '{brand}: E-Mail bestätigen'); ?>">

                    <label>Text: Konto bestätigen</label>
                    <textarea name="mail_confirm_body" rows="7"><?php p($settings['mail_confirm_body'] ?? "Hallo,\n\nbitte bestätigen Sie Ihre Registrierung.\n\nBestätigungscode: {code}\n\nAlternativ können Sie diesen Link öffnen:\n{link}\n\nWenn Sie diese Anfrage nicht gestellt haben, können Sie diese E-Mail ignorieren."); ?></textarea>

                    <label>Betreff: Konto freigegeben</label>
                    <input type="text" name="mail_approved_subject" value="<?php p($settings['mail_approved_subject'] ?? '{brand}: Konto freigegeben'); ?>">

                    <label>Text: Konto freigegeben</label>
                    <textarea name="mail_approved_body" rows="6"><?php p($settings['mail_approved_body'] ?? "Hallo {displayName},\n\nIhr Konto wurde freigegeben.\n\nZugewiesene Gruppen: {groups}\n\nAnmeldung: {loginUrl}"); ?></textarea>

                    <label>Betreff: Registrierung abgelehnt</label>
                    <input type="text" name="mail_rejected_subject" value="<?php p($settings['mail_rejected_subject'] ?? '{brand}: Registrierung abgelehnt'); ?>">

                    <label>Text: Registrierung abgelehnt</label>
                    <textarea name="mail_rejected_body" rows="5"><?php p($settings['mail_rejected_body'] ?? "Hallo {displayName},\n\nIhre Registrierung wurde abgelehnt.\n\nBei Fragen wenden Sie sich bitte an einen Administrator."); ?></textarea>

                    <label>Betreff: Passwort zurücksetzen</label>
                    <input type="text" name="mail_password_reset_subject" value="<?php p($settings['mail_password_reset_subject'] ?? '{brand}: Passwort zurücksetzen'); ?>">

                    <label>Text: Passwort zurücksetzen</label>
                    <textarea name="mail_password_reset_body" rows="8"><?php p($settings['mail_password_reset_body'] ?? "Hallo,\n\nfür Ihr Konto wurde ein Passwortreset angefordert.\n\nBestätigungscode: {code}\n\nAlternativ können Sie diesen Link öffnen:\n{link}\n\nDer Code und Link sind 10 Minuten gültig und nur einmal verwendbar.\n\nWenn Sie diese Anfrage nicht gestellt haben, können Sie diese E-Mail ignorieren."); ?></textarea>
                </div>

                <div class="nc-settings-card">
                    <h4>Gruppen</h4>

                    <?php if ($pendingGroupId === '' || $blacklistGroupId === ''): ?>
                        <p class="nc-admin-message warning">Bitte Pending- und Blacklist-Gruppe auswählen und speichern.</p>
                    <?php endif; ?>

                    <label>Pending-Gruppe</label>
                    <select name="lldap_pending_group_id">
                        <?php foreach ($groups as $group): ?>
                            <?php $gid = (string)($group['id'] ?? ''); ?>
                            <option value="<?php p($gid); ?>" <?php if ($gid === $pendingGroupId) { echo 'selected'; } ?>>
                                <?php p(($group['displayName'] ?? '') . ' #' . $gid); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Blacklist-Gruppe</label>
                    <select name="lldap_blacklist_group_id">
                        <?php foreach ($groups as $group): ?>
                            <?php $gid = (string)($group['id'] ?? ''); ?>
                            <option value="<?php p($gid); ?>" <?php if ($gid === $blacklistGroupId) { echo 'selected'; } ?>>
                                <?php p(($group['displayName'] ?? '') . ' #' . $gid); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Ablehnen-Verhalten</label>
                    <?php $rejectionAction = (string)($settings['rejection_action'] ?? 'blacklist'); ?>
                    <select name="rejection_action">
                        <option value="blacklist" <?php if ($rejectionAction === 'blacklist') { echo 'selected'; } ?>>Zur Blacklist hinzufügen</option>
                        <option value="remove_pending" <?php if ($rejectionAction === 'remove_pending') { echo 'selected'; } ?>>Nur aus Pending-Gruppe entfernen</option>
                        <option value="delete_user" <?php if ($rejectionAction === 'delete_user') { echo 'selected'; } ?>>LDAP-Benutzer löschen</option>
                    </select>
                    <p class="nc-muted">
                        Legt fest, was passiert, wenn eine ausstehende Registrierung abgelehnt wird.
                        „LDAP-Benutzer löschen“ entfernt den Benutzer vollständig aus LLDAP.
                    </p>

                    <label>Standardgruppen bei Freigabe</label>
                    <div class="nc-group-list" style="margin-top:8px;">
                        <?php foreach ($assignableGroups as $group): ?>
                            <?php $gid = (string)($group['id'] ?? ''); ?>
                            <label>
                                <input
                                    type="checkbox"
                                    name="default_approval_group_ids[]"
                                    value="<?php p($gid); ?>"
                                    <?php if (in_array($gid, $defaultApprovalGroupIds, true)) { echo 'checked'; } ?>
                                >
                                <?php p($group['displayName'] ?? $gid); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="nc-muted">
                        Diese Gruppen sind bei ausstehenden Registrierungen automatisch vorausgewählt. Sie können pro Benutzer weiterhin angepasst werden.
                    </p>

                    <p class="nc-admin-message warning">
                        LDAP-Loginfilter-Hinweis: Die Pending-Gruppe sollte in Nextcloud nicht als erlaubte Login-Gruppe gelten.
                        Empfohlen ist ein Loginfilter, der nur freigegebene Zielgruppen erlaubt und Pending/Blacklist ausschließt.
                    </p>

                    <label>Geschützte Gruppen-Namen</label>
                    <input type="text" name="protected_group_names" value="<?php p($settings['protected_group_names'] ?? 'pending-users,blacklist'); ?>" placeholder="pending-users,blacklist">
                    <p class="nc-muted">Kommagetrennt. Diese Gruppen werden nicht zur Auswahl angezeigt.</p>

                    <label>Geschützte Gruppen-Präfixe</label>
                    <input type="text" name="protected_group_prefixes" value="<?php p($settings['protected_group_prefixes'] ?? 'lldap_'); ?>" placeholder="lldap_">
                    <p class="nc-muted">Kommagetrennt. Gruppen mit diesen Präfixen werden ausgeblendet.</p>

                    <label>Geschützte Benutzer-IDs</label>
                    <input type="text" name="protected_user_ids" value="<?php p($settings['protected_user_ids'] ?? 'admin'); ?>" placeholder="admin">
                    <p class="nc-muted">Kommagetrennt. Diese Benutzer werden unter „Benutzer & Berechtigungen“ nicht angezeigt.</p>

                    <label style="margin-top:16px;">
                        <input type="hidden" name="store_user_email_in_ldap" value="0">
                        <input
                            type="checkbox"
                            name="store_user_email_in_ldap"
                            value="1"
                            <?php if (($settings['store_user_email_in_ldap'] ?? '1') === '1') { echo 'checked'; } ?>
                            style="width:auto;margin-right:8px;"
                        >
                        E-Mail-Adresse im LDAP-Benutzer speichern
                    </label>
                    <p class="nc-muted">
                        Wenn aktiviert, wird die Registrierungs-E-Mail im LDAP-Feld gespeichert und kann von Nextcloud oder verbundenen Diensten übernommen werden.
                        Wenn deaktiviert, können Freigabe-Mails und Passwortreset per E-Mail eingeschränkt sein, solange die E-Mail nicht anderweitig im LDAP gesetzt wird.
                    </p>

                    <label>Erlaubte E-Mail-Domains</label>
                    <input type="text" name="allowed_email_domains" value="<?php p($settings['allowed_email_domains'] ?? ''); ?>" placeholder="example.com,*.example.org">
                    <p class="nc-muted">Leer = alle Domains erlaubt. Kommagetrennt. Beispiel: example.com,*.example.org</p>

                    <label>Gesperrte E-Mail-Domains</label>
                    <input type="text" name="denied_email_domains" value="<?php p($settings['denied_email_domains'] ?? ''); ?>" placeholder="tempmail.com,spam.test">
                    <p class="nc-muted">Kommagetrennt. Gesperrte Domains haben immer Vorrang vor erlaubten Domains.</p>

                    <h4 style="margin-top:22px;">Rate-Limit</h4>

                    <label style="margin-top:12px;">
                        <input type="hidden" name="rate_limit_enabled" value="0">
                        <input
                            type="checkbox"
                            name="rate_limit_enabled"
                            value="1"
                            <?php if (($settings['rate_limit_enabled'] ?? '1') === '1') { echo 'checked'; } ?>
                            style="width:auto;margin-right:8px;"
                        >
                        Rate-Limit aktivieren
                    </label>

                    <label>Cooldown in Sekunden</label>
                    <input type="number" min="0" max="3600" name="rate_limit_cooldown_seconds" value="<?php p($settings['rate_limit_cooldown_seconds'] ?? '60'); ?>">

                    <label>Zeitfenster in Minuten</label>
                    <input type="number" min="1" max="1440" name="rate_limit_window_minutes" value="<?php p($settings['rate_limit_window_minutes'] ?? '15'); ?>">

                    <label>Maximale Anfragen pro Zeitfenster</label>
                    <input type="number" min="1" max="100" name="rate_limit_max_attempts" value="<?php p($settings['rate_limit_max_attempts'] ?? '5'); ?>">

                    <p class="nc-muted">
                        Gilt für Registrierungscode, Code erneut senden, Passwortreset und Passwortreset erneut senden.
                    </p>

                    <h4 style="margin-top:22px;">Passwortregeln</h4>

                    <label>Mindestlänge</label>
                    <input type="number" min="1" max="128" name="password_min_length" value="<?php p($settings['password_min_length'] ?? '12'); ?>">

                    <label style="margin-top:12px;">
                        <input type="hidden" name="password_require_uppercase" value="0">
                        <input type="checkbox" name="password_require_uppercase" value="1" <?php if (($settings['password_require_uppercase'] ?? '1') === '1') { echo 'checked'; } ?> style="width:auto;margin-right:8px;">
                        Großbuchstabe erforderlich
                    </label>

                    <label style="margin-top:12px;">
                        <input type="hidden" name="password_require_lowercase" value="0">
                        <input type="checkbox" name="password_require_lowercase" value="1" <?php if (($settings['password_require_lowercase'] ?? '1') === '1') { echo 'checked'; } ?> style="width:auto;margin-right:8px;">
                        Kleinbuchstabe erforderlich
                    </label>

                    <label style="margin-top:12px;">
                        <input type="hidden" name="password_require_number" value="0">
                        <input type="checkbox" name="password_require_number" value="1" <?php if (($settings['password_require_number'] ?? '1') === '1') { echo 'checked'; } ?> style="width:auto;margin-right:8px;">
                        Zahl erforderlich
                    </label>

                    <label style="margin-top:12px;">
                        <input type="hidden" name="password_require_special" value="0">
                        <input type="checkbox" name="password_require_special" value="1" <?php if (($settings['password_require_special'] ?? '1') === '1') { echo 'checked'; } ?> style="width:auto;margin-right:8px;">
                        Sonderzeichen erforderlich
                    </label>

                    <label style="margin-top:12px;">
                        <input type="hidden" name="password_hibp_enabled" value="0">
                        <input type="checkbox" name="password_hibp_enabled" value="1" <?php if (($settings['password_hibp_enabled'] ?? '1') === '1') { echo 'checked'; } ?> style="width:auto;margin-right:8px;">
                        HaveIBeenPwned-Prüfung aktivieren
                    </label>

                    <p class="nc-muted">
                        Die HaveIBeenPwned-Prüfung nutzt das k-Anonymity-Verfahren und sendet nicht das vollständige Passwort.
                    </p>
                </div>
            </div>

            <p style="margin-top:18px;">
                <button type="submit" class="button primary">Einstellungen speichern</button>
            </p>
        </form>
    </div>

    </div>

    <div class="nc-admin-page <?php echo $initialAdminPage === 'pending' ? 'active' : ''; ?>" data-page="pending">
    <h3>Ausstehende Registrierungen</h3>

    <?php if (empty($_['pendingUsers'])): ?>
        <p>Keine ausstehenden Registrierungen.</p>
    <?php else: ?>
        <div class="nc-reg-grid">
            <?php foreach ($_['pendingUsers'] as $user): ?>
                <div class="nc-reg-card">
                    <div class="nc-reg-user"><?php p($user['id'] ?? ''); ?></div>
                    <div class="nc-reg-name"><?php p($user['displayName'] ?? ''); ?></div>
                    <div class="nc-reg-mail"><?php p($user['email'] ?? ''); ?></div>

                    <form method="POST" action="/index.php/apps/enhanced_registration/admin/approve">
                    <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                        <input type="hidden" name="userId" value="<?php p($user['id'] ?? ''); ?>">

                        <div class="nc-reg-select nc-group-list">
                            <?php foreach ($assignableGroups as $group): ?>
                                <?php $gid = (string)($group['id'] ?? ''); ?>
                                <label>
                                    <input
                                        type="checkbox"
                                        name="groupIds[]"
                                        value="<?php p($gid); ?>"
                                        <?php if (in_array($gid, $defaultApprovalGroupIds, true)) { echo 'checked'; } ?>
                                    >
                                    <?php p(($group['displayName'] ?? '') . ' #' . $gid); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="nc-reg-actions">
                            <button type="submit" class="button nc-btn-approve">✅ Freigeben</button>
                    </form>

                            <form method="POST" action="/index.php/apps/enhanced_registration/admin/blacklist">
                    <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                                <input type="hidden" name="userId" value="<?php p($user['id'] ?? ''); ?>">
                                <button type="submit" class="button nc-btn-blacklist">⛔ Blacklist</button>
                            </form>
                        </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div>

    <div class="nc-admin-page <?php echo $initialAdminPage === 'users' ? 'active' : ''; ?>" data-page="users">
    <h3 style="margin-top:32px;">Benutzer & Berechtigungen</h3>
    <p class="nc-admin-message warning">
        Der Löschbutton entfernt Benutzer vollständig aus LLDAP. Das ist nicht nur eine Gruppenänderung.
        Geschützte Benutzer-IDs werden blockiert.
    </p>

    <input
        type="text"
        id="nc-user-search"
        placeholder="Benutzer suchen..."
        style="width:100%;max-width:520px;margin:8px 0 18px;"
    >

    <div class="nc-reg-grid" id="nc-user-list">
        <?php foreach (($_['users'] ?? []) as $user): ?>
            <?php
                $userId = (string)($user['id'] ?? '');
                if (in_array($userId, $protectedUserIds, true)) {
                    continue;
                }

                $searchText = strtolower(
                    ($user['id'] ?? '') . ' ' .
                    ($user['displayName'] ?? '') . ' ' .
                    ($user['email'] ?? '')
                );
            ?>
            <div class="nc-reg-card nc-user-card" data-search="<?php p($searchText); ?>">
                <div class="nc-reg-user"><?php p($user['id'] ?? ''); ?></div>
                <div class="nc-reg-name"><?php p($user['displayName'] ?? ''); ?></div>
                <div class="nc-reg-mail"><?php p($user['email'] ?? ''); ?></div>

                <?php
                    $currentGroupIds = array_map(function ($group) {
                        return (string)($group['id'] ?? '');
                    }, $user['groups'] ?? []);
                ?>

                <form method="POST" action="/index.php/apps/enhanced_registration/admin/settings">
                    <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                    <input type="hidden" name="save_type" value="user_groups">
                    <input type="hidden" name="userId" value="<?php p($user['id'] ?? ''); ?>">

                    <div class="nc-muted">Berechtigungen:</div>

                    <div class="nc-group-list" style="margin-top:8px;">
                        <?php foreach ($assignableGroups as $group): ?>
                            <?php $gid = (string)($group['id'] ?? ''); ?>
                            <label>
                                <input
                                    type="checkbox"
                                    name="groupIds[]"
                                    value="<?php p($gid); ?>"
                                    <?php if (in_array($gid, $currentGroupIds, true)) { echo 'checked'; } ?>
                                >
                                <?php p(($group['displayName'] ?? '') . ' #' . $gid); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="button" style="margin-top:12px;">
                        Berechtigungen speichern
                    </button>
                </form>

                <form
                    method="POST"
                    action="/index.php/apps/enhanced_registration/admin/users/delete"
                    onsubmit="return confirm('Benutzer wirklich vollständig aus LLDAP löschen? Diese Aktion kann nicht rückgängig gemacht werden.');"
                    style="margin-top:12px;"
                >
                    <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                    <input type="hidden" name="userId" value="<?php p($user['id'] ?? ''); ?>">
                    <button type="submit" class="button nc-btn-blacklist">
                        Benutzer aus LLDAP löschen
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    </div>

    <div class="nc-admin-page <?php echo $initialAdminPage === 'audit' ? 'active' : ''; ?>" data-page="audit">
        <h3>Audit-Log</h3>
        <p class="nc-muted">
            Audit-Ereignisse werden nach den unten gesetzten Grenzen gespeichert. E-Mail-Adressen werden nur als Hash und Domain gespeichert.
        </p>

        <div class="nc-settings-card" style="max-width:720px;margin:14px 0;">
            <h4>Audit-Aufbewahrung</h4>
            <form method="POST" action="/index.php/apps/enhanced_registration/admin/settings">
                <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                <input type="hidden" name="save_type" value="audit_settings">

                <label>Maximale Audit-Einträge</label>
                <input type="number" min="1" max="1000" name="audit_max_events" value="<?php p($settings['audit_max_events'] ?? '100'); ?>">

                <label>Audit-Aufbewahrung in Tagen</label>
                <input type="number" min="0" max="3650" name="audit_retention_days" value="<?php p($settings['audit_retention_days'] ?? '90'); ?>">

                <p class="nc-muted">0 Tage = keine zeitbasierte Löschung. Die maximale Anzahl begrenzt trotzdem die gespeicherten Einträge.</p>

                <button type="submit" class="button primary">Audit-Einstellungen speichern</button>
            </form>
        </div>

        <div class="nc-audit-actions">
            <form method="POST" action="/index.php/apps/enhanced_registration/admin/settings">
                <input type="hidden" name="requesttoken" value="<?php p($requestToken); ?>">
                <input type="hidden" name="save_type" value="clear_audit">
                <button type="submit" class="button">Audit-Log leeren</button>
            </form>
        </div>

        <?php if (empty($auditEvents)): ?>
            <p>Noch keine Audit-Einträge.</p>
        <?php else: ?>
            <table class="nc-audit-table">
                <thead>
                    <tr>
                        <th>Zeit</th>
                        <th>Aktion</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditEvents as $event): ?>
                        <?php
                            $context = $event['context'] ?? [];
                            if (!is_array($context)) {
                                $context = [];
                            }
                        ?>
                        <tr>
                            <td><?php p((string)($event['time'] ?? '')); ?></td>
                            <td><code><?php p((string)($event['action'] ?? '')); ?></code></td>
                            <td><code><?php p(json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>


</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
