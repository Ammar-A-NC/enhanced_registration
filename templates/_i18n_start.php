<?php

if (!function_exists('nc_er_ui_language')) {
    function nc_er_ui_language(array $params): string {
        $settings = $params['settings'] ?? [];
        $configured = strtolower(trim((string)($params['ui_language'] ?? ($settings['ui_language'] ?? 'auto'))));

        if (in_array($configured, ['de', 'en'], true)) {
            return $configured;
        }

        if ($configured !== 'auto') {
            return 'en';
        }

        $acceptLanguage = strtolower((string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));

        foreach (explode(',', $acceptLanguage) as $part) {
            $lang = trim(explode(';', $part)[0] ?? '');

            if ($lang === 'de' || str_starts_with($lang, 'de-')) {
                return 'de';
            }

            if ($lang === 'en' || str_starts_with($lang, 'en-')) {
                return 'en';
            }
        }

        return 'en';
    }
}

$GLOBALS['nc_er_ui_language'] = nc_er_ui_language($_);

ob_start();
