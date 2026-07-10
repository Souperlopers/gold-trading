<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // drop request if phone or password is invalid
        if (! Auth::attempt($request->validatedUserData())) {
            return response()->json([
                'message' => Lang::get('auth.login.failed'),
            ], 422);
        }

        return $this->authWithPhonePass($request, Auth::user());
    }

    // accessed from LoginController and RegisterController
    public function authWithPhonePass(LoginRequest|RegisterRequest $request, User $user)
    {
        $isLogin = $request instanceof LoginRequest;
        $message = Lang::get($isLogin ? 'auth.login.success' : 'auth.register.success');

        // Web client -> session cookie
        if ($request->validated('client') === 'web') {
            Auth::login($user);
            $request->session()->regenerate();
            return response()->json([
                'message' => $message,
                'user'    => new UserResource($user),
            ]);
        }

        // Mobile client -> token
        if ($request->validated('client') === 'mobile') {
            return response()->json([
                'message' => $message,
                'token'   => $user->createToken('auth-token')->plainTextToken,
                'user'    => new UserResource($user),
            ], $isLogin ? 200 : 201);
        }
    }

    public function logout(LogoutRequest $request)
    {
        if ($request->validated('client') === 'mobile') {
            if (
                PersonalAccessToken::findToken(
                    $request->bearerToken()
                )?->delete()
            ) {
                return response()->json([
                    'message' => Lang::get('auth.logout.success'),
                ], 200);
            }

            return response()->json([
                'message' => Lang::get('auth.logout.failed'),
            ], 200);
        }
    }
}
