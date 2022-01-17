<?php

namespace Aldemeery\Shieldify\Actions;

use Aldemeery\Shieldify\Events\TwoFactorAuthenticationDisabled;

class DisableTwoFactorAuthentication
{
    /**
     * Disable two factor authentication for the user.
     *
     * @param mixed $user
     *
     * @return void
     */
    public function __invoke($user)
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        TwoFactorAuthenticationDisabled::dispatch($user);
    }
}
