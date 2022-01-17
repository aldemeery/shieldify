<?php

namespace Aldemeery\Shieldify\Http\Requests;

use Aldemeery\Shieldify\Shieldify;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            Shieldify::username() => 'required|string',
            'password' => 'required|string',
            'device' => 'sometimes|string|min:1|max:255',
        ];
    }
}
