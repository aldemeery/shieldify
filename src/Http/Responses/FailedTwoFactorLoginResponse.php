<?php

namespace Aldemeery\Shieldify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Aldemeery\Shieldify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

class FailedTwoFactorLoginResponse implements FailedTwoFactorLoginResponseContract
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
        $message = __('The provided two factor authentication code was invalid.');

        return new JsonResponse([
            'message' => 'The given data was invalid.',
            'errors' => [
                'code' => [$message],
            ],
        ], 422);
    }
}
