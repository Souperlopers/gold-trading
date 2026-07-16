<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SimpleRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // drop request if phone or password is invalid
        $user = User::where('phone', $request->validated('phone'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw new \Illuminate\Auth\AuthenticationException(
                Lang::get('auth.login.failed')
            );
        }

        return $this->authWithPhonePass($request, $user);
    }

    // accessed from LoginController and RegisterController
    public function authWithPhonePass(LoginRequest|RegisterRequest $request, User $user)
    {
        $isLogin = $request instanceof LoginRequest;
        $message = Lang::get($isLogin ? 'auth.login.success' : 'auth.register.success');

        // Mobile client -> token (for now this will run on both web and mobile untill I add session support)
        if ($request->validated('client')) {
            return response()->json([
                'message' => $message,
                'token'   => $user->createToken('auth-token')->plainTextToken,
                'user'    => new UserResource($user),
            ], $isLogin ? 200 : 201);
        }

        // Web client -> session cookie
        if ($request->validated('client') === 'web') {
            Auth::login($user);
            $request->session()->regenerate();
            return response()->json([
                'message' => $message,
                'user'    => new UserResource($user),
            ]);
        }
    }

    public function logout(SimpleRequest $request)
    {
        if ($request->validated('client') === 'mobile') {
            if (
                $request->user()->currentAccessToken()->delete()
            ) {
                return response()->json([
                    'message' => Lang::get('auth.logout.success'),
                ], 200);
            }
        }
    }
}
