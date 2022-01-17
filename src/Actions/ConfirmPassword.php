<?php

namespace Aldemeery\Shieldify\Actions;

use Aldemeery\Shieldify\Shieldify;
use Illuminate\Contracts\Auth\Guard;

class ConfirmPassword
{
    /**
     * Confirm that the given password is valid for the given user.
     *
     * @param \Illuminate\Contracts\Auth\Guard $guard
     * @param mixed                            $user
     * @param string|null                      $password
     *
     * @return bool
     */
    public function __invoke(Guard $guard, $user, ?string $password = null)
    {
        $username = config('shieldify.username');

        return is_null(Shieldify::$confirmPasswordsUsingCallback) ? $this->validate($guard, [
            $username => $user->{$username},
            'password' => $password,
        ]) : $this->confirmPasswordUsingCustomCallback($user, $password);
    }

    /**
     * Confirm the user's password using a custom callback.
     *
     * @param mixed       $user
     * @param string|null $password
     *
     * @return bool
     */
    protected function confirmPasswordUsingCustomCallback($user, ?string $password = null)
    {
        return call_user_func(
            Shieldify::$confirmPasswordsUsingCallback,
            $user,
            $password
        );
    }

    /**
     * Validate given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Guard $guard
     * @param array                            $credentials
     *
     * @return bool
     */
    protected function validate(Guard $guard, array $credentials)
    {
        return !is_null($guard->user()) && $guard->getProvider()->validateCredentials($guard->user(), $credentials);
    }
}
