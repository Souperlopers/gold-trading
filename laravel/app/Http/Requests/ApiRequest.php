<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OtpCode;
use App\Rules\NationalIdRule;
use Illuminate\Validation\Rules\Password;

class ApiRequest extends FormRequest
{
    protected function getRule(string $key)
    {
        return [
            'name'        => ['required', 'string', 'min:3', 'max:191'],
            'phone'       => ['required', 'starts_with:09', 'size:11', 'regex:/^09\d{9}$/'],
            'national_id' => ['required', 'unique:users,national_id', 'size:10', new NationalIdRule()],
            'code'        => ['required', 'digits:6'],
            'purpose'     => ['required', 'in:' . implode(',', array_keys(OtpCode::PURPOSE))],
            'password'    => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'otp_token'   => ['required', 'string', 'size:40', 'exists:otp_codes,verification_token'],
            'client'      => ['required', 'string', 'in:mobile,web'],
        ][$key];
    }
}
