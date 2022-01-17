<?php

use Aldemeery\Shieldify\Features;

return [
    /*
    |--------------------------------------------------------------------------
    | Shieldify Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Shieldify will use while
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'sanctum',

    /*
    |--------------------------------------------------------------------------
    | Shieldify Routes Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware Shieldify will assign to the routes
    | that it registers with the application. If necessary, you may change
    | these middleware but typically this provided default is preferred.
    |
    */

    'middleware' => ['api'],

    /*
    |--------------------------------------------------------------------------
    | Shieldify Password Broker
    |--------------------------------------------------------------------------
    |
    | Here you may specify which password broker Shieldify can use when a user
    | is resetting their password. This configured value should match one
    | of your password brokers setup in your "auth" configuration file.
    |
    */

    'passwords' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Username / Email
    |--------------------------------------------------------------------------
    |
    | This value defines which model attribute should be considered as your
    | application's "username" field. Typically, this might be the email
    | address of the users but you are free to change this value here.
    |
    | Out of the box, Shieldify expects forgot password and reset password
    | requests to have a field named 'email'. If the application uses
    | another name for the field you may define it below as needed.
    |
    */

    'username' => 'email',

    'email' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Shieldify Routes Prefix / Subdomain
    |--------------------------------------------------------------------------
    |
    | Here you may specify which prefix Shieldify will assign to all the routes
    | that it registers with the application. If necessary, you may change
    | subdomain under which all of the Shieldify routes will be available.
    |
    */

    'prefix' => 'api',

    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | By default, Shieldify will throttle logins to five requests per minute for
    | every email and IP address combination. However, if you would like to
    | specify a custom rate limiter to call then you may specify it here.
    |
    */

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Two Factor Key Timeout
    |--------------------------------------------------------------------------
    |
    | Because Shieldify is stateless, it responds with an encrypted key that
    | holds the id of user trying to log in, in a two-factor authentication process.
    | For security purposes, we set a timeout for the key, after which, the key can
    | no loger be used for two-factor authentication.
    | By default the timeout is set to two minutes.
    |
    */

    'two_factor_key_timeout' => 120,

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    |
    | Since shieldify is typically used in stateless API backends, we expect that
    | the backend and the frontend can have two different base URLs.
    |
    | Here, you can set the base URL for your frontend, where the user will be
    | taken to, when they click on the verfication link sent in the email.
    |
    | You should also set the number of minutes after which the verification
    | link will expire, which defaults to 30 minutes.
    |
    */

    'email_verification' => [
        'url' => env('APP_URL', 'http://localhost'),
        'expire' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    |
    | Since shieldify is typically used in stateless API backends, we expect that
    | the backend and the frontend can have two different base URLs.
    |
    | Here, you can set the base URL for your frontend, where the user will be
    | taken to, when they click on the reset link sent in the email.
    |
    */

    'password_reset' => [
        'url' => env('APP_URL', 'http://localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Some of the Shieldify features are optional. You may disable the features
    | by removing them from this array. You're free to only remove some of
    | these features or you can even remove all of these if you need to.
    |
    */

    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        // Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication([
            'confirmPassword' => true,
        ]),
    ],
];
