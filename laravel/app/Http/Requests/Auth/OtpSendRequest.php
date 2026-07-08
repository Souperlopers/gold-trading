<?php

namespace App\Http\Requests\Auth;

use App\Helpers\SanitizeHelper;
use App\Http\Requests\ApiRequest;

class OtpSendRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'purpose' => $this->getRule('purpose'),
            'phone' => array_merge(
                $this->getRule('phone'),
                ($this->input('purpose') === 'registration') ? ['unique:users,phone'] : []
            ),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => SanitizeHelper::sanitizePhone($this->phone),
        ]);
    }
}
