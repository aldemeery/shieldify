<?php

namespace Aldemeery\Shieldify\Actions;

use Illuminate\Support\Collection;
use Aldemeery\Shieldify\RecoveryCode;
use Aldemeery\Shieldify\Events\RecoveryCodesGenerated;

class GenerateNewRecoveryCodes
{
    /**
     * Generate new recovery codes for the user.
     *
     * @param mixed $user
     *
     * @return void
     */
    public function __invoke($user)
    {
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
