<?php

namespace App\Http\Requests\Auth;

use App\Helpers\SanitizeHelper;
use App\Http\Requests\ApiRequest;

class RegisterRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name'        => $this->getRule('name'),
            'password'    => $this->getRule('password'),
            'national_id' => array_merge($this->getRule('national_id'), ['unique:users,national_id']),
            'otp_token'   => $this->getRule('otp_token'),
            'client'      => $this->getRule('client'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone'       => SanitizeHelper::sanitizePhone($this->phone),
            'national_id' => SanitizeHelper::sanitizeNationalCode($this->national_id),
        ]);
    }

    public function registerData(): array
    {
        return [
            'name'     => $this->validated('name'),
            'password' => $this->validated('password'),
            'national_id' => $this->validated('national_id'),
        ];
    }
}
