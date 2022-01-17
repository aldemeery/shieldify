<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Cache;
use Aldemeery\Shieldify\Actions\ConfirmPassword;
use Aldemeery\Shieldify\Contracts\PasswordConfirmedResponse;
use Aldemeery\Shieldify\Contracts\FailedPasswordConfirmationResponse;

class ConfirmablePasswordController extends Controller
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
     * Confirm the user's password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request)
    {
        $confirmed = app(ConfirmPassword::class)(
            $this->guard,
            $this->guard->user(),
            $request->input('password')
        );

        if ($confirmed) {
            $key = sprintf('auth.user_%s_password_confirmed_at', $this->guard->id());
            Cache::put($key, time(), config('auth.password_timeout', 10800));
        }

        return $confirmed
            ? app(PasswordConfirmedResponse::class)
            : app(FailedPasswordConfirmationResponse::class);
    }
}
