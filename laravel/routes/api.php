<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PhoneCheckController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(function () {
    Route::get('/phone', PhoneCheckController::class);
    Route::post('/phone/send-otp', [OtpController::class, 'send']);
    Route::post('/phone/verify', [OtpController::class, 'verify']);
})->middleware('throttle:10,1');
