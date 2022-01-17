<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Cache;

class ConfirmedPasswordStatusController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */

    protected $guard;

    /**
     * Create a new middleware instance.
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
     * Get the password confirmation status.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $key = sprintf('auth.user_%s_password_confirmed_at', $this->guard->id());

        return new JsonResponse([
            'confirmed' => (time() - Cache::get($key, 0)) < $request->input(
                'seconds',
                config('auth.password_timeout', 900)
            ),
        ]);
    }
}
