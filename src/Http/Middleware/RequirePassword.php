<?php

namespace Aldemeery\Shieldify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Cache;

class RequirePassword
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */

    protected $guard;

    /**
     * The password timeout.
     *
     * @var integer
     */
    protected $passwordTimeout;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $guard
     * @param int|null                         $passwordTimeout
     *
     * @return void
     */
    public function __construct(Guard $guard, $passwordTimeout = null)
    {
        $this->guard = $guard;
        $this->passwordTimeout = $passwordTimeout ?: 10800;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldConfirmPassword($request)) {
            return new JsonResponse([
                'message' => 'Password confirmation required.',
            ], 423);
        }

        return $next($request);
    }

    /**
     * Determine if the confirmation timeout has expired.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldConfirmPassword(Request $request)
    {
        $key = sprintf('auth.user_%s_password_confirmed_at', $this->guard->id());

        $confirmedAt = time() - Cache::get($key, 0);

        return $confirmedAt > $this->passwordTimeout;
    }
}
