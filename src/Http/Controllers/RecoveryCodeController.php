<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Aldemeery\Shieldify\Actions\GenerateNewRecoveryCodes;

class RecoveryCodeController extends Controller
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
     * Get the two factor authentication recovery codes for authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$this->guard->user()->two_factor_secret ||
            !$this->guard->user()->two_factor_recovery_codes
        ) {
            return new JsonResponse([]);
        }

        return new JsonResponse(json_decode(decrypt(
            $this->guard->user()->two_factor_recovery_codes
        ), true));
    }

    /**
     * Generate a fresh set of two factor authentication recovery codes.
     *
     * @param \Illuminate\Http\Request                              $request
     * @param \Aldemeery\Shieldify\Actions\GenerateNewRecoveryCodes $generate
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, GenerateNewRecoveryCodes $generate)
    {
        $generate($this->guard->user());

        return new JsonResponse('', 200);
    }
}
