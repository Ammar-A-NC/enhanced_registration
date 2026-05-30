<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Controller;

use OCA\EnhancedRegistration\Service\RegistrationService;
use OCA\EnhancedRegistration\Service\LldapService;
use OCA\EnhancedRegistration\Service\PasswordResetService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\IRequest;
use OCP\Mail\IMailer;
use OCP\IURLGenerator;
use OCP\IConfig;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;

class RegisterController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        private LldapService $lldapService,
        private RegistrationService $registrationService,
        private PasswordResetService $passwordResetService,
        private IMailer $mailer,
        private IURLGenerator $urlGenerator,
        private IConfig $config,
        private IDBConnection $db,
        private LoggerInterface $logger
    ) {
        parent::__construct($appName, $request);
    }

    private function noStore(): void {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
    }

    private function passwordPolicyTemplateParams(): array {
        return [
            'password_min_length' => $this->passwordMinLength(),
            'password_require_uppercase' => $this->passwordPolicyEnabled('password_require_uppercase') ? '1' : '0',
            'password_require_lowercase' => $this->passwordPolicyEnabled('password_require_lowercase') ? '1' : '0',
            'password_require_number' => $this->passwordPolicyEnabled('password_require_number') ? '1' : '0',
            'password_require_special' => $this->passwordPolicyEnabled('password_require_special') ? '1' : '0',
        ];
    }

    private function noStoreTemplate(string $template, array $params = []): TemplateResponse {
        $this->noStore();

        if (!isset($params['ui_language'])) {
            $params['ui_language'] = $this->config->getAppValue('enhanced_registration', 'ui_language', 'auto');
        }

        if (!isset($params['brand_name'])) {
            $params['brand_name'] = $this->brandName();
        }

        $params = array_merge($this->passwordPolicyTemplateParams(), $params);

        return new TemplateResponse("enhanced_registration", $template, $params, "guest");
    }

    private function safeRedirectUrl(string $url, string $fallback = '/login'): string {
        $url = trim($url);
        $fallback = trim($fallback) !== '' ? trim($fallback) : '/login';

        if ($url === '') {
            return $fallback;
        }

        if (str_contains($url, "\n") || str_contains($url, "\r")) {
            return $fallback;
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        $host = strtolower((string)($parts['host'] ?? ''));

        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            return $fallback;
        }

        $serverHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        $serverHost = explode(':', $serverHost)[0];

        if ($serverHost !== '' && $host === $serverHost) {
            return $url;
        }

        return $fallback;
    }

    private function brandName(): string {
        return trim($this->config->getAppValue('enhanced_registration', 'brand_name', 'Enhanced Registration')) ?: 'Enhanced Registration';
    }

    private function mailTemplate(string $key, string $default): string {
        $value = $this->config->getAppValue('enhanced_registration', $key, '');

        if (trim($value) === '') {
            return $default;
        }

        return $value;
    }

    private function auditHash(string $value): string {
        return substr(hash('sha256', strtolower(trim($value))), 0, 16);
    }

    private function auditEmailContext(string $email): array {
        $email = strtolower(trim($email));
        $domain = '';

        if (strpos($email, '@') !== false) {
            $domain = substr(strrchr($email, '@') ?: '', 1);
        }

        return [
            'email_hash' => $this->auditHash($email),
            'email_domain' => $domain,
        ];
    }

    private function audit(string $action, array $context = []): void {
        foreach ([
            'password',
            'password_confirm',
            'token',
            'secret',
            'bridge_secret',
            'lldap_admin_password',
            'code',
        ] as $sensitiveKey) {
            unset($context[$sensitiveKey]);
        }

        $this->logger->warning($this->brandName() . ': audit: ' . $action, array_merge([
            'audit' => true,
            'action' => $action,
        ], $context));

        try {
            $raw = $this->config->getAppValue('enhanced_registration', 'audit_events', '[]');
            $events = json_decode($raw, true);

            if (!is_array($events)) {
                $events = [];
            }

            array_unshift($events, [
                'time' => gmdate('c'),
                'action' => $action,
                'context' => $context,
                'remote_hash' => $this->auditHash((string)($this->request->getRemoteAddress() ?? '')),
            ]);

            $maxEvents = (int)$this->config->getAppValue('enhanced_registration', 'audit_max_events', '100');
            if ($maxEvents < 1) {
                $maxEvents = 1;
            }
            if ($maxEvents > 1000) {
                $maxEvents = 1000;
            }

            $retentionDays = (int)$this->config->getAppValue('enhanced_registration', 'audit_retention_days', '90');

            if ($retentionDays > 0) {
                $cutoff = time() - ($retentionDays * 86400);
                $events = array_values(array_filter($events, function ($event) use ($cutoff) {
                    $time = strtotime((string)($event['time'] ?? ''));
                    return $time === false || $time >= $cutoff;
                }));
            }

            $events = array_slice($events, 0, $maxEvents);

            $this->config->setAppValue(
                'enhanced_registration',
                'audit_events',
                json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        } catch (\Throwable $e) {
            $this->logger->warning($this->brandName() . ': audit storage failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function renderMailTemplate(string $template, array $values): string {
        $replacements = [];

        foreach ($values as $key => $value) {
            $replacements['{' . $key . '}'] = (string)$value;
        }

        return strtr($template, $replacements);
    }


    private function sendRegistrationConfirmationMail(string $email, string $manualCode, string $linkToken): void {
        $link = $this->urlGenerator->getAbsoluteURL("/index.php/apps/enhanced_registration/verify?code=" . urlencode($linkToken));

        $message = $this->mailer->createMessage();
        $message->setTo([$email]);
        $message->setSubject($this->renderMailTemplate(
            $this->mailTemplate('mail_confirm_subject', '{brand}: E-Mail bestätigen'),
            [
                'brand' => $this->brandName(),
                'code' => $manualCode,
                'link' => $link,
            ]
        ));
        $message->setPlainBody($this->renderMailTemplate(
            $this->mailTemplate(
                'mail_confirm_body',
                "Hallo,

bitte bestätigen Sie Ihre Registrierung.

Bestätigungscode: {code}

Alternativ können Sie diesen Link öffnen:
{link}

Wenn Sie diese Anfrage nicht gestellt haben, können Sie diese E-Mail ignorieren."
            ),
            [
                'brand' => $this->brandName(),
                'code' => $manualCode,
                'link' => $link,
            ]
        ));
        $this->mailer->send($message);
    }

    private function sendPasswordResetMail(string $email, string $token): void {
        $resetLink = $this->urlGenerator->getAbsoluteURL("/index.php/apps/enhanced_registration/passreset/verify?token=" . urlencode($token));

        $message = $this->mailer->createMessage();
        $message->setTo([$email]);
        $message->setSubject($this->renderMailTemplate(
            $this->mailTemplate('mail_password_reset_subject', '{brand}: Passwort zurücksetzen'),
            [
                'brand' => $this->brandName(),
                'code' => $token,
                'link' => $resetLink,
            ]
        ));
        $message->setPlainBody($this->renderMailTemplate(
            $this->mailTemplate(
                'mail_password_reset_body',
                "Hallo,\n\nfür Ihr Konto wurde ein Passwortreset angefordert.\n\nBestätigungscode: {code}\n\nAlternativ können Sie diesen Link öffnen:\n{link}\n\nDer Code und Link sind 10 Minuten gültig und nur einmal verwendbar.\n\nWenn Sie diese Anfrage nicht gestellt haben, können Sie diese E-Mail ignorieren."
            ),
            [
                'brand' => $this->brandName(),
                'code' => $token,
                'link' => $resetLink,
            ]
        ));
        $this->mailer->send($message);
    }


    private function parseDomainList(string $value): array {
        $items = preg_split('/[\s,;]+/', strtolower($value));
        $items = array_map('trim', $items ?: []);
        $items = array_filter($items, function ($item) {
            return $item !== '';
        });

        return array_values(array_unique($items));
    }

    private function domainMatchesRule(string $domain, string $rule): bool {
        $domain = strtolower(trim($domain));
        $rule = strtolower(trim($rule));

        if ($domain === '' || $rule === '') {
            return false;
        }

        if (str_starts_with($rule, '@')) {
            $rule = substr($rule, 1);
        }

        if (str_starts_with($rule, '*.')) {
            $base = substr($rule, 2);
            return $domain !== $base && str_ends_with($domain, '.' . $base);
        }

        return $domain === $rule;
    }

    private function isEmailDomainAllowed(string $email): bool {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));

        if ($domain === '') {
            return false;
        }

        $denied = $this->parseDomainList(
            $this->config->getAppValue('enhanced_registration', 'denied_email_domains', '')
        );

        foreach ($denied as $rule) {
            if ($this->domainMatchesRule($domain, $rule)) {
                return false;
            }
        }

        $allowed = $this->parseDomainList(
            $this->config->getAppValue('enhanced_registration', 'allowed_email_domains', '')
        );

        if (empty($allowed)) {
            return true;
        }

        foreach ($allowed as $rule) {
            if ($this->domainMatchesRule($domain, $rule)) {
                return true;
            }
        }

        return false;
    }


    private function rateLimitInt(string $key, int $default, int $min, int $max): int {
        $value = (int)$this->config->getAppValue('enhanced_registration', $key, (string)$default);

        if ($value < $min) {
            return $min;
        }

        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    private function checkRateLimitAndRecord(string $action, string $identity): ?string {
        if ($this->config->getAppValue('enhanced_registration', 'rate_limit_enabled', '1') !== '1') {
            return null;
        }

        $action = strtolower(trim($action));
        $identity = strtolower(trim($identity));

        if ($action === '') {
            $action = 'default';
        }

        if ($identity === '') {
            $identity = 'anonymous';
        }

        $cooldown = $this->rateLimitInt('rate_limit_cooldown_seconds', 60, 0, 3600);
        $windowSeconds = $this->rateLimitInt('rate_limit_window_minutes', 15, 1, 1440) * 60;
        $maxAttempts = $this->rateLimitInt('rate_limit_max_attempts', 5, 1, 100);
        $now = time();
        $identityHash = hash('sha256', $identity);

        $cleanupBefore = $now - max($windowSeconds, $cooldown, 60) - 3600;
        $cleanup = $this->db->getQueryBuilder();
        $cleanup->delete('enhanced_rate_limits')
            ->where($cleanup->expr()->lt('updated_at', $cleanup->createNamedParameter($cleanupBefore)))
            ->executeStatement();

        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('enhanced_rate_limits')
            ->where($query->expr()->eq('action', $query->createNamedParameter($action)))
            ->andWhere($query->expr()->eq('identity_hash', $query->createNamedParameter($identityHash)))
            ->setMaxResults(1);

        $row = $query->executeQuery()->fetchAssociative();

        $id = $row ? (int)$row['id'] : 0;
        $windowStart = $row ? (int)$row['window_start'] : 0;
        $last = $row ? (int)$row['last_attempt'] : 0;
        $count = $row ? (int)$row['attempt_count'] : 0;

        if ($windowStart <= 0 || ($now - $windowStart) >= $windowSeconds) {
            $windowStart = $now;
            $last = 0;
            $count = 0;
        }

        if ($cooldown > 0 && $last > 0 && ($now - $last) < $cooldown) {
            $remaining = $cooldown - ($now - $last);
            return 'Bitte warten Sie ' . $remaining . ' Sekunden, bevor Sie erneut einen Code anfordern.';
        }

        if ($count >= $maxAttempts) {
            return 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.';
        }

        if ($id > 0) {
            $update = $this->db->getQueryBuilder();
            $update->update('enhanced_rate_limits')
                ->set('window_start', $update->createNamedParameter($windowStart))
                ->set('last_attempt', $update->createNamedParameter($now))
                ->set('attempt_count', $update->createNamedParameter($count + 1))
                ->set('updated_at', $update->createNamedParameter($now))
                ->where($update->expr()->eq('id', $update->createNamedParameter($id)))
                ->executeStatement();
        } else {
            $insert = $this->db->getQueryBuilder();
            $insert->insert('enhanced_rate_limits')
                ->values([
                    'action' => $insert->createNamedParameter($action),
                    'identity_hash' => $insert->createNamedParameter($identityHash),
                    'window_start' => $insert->createNamedParameter($windowStart),
                    'last_attempt' => $insert->createNamedParameter($now),
                    'attempt_count' => $insert->createNamedParameter($count + 1),
                    'updated_at' => $insert->createNamedParameter($now),
                ])
                ->executeStatement();
        }

        return null;
    }

    private function clientRateLimitIdentity(): string {
        $remote = (string)($_SERVER['REMOTE_ADDR'] ?? 'anonymous');

        if ($remote === '') {
            return 'anonymous';
        }

        return $remote;
    }


    private function passwordPolicyEnabled(string $key): bool {
        return $this->config->getAppValue('enhanced_registration', $key, '1') === '1';
    }

    private function passwordMinLength(): int {
        $length = (int)$this->config->getAppValue('enhanced_registration', 'password_min_length', '12');

        if ($length < 1) {
            return 1;
        }

        if ($length > 128) {
            return 128;
        }

        return $length;
    }

    private function validatePasswordPolicy(string $password): ?string {
        $requirements = [];
        $minLength = $this->passwordMinLength();

        if (strlen($password) < $minLength) {
            $requirements[] = 'mindestens ' . $minLength . ' Zeichen';
        }

        if ($this->passwordPolicyEnabled('password_require_uppercase') && !preg_match('/[A-Z]/', $password)) {
            $requirements[] = 'mindestens ein Großbuchstabe';
        }

        if ($this->passwordPolicyEnabled('password_require_lowercase') && !preg_match('/[a-z]/', $password)) {
            $requirements[] = 'mindestens ein Kleinbuchstabe';
        }

        if ($this->passwordPolicyEnabled('password_require_number') && !preg_match('/[0-9]/', $password)) {
            $requirements[] = 'mindestens eine Zahl';
        }

        if ($this->passwordPolicyEnabled('password_require_special') && !preg_match('/[\W_]/', $password)) {
            $requirements[] = 'mindestens ein Sonderzeichen';
        }

        if (!empty($requirements)) {
            return 'Passwort zu schwach. Erforderlich: ' . implode(', ', $requirements) . '.';
        }

        if ($this->passwordPolicyEnabled('password_hibp_enabled')) {
            $sha1 = strtoupper(sha1($password));
            $prefix = substr($sha1, 0, 5);
            $suffix = substr($sha1, 5);
            $response = @file_get_contents("https://api.pwnedpasswords.com/range/" . $prefix);

            if ($response !== false && strpos($response, $suffix . ":") !== false) {
                return 'Dieses Passwort wurde bereits in Datenlecks gefunden. Bitte wählen Sie ein anderes Passwort.';
            }
        }

        return null;
    }

    private function validateMailTemplates(): ?string {
        $required = [
            'mail_confirm_subject' => ['{brand}'],
            'mail_confirm_body' => ['{code}', '{link}'],
            'mail_approved_subject' => ['{brand}'],
            'mail_approved_body' => ['{displayName}', '{loginUrl}'],
            'mail_rejected_subject' => ['{brand}'],
            'mail_rejected_body' => ['{displayName}'],
            'mail_password_reset_subject' => ['{brand}'],
            'mail_password_reset_body' => ['{code}', '{link}'],
        ];

        foreach ($required as $field => $placeholders) {
            $value = (string)$this->request->getParam($field, '');

            foreach ($placeholders as $placeholder) {
                if (strpos($value, $placeholder) === false) {
                    return 'mail_template_invalid';
                }
            }
        }

        return null;
    }


    public function index(): TemplateResponse {
        return $this->noStoreTemplate('register');
    }

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function submitEmail(): TemplateResponse|RedirectResponse {
        $email = trim((string)$this->request->getParam("email"));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->noStoreTemplate('register', [
                'email' => $email,
                'message' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.'
            ], 'guest');
        }

        if (!$this->isEmailDomainAllowed($email)) {
            return $this->noStoreTemplate('register', [
                'email' => $email,
                'message' => 'Diese E-Mail-Domain ist für die Registrierung nicht zugelassen.'
            ], 'guest');
        }

        $rateLimitMessage = $this->checkRateLimitAndRecord('registration_request', $email);

        if ($rateLimitMessage !== null) {
            return $this->noStoreTemplate('register', [
                'email' => $email,
                'message' => $rateLimitMessage
            ], 'guest');
        }

        try {
            $registrationTokens = $this->registrationService->createRegistration($email);
            $this->sendRegistrationConfirmationMail(
                $email,
                (string)$registrationTokens['code'],
                (string)$registrationTokens['token']
            );
        } catch (\Throwable $e) {
            $this->logger->warning($this->brandName() . ': registration confirmation failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return new RedirectResponse("/index.php/apps/enhanced_registration/already");
        }

        $this->audit('registration_code_requested', $this->auditEmailContext($email));

        return new RedirectResponse("/index.php/apps/enhanced_registration/checkmail?email=" . urlencode($email));
    }


    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function checkMail(): TemplateResponse {
        return new TemplateResponse('enhanced_registration', 'checkmail', [
            'email' => (string)$this->request->getParam('email')
        ], 'guest');
    }

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function submitCode(): RedirectResponse {
        $code = trim((string)$this->request->getParam('code'));
        return new RedirectResponse('/index.php/apps/enhanced_registration/verify?code=' . urlencode($code));
    }

    /**
 * @PublicPage
 * @NoAdminRequired
 * @NoCSRFRequired
 */

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function resendCode(): TemplateResponse {
        $email = trim((string)$this->request->getParam('email'));

        if ($email === '') {
            return $this->noStoreTemplate('checkmail', [
                'message' => 'Bitte geben Sie Ihre E-Mail-Adresse erneut ein.'
            ], 'guest');
        }

        $rateLimitMessage = $this->checkRateLimitAndRecord('registration_resend', $email);

        if ($rateLimitMessage !== null) {
            return $this->noStoreTemplate('checkmail', [
                'email' => $email,
                'message' => $rateLimitMessage
            ], 'guest');
        }

        try {
            $registrationTokens = $this->registrationService->resendRegistration($email);
            $this->sendRegistrationConfirmationMail(
                $email,
                (string)$registrationTokens['code'],
                (string)$registrationTokens['token']
            );
            $this->audit('registration_code_resent', $this->auditEmailContext($email));
        } catch (\Throwable $e) {
            $this->logger->warning($this->brandName() . ': registration code resend failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return $this->noStoreTemplate('checkmail', [
                'email' => $email,
                'message' => 'Der Bestätigungscode konnte nicht erneut gesendet werden.'
            ], 'guest');
        }

        return $this->noStoreTemplate('checkmail', [
            'email' => $email,
            'message' => 'Bestätigungscode wurde erneut gesendet.'
        ], 'guest');
    }

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function verify(): TemplateResponse {
        $code = trim((string)$this->request->getParam('code'));
        $rateLimitMessage = $this->checkRateLimitAndRecord('registration_verify', $this->clientRateLimitIdentity());

        if ($rateLimitMessage !== null) {
            return $this->noStoreTemplate('error', [
                'message' => $rateLimitMessage
            ], 'guest');
        }

        $registration = $this->registrationService->getRegistrationByToken($code);

        if (!$registration) {
            return $this->noStoreTemplate('error', [
                'message' => 'Ungültiger oder abgelaufener Bestätigungscode.'
            ], 'guest');
        }

        return $this->noStoreTemplate('details', [
            'email' => $registration['email'],
            'token' => $code
        ], 'guest');
    }

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function submitDetails(): TemplateResponse {
        $token = trim((string)$this->request->getParam('token'));
        $rateLimitMessage = $this->checkRateLimitAndRecord('registration_details', $this->clientRateLimitIdentity());

        if ($rateLimitMessage !== null) {
            return $this->noStoreTemplate('error', [
                'message' => $rateLimitMessage
            ], 'guest');
        }

        $registration = $this->registrationService->getRegistrationByToken($token);

        if (!$registration) {
            return $this->noStoreTemplate('error', [
                'message' => 'Ungültiger oder abgelaufener Registrierungscode.'
            ], 'guest');
        }

        $email = (string)$registration['email'];
        $username = trim((string)$this->request->getParam('username'));
        $displayname = trim((string)$this->request->getParam('displayname'));
        $phone = trim((string)$this->request->getParam('phone'));
        $password = (string)$this->request->getParam('password');
        $passwordConfirm = (string)$this->request->getParam('password_confirm');

        if ($password !== $passwordConfirm) {
            return $this->noStoreTemplate('details', [
                'token' => $token,
                'email' => $email,
                'username' => $username,
                'displayname' => $displayname,
                'phone' => $phone,
                'message' => 'Die Passwörter stimmen nicht überein.'
            ], 'guest');
        }

        if (!preg_match('/^[A-Za-z][A-Za-z0-9._-]{2,31}$/', $username)) {
            return $this->noStoreTemplate('details', [
                'email' => $email,
                'token' => $token,
                'username' => $username,
                'displayname' => $displayname,
                'phone' => $phone,
                'message' => 'Ungültiger Anmeldename. Erlaubt sind 3–32 Zeichen: Buchstaben, Zahlen, Punkt, Unterstrich und Bindestrich. Das erste Zeichen muss ein Buchstabe sein.'
            ], 'guest');
        }

        if ($phone !== '' && !preg_match('/^[0-9+()\/ .-]{6,30}$/', $phone)) {
            return $this->noStoreTemplate('details', [
                'email' => $email,
                'token' => $token,
                'username' => $username,
                'displayname' => $displayname,
                'phone' => $phone,
                'message' => 'Ungültige Telefonnummer. Erlaubt sind Zahlen, +, Leerzeichen, -, / und Klammern.'
            ], 'guest');
        }

        $passwordPolicyMessage = $this->validatePasswordPolicy($password);

        if ($passwordPolicyMessage !== null) {
            return $this->noStoreTemplate('details', [
                'email' => $email,
                'token' => $token,
                'username' => $username,
                'displayname' => $displayname,
                'phone' => $phone,
                'message' => $passwordPolicyMessage
            ], 'guest');
        }

        try {
            $this->lldapService->createPendingUser($username, $email, $displayname, $password);
            $this->registrationService->markTokenUsed($token);
            $this->audit('registration_submitted_pending', array_merge([
                'user' => $username,
            ], $this->auditEmailContext($email)));
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $message = 'Account konnte nicht erstellt werden. Bitte versuchen Sie es später erneut.';

            if (stripos($error, 'lowercase_email') !== false || stripos($error, 'email') !== false) {
                $message = 'Diese E-Mail-Adresse ist bereits registriert.';
            } elseif (stripos($error, 'UNIQUE') !== false || stripos($error, 'already exists') !== false || stripos($error, 'duplicate') !== false) {
                $message = 'Dieser Anmeldename ist bereits vergeben.';
            }

            $this->logger->warning($this->brandName() . ': account creation failed', [
                'user' => $username,
                'email' => $email,
                'error' => $error,
            ]);

            return $this->noStoreTemplate('details', [
                'email' => $email,
                'token' => $token,
                'username' => $username,
                'displayname' => $displayname,
                'phone' => $phone,
                'message' => $message
            ], 'guest');
        }

        $loginUrl = $this->safeRedirectUrl(
            $this->config->getAppValue('enhanced_registration', 'login_url', '/login'),
            '/login'
        );

        $redirectUrl = $this->safeRedirectUrl(
            $this->config->getAppValue('enhanced_registration', 'registration_success_redirect_url', ''),
            $loginUrl
        );

        return new TemplateResponse(
            'enhanced_registration',
            'success',
            [
                'message' => 'Registrierungsantrag gespeichert und wartet auf Freigabe.',
                'redirect_url' => $redirectUrl,
                'ui_language' => $this->config->getAppValue('enhanced_registration', 'ui_language', 'auto')
            ],
            'guest'
        );
    }
    public function already(): TemplateResponse {
        return new TemplateResponse("enhanced_registration", "already", [], "guest");
    }

    /**
     * @AdminRequired
     */
    public function saveSettings(): RedirectResponse {
        $saveType = (string)$this->request->getParam("save_type", "");

        if (
            $saveType === "audit_settings" ||
            $this->request->getParam("audit_max_events", null) !== null ||
            $this->request->getParam("audit_retention_days", null) !== null
        ) {
            $maxEvents = (int)$this->request->getParam("audit_max_events", "100");
            $retentionDays = (int)$this->request->getParam("audit_retention_days", "90");

            if ($maxEvents < 1) {
                $maxEvents = 1;
            }

            if ($maxEvents > 1000) {
                $maxEvents = 1000;
            }

            if ($retentionDays < 0) {
                $retentionDays = 0;
            }

            if ($retentionDays > 3650) {
                $retentionDays = 3650;
            }

            $this->config->setAppValue("enhanced_registration", "audit_max_events", (string)$maxEvents);
            $this->config->setAppValue("enhanced_registration", "audit_retention_days", (string)$retentionDays);

            $this->audit("audit_settings_saved", [
                "audit_max_events" => $maxEvents,
                "audit_retention_days" => $retentionDays,
            ]);

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=audit_settings_saved");
        }

        if ($saveType === "clear_audit") {
            $this->config->setAppValue('enhanced_registration', 'audit_events', '[]');
            $this->audit('audit_log_cleared');

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=audit_cleared");
        }

        if ($saveType === "audit_settings") {
            $maxEvents = (int)$this->request->getParam("audit_max_events", "100");
            $retentionDays = (int)$this->request->getParam("audit_retention_days", "90");

            if ($maxEvents < 1) {
                $maxEvents = 1;
            }

            if ($maxEvents > 1000) {
                $maxEvents = 1000;
            }

            if ($retentionDays < 0) {
                $retentionDays = 0;
            }

            if ($retentionDays > 3650) {
                $retentionDays = 3650;
            }

            $this->config->setAppValue("enhanced_registration", "audit_max_events", (string)$maxEvents);
            $this->config->setAppValue("enhanced_registration", "audit_retention_days", (string)$retentionDays);

            $this->audit("audit_settings_saved", [
                "audit_max_events" => $maxEvents,
                "audit_retention_days" => $retentionDays,
            ]);

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=audit_settings_saved");
        }

        if ($saveType === "test_lldap") {
            return $this->testLldap();
        }

        if ($saveType === "test_bridge") {
            return $this->testBridge();
        }

        if ($saveType === "test_mail") {
            return $this->testMail();
        }

        if ($saveType === "user_groups") {
            $userId = trim((string)$this->request->getParam("userId"));
            $groupIds = $this->request->getParam("groupIds", []);

            if (!is_array($groupIds)) {
                $groupIds = [$groupIds];
            }

            $groupIds = array_values(array_unique(array_filter(array_map('intval', $groupIds))));

            if ($userId !== "") {
                $this->lldapService->updateUserGroups($userId, $groupIds);
                $this->logger->info($this->brandName() . ": user groups updated", [
                    "user" => $userId,
                    "groups" => $groupIds,
                ]);
            }

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=user_groups_saved");
        }

        $mailTemplateError = $this->validateMailTemplates();

        if ($mailTemplateError !== null) {
            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=" . $mailTemplateError);
        }

        $keys = [
            "lldap_url",
            "lldap_admin_user",
            "lldap_pending_group_id",
            "lldap_blacklist_group_id",
            "rejection_action",
            "bridge_url",
            "login_url",
            "registration_success_redirect_url",
            "password_reset_success_redirect_url",
            "brand_name",
            "ui_language",
            "mail_confirm_subject",
            "mail_confirm_body",
            "mail_approved_subject",
            "mail_approved_body",
            "mail_rejected_subject",
            "mail_rejected_body",
            "mail_password_reset_subject",
            "mail_password_reset_body",
            "protected_group_names",
            "protected_group_prefixes",
            "protected_user_ids",
            "default_approval_group_ids",
            "store_user_email_in_ldap",
            "allowed_email_domains",
            "denied_email_domains",
            "rate_limit_enabled",
            "rate_limit_cooldown_seconds",
            "rate_limit_window_minutes",
            "rate_limit_max_attempts",
            "password_min_length",
            "password_require_uppercase",
            "password_require_lowercase",
            "password_require_number",
            "password_require_special",
            "password_hibp_enabled",
            "audit_max_events",
            "audit_retention_days",
            "store_user_email_in_ldap",
            "allowed_email_domains",
            "denied_email_domains",
            "test_mail_recipient"
        ];

        $defaultApprovalGroupIdsParam = $this->request->getParam("default_approval_group_ids", "");

        if (is_array($defaultApprovalGroupIdsParam)) {
            $defaultApprovalGroupIdsParam = implode(",", array_values(array_unique(array_filter(array_map("intval", $defaultApprovalGroupIdsParam)))));
        }

        $this->config->setAppValue("enhanced_registration", "default_approval_group_ids", trim((string)$defaultApprovalGroupIdsParam));

        foreach ($keys as $key) {
            if ($key === "default_approval_group_ids") {
                continue;
            }

            $value = trim((string)$this->request->getParam($key));
            $this->config->setAppValue("enhanced_registration", $key, $value);
        }

        foreach (["lldap_admin_password", "bridge_secret"] as $secretKey) {
            $secretValue = (string)$this->request->getParam($secretKey, "");
            if ($secretValue !== "") {
                $this->config->setAppValue("enhanced_registration", $secretKey, $secretValue);
            }
        }

        $this->audit('settings_saved');

        return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=settings_saved");
    }


    /**
     * @AdminRequired
     */
    public function testLldap(): RedirectResponse {
        try {
            $groups = $this->lldapService->getGroups();

            if (!is_array($groups)) {
                throw new \RuntimeException('Unexpected LLDAP response.');
            }

            $this->audit('test_lldap_ok');

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=lldap_test_ok");
        } catch (\Throwable $e) {
            $this->logger->warning($this->brandName() . ': LLDAP test failed', [
                'error' => $e->getMessage(),
            ]);

            $this->audit('test_lldap_failed');

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=lldap_test_failed");
        }
    }

    /**
     * @AdminRequired
     */
    public function testBridge(): RedirectResponse {
        try {
            $bridgeUrl = trim($this->config->getAppValue('enhanced_registration', 'bridge_url', ''));

            if ($bridgeUrl === '') {
                throw new \RuntimeException('Bridge URL is empty.');
            }

            $healthUrl = rtrim($bridgeUrl, '/') . '/health';

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 8,
                    'ignore_errors' => true,
                ],
            ]);

            $response = @file_get_contents($healthUrl, false, $context);

            if ($response === false || trim((string)$response) !== 'ok') {
                throw new \RuntimeException('Bridge healthcheck failed: ' . (string)$response);
            }

            $this->audit('test_bridge_ok');

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=bridge_test_ok");
        } catch (\Throwable $e) {
            $this->logger->warning($this->brandName() . ': Bridge test failed', [
                'error' => $e->getMessage(),
            ]);

            $this->audit('test_bridge_failed');

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=bridge_test_failed");
        }
    }

    /**
     * @AdminRequired
     */
    public function testMail(): RedirectResponse {
        $email = trim((string)$this->request->getParam('test_email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=test_mail_invalid");
        }

        try {
            $this->config->setAppValue('enhanced_registration', 'test_mail_recipient', $email);

            $message = $this->mailer->createMessage();
            $message->setTo([$email]);
            $message->setSubject($this->brandName() . ': Test-Mail');
            $message->setPlainBody(
                "This is a test email from " . $this->brandName() . ".\n\n" .
                "If you received this message, mail delivery is working."
            );

            $this->mailer->send($message);
            $this->audit('test_mail_ok', $this->auditEmailContext($email));

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=test_mail_ok");
        } catch (\Throwable $e) {
            $this->logger->warning($this->brandName() . ': test mail failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            $this->audit('test_mail_failed', $this->auditEmailContext($email));

            return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=test_mail_failed");
        }
    }

    /**
     * @AdminRequired
     */
    public function approve(): RedirectResponse {
        $userId = (string)$this->request->getParam("userId");
        $groupIds = $this->request->getParam("groupIds", []);

        if (!is_array($groupIds)) {
            $groupIds = [$groupIds];
        }

        $groupIds = array_values(array_unique(array_filter(array_map('intval', $groupIds))));

        $allGroups = $this->lldapService->getGroups();
        $groupNames = [];

        foreach ($allGroups as $group) {
            $gid = (int)($group["id"] ?? 0);
            if (in_array($gid, $groupIds, true)) {
                $groupNames[] = (string)($group["displayName"] ?? $gid);
            }
        }

        $user = $this->lldapService->getUserById($userId);
        $this->lldapService->approveUser($userId, $groupIds);
        $this->audit('user_approved', [
            'user' => $userId,
            'groups' => implode(',', array_map('strval', $groupIds)),
        ]);

        $groupText = empty($groupNames) ? "Keine Gruppe" : implode(", ", $groupNames);
        $loginUrl = $this->safeRedirectUrl($this->config->getAppValue("enhanced_registration", "login_url", "/login"), "/login");

        if ($user && !empty($user["email"])) {
            $displayName = (string)($user["displayName"] ?? $userId);

            $message = $this->mailer->createMessage();
            $message->setTo([$user["email"]]);
            $message->setSubject($this->renderMailTemplate(
                $this->mailTemplate('mail_approved_subject', '{brand}: Konto freigegeben'),
                [
                    'brand' => $this->brandName(),
                    'displayName' => $displayName,
                    'userId' => $userId,
                    'groups' => $groupText,
                    'loginUrl' => $loginUrl,
                ]
            ));
            $message->setPlainBody($this->renderMailTemplate(
                $this->mailTemplate(
                    'mail_approved_body',
                    "Hallo {displayName},\n\nIhr Konto wurde freigegeben.\n\nZugewiesene Gruppen: {groups}\n\nAnmeldung: {loginUrl}"
                ),
                [
                    'brand' => $this->brandName(),
                    'displayName' => $displayName,
                    'userId' => $userId,
                    'groups' => $groupText,
                    'loginUrl' => $loginUrl,
                ]
            ));
            $this->mailer->send($message);
        }

        $this->logger->info($this->brandName() . ": user approved", ["user" => $userId, "groups" => $groupNames]);
        return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=approved");
    }


    /**
     * @AdminRequired
     */
    public function blacklist(): RedirectResponse {
        $userId = (string)$this->request->getParam("userId");
        $user = $this->lldapService->getUserById($userId);
        $rejectionAction = $this->config->getAppValue('enhanced_registration', 'rejection_action', 'blacklist');

        if (!in_array($rejectionAction, ['blacklist', 'remove_pending', 'delete_user'], true)) {
            $rejectionAction = 'blacklist';
        }

        $this->lldapService->rejectUser($userId, $rejectionAction);

        $this->audit('user_rejected', [
            'user' => $userId,
            'rejection_action' => $rejectionAction,
        ]);

        if ($user && !empty($user["email"])) {
            $displayName = (string)($user["displayName"] ?? $userId);

            $message = $this->mailer->createMessage();
            $message->setTo([$user["email"]]);
            $message->setSubject($this->renderMailTemplate(
                $this->mailTemplate('mail_rejected_subject', '{brand}: Registrierung abgelehnt'),
                [
                    'brand' => $this->brandName(),
                    'displayName' => $displayName,
                    'userId' => $userId,
                ]
            ));
            $message->setPlainBody($this->renderMailTemplate(
                $this->mailTemplate(
                    'mail_rejected_body',
                    "Hallo {displayName},\n\nIhre Registrierung wurde abgelehnt.\n\nBei Fragen wenden Sie sich bitte an einen Administrator."
                ),
                [
                    'brand' => $this->brandName(),
                    'displayName' => $displayName,
                    'userId' => $userId,
                ]
            ));
            $this->mailer->send($message);
        }

        $this->logger->warning($this->brandName() . ": user rejected", [
            "user" => $userId,
            "rejection_action" => $rejectionAction,
        ]);

        return new RedirectResponse("/index.php/settings/admin/enhanced_registration?msg=blacklisted");
    }


    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function passreset(): TemplateResponse {
        return $this->noStoreTemplate('passreset');
    }

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function submitpassreset(): TemplateResponse {
        $email = trim((string)$this->request->getParam('email'));

        $rateLimitMessage = $this->checkRateLimitAndRecord('password_reset_request', $email);

        if ($rateLimitMessage !== null) {
            return $this->noStoreTemplate('passreset', [
                'email' => $email,
                'message' => $rateLimitMessage
            ], 'guest');
        }

        $user = $this->lldapService->findUserByEmail($email);

        if (!$user) {
            return new TemplateResponse('enhanced_registration', 'passreset', [
                'message' => 'Falls diese E-Mail-Adresse existiert, wurde ein Code versendet.'
            ], 'guest');
        }

        $token = $this->passwordResetService->createReset($email, (string)$user['id']);

        $this->sendPasswordResetMail($email, $token);
        $this->audit('password_reset_requested', $this->auditEmailContext($email));

        return $this->noStoreTemplate('passreset_code', [
            'email' => $email,
            'message' => 'Wir haben Ihnen einen Bestätigungscode gesendet.'
        ], 'guest');
    }

    /**
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     */

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function resendpassreset(): TemplateResponse {
        $email = trim((string)$this->request->getParam('email'));

        if ($email === '') {
            return $this->noStoreTemplate('passreset', [
                'message' => 'Bitte geben Sie Ihre E-Mail-Adresse erneut ein.'
            ], 'guest');
        }

        $rateLimitMessage = $this->checkRateLimitAndRecord('password_reset_resend', $email);

        if ($rateLimitMessage !== null) {
            return $this->noStoreTemplate('passreset_code', [
                'email' => $email,
                'message' => $rateLimitMessage
            ], 'guest');
        }

        $user = $this->lldapService->findUserByEmail($email);

        if (!$user) {
            return $this->noStoreTemplate('passreset', [
                'message' => 'Falls ein Konto mit dieser E-Mail-Adresse existiert, wurde ein neuer Code gesendet.'
            ], 'guest');
        }

        $token = $this->passwordResetService->createReset($email, (string)$user['id']);

        try {
            $this->sendPasswordResetMail($email, $token);
            $this->audit('password_reset_code_resent', $this->auditEmailContext($email));
        } catch (\Throwable $e) {
            $this->logger->warning($this->brandName() . ': password reset code resend failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return $this->noStoreTemplate('passreset_code', [
                'email' => $email,
                'message' => 'Der Bestätigungscode konnte nicht erneut gesendet werden.'
            ], 'guest');
        }

        return $this->noStoreTemplate('passreset_code', [
            'email' => $email,
            'message' => 'Bestätigungscode wurde erneut gesendet.'
        ], 'guest');
    }

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function verifypassreset(): TemplateResponse {
        $token = trim((string)$this->request->getParam('token'));
        $rateLimitMessage = $this->checkRateLimitAndRecord('password_reset_verify', $this->clientRateLimitIdentity());

        if ($rateLimitMessage !== null) {
            return $this->noStoreTemplate('passreset_code', [
                'message' => $rateLimitMessage
            ], 'guest');
        }

        $reset = $this->passwordResetService->getValidReset($token);

        if (!$reset) {
            return $this->noStoreTemplate('passreset_code', [
                'message' => 'Ungültiger oder abgelaufener Code.'
            ], 'guest');
        }

        return $this->noStoreTemplate('passreset_set', [
            'token' => $token
        ], 'guest');
    }

    #[PublicPage]
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function setnewpassword(): TemplateResponse {
        $token = trim((string)$this->request->getParam('token'));
        $rateLimitMessage = $this->checkRateLimitAndRecord('password_reset_set', $this->clientRateLimitIdentity());

        if ($rateLimitMessage !== null) {
            return $this->noStoreTemplate('passreset_code', [
                'message' => $rateLimitMessage
            ], 'guest');
        }

        $password = (string)$this->request->getParam('password');
        $passwordConfirm = (string)$this->request->getParam('password_confirm');

        if ($password !== $passwordConfirm) {
            return $this->noStoreTemplate('passreset_set', [
                'token' => $token,
                'message' => 'Die Passwörter stimmen nicht überein.'
            ], 'guest');
        }

        $reset = $this->passwordResetService->getValidReset($token);

        if (!$reset) {
            return $this->noStoreTemplate('passreset_code', [
                'message' => 'Ungültiger oder abgelaufener Code.'
            ], 'guest');
        }

        $passwordPolicyMessage = $this->validatePasswordPolicy($password);

        if ($passwordPolicyMessage !== null) {
            return $this->noStoreTemplate('passreset_set', [
                'token' => $token,
                'message' => $passwordPolicyMessage
            ], 'guest');
        }

        $this->lldapService->setUserPassword((string)$reset['user_id'], $password);
        $this->passwordResetService->markUsed($token);
        $this->audit('password_changed', [
            'user' => (string)$reset['user_id'],
        ]);

        $loginUrl = $this->safeRedirectUrl(
            $this->config->getAppValue('enhanced_registration', 'login_url', '/login'),
            '/login'
        );

        $redirectUrl = $this->safeRedirectUrl(
            $this->config->getAppValue('enhanced_registration', 'password_reset_success_redirect_url', ''),
            $loginUrl
        );

        return $this->noStoreTemplate('passreset_success', [
            'redirect_url' => $redirectUrl
        ]);
    }

}
