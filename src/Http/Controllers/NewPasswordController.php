<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Aldemeery\Shieldify\Shieldify;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Support\Responsable;
use Aldemeery\Shieldify\Actions\CompletePasswordReset;
use Aldemeery\Shieldify\Contracts\ResetsUserPasswords;
use Aldemeery\Shieldify\Contracts\PasswordResetResponse;
use Aldemeery\Shieldify\Contracts\FailedPasswordResetResponse;

class NewPasswordController extends Controller
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
     * Reset the user's password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request): Responsable
    {
        $request->validate([
            'token' => 'required',
            Shieldify::email() => 'required|email',
            'password' => 'required',
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = $this->broker()->reset(
            $request->only(Shieldify::email(), 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                app(ResetsUserPasswords::class)->reset($user, $request->all());

                app(CompletePasswordReset::class)($this->guard, $user);
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? app(PasswordResetResponse::class, ['status' => $status])
                    : app(FailedPasswordResetResponse::class, ['status' => $status]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    protected function broker(): PasswordBroker
    {
        return Password::broker(config('shieldify.passwords'));
    }
}
