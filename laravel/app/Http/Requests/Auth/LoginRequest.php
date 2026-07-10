<?php

namespace App\Http\Requests\Auth;

use App\Helpers\SanitizeHelper;
use App\Http\Requests\ApiRequest;

class LoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'phone'    => array_merge($this->getRule('phone'), ['exists:users,phone']),
            'password' => $this->getRule('password'),
            'client' => $this->getRule('client'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => SanitizeHelper::sanitizePhone($this->phone),
        ]);
    }

    public function validatedUserData(): array
    {
        return [
            'phone'    => $this->validated('phone'),
            'password' => $this->validated('password'),
        ];
    }
}
