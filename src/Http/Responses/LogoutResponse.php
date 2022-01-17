<?php

namespace Aldemeery\Shieldify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Aldemeery\Shieldify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return new JsonResponse('', 204);
    }
}
