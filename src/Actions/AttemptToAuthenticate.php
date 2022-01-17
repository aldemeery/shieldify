<?php

namespace Aldemeery\Shieldify\Actions;

use Illuminate\Http\Request;
use Aldemeery\Shieldify\Shieldify;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\Guard;
use Aldemeery\Shieldify\LoginRateLimiter;
use Illuminate\Validation\ValidationException;

class AttemptToAuthenticate
{
    /**
     * The login rate limiter instance.
     *
     * @var \Aldemeery\Shieldify\LoginRateLimiter
     */
    protected $limiter;

    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param \Aldemeery\Shieldify\LoginRateLimiter $limiter
     * @param \Illuminate\Contracts\Auth\Guard      $guard
     *
     * @return void
     */
    public function __construct(LoginRateLimiter $limiter, Guard $guard)
    {
        $this->limiter = $limiter;
        $this->guard = $guard;
    }

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param callable                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        if (Shieldify::$authenticateUsingCallback) {
            return $this->handleUsingCustomCallback($request, $next);
        }

        $user = $this->guard->getProvider()->retrieveByCredentials($request->only(Shieldify::username(), 'password'));

        if (!$user ||
            !$this->guard->getProvider()->validateCredentials($user, ['password' => $request->password])
        ) {
            $this->fireFailedEvent($request, $user);

            $this->throwFailedAuthenticationException($request);
        }

        $this->guard->setUser($user);
        $this->limiter->clear($request);

        return $next($request);
    }

    /**
     * Attempt to authenticate using a custom callback.
     *
     * @param \Illuminate\Http\Request $request
     * @param callable                 $next
     *
     * @return mixed
     */
    protected function handleUsingCustomCallback($request, $next)
    {
        $user = call_user_func(Shieldify::$authenticateUsingCallback, $request);

        if (!$user) {
            $this->fireFailedEvent($request);

            return $this->throwFailedAuthenticationException($request);
        }

        $this->guard->setUser($user);
        $this->limiter->clear($request);

        return $next($request);
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
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function fireFailedEvent($request)
    {
        event(new Failed(config('shieldify.guard'), null, [
            Shieldify::username() => $request->{Shieldify::username()},
            'password' => $request->password,
        ]));
    }
}
