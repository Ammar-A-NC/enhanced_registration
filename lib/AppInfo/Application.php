<?php

declare(strict_types=1);

namespace OCA\EnhancedRegistration\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
    public const APP_ID = "enhanced_registration";

    public function __construct() {
        parent::__construct(self::APP_ID);
    }
}
