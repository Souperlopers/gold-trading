<?php

use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PhoneController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {

    Route::prefix('/phone')->group(function () {
        Route::get('/', [PhoneController::class, 'check']);
        Route::post('/send-otp', [OtpController::class, 'send']);
        Route::post('/verify', [OtpController::class, 'verify']);
    });

    Route::prefix('/auth')->group(function () {
        Route::post('/register', [RegistrationController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::post('/reset-password', [PasswordController::class, 'reset'])->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->prefix('/user')->group(function () {
        Route::get('/', [AuthController::class, 'user']);
    });
});
