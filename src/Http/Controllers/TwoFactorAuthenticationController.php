<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Aldemeery\Shieldify\Events\RecoveryCodeReplaced;
use Aldemeery\Shieldify\Contracts\TwoFactorLoginResponse;
use Aldemeery\Shieldify\Http\Requests\TwoFactorLoginRequest;
use Aldemeery\Shieldify\Actions\EnableTwoFactorAuthentication;
use Aldemeery\Shieldify\Actions\DisableTwoFactorAuthentication;
use Aldemeery\Shieldify\Contracts\FailedTwoFactorLoginResponse;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $guard;

    /**
     * Constructor.
     *
     * @param \Illuminate\Contracts\Auth\Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Attempt to authenticate a new session using the two factor authentication code.
     *
     * @param \Aldemeery\Shieldify\Http\Requests\TwoFactorLoginRequest $request
     *
     * @return mixed
     */
    public function store(TwoFactorLoginRequest $request)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new RecoveryCodeReplaced($user, $code));
        } elseif (!$request->hasValidCode()) {
            return app(FailedTwoFactorLoginResponse::class);
        }

        $this->guard->setUser($user);

        return app(TwoFactorLoginResponse::class);
    }

    /**
     * Enable two factor authentication for the user.
     *
     * @param \Illuminate\Http\Request                                   $request
     * @param \Aldemeery\Shieldify\Actions\EnableTwoFactorAuthentication $enable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $enable($this->guard->user());

        return new JsonResponse('', 200);
    }

    /**
     * Disable two factor authentication for the user.
     *
     * @param \Illuminate\Http\Request                                    $request
     * @param \Aldemeery\Shieldify\Actions\DisableTwoFactorAuthentication $disable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $disable($this->guard->user());

        return new JsonResponse('', 200);
    }
}
