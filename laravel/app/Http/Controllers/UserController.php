<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Lang;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegisterRequest $request)
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
        $user = new User([
            "name" => $request->validated('name'),
            "phone" => $verifiedPhone,
            "national_id" => $request->validated('national_id'),
        ]);
        $user->password = $request->validated('password');
        $user->role = 'viewer';
        $user->save();

        // start authentication process
        return (new AuthController)->authWithPhonePass($request, $user);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
