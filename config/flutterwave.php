<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Secret Key
    |--------------------------------------------------------------------------
    | Your secret key from the Flutterwave dashboard.
    | Never expose this key on the client side.
    */
    'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Public Key
    |--------------------------------------------------------------------------
    | Your public key from the Flutterwave dashboard.
    */
    'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Base URL
    |--------------------------------------------------------------------------
    | The base URL for the Flutterwave v3 API.
    */
    'base_url' => env('FLUTTERWAVE_BASE_URL', 'https://api.flutterwave.com/v3'),

    /*
    |--------------------------------------------------------------------------
    | Flutterwave Webhook Secret Hash
    |--------------------------------------------------------------------------
    | Set this in your Flutterwave dashboard and add it to your .env file.
    | Webhooks will be rejected if this value is not configured (except locally).
    */
    'webhook_secret' => env('FLUTTERWAVE_WEBHOOK_SECRET'),

];
