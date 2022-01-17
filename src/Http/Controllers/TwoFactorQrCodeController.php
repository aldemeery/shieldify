<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;

class TwoFactorQrCodeController extends Controller
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
     * Get the SVG element for the user's two factor authentication QR code.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request)
    {
        if (is_null($this->guard->user()->two_factor_secret)) {
            return [];
        }

        return new JsonResponse(['svg' => $this->guard->user()->twoFactorQrCodeSvg()]);
    }
}
