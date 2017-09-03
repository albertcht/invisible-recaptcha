<?php

return [
    'siteKey' => env('INVISIBLE_RECAPTCHA_SITEKEY'),
    'secretKey' => env('INVISIBLE_RECAPTCHA_SECRETKEY'),
    'hideBadge' => env('INVISIBLE_RECAPTCHA_BADGEHIDE', false),
    'dataBadge' => env('INVISIBLE_RECAPTCHA_DATABADGE', 'bottomright'),
    'debug' => env('INVISIBLE_RECAPTCHA_DEBUG', false)
];
