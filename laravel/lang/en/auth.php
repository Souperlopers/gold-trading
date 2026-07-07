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
        'taken' => 'This phone number is already registered.',
        'available' => 'This phone number is available.'
    ],
    'otp' => [
        'send' => [
            'success' => 'OTP sent successfully.',
            'fail' => 'OTP API returned error.',
            'timeout' => 'External service timeout reached! please call admin.',
            'already_sent' => 'Please wait before requesting another OTP code.',
        ]
    ],
];
