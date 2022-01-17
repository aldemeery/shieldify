<?php

namespace Aldemeery\Shieldify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Auth\Events\Registered;
use Aldemeery\Shieldify\Contracts\CreatesNewUsers;
use Aldemeery\Shieldify\Contracts\RegisterResponse;

class RegisteredUserController extends Controller
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
     * Create a new registered user.
     *
     * @param \Illuminate\Http\Request                       $request
     * @param \Aldemeery\Shieldify\Contracts\CreatesNewUsers $creator
     *
     * @return \Aldemeery\Shieldify\Contracts\RegisterResponse
     */
    public function store(
        Request $request,
        CreatesNewUsers $creator
    ): RegisterResponse {
        event(new Registered($user = $creator->create($request->all())));

        $this->guard->setUser($user);

        return app(RegisterResponse::class);
    }
}
