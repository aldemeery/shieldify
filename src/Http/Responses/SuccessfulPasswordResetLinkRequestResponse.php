<?php

namespace Aldemeery\Shieldify\Http\Responses;

use Illuminate\Http\JsonResponse;
use Aldemeery\Shieldify\Contracts\SuccessfulPasswordResetLinkRequestResponse as ResponseContract;

class SuccessfulPasswordResetLinkRequestResponse implements ResponseContract
{
    /**
     * The response status language key.
     *
     * @var string
     */
    protected $status;

    /**
     * Create a new response instance.
     *
     * @param string $status
     *
     * @return void
     */
    public function __construct(string $status)
    {
        $this->status = $status;
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
        return new JsonResponse(['message' => trans($this->status)], 200);
    }
}
