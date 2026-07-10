<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PhoneCheckController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(function () {
    Route::get('/phone', PhoneCheckController::class);
    Route::post('/phone/send-otp', [OtpController::class, 'send']);
    Route::post('/phone/verify', [OtpController::class, 'verify']);

    Route::post('/register', [RegistrationController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
})->middleware('throttle:10,1');

Route::prefix('/user')->group(function () {
    Route::get('/', [AuthController::class, 'user']);
})->middleware(['auth:sanctum', 'throttle:5,1']);
