<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckPhoneRequest;
use App\Models\User;
use Illuminate\Support\Facades\Lang;

class PhoneCheckController extends Controller
{
    public function __invoke(CheckPhoneRequest $request)
    {
        $phone = $request->validated('phone');
        $exists = User::where('phone', $phone)->exists();

        return response()->json([
            'available' => ! $exists,
            'message'   => Lang::get(
                $exists
                    ? 'auth.phone.taken'
                    : 'auth.phone.available'
            )
        ]);
    }
}
