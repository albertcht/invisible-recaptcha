<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use AlbertCht\InvisibleReCaptcha\InvisibleReCaptcha;

class CaptchaTest extends TestCase
{
    const SITE_KEY = 'site_key';
    const SECRET_KEY = 'secret_key';
    const BADGE_HIDE = false;

    protected $captcha;

    public function __construct()
    {
        parent::__construct();
        $this->captcha = new InvisibleReCaptcha(
            self::SITE_KEY,
            self::SECRET_KEY,
            self::BADGE_HIDE
            );
    }

    public function testConstructor()
    {
        $this->assertEquals(self::SITE_KEY, $this->captcha->getSiteKey());
        $this->assertEquals(self::SECRET_KEY, $this->captcha->getSecretKey());
        $this->assertEquals(self::BADGE_HIDE, $this->captcha->getHideBadge());
        $this->assertTrue($this->captcha->getClient() instanceof \GuzzleHttp\Client);
    }

    public function testGetJS()
    {
        $js = 'https://www.google.com/recaptcha/api.js';

        $this->assertEquals($js, $this->captcha->getJs());
        $this->assertEquals($js . '?hl=us', $this->captcha->getJs('us'));
    }
}