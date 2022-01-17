<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use UnexpectedValueException;
use Illuminate\Routing\Pipeline;
use Aldemeery\Shieldify\Features;
use Aldemeery\Shieldify\Shieldify;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Aldemeery\Shieldify\Contracts\LoginResponse;
use Aldemeery\Shieldify\Contracts\LogoutResponse;
use Aldemeery\Shieldify\Http\Requests\LoginRequest;
use Aldemeery\Shieldify\Actions\AttemptToAuthenticate;
use Aldemeery\Shieldify\Actions\EnsureLoginIsNotThrottled;
use Aldemeery\Shieldify\Actions\RespondIfTwoFactorAuthenticatable;

class AuthenticationController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $guard
     *
     * @return void
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @param \Aldemeery\Shieldify\Http\Requests\LoginRequest $request
     *
     * @return mixed
     */
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            return app(LoginResponse::class);
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param \Aldemeery\Shieldify\Http\Requests\LoginRequest $request
     *
     * @return \Illuminate\Pipeline\Pipeline
     */
    protected function loginPipeline(LoginRequest $request)
    {
        if (Shieldify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Shieldify::$authenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('shieldify.pipelines.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('shieldify.pipelines.login')
            ));
        }

        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('shieldify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            Features::enabled(Features::twoFactorAuthentication()) ? RespondIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
        ]));
    }

    /**
     * Destroy an authenticated session.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Aldemeery\Shieldify\Contracts\LogoutResponse
     *
     * @throws \UnexpectedValueException
     */
    public function destroy(Request $request): LogoutResponse
    {
        if (!$this->guard->user() instanceof HasApiTokens) {
            throw new UnexpectedValueException(sprintf("User must be instanceof '%s'", HasApiTokens::class), 1);
        }

        $this->guard->user()->currentAccessToken()->delete();

        return app(LogoutResponse::class);
    }
}
