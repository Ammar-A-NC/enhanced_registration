<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Settings;

use OCA\EnhancedRegistration\Service\LldapService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Admin implements ISettings {
    public function __construct(
        private LldapService $lldapService,
        private IConfig $config
    ) {}

    private function safeLldapData(): array {
        $lldapUrl = trim($this->config->getAppValue('enhanced_registration', 'lldap_url', ''));
        $lldapAdminUser = trim($this->config->getAppValue('enhanced_registration', 'lldap_admin_user', ''));
        $lldapAdminPassword = trim($this->config->getAppValue('enhanced_registration', 'lldap_admin_password', ''));

        $pendingGroupId = trim($this->config->getAppValue('enhanced_registration', 'lldap_pending_group_id', ''));
        $blacklistGroupId = trim($this->config->getAppValue('enhanced_registration', 'lldap_blacklist_group_id', ''));

        $data = [
            'pendingUsers' => [],
            'groups' => [],
            'users' => [],
            'lldap_load_error' => '',
        ];

        if ($lldapUrl === '' || $lldapAdminUser === '' || $lldapAdminPassword === '') {
            $data['lldap_load_error'] = 'LLDAP ist noch nicht vollständig konfiguriert. Bitte Verbindungseinstellungen speichern.';
            return $data;
        }

        try {
            $data['groups'] = $this->lldapService->getGroups();
        } catch (\Throwable $e) {
            $data['lldap_load_error'] = 'LLDAP-Gruppen konnten nicht geladen werden: ' . $e->getMessage();
            return $data;
        }

        try {
            $data['users'] = $this->lldapService->getUsersWithGroups();
        } catch (\Throwable $e) {
            if ($data['lldap_load_error'] === '') {
                $data['lldap_load_error'] = 'LLDAP-Benutzer konnten nicht geladen werden: ' . $e->getMessage();
            }
        }

        if ($pendingGroupId === '' || $blacklistGroupId === '') {
            if ($data['lldap_load_error'] === '') {
                $data['lldap_load_error'] = 'Pending- und Blacklist-Gruppe sind noch nicht konfiguriert.';
            }
            return $data;
        }

        try {
            $data['pendingUsers'] = $this->lldapService->getPendingUsers();
        } catch (\Throwable $e) {
            if ($data['lldap_load_error'] === '') {
                $data['lldap_load_error'] = 'Ausstehende Registrierungen konnten nicht geladen werden: ' . $e->getMessage();
            }
        }

        return $data;
    }

    public function getForm(): TemplateResponse {
        $lldapData = $this->safeLldapData();
        return new TemplateResponse(
            'enhanced_registration',
            'admin',
            [
                'pendingUsers' => $lldapData['pendingUsers'],
                'groups' => $lldapData['groups'],
                'users' => $lldapData['users'],
                    'lldap_load_error' => $lldapData['lldap_load_error'],
                'settings' => [
                    'lldap_url' => $this->config->getAppValue('enhanced_registration', 'lldap_url', ''),
                    'lldap_admin_user' => $this->config->getAppValue('enhanced_registration', 'lldap_admin_user', ''),
                    'lldap_pending_group_id' => $this->config->getAppValue('enhanced_registration', 'lldap_pending_group_id', ''),
                    'lldap_blacklist_group_id' => $this->config->getAppValue('enhanced_registration', 'lldap_blacklist_group_id', ''),
                    'rejection_action' => $this->config->getAppValue('enhanced_registration', 'rejection_action', 'blacklist'),
                    'password_writer' => $this->config->getAppValue('enhanced_registration', 'password_writer', 'direct_ldap'),
                    'lldap_ldap_url' => $this->config->getAppValue('enhanced_registration', 'lldap_ldap_url', ''),
                    'lldap_base_dn' => $this->config->getAppValue('enhanced_registration', 'lldap_base_dn', ''),
                    'lldap_admin_dn' => $this->config->getAppValue('enhanced_registration', 'lldap_admin_dn', ''),
                    'lldap_user_dn_template' => $this->config->getAppValue('enhanced_registration', 'lldap_user_dn_template', 'uid={uid},ou=people,{base}'),
                    'has_php_ldap' => function_exists('ldap_connect') ? '1' : '0',
                    'has_ldap_exop_passwd' => function_exists('ldap_exop_passwd') ? '1' : '0',
                    'bridge_url' => $this->config->getAppValue('enhanced_registration', 'bridge_url', ''),
                    'login_url' => $this->config->getAppValue('enhanced_registration', 'login_url', '/login'),
                    'registration_success_redirect_url' => $this->config->getAppValue('enhanced_registration', 'registration_success_redirect_url', ''),
                    'password_reset_success_redirect_url' => $this->config->getAppValue('enhanced_registration', 'password_reset_success_redirect_url', ''),
                    'brand_name' => $this->config->getAppValue('enhanced_registration', 'brand_name', 'Enhanced Registration'),
                    'ui_language' => $this->config->getAppValue('enhanced_registration', 'ui_language', 'auto'),
                    'protected_group_names' => $this->config->getAppValue('enhanced_registration', 'protected_group_names', 'pending-users,blacklist'),
                    'protected_group_prefixes' => $this->config->getAppValue('enhanced_registration', 'protected_group_prefixes', 'lldap_'),
                    'protected_user_ids' => $this->config->getAppValue('enhanced_registration', 'protected_user_ids', 'admin'),
                    'default_approval_group_ids' => $this->config->getAppValue('enhanced_registration', 'default_approval_group_ids', ''),
                    'store_user_email_in_ldap' => $this->config->getAppValue('enhanced_registration', 'store_user_email_in_ldap', '1'),
                    'allowed_email_domains' => $this->config->getAppValue('enhanced_registration', 'allowed_email_domains', ''),
                    'denied_email_domains' => $this->config->getAppValue('enhanced_registration', 'denied_email_domains', ''),
                    'registration_access_mode' => $this->config->getAppValue('enhanced_registration', 'registration_access_mode', 'public'),
                    'registration_allowed_networks' => $this->config->getAppValue('enhanced_registration', 'registration_allowed_networks', '127.0.0.1/32,::1/128,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,169.254.0.0/16,fc00::/7,fe80::/10'),
                    'password_reset_access_mode' => $this->config->getAppValue('enhanced_registration', 'password_reset_access_mode', 'public'),
                    'password_reset_allowed_networks' => $this->config->getAppValue('enhanced_registration', 'password_reset_allowed_networks', '127.0.0.1/32,::1/128,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,169.254.0.0/16,fc00::/7,fe80::/10'),
                    'test_mail_recipient' => $this->config->getAppValue('enhanced_registration', 'test_mail_recipient', ''),
                    'audit_events' => $this->config->getAppValue('enhanced_registration', 'audit_events', '[]'),
                    'audit_max_events' => $this->config->getAppValue('enhanced_registration', 'audit_max_events', '100'),
                    'audit_retention_days' => $this->config->getAppValue('enhanced_registration', 'audit_retention_days', '90'),
                    'rate_limit_enabled' => $this->config->getAppValue('enhanced_registration', 'rate_limit_enabled', '1'),
                    'rate_limit_cooldown_seconds' => $this->config->getAppValue('enhanced_registration', 'rate_limit_cooldown_seconds', '60'),
                    'rate_limit_window_minutes' => $this->config->getAppValue('enhanced_registration', 'rate_limit_window_minutes', '15'),
                    'rate_limit_max_attempts' => $this->config->getAppValue('enhanced_registration', 'rate_limit_max_attempts', '5'),
                    'password_min_length' => $this->config->getAppValue('enhanced_registration', 'password_min_length', '12'),
                    'password_require_uppercase' => $this->config->getAppValue('enhanced_registration', 'password_require_uppercase', '1'),
                    'password_require_lowercase' => $this->config->getAppValue('enhanced_registration', 'password_require_lowercase', '1'),
                    'password_require_number' => $this->config->getAppValue('enhanced_registration', 'password_require_number', '1'),
                    'password_require_special' => $this->config->getAppValue('enhanced_registration', 'password_require_special', '1'),
                    'password_hibp_enabled' => $this->config->getAppValue('enhanced_registration', 'password_hibp_enabled', '1'),
                    'mail_confirm_subject' => $this->config->getAppValue('enhanced_registration', 'mail_confirm_subject', '{brand}: E-Mail bestätigen'),
                    'mail_confirm_body' => $this->config->getAppValue('enhanced_registration', 'mail_confirm_body', "Hallo,\n\nbitte bestätigen Sie Ihre Registrierung.\n\nBestätigungscode: {code}\n\nAlternativ können Sie diesen Link öffnen:\n{link}\n\nWenn Sie diese Anfrage nicht gestellt haben, können Sie diese E-Mail ignorieren."),
                    'mail_approved_subject' => $this->config->getAppValue('enhanced_registration', 'mail_approved_subject', '{brand}: Konto freigegeben'),
                    'mail_approved_body' => $this->config->getAppValue('enhanced_registration', 'mail_approved_body', "Hallo {displayName},\n\nIhr Konto wurde freigegeben.\n\nZugewiesene Gruppen: {groups}\n\nAnmeldung: {loginUrl}"),
                    'mail_rejected_subject' => $this->config->getAppValue('enhanced_registration', 'mail_rejected_subject', '{brand}: Registrierung abgelehnt'),
                    'mail_rejected_body' => $this->config->getAppValue('enhanced_registration', 'mail_rejected_body', "Hallo {displayName},\n\nIhre Registrierung wurde abgelehnt.\n\nBei Fragen wenden Sie sich bitte an einen Administrator."),
                    'mail_password_reset_subject' => $this->config->getAppValue('enhanced_registration', 'mail_password_reset_subject', '{brand}: Passwort zurücksetzen'),
                    'mail_password_reset_body' => $this->config->getAppValue('enhanced_registration', 'mail_password_reset_body', "Hallo,\n\nfür Ihr Konto wurde ein Passwortreset angefordert.\n\nBestätigungscode: {code}\n\nAlternativ können Sie diesen Link öffnen:\n{link}\n\nDer Code und Link sind 10 Minuten gültig und nur einmal verwendbar.\n\nWenn Sie diese Anfrage nicht gestellt haben, können Sie diese E-Mail ignorieren."),
                    'has_lldap_admin_password' => $this->config->getAppValue('enhanced_registration', 'lldap_admin_password', '') !== '' ? '1' : '0',
                    'has_bridge_secret' => $this->config->getAppValue('enhanced_registration', 'bridge_secret', '') !== '' ? '1' : '0',
                ],
            ],
            ''
        );
    }

    public function getSection(): string {
        return 'enhanced_registration';
    }

    public function getPriority(): int {
        return 50;
    }
}
