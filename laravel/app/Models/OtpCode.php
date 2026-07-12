<?php
// app/Models/OtpCode.php

namespace App\Models;

use App\Exceptions\OtpThrottledException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['phone', 'code', 'purpose', 'expires_at', 'token_used_at', 'attempts', 'service_response', 'verification_token'])]
class OtpCode extends Model
{
    public const TOKEN_HASH_ALGO = "sha256";
    public const MAX_ATTEMPT = 3;
    public const PURPOSE = [
        'password_reset' => 'تغییر رمز عبور',
        'registration' => 'ثبت نام'
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'token_used_at' => 'datetime',
        ];
    }

    /**
     * custom methods
     */

    public function isWithinWaitPeriod(): bool
    {
        return $this->created_at->diffInSeconds(now()) < config('auth.otp.resend_wait');
    }

    public static function getPhoneByToken(string $token): ?string
    {
        return static::query()
            ->where('verification_token', hash(static::TOKEN_HASH_ALGO, $token))
            ->where('token_used_at', '>', now()->subSeconds(config('auth.otp.token_expiry'))) // is not expired
            ->first()?->phone;
    }

    public function scopeIsValid($query)
    {
        return $query
            ->whereNull('token_used_at') // not used
            ->where('attempts', '<', self::MAX_ATTEMPT) // can be attempted
            ->where('created_at', '>', now()->subSeconds(config('auth.otp.expiry'))) // is not expired
        ;
    }
}
