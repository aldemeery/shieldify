<?php

namespace Aldemeery\Shieldify\Http\Requests;

use Aldemeery\Shieldify\Key;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Aldemeery\Shieldify\Contracts\FailedTwoFactorLoginResponse;
use Aldemeery\Shieldify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorLoginRequest extends FormRequest
{
    /**
     * The user attempting the two factor challenge.
     *
     * @var mixed
     */
    protected $challengedUser;

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
            'key' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $key = Key::from($value);

                    if (is_null($key->id())) {
                        $fail('The ' . $attribute . ' is invalid.');
                    }

                    if ($key->hasExpired()) {
                        $fail('The ' . $attribute . ' is expired.');
                    }
                },
            ],
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ];
    }

    /**
     * Determine if the request has a valid two factor code.
     *
     * @return bool
     */
    public function hasValidCode()
    {
        return $this->code && app(TwoFactorAuthenticationProvider::class)->verify(
            decrypt($this->challengedUser()->two_factor_secret),
            $this->code
        );
    }

    /**
     * Get the valid recovery code if one exists on the request.
     *
     * @return string|null
     */
    public function validRecoveryCode()
    {
        if (!$this->recovery_code) {
            return;
        }

        return collect($this->challengedUser()->recoveryCodes())->first(function ($code) {
            return hash_equals($this->recovery_code, $code) ? $code : null;
        });
    }

    /**
     * Determine if there is a challenged user in the current request.
     *
     * @return bool
     */
    public function hasChallengedUser()
    {
        $model = app(Guard::class)->getProvider()->getModel();

        return !is_null($this->userId()) &&
            !is_null($model::find($this->userId()));
    }

    /**
     * Get the user that is attempting the two factor challenge.
     *
     * @return mixed
     */
    public function challengedUser()
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }

        $model = app(Guard::class)->getProvider()->getModel();

        if (is_null($this->userId()) ||
            !$user = $model::find($this->userId())
        ) {
            throw new HttpResponseException(
                app(FailedTwoFactorLoginResponse::class)->toResponse($this)
            );
        }

        return $this->challengedUser = $user;
    }

    /**
     * Resolve the user id.
     *
     * @return mixed
     */
    private function userId()
    {
        return Key::from($this->key)->id();
    }
}
