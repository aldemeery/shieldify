<?php

namespace Aldemeery\Shieldify\Actions;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Auth\Events\PasswordReset;

class CompletePasswordReset
{
    /**
     * Complete the password reset process for the given user.
     *
     * @param \Illuminate\Contracts\Auth\Guard $guard
     * @param mixed                            $user
     *
     * @return void
     */
    public function __invoke(Guard $guard, $user)
    {
        event(new PasswordReset($user));
    }
}
