<?php

namespace AlbertCht\InvisibleReCaptcha;

use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

class InvisibleReCaptcha
{
    const API_URI = 'https://www.google.com/recaptcha/api.js';
    const VERIFY_URI = 'https://www.google.com/recaptcha/api/siteverify';
    const POLYFILL_URI = 'https://cdn.polyfill.io/v2/polyfill.min.js';
    const DEBUG_ELEMENTS = [
        '_submitForm',
        '_captchaForm',
        '_captchaSubmit'
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
     * The config to determine if hide the badge.
     *
     * @var boolean
     */
    protected $hideBadge;

    /**
     * The config to determine if show debug info.
     *
     * @var boolean
     */
    protected $debug;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * InvisibleReCaptcha.
     *
     * @param string $secretKey
     * @param string $siteKey
     * @param boolean $hideBadge
     */
    public function __construct($siteKey, $secretKey, $hideBadge = false, $debug = false)
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
        $this->hideBadge = $hideBadge;
        $this->debug = $debug;
        $this->client = new Client(['timeout' => 5]);
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
        return $lang ? static::API_URI . '?hl=' . $lang : static::API_URI;
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
    public function render($lang = null)
    {
        $html = '<script src="' . $this->getPolyfillJs() . '"></script>' . PHP_EOL;
        $html .= '<div id="_g-recaptcha"></div>' . PHP_EOL;
        if ($this->hideBadge) {
            $html .= '<style>.grecaptcha-badge{display:none;!important}</style>' . PHP_EOL;
        }
        $html .= '<div class="g-recaptcha" data-sitekey="' . $this->siteKey .'" ';
        $html .= 'data-size="invisible" data-callback="_submitForm"></div>';
        $html .= '<script src="' . $this->getCaptchaJs($lang) . '" async defer></script>' . PHP_EOL;
        $html .= '<script>var _submitForm,_captchaForm,_captchaSubmit;</script>';
        $html .= '<script>window.onload=function(){';
        $html .= '_captchaForm=document.querySelector("#_g-recaptcha").closest("form");';
        $html .= "_captchaSubmit=_captchaForm.querySelector('[type=submit]');";
        $html .= '_submitForm=function(){if(typeof _submitEvent==="function"){_submitEvent();';
        $html .= 'grecaptcha.reset();}else{_captchaForm.submit();}};';
        $html .= "_captchaForm.addEventListener('submit',";
        $html .= "function(event){event.preventDefault();grecaptcha.execute();});";
        if ($this->debug) {
            $html .= $this->renderDebug();
        }
        $html .= "}</script>" . PHP_EOL;

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
            $html .= $this->consoleLog($element . '!==undefined');
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
     * @return strnig
     */
    public function getSiteKey()
    {
        return $this->siteKey;
    }

    /**
     * Getter function of secret key
     *
     * @return strnig
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Getter function of hideBadge
     *
     * @return strnig
     */
    public function getHideBadge()
    {
        return $this->hideBadge;
    }

    /**
     * Getter function of debug
     *
     * @return strnig
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Getter function of guzzle client
     *
     * @return strnig
     */
    public function getClient()
    {
        return $this->client;
    }
}
