<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    protected function getRule(string $key)
    {
        return [
            'phone' => ['required', 'starts_with:09', 'size:11', 'regex:/^09\d{9}$/'],
        ][$key];
    }
}
