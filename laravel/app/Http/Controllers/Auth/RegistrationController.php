<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Lang;

class RegistrationController extends Controller
{
    public function register(RegisterRequest $request)
    {
        // extract mobile from verification token received from otp
        if (!$verifiedPhone = OtpCode::getPhoneByToken($request->validated('otp_token'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'otp_token' => Lang::get('validation.custom.verification_token.invalid')
            ]);
        }

        // altough it's checked in otp flow, but here we check if the phone is already registered, we stop the process
        if (User::where('phone', $verifiedPhone)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'phone' => Lang::get('auth.register.already')
            ]);
        }

        // // here I should verify national id, but I skip this part for the moment
        //

        // create new user
        $user = User::create(['phone' => $verifiedPhone, ...$request->registerData()]);

        // start authentication process
        return (new AuthController)->authWithPhonePass($request, $user);
    }
}
