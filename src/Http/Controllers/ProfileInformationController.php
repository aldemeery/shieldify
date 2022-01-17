<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Aldemeery\Shieldify\Contracts\UpdatesUserProfileInformation;

class ProfileInformationController extends Controller
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
     * Update the user's profile information.
     *
     * @param \Illuminate\Http\Request                                     $request
     * @param \Aldemeery\Shieldify\Contracts\UpdatesUserProfileInformation $updater
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(
        Request $request,
        UpdatesUserProfileInformation $updater
    ) {
        $updater->update($this->guard->user(), $request->all());

        return new JsonResponse('', 200);
    }
}
