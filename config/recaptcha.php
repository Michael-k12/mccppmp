<?php

return [

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Site Key (Public)
    |--------------------------------------------------------------------------
    |
    | Used to display the reCAPTCHA widget on your site.
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Secret Key (Private)
    |--------------------------------------------------------------------------
    |
    | Used to communicate between your site and Google for verification.
    |
    */

    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    
    // Add other necessary options for your specific reCAPTCHA package here.
];