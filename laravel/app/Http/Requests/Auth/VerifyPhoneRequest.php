<?php

namespace App\Http\Requests\Auth;

use App\Helpers\SanitizeHelper;
use App\Http\Requests\ApiRequest;

class VerifyPhoneRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'phone' => $this->getRule('phone'),
            'code' => array_merge($this->getRule('code'), ['exists:otp_codes,verification_token']),
            'purpose' => $this->getRule('purpose'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => SanitizeHelper::sanitizePhone($this->phone),
        ]);
    }
}
