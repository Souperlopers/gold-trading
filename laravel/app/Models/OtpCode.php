<?php
// app/Models/OtpCode.php

namespace App\Models;

use App\Exceptions\OtpThrottledException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['phone', 'code', 'purpose', 'expires_at', 'used_at', 'attempts'])]
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

    public static function generateFor(string $phone, string $purpose): self
    {
        $recent = static::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('created_at', '>', now()->subSeconds(60))
            ->exists();

        if ($recent) {
            throw new OtpThrottledException();
        }

        static::where('phone', $phone)->where('purpose', $purpose)
            ->whereNull('used_at')->update(['used_at' => now()]);

        return static::create([
            'phone' => $phone,
            'code' => (string) random_int(100000, 999999),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    /**
     * custom methods
     */

    public function waitNotPassed(): bool
    {
        return $this->created_at->diffInSeconds(now()) < config('services.otp.resend_wait');
    }

    public function scopeIsValid($query)
    {
        return $query
            ->whereNull('used_at') // not used
            ->where('attempts', '<', self::MAX_ATTEMPT) // can be attempted
            ->where('created_at', '>', now()->subSeconds(config('services.otp.expiry'))) // is not expired
        ;
    }
}
