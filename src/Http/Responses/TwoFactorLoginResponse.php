<?php

namespace Aldemeery\Shieldify\Http\Responses;

use Illuminate\Http\Request;
use UnexpectedValueException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Auth\Guard;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Aldemeery\Shieldify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
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
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return new JsonResponse([
            'token' => $this->createToken($request),
        ]);
    }

    /**
     * Create a new token for the user logging in.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    protected function createToken(Request $request)
    {
        if (!$this->guard->user() instanceof HasApiTokens) {
            throw new UnexpectedValueException(sprintf("User must be instanceof '%s'", HasApiTokens::class), 1);
        }

        return $this->guard->user()->createToken($request->device ?? 'Unknown')->plainTextToken;
    }
}
