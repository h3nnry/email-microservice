<?php

return [

    'mailjet' => [
        'public_key' => env('MAILJET_API_KEY'),
        'private_key' => env('MAILJET_API_SECRET'),
    ],

    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],

];
