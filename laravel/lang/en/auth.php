<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'phone'   => [
        'taken'     => 'This phone number is already registered.',
        'available' => 'This phone number is available.'
    ],
    'otp' => [
        'send' => [
            'success'      => 'OTP sent successfully.',
            'fail'         => 'OTP API returned error.',
            'timeout'      => 'External service timeout reached! please call the admin.',
            'already_sent' => 'Please wait before requesting another OTP code.',
        ],
        'verify' => [
            'incorrect' => 'Incorrect code.',
        ],
    ],
    'password'     => [
        'reset' => 'Your password has been reset.',
        'token' => 'This password reset token is invalid.',
        'user'  => "We can't find a user with that phone number.",
    ],
    'throttle'     => 'Too many login attempts. Please try again in :seconds seconds.',
    'register' => [
        'already'       => 'You have already registerd, please call admin.',
        'success'       => 'Successful register.',
    ],
    'login' => [
        'success'       => 'Successful login.',
        'failed'        => 'These credentials do not match our records.',
    ],
    'logout' => [
        'success' => 'Successful logout.',
        'failed'  => 'Bearer token mismatch.',
    ],
];
