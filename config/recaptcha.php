<?php

return [

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Site Key
    |--------------------------------------------------------------------------
    |
    | The site key is used to display the reCAPTCHA widget on your site.
    | It should be pulled from your .env file.
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Secret Key
    |--------------------------------------------------------------------------
    |
    | The secret key is used to communicate between your site and Google.
    | It should be pulled from your .env file.
    |
    */

    'secret_key' => env('RECAPTCHA_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Validation Rule
    |--------------------------------------------------------------------------
    |
    | This is often configured by the package you installed (e.g., anhskohbo/no-captcha).
    | You can add other defaults here if needed by your package.
    |
    */

    'options' => [
        'timeout' => 5,
    ],
];