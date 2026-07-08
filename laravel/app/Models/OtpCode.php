<?php
// app/Models/OtpCode.php

namespace App\Models;

use App\Exceptions\OtpThrottledException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['phone', 'code', 'purpose', 'expires_at', 'used_at', 'attempts', 'service_response', 'verification_token'])]
class OtpCode extends Model
{
    public const MAX_ATTEMPT = 3;
    public const PURPOSE = [
        'password_reset' => 'تغییر رمز عبور',
        'registration' => 'ثبت نام'
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * custom methods
     */

    public function isWithinWaitPeriod(): bool
    {
        return $this->created_at->diffInSeconds(now()) < config('auth.otp.resend_wait');
    }

    public function scopeIsValid($query)
    {
        return $query
            ->whereNull('used_at') // not used
            ->where('attempts', '<', self::MAX_ATTEMPT) // can be attempted
            ->where('created_at', '>', now()->subSeconds(config('auth.otp.expiry'))) // is not expired
        ;
    }
}
