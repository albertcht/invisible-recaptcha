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
     * The other config options.
     *
     * @var array
     */
    protected $options;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

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
        if ($this->getOption('hideBadge', false)) {
            $html .= '<style>.grecaptcha-badge{display:none;!important}</style>' . PHP_EOL;
        }
        $html .= '<div class="g-recaptcha" data-sitekey="' . $this->siteKey .'" ';
        $html .= 'data-size="invisible" data-callback="_submitForm" data-badge="' . $this->getOption('dataBadge', 'bottomright') . '"></div>';
        $html .= '<script src="' . $this->getCaptchaJs($lang) . '" async defer></script>' . PHP_EOL;
        $html .= '<script>var _submitForm,_captchaForm,_captchaSubmit,_execute=true;</script>';
		//beginn ie patch for closest
		$html.= '<script>if (window.Element && !Element.prototype.closest) {';
		$html.= 'Element.prototype.closest = ';
		$html.= 'function(s) {';
		$html.= '	var matches = (this.document || this.ownerDocument).querySelectorAll(s),';
		$html.= '		i,';
		$html.= '		el = this;';
		$html.= '	do {';
		$html.= '		i = matches.length;';
		$html.= '		while (--i >= 0 && matches.item(i) !== el) {};';
		$html.= '	} while ((i < 0) && (el = el.parentElement)); ';
		$html.= '	return el;';
		$html.= '};';
		$html.= '}';
		//end ie patch for closest		
        $html .= '<script>window.onload=function(){';
        $html .= '_captchaForm=document.querySelector("#_g-recaptcha").closest("form");';
        $html .= "_captchaSubmit=_captchaForm.querySelector('[type=submit]');";
        $html .= '_submitForm=function(){if(typeof _submitEvent==="function"){_submitEvent();';
        $html .= 'grecaptcha.reset();}else{_captchaForm.submit();}};';
        $html .= "_captchaForm.addEventListener('submit',";
        $html .= "function(e){e.preventDefault();if(typeof _beforeSubmit==='function'){";
        $html .= "_execute=_beforeSubmit();}if(_execute){grecaptcha.execute();}});";
        if ($this->getOption('debug', false)) {
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
     * @return strnig
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
     * Set guzzle client
     *
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
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
