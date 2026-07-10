<?php

namespace App\Http\Requests\Auth;

use App\Helpers\SanitizeHelper;
use App\Http\Requests\ApiRequest;

class PasswordResetRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'password'      => array_merge($this->getRule('password'), ['confirmed']),
            'logout_others' => $this->getRule('logout_others'),
            'otp_token'     => $this->getRule('otp_token'),
        ];
    }
}
