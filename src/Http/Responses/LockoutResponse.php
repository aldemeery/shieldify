<?php

namespace Aldemeery\Shieldify\Http\Responses;

use Illuminate\Http\Response;
use Aldemeery\Shieldify\Shieldify;
use Aldemeery\Shieldify\LoginRateLimiter;
use Illuminate\Validation\ValidationException;
use Aldemeery\Shieldify\Contracts\LockoutResponse as LockoutResponseContract;

class LockoutResponse implements LockoutResponseContract
{
    /**
     * The login rate limiter instance.
     *
     * @var \Aldemeery\Shieldify\LoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new response instance.
     *
     * @param \Aldemeery\Shieldify\LoginRateLimiter $limiter
     *
     * @return void
     */
    public function __construct(LoginRateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return with($this->limiter->availableIn($request), function ($seconds) {
            throw ValidationException::withMessages([
                Shieldify::username() => [
                    trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ],
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        });
    }
}
