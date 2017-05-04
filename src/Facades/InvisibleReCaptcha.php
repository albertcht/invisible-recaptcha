<?php

namespace AlbertCht\InvisibleReCaptcha\Facades;

use Illuminate\Support\Facades\Facade;

class InvisibleReCaptcha extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'captcha';
    }
}
