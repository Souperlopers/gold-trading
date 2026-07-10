<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Laravel\Sanctum\PersonalAccessToken;

class PasswordController extends Controller
{
    public function reset(PasswordResetRequest $request)
    {
        // extract mobile from verification token received from otp
        if (!$verifiedPhone = OtpCode::getPhoneByToken($request->validated('otp_token'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'otp_token' => Lang::get('auth.password.token')
            ]);
        }

        // altough it's checked in otp flow, but here we check if the phone isn't registered, we stop the process
        $user = User::query()->where('phone', $verifiedPhone)->first();
        if (!$user) {
            return response()->json([
                'message' => Lang::get('auth.password.user'),
            ], 404);
        }

        // update user password
        $user->update([
            'password' => $request->validated('password')
        ]);

        // logout other sessions 
        if ($request->validated('logout_others')) {
            $token = $request->bearerToken();
            $tokenId = PersonalAccessToken::findToken($token)?->id;
            $user->tokens()->whereNot('id', $tokenId)->delete();
        }

        // send response
        return response()->json([
            'message' => Lang::get('auth.password.reset')
        ], 200);
    }
}
