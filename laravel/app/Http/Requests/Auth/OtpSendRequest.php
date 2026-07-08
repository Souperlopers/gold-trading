<?php

namespace App\Http\Requests\Auth;

use App\Helpers\SanitizeHelper;
use App\Http\Requests\ApiRequest;

class OtpSendRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'phone' => $this->getRule('phone'),
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
