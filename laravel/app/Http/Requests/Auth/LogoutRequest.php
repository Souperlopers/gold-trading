<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class LogoutRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'client' => $this->getRule('client'),
        ];
    }
}
