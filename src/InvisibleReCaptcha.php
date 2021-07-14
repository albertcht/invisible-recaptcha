<?php

namespace AlbertCht\InvisibleReCaptcha;

use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

class InvisibleReCaptcha
{
    const API_URI = 'https://www.google.com/recaptcha/api.js';
    const VERIFY_URI = 'https://www.google.com/recaptcha/api/siteverify';
    const POLYFILL_URI = 'https://cdn.polyfill.io/v2/polyfill.min.js';
    const DEBUG_ELEMENTS = [
        '_submitForm',
        '_captchaCallback'
    ];

    /**
     * The reCaptcha site key.
     *
     * @var string
     */
    protected $siteKey;

    /**
     * The reCaptcha secret key.
     *
     * @var string
     */
    protected $secretKey;

    /**
     * The other config options.
     *
     * @var array
     */
    protected $options;

    /**
     * Rendered number in total.
     *
     * @var integer
     */
    protected $renderedTimes = 0;

    /**
     * InvisibleReCaptcha.
     *
     * @param string $secretKey
     * @param string $siteKey
     * @param array $options
     */
    public function __construct($siteKey, $secretKey, $options = [])
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
        $this->setOptions($options);
        $this->setClient(
            new Client([
                'timeout' => $this->getOption('timeout', 5)
            ])
        );
    }

    /**
     * Get reCaptcha js by optional language param.
     *
     * @param string $lang
     *
     * @return string
     */
    public function getCaptchaJs($lang = null)
    {
        $api = static::API_URI . '?onload=_captchaCallback&render=explicit';
        return $lang ? $api . '&hl=' . $lang : $api;
    }

    /**
     * Get polyfill js
     *
     * @return string
     */
    public function getPolyfillJs()
    {
        return static::POLYFILL_URI;
    }

    /**
     * Render HTML reCaptcha by optional language param.
     *
     * @return string
     */
    public function render($lang = null, $nonce = null)
    {
        return $this->renderCaptchaHTML($lang, $nonce);
    }

    /**
     * Render HTML reCaptcha from directive.
     *
     * @return string
     */
    public function renderCaptcha(...$arguments)
    {
        return $this->render(...$arguments);
    }

    /**
     * Render the polyfill JS components only.
     *
     * @return string
     */
    public function renderPolyfill()
    {
        return '<script src="' . $this->getPolyfillJs() . '"></script>' . PHP_EOL;
    }

    /**
     * Render the captcha HTML.
     *
     * @return string
     */
    public function renderCaptchaHTML(...$arguments)
    {
        $html = '';
        if ($this->renderedTimes === 0) {
            $html .= $this->renderFooterJS(...$arguments);
            if ($this->getOption('hideBadge', false)) {
                $html .= '<style>.grecaptcha-badge{display:none !important;}</style>' . PHP_EOL;
            }
        } else {
            $this->renderedTimes++;
        }
        $html .= "<div class='_g-recaptcha' id='_g-recaptcha_{$this->renderedTimes}' data-badge='{$this->getOption('dataBadge', 'bottomright')}'></div>" . PHP_EOL;

        return $html;
    }

    /**
     * Render the footer JS necessary for the recaptcha integration.
     *
     * @return string
     */
    public function renderFooterJS(...$arguments)
    {
        $lang = Arr::get($arguments, 0);
        $nonce = Arr::get($arguments, 1);
        $src = $this->getCaptchaJs($lang);

        if ($this->getOption('debug', false)) {
            $debug = $this->renderDebug();
        } else { $debug = ''; }



        if ( $this->getOption('hideBadge', false) ) {
$badge = <<<EOD
        _captchaBadge=document.querySelector('.grecaptcha-badge');
        if(_captchaBadge){_captchaBadge.style = 'display:none !important;';}
EOD;
        } else { $badge = ''; }

        if ( $this->getOption('lazyLoad', false) ) {
$eventListener = <<<EOD
        window.addEventListener('scroll', loadReCaptcha);
        window.addEventListener('click', loadReCaptcha);
        window.addEventListener('keydown', loadReCaptcha);
EOD;
        } else {
$eventListener = <<<EOD
        window.addEventListener('load', loadReCaptcha);
EOD;
        }

$html = <<<EOD
{$this->renderPolyfill()}
<script>
    var _executed = false;
    var _execute = true;
    var loadReCaptcha = function (event) {
        if ( _executed ) return;
        {$badge}
        window._renderedTimes=$("._g-recaptcha").length;
        _captchaForms=$("._g-recaptcha").closest("form");
        _captchaForms.each(function(){
            $(this)[0].addEventListener('submit', function(e) {
                e.preventDefault();
                if(typeof _beforeSubmit==='function') {
                    _execute=_beforeSubmit(e);
                }
                if(_execute){
                    _captchaForm=$(this);
                    grecaptcha.execute();
                }
            });
        });
        window._submitForm=function(){
            if(typeof _submitEvent === "function"){
                _submitEvent();
                grecaptcha.reset();
            } else {
                _captchaForm.submit();
            }
        };
        window._captchaCallback = function(e) {
            grecaptcha.render("_g-recaptcha_"+_renderedTimes,{
                sitekey:'{$this->siteKey}',
                size:'invisible',
                callback:window._submitForm
            });
        }
        {$debug}
        $.ajax({
            dataType : "script",
            url      : "{$src}",
            attrs    : {
                nonce: "{$nonce}",
                defer: 1
            },
        });
        _executed = true;
    };

    {$eventListener}
</script>
EOD;
        $this->renderedTimes++;
        return $html;
    }

    /**
     * Get debug javascript code.
     *
     * @return string
     */
    public function renderDebug()
    {
        $html = '';
        foreach (static::DEBUG_ELEMENTS as $element) {
            $html .= $this->consoleLog('"Checking element binding of ' . $element . '..."');
            $html .= $this->consoleLog("window.$element!==undefined");
        }

        return $html;
    }

    /**
     * Get console.log function for javascript code.
     *
     * @return string
     */
    public function consoleLog($string)
    {
        return "console.log({$string});";
    }

    /**
     * Verify invisible reCaptcha response.
     *
     * @param string $response
     * @param string $clientIp
     *
     * @return bool
     */
    public function verifyResponse($response, $clientIp)
    {
        if (empty($response)) {
            return false;
        }

        $response = $this->sendVerifyRequest([
            'secret' => $this->secretKey,
            'remoteip' => $clientIp,
            'response' => $response
        ]);

        return isset($response['success']) && $response['success'] === true;
    }

    /**
     * Verify invisible reCaptcha response by Symfony Request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return $this->verifyResponse(
            $request->get('g-recaptcha-response'),
            $request->getClientIp()
        );
    }

    /**
     * Send verify request.
     *
     * @param array $query
     *
     * @return array
     */
    protected function sendVerifyRequest(array $query = [])
    {
        $response = $this->client->post(static::VERIFY_URI, [
            'form_params' => $query,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Getter function of site key
     *
     * @return string
     */
    public function getSiteKey()
    {
        return $this->siteKey;
    }

    /**
     * Getter function of secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set options
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Set option
     *
     * @param string $key
     * @param string $value
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Getter function of options
     *
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get default option value for options. (for support under PHP 7.0)
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function getOption($key, $value = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $value;
    }

    /**
     * Getter function of guzzle client
     *
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set guzzle client
     *
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Getter function of rendered times
     *
     * @return string
     */
    public function getRenderedTimes()
    {
        return $this->renderedTimes;
    }
}
