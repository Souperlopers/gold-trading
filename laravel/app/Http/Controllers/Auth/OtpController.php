<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpSendRequest;
use App\Http\Requests\Auth\VerifyPhoneRequest;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function __construct(
        private OtpService $service
    ) {}

    private function callService(OtpCode $otp)
    {
        $service = $this->service;

        // send the OTP via SMS
        $result = $service(
            phone: $otp->phone,
            operation: OtpCode::PURPOSE[$otp->purpose],
            code: $otp->code,
        );

        $otp->update([
            'service_response' => $result['body']['data']['messageId'] ?? -1,
        ]);

        $response = [
            'message' => Lang::get($result['success']
                ? 'auth.otp.send.success'
                : 'auth.otp.send.fail'),
        ];

        if ($result['success']) {
            $response['expires_at'] = $otp->created_at->addSeconds((int) config('auth.otp.expiry'));
        }

        return response()->json($response, $result['success'] ? 200 : 503);
    }

    public function send(OtpSendRequest $request)
    {
        // Check for existing OTP
        $otp = OtpCode
            ::where('phone', $phone = $request->validated('phone'))
            ->where('purpose', $purpose = $request->validated('purpose'))
            ->isValid()->first();

        // return last code if its not expired
        if ($otp?->isWithinWaitPeriod()) {
            return response()->json([
                'message' => Lang::get('auth.otp.send.already_sent')
            ], 429);
        }

        return $this->callService($otp ?? OtpCode::create([
            'phone'   => $phone,
            'code'    => random_int(100000, 999999),
            'purpose' => $purpose,
        ]));
    }

    public function verify(VerifyPhoneRequest $request)
    {
        $otp = OtpCode
            ::where('phone', $request->validated('phone'))
            ->where('purpose', $request->validated('purpose'))
            ->isValid()->first();

        if (!$otp) {
            return response()->json([
                'message' => Lang::get('auth.otp.verify.invalid')
            ], 422);
        }

        $otp->increment('attempts');

        if (!hash_equals($otp->code, $request->validated('code'))) {
            return response()->json([
                'message' => Lang::get('auth.otp.verify.incorrect')
            ], 422);
        }

        $otp->update([
            'used_at' => now(),
            'verification_token' => hash('sha256', $token = Str::random(40))
        ]);

        return response()->json([
            'verification_token' => $token,
            'expires_in' => config('auth.otp.token_expiry')
        ], 200);
    }
}
