<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Push Token
    |--------------------------------------------------------------------------
    | This token is used to secure the receiver endpoint.
    | The Pusher must include this token in the X-Push-Token header.
    | Set this in your .env file: PUSH_TOKEN=your-secret-token
    */
    'push_token' => env('PUSH_TOKEN', 'change-this-secret-token'),
];
