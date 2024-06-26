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
    const OPTIONS = [
        'hideBadge' => false,
        'dataBadge' => 'bottomright',
        'timeout' => 5,
        'debug' => false
    ];

    protected $captcha;

    protected function setUp(): void
    {
        $this->captcha = new InvisibleReCaptcha(
            static::SITE_KEY,
            static::SECRET_KEY,
            static::OPTIONS
        );
    }

    public function testConstructor()
    {
        $this->assertEquals(static::SITE_KEY, $this->captcha->getSiteKey());
        $this->assertEquals(static::SECRET_KEY, $this->captcha->getSecretKey());
        $this->assertTrue($this->captcha->getClient() instanceof \GuzzleHttp\Client);
    }

    public function testGetOptions()
    {
        $this->assertEquals(static::OPTIONS, $this->captcha->getOptions());
    }

    public function testSetOption()
    {
        $this->captcha->setOption('debug', true);
        $this->captcha->setOption('timeout', 10);
        $this->assertEquals(10, $this->captcha->getOption('timeout'));
        $this->assertTrue($this->captcha->getOption('debug'));
    }

    public function testGetCaptchaJs()
    {
        $js = 'https://www.google.com/recaptcha/api.js';

        $this->assertEquals($js, $this->captcha->getCaptchaJs());
        $this->assertEquals($js . '?hl=us', $this->captcha->getCaptchaJs('us'));
    }

    public function testJavascriptHasNonce()
    {
        $this->assertStringContainsString('nonce="nonce-ASDFGHJKL"', $this->captcha->renderFooterJS('us', 'nonce-ASDFGHJKL'));
    }

    public function testGetPolyfillJs()
    {
        $js = 'https://cdnjs.cloudflare.com/polyfill/v2/polyfill.min.js';

        $this->assertEquals($js, $this->captcha->getPolyfillJs());
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

        $this->assertEquals(
            "<?php echo app('captcha')->renderCaptcha(); ?>",
            $blade->compileString('@captcha()')
        );

        $this->assertEquals(
            "<?php echo app('captcha')->renderCaptcha('us'); ?>",
            $blade->compileString("@captcha('us')")
        );

        $this->assertEquals(
            "<?php echo app('captcha')->renderCaptcha('us', 'nonce-ASDFGHJKL'); ?>",
            $blade->compileString("@captcha('us', 'nonce-ASDFGHJKL')")
        );

        $this->assertEquals(
            "<?php echo app('captcha')->renderPolyfill(); ?>",
            $blade->compileString('@captchaPolyfill()')
        );

        $this->assertEquals(
            "<?php echo app('captcha')->renderCaptchaHTML(); ?>",
            $blade->compileString('@captchaHTML()')
        );

        $this->assertEquals(
            "<?php echo app('captcha')->renderFooterJS(); ?>",
            $blade->compileString('@captchaScripts()')
        );

        $this->assertEquals(
            "<?php echo app('captcha')->renderFooterJS('us'); ?>",
            $blade->compileString("@captchaScripts('us')")
        );

        $this->assertEquals(
            "<?php echo app('captcha')->renderFooterJS('us', 'nonce-ASDFGHJKL'); ?>",
            $blade->compileString("@captchaScripts('us', 'nonce-ASDFGHJKL')")
        );
    }
}
