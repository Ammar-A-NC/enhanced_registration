<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Settings\ISettings;

class Personal implements ISettings {
    public function __construct(
        private IURLGenerator $urlGenerator,
        private IRequest $request,
        private IConfig $config,
        private IUserSession $userSession
    ) {}

    private function routeUrl(string $route, array $params = []): string {
        return $this->urlGenerator->linkToRoute('enhanced_registration.register.' . $route, $params);
    }

    public function getForm(): TemplateResponse {
        $user = $this->userSession->getUser();
        $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/settings/user/enhanced_registration');

        return new TemplateResponse('enhanced_registration', 'personal', [
            'personal_password_url' => $this->routeUrl('personalPassword'),
            'return_url' => $requestUri,
            'user_id' => $user !== null ? $user->getUID() : '',
            'status' => (string)$this->request->getParam('enhanced_registration_status', ''),
            'message' => (string)$this->request->getParam('enhanced_registration_message', ''),
            'password_min_length' => $this->config->getAppValue('enhanced_registration', 'password_min_length', '12'),
            'password_require_uppercase' => $this->config->getAppValue('enhanced_registration', 'password_require_uppercase', '1'),
            'password_require_lowercase' => $this->config->getAppValue('enhanced_registration', 'password_require_lowercase', '1'),
            'password_require_number' => $this->config->getAppValue('enhanced_registration', 'password_require_number', '1'),
            'password_require_special' => $this->config->getAppValue('enhanced_registration', 'password_require_special', '1'),
        ], '');
    }

    public function getSection(): string {
        return 'enhanced_registration';
    }

    public function getPriority(): int {
        return 20;
    }
}
