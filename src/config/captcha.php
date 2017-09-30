<?php

return [
    // your recapthca site key.
    'siteKey' => env('INVISIBLE_RECAPTCHA_SITEKEY'),
    // your recapthca secret key.
    'secretKey' => env('INVISIBLE_RECAPTCHA_SECRETKEY'),
    // other options to customize your configs
    'options' => [
        // set true if you want to hide your recaptcha badge
        'hideBadge' => env('INVISIBLE_RECAPTCHA_BADGEHIDE', false),
        // optional, reposition the reCAPTCHA badge. 'inline' allows you to control the CSS.
        // available values: bottomright, bottomleft, inline
        'dataBadge' => env('INVISIBLE_RECAPTCHA_DATABADGE', 'bottomright'),
        // timeout value for guzzle client
        'timeout' => env('INVISIBLE_RECAPTCHA_TIMEOUT', 5),
        // set true to show binding status on your javascript console
        'debug' => env('INVISIBLE_RECAPTCHA_DEBUG', false)
    ]
];
