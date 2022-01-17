<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Aldemeery\Shieldify\Contracts\UpdatesUserPasswords;
use Aldemeery\Shieldify\Contracts\PasswordUpdateResponse;

class PasswordController extends Controller
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
     * Update the user's password.
     *
     * @param \Illuminate\Http\Request                            $request
     * @param \Aldemeery\Shieldify\Contracts\UpdatesUserPasswords $updater
     *
     * @return \Aldemeery\Shieldify\Contracts\PasswordUpdateResponse
     */
    public function update(Request $request, UpdatesUserPasswords $updater)
    {
        $updater->update($this->guard->user(), $request->all());

        return app(PasswordUpdateResponse::class);
    }
}
