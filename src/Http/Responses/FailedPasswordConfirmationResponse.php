<?php

namespace Aldemeery\Shieldify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Aldemeery\Shieldify\Contracts\FailedPasswordConfirmationResponse as FailedPasswordConfirmationResponseContract;

class FailedPasswordConfirmationResponse implements FailedPasswordConfirmationResponseContract
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
        $message = __('The provided password was incorrect.');

        return new JsonResponse([
            'message' => 'The given data was invalid.',
            'errors' => [
                'password' => [$message],
            ],
        ], 422);
    }
}
