<?php

namespace AlbertCht\InvisibleReCaptcha;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class InvisibleReCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Boot the services for the application.
     *
     * @param BladeCompiler $blade
     * @return void
     */
    public function boot(BladeCompiler $blade)
    {
        $app = $this->app;
        $this->bootConfig();
        $this->app['validator']->extend('captcha', function ($attribute, $value) use ($app) {
            return $app['captcha']->verifyResponse($value, $app['request']->getClientIp());
        });

        $this->addBladeDirective($blade);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('captcha', function ($app) {
            return new InvisibleReCaptcha(
                $app['config']['captcha.siteKey'],
                $app['config']['captcha.secretKey'],
                $app['config']['captcha.hideBadge'],
                $app['config']['captcha.debug']
            );
        });
    }

    /**
     * Boot configure.
     *
     * @return void
     */
    protected function bootConfig()
    {
        $path = __DIR__.'/config/captcha.php';

        $this->mergeConfigFrom($path, 'captcha');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('captcha.php')]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['captcha'];
    }

    /**
     * @param BladeCompiler $blade
     * @return void
     */
    public function addBladeDirective(BladeCompiler $blade)
    {
        $blade->directive('captcha', function ($lang) {
            return "<?php echo app('captcha')->render(" . ($lang ? "'$lang'" : '') . '); ?>';
        });
    }
}
