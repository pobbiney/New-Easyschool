<?php

return [

    'public_key' => env('PAYSTACK_PUBLIC_KEY'),

    'secret_key' => env('PAYSTACK_SECRET_KEY'),

    'currency' => env('PAYSTACK_CURRENCY', 'GHS'),

    'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),

    /*
    |--------------------------------------------------------------------------
    | Placeholder Email Domain
    |--------------------------------------------------------------------------
    |
    | Paystack requires an email, even when parents do not have one. We generate
    | addresses like 0244111001@yourdomain.com using the student's phone number.
    |
    */

    'placeholder_domain' => env('PAYSTACK_PLACEHOLDER_DOMAIN', 'easyschool.com'),

];
