<?php

namespace App\Http\Requests\Auth;

use App\Helpers\SanitizeHelper;
use App\Http\Requests\ApiRequest;

class CheckPhoneRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'phone' => $this->getRule('phone'),
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
            'phone' => $this->validated('phone'),
        ];
    }
}
