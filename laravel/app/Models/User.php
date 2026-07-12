<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'phone', 'password', 'role', 'national_id', 'phone_verified_at', 'national_id_verified_at', 'approved_at', 'approved_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'phone_verified_at'       => 'datetime',
            'national_id_verified_at' => 'datetime',
            'approved_at'             => 'datetime',
            'password'                => 'hashed',
        ];
    }

    public const ROLES = ['owner', 'admin', 'trader', 'viewer'];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canTrade(): bool
    {
        return $this->approved_at !== null;
    }
}
