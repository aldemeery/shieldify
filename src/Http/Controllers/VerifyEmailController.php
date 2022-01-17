<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Guard;
use Aldemeery\Shieldify\Http\Requests\VerifyEmailRequest;

class VerifyEmailController extends Controller
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
     * Mark the authenticated user's email address as verified.
     *
     * @param \Aldemeery\Shieldify\Http\Requests\VerifyEmailRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(VerifyEmailRequest $request)
    {
        if ($this->guard->user()->hasVerifiedEmail()) {
            return new JsonResponse('', 204);
        }

        if ($this->guard->user()->markEmailAsVerified()) {
            event(new Verified($this->guard->user()));
        }

        return new JsonResponse('', 202);
    }
}
