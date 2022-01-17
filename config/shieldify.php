<?php

use Aldemeery\Shieldify\Features;

return [
    'guard' => 'sanctum',
    'middleware' => ['api'],
    'auth_middleware' => 'auth',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'prefix' => '',
    'domain' => null,
    'limiters' => [
        'login' => null,
    ],
    'two_factor_key_timeout' => 300,
    'email_verification' => [
        'url' => env('APP_URL', 'http://localhost'),
        'expire' => 30,
    ],
    'password_reset' => [
        'url' => env('APP_URL', 'http://localhost'),
    ],
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication(),
    ],
];
