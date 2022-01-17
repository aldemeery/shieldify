<?php

namespace Aldemeery\Shieldify;

use Aldemeery\Shieldify\Contracts\CreatesNewUsers;
use Aldemeery\Shieldify\Contracts\ResetsUserPasswords;
use Aldemeery\Shieldify\Contracts\UpdatesUserPasswords;
use Aldemeery\Shieldify\Contracts\UpdatesUserProfileInformation;

class Shieldify
{
    /**
     * The callback that is responsible for building the authentication pipeline array, if applicable.
     *
     * @var callable|null
     */
    public static $authenticateThroughCallback;

    /**
     * The callback that is responsible for validating authentication credentials, if applicable.
     *
     * @var callable|null
     */
    public static $authenticateUsingCallback;

    /**
     * The callback that is responsible for confirming user passwords.
     *
     * @var callable|null
     */
    public static $confirmPasswordsUsingCallback;

    /**
     * Indicates if Shieldify routes will be registered.
     *
     * @var boolean
     */
    public static $registersRoutes = true;

    /**
     * Get the username used for authentication.
     *
     * @return string
     */
    public static function username()
    {
        return config('shieldify.username', 'email');
    }

    /**
     * Get the name of the email address request variable / field.
     *
     * @return string
     */
    public static function email()
    {
        return config('shieldify.email', 'email');
    }

    /**
     * Register a callback that is responsible for building the authentication pipeline array.
     *
     * @param callable $callback
     *
     * @return void
     */
    public static function loginThrough(callable $callback)
    {
        static::authenticateThrough($callback);
    }

    /**
     * Register a callback that is responsible for building the authentication pipeline array.
     *
     * @param callable $callback
     *
     * @return void
     */
    public static function authenticateThrough(callable $callback)
    {
        static::$authenticateThroughCallback = $callback;
    }

    /**
     * Register a callback that is responsible for validating incoming authentication credentials.
     *
     * @param callable $callback
     *
     * @return void
     */
    public static function authenticateUsing(callable $callback)
    {
        static::$authenticateUsingCallback = $callback;
    }

    /**
     * Register a callback that is responsible for confirming existing user passwords as valid.
     *
     * @param callable $callback
     *
     * @return void
     */
    public static function confirmPasswordsUsing(callable $callback)
    {
        static::$confirmPasswordsUsingCallback = $callback;
    }

    /**
     * Register a class / callback that should be used to create new users.
     *
     * @param string $callback
     *
     * @return void
     */
    public static function createUsersUsing(string $callback)
    {
        app()->singleton(CreatesNewUsers::class, $callback);
    }

    /**
     * Register a class / callback that should be used to update user profile information.
     *
     * @param string $callback
     *
     * @return void
     */
    public static function updateUserProfileInformationUsing(string $callback)
    {
        app()->singleton(UpdatesUserProfileInformation::class, $callback);
    }

    /**
     * Register a class / callback that should be used to update user passwords.
     *
     * @param string $callback
     *
     * @return void
     */
    public static function updateUserPasswordsUsing(string $callback)
    {
        app()->singleton(UpdatesUserPasswords::class, $callback);
    }

    /**
     * Register a class / callback that should be used to reset user passwords.
     *
     * @param string $callback
     *
     * @return void
     */
    public static function resetUserPasswordsUsing(string $callback)
    {
        app()->singleton(ResetsUserPasswords::class, $callback);
    }

    /**
     * Configure Shieldify to not register its routes.
     *
     * @return static
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static();
    }
}
