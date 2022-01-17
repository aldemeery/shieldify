<?php

namespace Aldemeery\Shieldify\Http\Requests;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
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
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!hash_equals((string) $this->query('id'), (string) $this->guard->user()->getKey())) {
            return false;
        }

        if (!hash_equals((string) $this->query('hash'), sha1($this->guard->user()->getEmailForVerification()))) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
