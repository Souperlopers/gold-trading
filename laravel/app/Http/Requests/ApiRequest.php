<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OtpCode;

class ApiRequest extends FormRequest
{
    protected function getRule(string $key)
    {
        return [
            'phone' => ['required', 'starts_with:09', 'size:11', 'regex:/^09\d{9}$/'],

            // otp code purpose
            'purpose' => ['required', 'in:' . implode(',', array_keys(OtpCode::PURPOSE))],

            'code' => ['required', 'digits:6'],
        ][$key];
    }
}
