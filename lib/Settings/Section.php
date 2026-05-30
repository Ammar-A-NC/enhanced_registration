<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\Settings;

use OCP\IL10N;
use OCP\Settings\IIconSection;

class Section implements IIconSection {

    public function __construct(
        private IL10N $l,
    ) {
    }

    public function getID(): string {
        return 'enhanced_registration';
    }

    public function getName(): string {
        return $this->l->t('Enhanced Registration');
    }

    public function getPriority(): int {
        return 80;
    }

    public function getIcon(): string {
        return '/apps/settings/img/admin.svg';
    }
}
