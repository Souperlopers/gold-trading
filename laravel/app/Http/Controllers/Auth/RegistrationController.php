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
        if (!$verifiedPhone = OtpCode::getPhoneByToken($request->validated('otp_token'))) {
            return response()->json([
                'message' => Lang::get('validation.custom.verification_token.invalid'),
            ], 422);
        }

        // // here I should verify national id, but I skip this part for the moment
        //

        $user = User::create(['phone' => $verifiedPhone, ...$request->registerData()]);

        return (new AuthController)->authWithPhonePass($request, $user);
    }
}
