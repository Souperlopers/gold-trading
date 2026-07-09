<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OtpCode;
use App\Rules\NationalIdChecksumRule;
use Illuminate\Validation\Rules\Password;

class ApiRequest extends FormRequest
{
    protected function getRule(string $key)
    {
        return [
            'name'        => ['required', 'string', 'min:3', 'max:191'],
            'phone'       => ['required', 'numeric', 'digits:11', 'starts_with:09'],
            'national_id' => ['required', 'numeric', 'digits:10', new NationalIdChecksumRule()],
            'code'        => ['required', 'digits:6'],
            'purpose'     => ['required', 'in:' . implode(',', array_keys(OtpCode::PURPOSE))],
            'password'    => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'otp_token'   => ['required', 'string', 'size:40'],
            'client'      => ['required', 'string', 'in:mobile,web'],
        ][$key];
    }
}
