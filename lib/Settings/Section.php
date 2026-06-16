<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Settings;

use OCP\IL10N;
use OCP\IConfig;
use OCP\Settings\IIconSection;

class Section implements IIconSection {

    public function __construct(
        private IL10N $l,
        private IConfig $config,
    ) {
    }

    public function getID(): string {
        return 'enhanced_registration';
    }

    public function getName(): string {
        $language = strtolower($this->config->getAppValue('enhanced_registration', 'ui_language', 'auto'));

        if (str_starts_with($language, 'en')) {
            return $this->l->t('Change password');
        }

        return $this->l->t('Passwort ändern');
    }

    public function getPriority(): int {
        return 80;
    }

    public function getIcon(): string {
        return '/apps/settings/img/admin.svg';
    }
}
