<?php

namespace Aldemeery\Shieldify\Actions;

use Illuminate\Support\Collection;
use Aldemeery\Shieldify\RecoveryCode;
use Aldemeery\Shieldify\Events\TwoFactorAuthenticationEnabled;
use Aldemeery\Shieldify\Contracts\TwoFactorAuthenticationProvider;

class EnableTwoFactorAuthentication
{
    /**
     * The two factor authentication provider.
     *
     * @var \Aldemeery\Shieldify\Contracts\TwoFactorAuthenticationProvider
     */
    protected $provider;

    /**
     * Create a new action instance.
     *
     * @param \Aldemeery\Shieldify\Contracts\TwoFactorAuthenticationProvider $provider
     *
     * @return void
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Enable two factor authentication for the user.
     *
     * @param mixed $user
     *
     * @return void
     */
    public function __invoke($user)
    {
        $user->forceFill([
            'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        TwoFactorAuthenticationEnabled::dispatch($user);
    }
}
