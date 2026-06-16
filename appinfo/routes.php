<?php

return [
    'routes' => [
        ['name' => 'register#index', 'url' => '/register', 'verb' => 'GET'],
        ['name' => 'register#submitEmail', 'url' => '/register', 'verb' => 'POST'],
        ['name' => 'register#checkMail', 'url' => '/checkmail', 'verb' => 'GET'],
        ['name' => 'register#submitCode', 'url' => '/checkmail', 'verb' => 'POST'],
        ['name' => 'register#resendCode', 'url' => '/resend-code', 'verb' => 'POST'],
        ['name' => 'register#verify', 'url' => '/verify', 'verb' => 'GET'],
        ['name' => 'register#submitDetails', 'url' => '/details', 'verb' => 'POST'],

        ['name' => 'register#approve', 'url' => '/admin/approve', 'verb' => 'POST'],
        ['name' => 'register#blacklist', 'url' => '/admin/blacklist', 'verb' => 'POST'],
        ['name' => 'register#saveSettings', 'url' => '/admin/settings', 'verb' => 'POST'],
        ['name' => 'register#deleteUser', 'url' => '/admin/users/delete', 'verb' => 'POST'],

        ['name' => 'register#already', 'url' => '/already', 'verb' => 'GET'],

        ['name' => 'register#passreset', 'url' => '/passreset', 'verb' => 'GET'],
        ['name' => 'register#submitpassreset', 'url' => '/passreset', 'verb' => 'POST'],
        ['name' => 'register#verifypassreset', 'url' => '/passreset/verify', 'verb' => 'GET'],
        ['name' => 'register#setnewpassword', 'url' => '/passreset/set', 'verb' => 'POST'],
        ['name' => 'register#resendpassreset', 'url' => '/passreset/resend', 'verb' => 'POST'],

        ['name' => 'register#personalPassword', 'url' => '/personal/password', 'verb' => 'POST'],
    ],
];
