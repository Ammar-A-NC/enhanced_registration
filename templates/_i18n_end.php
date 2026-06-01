<?php
$out = ob_get_clean();
$lang = $GLOBALS['nc_er_ui_language'] ?? 'en';

if ($lang === 'en') {
    $map = [
        // Common
        'Deutsch' => 'German',
        'English' => 'English',
        'Speichern' => 'Save',
        'Weiter' => 'Continue',
        'Zurück zum Login' => 'Back to login',
        'Zur Anmeldung' => 'To login',
        'Jetzt weiter' => 'Continue now',
        'Jetzt zur Anmeldung' => 'Go to login now',
        'Automatische Weiterleitung in' => 'Automatic redirect in',
        'Sekunden' => 'seconds',

        // Admin messages
        'Einstellungen gespeichert.' => 'Settings saved.',
        'Benutzer-Berechtigungen gespeichert.' => 'User permissions saved.',
        'Registrierungsantrag freigegeben.' => 'Registration request approved.',
        'Registrierungsantrag abgelehnt und zur Blacklist hinzugefügt.' => 'Registration request rejected and added to blacklist.',
        'Mail-Vorlage nicht gespeichert: Ein Pflicht-Platzhalter fehlt oder wurde verändert.' => 'Mail template not saved: a required placeholder is missing or was changed.',

        // Admin headings / labels
        'Einstellungen' => 'Settings',
        'Verbindung' => 'Connection',
        'Branding / Anzeigename' => 'Branding / display name',
        'Sprache / Language' => 'Language',
        'Legt die Sprache für sichtbare App-Texte fest. Bei „Automatisch“ wird die Browser-Sprache genutzt. Mail-Vorlagen bleiben separat editierbar.' => 'Sets the language for visible app texts. When set to “Auto”, the browser language is used. Mail templates remain separately editable.',
        'LLDAP GraphQL URL' => 'LLDAP GraphQL URL',
        'LLDAP Admin User' => 'LLDAP admin user',
        'LLDAP Admin-Passwort' => 'LLDAP admin password',
        'Status:' => 'Status:',
        'gespeichert' => 'saved',
        'nicht gesetzt' => 'not set',
        'Leer lassen, um den bestehenden Wert beizubehalten.' => 'Leave empty to keep the existing value.',
        'Bridge & Weiterleitung' => 'Bridge & redirects',
        'Bridge URL' => 'Bridge URL',
        'Bridge-Secret' => 'Bridge secret',
        'Login URL' => 'Login URL',
        'Redirect nach erfolgreicher Registrierung' => 'Redirect after successful registration',
        'Redirect nach erfolgreichem Passwortreset' => 'Redirect after successful password reset',
        'Leer = Login URL' => 'Empty = login URL',
        'Gruppen' => 'Groups',
        'Pending-Gruppe' => 'Pending group',
        'Blacklist-Gruppe' => 'Blacklist group',
        'Bitte Pending- und Blacklist-Gruppe auswählen und speichern.' => 'Please select and save the pending and blacklist groups.',
        'Geschützte Gruppen-Namen' => 'Protected group names',
        'Geschützte Gruppen-Präfixe' => 'Protected group prefixes',
        'Geschützte Benutzer-IDs' => 'Protected user IDs',
        'Kommagetrennt. Diese Gruppen werden nicht zur Auswahl angezeigt.' => 'Comma-separated. These groups are hidden from selection.',
        'Kommagetrennt. Gruppen mit diesen Präfixen werden ausgeblendet.' => 'Comma-separated. Groups with these prefixes are hidden.',
        'Kommagetrennt. Diese Benutzer werden unter „Benutzer & Berechtigungen“ nicht angezeigt.' => 'Comma-separated. These users are hidden from “Users & permissions”.',
        'Mail-Vorlagen' => 'Mail templates',
        'Platzhalter:' => 'Placeholders:',
        'Pflicht-Platzhalter bitte nicht entfernen oder verändern. Beim Speichern werden sie geprüft.' => 'Please do not remove or change required placeholders. They are checked when saving.',
        'Betreff: Konto bestätigen' => 'Subject: confirm account',
        'Text: Konto bestätigen' => 'Body: confirm account',
        'Betreff: Konto freigegeben' => 'Subject: account approved',
        'Text: Konto freigegeben' => 'Body: account approved',
        'Betreff: Registrierung abgelehnt' => 'Subject: registration rejected',
        'Text: Registrierung abgelehnt' => 'Body: registration rejected',
        'Betreff: Passwort zurücksetzen' => 'Subject: password reset',
        'Text: Passwort zurücksetzen' => 'Body: password reset',
        'Einstellungen speichern' => 'Save settings',

        // Admin user sections
        'Ausstehende Registrierungen' => 'Pending registrations',
        'Keine ausstehenden Registrierungen.' => 'No pending registrations.',
        'Freigeben' => 'Approve',
        'Blacklist' => 'Blacklist',
        'Benutzer & Berechtigungen' => 'Users & permissions',
        'Benutzer suchen...' => 'Search users...',
        'Berechtigungen:' => 'Permissions:',
        'Berechtigungen speichern' => 'Save permissions',

        // Registration / verification
        'Bestätigungscode eingeben' => 'Enter confirmation code',
        'Bestätigungscode' => 'Confirmation code',
        'Konto erstellen' => 'Create account',
        'Anmeldename' => 'Username',
        'Anzeigename' => 'Display name',
        'Telefonnummer' => 'Phone number',
        'Passwort' => 'Password',
        'Passwort wiederholen' => 'Repeat password',
        'Registrierung absenden' => 'Submit registration',
        'Wir haben Ihnen einen Bestätigungscode gesendet.' => 'We have sent you a confirmation code.',
        'Ungültiger oder abgelaufener Code.' => 'Invalid or expired code.',
        'Ungültiger Code' => 'Invalid code',

        // Password reset
        'Passwort zurücksetzen' => 'Reset password',
        'Neues Passwort setzen' => 'Set new password',
        'Neues Passwort' => 'New password',
        'Passwort ändern' => 'Change password',
        'Passwort geändert' => 'Password changed',
        'Ihr Passwort wurde erfolgreich aktualisiert. Sie können sich nun mit dem neuen Passwort anmelden.' => 'Your password has been updated successfully. You can now log in with the new password.',
        'Geschafft' => 'Done',
        'Ihr Passwort wurde erfolgreich geändert.' => 'Your password has been changed successfully.',

        // Success pages
        'Antrag gespeichert' => 'Request saved',
        'Ihr Registrierungsantrag wurde gespeichert und wartet auf Freigabe.' => 'Your registration request has been saved and is waiting for approval.',
        'Registrierungsantrag gespeichert und wartet auf Freigabe.' => 'Registration request saved and waiting for approval.',

        // Password rules
        'Mindestens 12 Zeichen' => 'At least 12 characters',
        'Mindestens ein Grossbuchstabe' => 'At least one uppercase letter',
        'Mindestens ein Kleinbuchstabe' => 'At least one lowercase letter',
        'Mindestens eine Zahl' => 'At least one number',
        'Mindestens ein Sonderzeichen' => 'At least one special character',
        'Passwoerter stimmen ueberein' => 'Passwords match',

        // Additional public/admin UI strings
        'Geheimnisse' => 'Secrets',
        'Leer lassen = unverändert' => 'Leave empty = unchanged',
        'Geben Sie zuerst Ihre E-Mail-Adresse ein.' => 'Enter your email address first.',
        'E-Mail-Adresse' => 'Email address',
        'E-Mail' => 'Email',
        'Confirmation code anfordern' => 'Request confirmation code',
        'Bestätigungscode anfordern' => 'Request confirmation code',
        'Code senden' => 'Send code',
        'Zurück zur Anmeldung' => 'Back to login',
        'Zurück zur Anmeldung' => 'Back to login',
        'Passwort vergessen' => 'Forgot password',
        'Gib hier deine E-Mail-Adresse ein.' => 'Enter your email address here.',
        'Wir senden dir einen Bestätigungscode zum Zurücksetzen deines Passworts.' => 'We will send you a confirmation code to reset your password.',
        'Bestätigungscode senden' => 'Send confirmation code',
        'Bestätigungscode eingeben' => 'Enter confirmation code',
        'Code eingeben' => 'Enter code',
        'Konto erstellen' => 'Create account',
        'Anmeldename' => 'Username',
        'Name' => 'Name',
        'Anzeigename' => 'Display name',
        'E-Mail-Adresse' => 'Email address',
        'Telefonnummer' => 'Phone number',
        'Passwort anzeigen' => 'Show password',
        'Passwort verbergen' => 'Hide password',
        'Antrag absenden' => 'Submit request',
        'Registrieren' => 'Register',
        'Ihre Registrierung wurde gespeichert und wartet auf Freigabe.' => 'Your registration has been saved and is waiting for approval.',
        'Ihre Anfrage wurde gespeichert.' => 'Your request has been saved.',
        'Bitte prüfen Sie Ihr Postfach.' => 'Please check your inbox.',
        'Wir haben Ihnen eine E-Mail gesendet.' => 'We have sent you an email.',

        // Email domain rules
        'Erlaubte E-Mail-Domains' => 'Allowed email domains',
        'Gesperrte E-Mail-Domains' => 'Denied email domains',
        'Leer = alle Domains erlaubt. Kommagetrennt. Beispiel: example.com,*.example.org' => 'Empty = all domains allowed. Comma-separated. Example: example.com,*.example.org',
        'Kommagetrennt. Gesperrte Domains haben immer Vorrang vor erlaubten Domains.' => 'Comma-separated. Denied domains always override allowed domains.',
        'Registrierung mit dieser E-Mail-Domain ist nicht erlaubt.' => 'Registration with this email domain is not allowed.',

        // Setup status
        'Setup-Status' => 'Setup status',
        'Diese Übersicht zeigt, ob die wichtigsten Einstellungen gesetzt sind. Über die Buttons unten kannst du LLDAP, Bridge und Mailversand testen.' => 'This overview shows whether the most important settings are configured. Use the buttons below to test LLDAP, the bridge, and mail delivery.',
        'Branding gesetzt' => 'Branding configured',
        'LLDAP GraphQL URL gesetzt' => 'LLDAP GraphQL URL configured',
        'LLDAP Admin User gesetzt' => 'LLDAP admin user configured',
        'LLDAP Admin-Passwort gespeichert' => 'LLDAP admin password saved',
        'Pending-Gruppe ausgewählt' => 'Pending group selected',
        'Blacklist-Gruppe ausgewählt' => 'Blacklist group selected',
        'Bridge URL gesetzt' => 'Bridge URL configured',
        'Bridge Secret gespeichert' => 'Bridge secret saved',
        'Login URL gesetzt' => 'Login URL configured',
        'Mail-Vorlagen vollständig' => 'Mail templates complete',

        // Admin test buttons
        'LLDAP testen' => 'Test LLDAP',
        'Bridge testen' => 'Test bridge',
        'Test-Mail-Empfänger' => 'Test mail recipient',
        'Test-Mail senden' => 'Send test mail',
        'LLDAP-Verbindung erfolgreich getestet.' => 'LLDAP connection tested successfully.',
        'LLDAP-Test fehlgeschlagen. Bitte Logs prüfen.' => 'LLDAP test failed. Please check the logs.',
        'Bridge erfolgreich getestet.' => 'Bridge tested successfully.',
        'Bridge-Test fehlgeschlagen. Bitte Logs prüfen.' => 'Bridge test failed. Please check the logs.',
        'Test-Mail wurde gesendet.' => 'Test mail has been sent.',
        'Test-Mail konnte nicht gesendet werden. Bitte Logs prüfen.' => 'Test mail could not be sent. Please check the logs.',
        'Bitte eine gültige Test-Mail-Adresse eingeben.' => 'Please enter a valid test email address.',

        // Rate limits
        'Rate-Limit aktiviert' => 'Rate limit enabled',
        'Rate-Limit' => 'Rate limit',
        'Rate-Limit aktivieren' => 'Enable rate limit',
        'Cooldown in Sekunden' => 'Cooldown in seconds',
        'Zeitfenster in Minuten' => 'Time window in minutes',
        'Maximale Anfragen pro Zeitfenster' => 'Maximum requests per time window',
        'Gilt für Registrierungscode, Code erneut senden, Passwortreset und Passwortreset erneut senden.' => 'Applies to registration codes, resending codes, password resets, and resending password reset codes.',
        'Bitte warten Sie ' => 'Please wait ',
        ' Sekunden, bevor Sie erneut einen Code anfordern.' => ' seconds before requesting another code.',
        'Zu viele Anfragen. Bitte versuchen Sie es später erneut.' => 'Too many requests. Please try again later.',

        // Default approval groups
        'Standardgruppen bei Freigabe' => 'Default approval groups',
        'Diese Gruppen sind bei ausstehenden Registrierungen automatisch vorausgewählt. Sie können pro Benutzer weiterhin angepasst werden.' => 'These groups are selected by default for pending registrations. They can still be changed per user.',

        // Password policy
        'Passwortregeln' => 'Password rules',
        'Mindestlänge' => 'Minimum length',
        'Großbuchstabe erforderlich' => 'Uppercase letter required',
        'Kleinbuchstabe erforderlich' => 'Lowercase letter required',
        'Zahl erforderlich' => 'Number required',
        'Sonderzeichen erforderlich' => 'Special character required',
        'HaveIBeenPwned-Prüfung aktivieren' => 'Enable HaveIBeenPwned check',
        'Die HaveIBeenPwned-Prüfung nutzt das k-Anonymity-Verfahren und sendet nicht das vollständige Passwort.' => 'The HaveIBeenPwned check uses the k-anonymity model and does not send the full password.',
        'Passwort zu schwach. Erforderlich: ' => 'Password too weak. Required: ',
        'mindestens ' => 'at least ',
        ' Zeichen' => ' characters',
        'mindestens ein Großbuchstabe' => 'at least one uppercase letter',
        'mindestens ein Kleinbuchstabe' => 'at least one lowercase letter',
        'mindestens eine Zahl' => 'at least one number',
        'mindestens ein Sonderzeichen' => 'at least one special character',

        // Dynamic password rule text
        'Mindestens ein Grossbuchstabe' => 'At least one uppercase letter',
        'Passwoerter stimmen ueberein' => 'Passwords match',

        // Audit tab
        'Audit-Log' => 'Audit log',
        'Die letzten 100 Audit-Ereignisse. E-Mail-Adressen werden nur als Hash und Domain gespeichert.' => 'The last 100 audit events. Email addresses are stored only as a hash and domain.',
        'Audit-Log leeren' => 'Clear audit log',
        'Noch keine Audit-Einträge.' => 'No audit entries yet.',
        'Zeit' => 'Time',
        'Aktion' => 'Action',
        'Details' => 'Details',
        'Audit-Log wurde geleert.' => 'Audit log has been cleared.',

        // Audit retention
        'Audit-Ereignisse werden nach den unten gesetzten Grenzen gespeichert. E-Mail-Adressen werden nur als Hash und Domain gespeichert.' => 'Audit events are stored according to the limits below. Email addresses are stored only as a hash and domain.',
        'Audit-Ereignisse werden nach den eingestellten Grenzen gespeichert. E-Mail-Adressen werden nur als Hash und Domain gespeichert.' => 'Audit events are stored according to the configured limits. Email addresses are stored only as a hash and domain.',
        'Audit-Aufbewahrung' => 'Audit retention',
        'Maximale Audit-Einträge' => 'Maximum audit entries',
        'Audit-Aufbewahrung in Tagen' => 'Audit retention in days',
        '0 Tage = keine zeitbasierte Löschung. Die maximale Anzahl begrenzt trotzdem die gespeicherten Einträge.' => '0 days = no time-based deletion. The maximum number still limits stored entries.',
        'Audit-Einstellungen speichern' => 'Save audit settings',

        // Audit settings save
        'Audit-Einstellungen gespeichert.' => 'Audit settings saved.',
    ];


    $map = array_merge($map, [
        // v0.2.3 admin messages
        'Bitte wählen Sie mindestens eine freigegebene Zielgruppe aus. Der Benutzer bleibt in Pending.' => 'Please select at least one approved target group. The user remains pending.',
        'Benutzer konnte nicht gelöscht werden. Bitte Logs prüfen.' => 'The user could not be deleted. Please check the logs.',
        'Geschützter Benutzer wurde nicht gelöscht.' => 'Protected user was not deleted.',
        'Benutzer wurde aus LLDAP gelöscht.' => 'User was deleted from LLDAP.',

        // v0.2.3 access modes
        'Zugriff' => 'Access',
        'Registrierung' => 'Registration',
        'Öffentlich' => 'Public',
        'Nur lokale/LAN-Netzwerke' => 'Local/LAN networks only',
        'Deaktiviert' => 'Disabled',
        'Erlaubte Netzwerke für Registrierung' => 'Allowed networks for registration',
        'Erlaubte Netzwerke für Passwortreset' => 'Allowed networks for password reset',
        'CIDR-Liste, getrennt durch Komma, Leerzeichen oder neue Zeilen. Wird nur bei „Nur lokale/LAN-Netzwerke“ verwendet.' => 'CIDR list, separated by commas, spaces, or new lines. Only used for “Local/LAN networks only”.',
        'Öffentlich erlaubt Registrierungen aus dem Internet. Lokal/LAN erlaubt nur Clients aus den unten konfigurierten Netzwerken. Deaktiviert blendet die Registrierung aus und blockiert neue Registrierungsaktionen.' => 'Public allows registrations from the internet. Local/LAN allows only clients from the configured networks below. Disabled hides registration and blocks new registration actions.',
        'Standard ist öffentlich, damit Benutzer ihr Passwort auch außerhalb des LAN zurücksetzen können. Optional kann der Flow auf lokale/LAN-Netzwerke begrenzt oder deaktiviert werden.' => 'The default is public so users can reset their password outside the LAN. Optionally, the flow can be limited to local/LAN networks or disabled.',
        'Die Registrierung ist aktuell nicht von diesem Netzwerk aus verfügbar.' => 'Registration is currently not available from this network.',
        'Der Passwortreset ist aktuell nicht von diesem Netzwerk aus verfügbar.' => 'Password reset is currently not available from this network.',

        // Public registration flow
        'Vollständiger Name' => 'Full name',
        'Bereits registriert' => 'Already registered',
        'Es scheint so, als wären Sie bereits bei uns registriert.' => 'It looks like you are already registered.',
        'Zum Login' => 'To login',
        'Bestätigung fehlgeschlagen' => 'Verification failed',
        'Code erneut eingeben' => 'Enter code again',
        'Neue E-Mail anfordern' => 'Request a new email',
        'Ein Fehler ist aufgetreten.' => 'An error occurred.',
        'Ausstehende Registrierungen' => 'Pending registrations',
        'Hier erscheinen später alle pending-users.' => 'Pending users will appear here later.',

        // Password reset flow
        'Gib hier deine E-Mail-Adresse ein.' => 'Enter your email address here.',
        'Wir senden dir einen Bestätigungscode zum Zurücksetzen deines Passworts.' => 'We will send you a confirmation code to reset your password.',
        'Bestätigungscode senden' => 'Send confirmation code',
        'Code erneut senden' => 'Resend code',
        'Keinen Code erhalten?' => 'Did not receive a code?',
        'Passwort zurücksetzen' => 'Reset password',
        'Neues Passwort setzen' => 'Set new password',
        'Neues Passwort' => 'New password',
        'Passwort wiederholen' => 'Repeat password',
        'Passwort ändern' => 'Change password',
        'Passwort geändert' => 'Password changed',
        'Ihr Passwort wurde erfolgreich aktualisiert. Sie können sich nun mit dem neuen Passwort anmelden.' => 'Your password has been updated successfully. You can now log in with the new password.',

        // Password rules exact German spellings
        'Mindestens ein Großbuchstabe' => 'At least one uppercase letter',
        'Passwörter stimmen überein' => 'Passwords match',

        // Admin tabs and sections
        'Setup' => 'Setup',
        'Benutzer' => 'Users',
        'Audit' => 'Audit',
        'System' => 'System',
        'Freigaben' => 'Approvals',
        'Allgemein' => 'General',
        'Domains' => 'Domains',
        'Sicherheit' => 'Security',
        'Benutzer löschen' => 'Delete user',
        'Löschen' => 'Delete',
        'Schließen' => 'Close',
        'Details anzeigen' => 'Show details',
        'Gruppen speichern' => 'Save groups',

        // Direct LDAP / bridge wording
        'Passwort-Writer' => 'Password writer',
        'Direct LDAP' => 'Direct LDAP',
        'Direct LDAP mit Bridge-Fallback' => 'Direct LDAP with bridge fallback',
        'Legacy Bridge' => 'Legacy bridge',
        'LLDAP LDAP URL' => 'LLDAP LDAP URL',
        'LLDAP Base DN' => 'LLDAP base DN',
        'LLDAP User-DN Template' => 'LLDAP user DN template',
        'PHP LDAP Modul verfügbar' => 'PHP LDAP module available',
        'LDAP Passwortänderung verfügbar' => 'LDAP password change available',
        'LLDAP LDAP URL gesetzt' => 'LLDAP LDAP URL configured',
        'LLDAP Base DN gesetzt' => 'LLDAP base DN configured',
        'LLDAP User-DN Template gesetzt' => 'LLDAP user DN template configured',

        // Mail template admin wording
        'Konto bestätigen' => 'Confirm account',
        'Konto freigegeben' => 'Account approved',
        'Registrierung abgelehnt' => 'Registration rejected',
        'Passwort zurücksetzen' => 'Reset password',
        'Betreff' => 'Subject',
        'Text' => 'Body',

        // Audit/admin table labels
        'Benutzer-ID' => 'User ID',
        'Anzeigename' => 'Display name',
        'E-Mail' => 'Email',
        'Gruppen' => 'Groups',
        'Status' => 'Status',
        'Aktionen' => 'Actions',
        'Domain' => 'Domain',
        'Hash' => 'Hash',
        'Erstellt' => 'Created',
        'Aktualisiert' => 'Updated',
    ]);

    $out = strtr($out, $map);
}

echo $out;
