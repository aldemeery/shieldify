<?php

namespace Aldemeery\Shieldify\Actions;

use Aldemeery\Shieldify\Key;
use Illuminate\Http\JsonResponse;
use Aldemeery\Shieldify\Shieldify;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\Guard;
use Aldemeery\Shieldify\LoginRateLimiter;
use Illuminate\Validation\ValidationException;
use Aldemeery\Shieldify\TwoFactorAuthenticatable;
use Aldemeery\Shieldify\Events\TwoFactorAuthenticationChallenged;

class RespondIfTwoFactorAuthenticatable
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $guard;

    /**
     * The login rate limiter instance.
     *
     * @var \Aldemeery\Shieldify\LoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new controller instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard      $guard
     * @param \Aldemeery\Shieldify\LoginRateLimiter $limiter
     *
     * @return void
     */
    public function __construct(Guard $guard, LoginRateLimiter $limiter)
    {
        $this->guard = $guard;
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param callable                 $next
     *
     * @return mixed
     */
    public function handle($request, $next)
    {
        $user = $this->validateCredentials($request);

        if (optional($user)->two_factor_secret &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))
        ) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        return $next($request);
    }

    /**
     * Attempt to validate the incoming credentials.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function validateCredentials($request)
    {
        if (Shieldify::$authenticateUsingCallback) {
            return tap(
                call_user_func(Shieldify::$authenticateUsingCallback, $request),
                function ($user) use ($request) {
                    if (!$user) {
                        $this->fireFailedEvent($request);

                        $this->throwFailedAuthenticationException($request);
                    }
                }
            );
        }

        return tap(
            $this->guard->getProvider()->retrieveByCredentials($request->only(Shieldify::username(), 'password')),
            function ($user) use ($request) {
                if (!$user ||
                    !$this->guard->getProvider()->validateCredentials($user, ['password' => $request->password])
                ) {
                    $this->fireFailedEvent($request, $user);

                    $this->throwFailedAuthenticationException($request);
                }
            }
        );
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException($request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            Shieldify::username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param \Illuminate\Http\Request                        $request
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     *
     * @return void
     */
    protected function fireFailedEvent($request, $user = null)
    {
        event(new Failed(config('shieldify.guard'), $user, [
            Shieldify::username() => $request->{Shieldify::username()},
            'password' => $request->password,
        ]));
    }

    /**
     * Get the two factor authentication enabled response.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed                    $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function twoFactorChallengeResponse($request, $user)
    {
        TwoFactorAuthenticationChallenged::dispatch($user);

        return new JsonResponse([
            'key' => (string) new Key($user->id, time() + config('shieldify.two_factor_key_timeout', 300)),
            'two_factor' => true,
        ]);
    }
}
