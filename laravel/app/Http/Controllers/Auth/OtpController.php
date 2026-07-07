<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpSendRequest;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Http\Request;
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
        $otp->service_response = $result['body']['data']['messageId'] ?? -1;
        $otp->save();

        return response()->json(
            [
                'message'   => Lang::get($result['success'] ? 'auth.otp.send.success' : 'auth.otp.send.fail'),
                'expires_at' => $result['success'] && $otp->created_at->addMinutes(config('services.otp.timeout')),
            ],
            Lang::get($result['success'] ? 200 : 503)
        );
    }

    public function send(OtpSendRequest $request)
    {
        // Check for existing OTP
        $otp = OtpCode
            ::where('phone', $phone = $request->validated('phone'))
            ->where('purpose', $purpose = $request->validated('purpose'))
            ->isValid()->first();

        // return last code if its not expired
        if ($otp?->waitNotPassed()) {
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
}
