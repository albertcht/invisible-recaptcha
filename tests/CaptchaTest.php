<?php

namespace Tests;

use AlbertCht\InvisibleReCaptcha\InvisibleReCaptchaServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\TestCase;
use AlbertCht\InvisibleReCaptcha\InvisibleReCaptcha;

class CaptchaTest extends TestCase
{
    const SITE_KEY = 'site_key';
    const SECRET_KEY = 'secret_key';
    const BADGE_HIDE = false;
    const DEBUG = false;

    protected $captcha;

    protected function setUp()
    {
        $this->captcha = new InvisibleReCaptcha(
            static::SITE_KEY,
            static::SECRET_KEY,
            static::BADGE_HIDE,
            static::DEBUG
        );
    }

    public function testConstructor()
    {
        $this->assertEquals(static::SITE_KEY, $this->captcha->getSiteKey());
        $this->assertEquals(static::SECRET_KEY, $this->captcha->getSecretKey());
        $this->assertEquals(static::BADGE_HIDE, $this->captcha->getHideBadge());
        $this->assertEquals(static::DEBUG, $this->captcha->getDebug());
        $this->assertTrue($this->captcha->getClient() instanceof \GuzzleHttp\Client);
    }

    public function testGetJS()
    {
        $js = 'https://www.google.com/recaptcha/api.js';

        $this->assertEquals($js, $this->captcha->getJs());
        $this->assertEquals($js . '?hl=us', $this->captcha->getJs('us'));
    }

    public function testBladeDirective()
    {
        $app = Container::getInstance();
        $app->instance('captcha', $this->captcha);

        $blade = new BladeCompiler(
            $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock(),
            __DIR__
        );

        $serviceProvider = new InvisibleReCaptchaServiceProvider($app);
        $serviceProvider->addBladeDirective($blade);

        $result = $blade->compileString('@captcha()');
        $this->assertEquals(
            "<?php echo app('captcha')->render(); ?>",
            $result
        );
    }
}
