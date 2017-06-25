<?php

return [
    'siteKey' => config('app.invisible_recaptcha_sitekey', env('INVISIBLE_RECAPTCHA_SITEKEY')),
    'secretKey' => config('app.invisible_recaptcha_secretkey', env('INVISIBLE_RECAPTCHA_SECRETKEY')),
    'hideBadge' => config('app.invisible_recaptcha_badgehide', env('INVISIBLE_RECAPTCHA_BADGEHIDE', false)),
    'debug' => config('app.invisible_recaptcha_debug', env('INVISIBLE_RECAPTCHA_DEBUG', false))
];
