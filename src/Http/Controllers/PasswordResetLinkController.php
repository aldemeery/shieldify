<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Aldemeery\Shieldify\Shieldify;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Support\Responsable;
use Aldemeery\Shieldify\Contracts\FailedPasswordResetLinkRequestResponse;
use Aldemeery\Shieldify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

class PasswordResetLinkController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request): Responsable
    {
        $request->validate([Shieldify::email() => 'required|email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = $this->broker()->sendResetLink(
            $request->only(Shieldify::email())
        );

        return $status == Password::RESET_LINK_SENT
                    ? app(SuccessfulPasswordResetLinkRequestResponse::class, ['status' => $status])
                    : app(FailedPasswordResetLinkRequestResponse::class, ['status' => $status]);
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
