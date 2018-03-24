<?php

namespace AlbertCht\InvisibleReCaptcha;

use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

class InvisibleReCaptcha
{

    const API_URI = 'https://www.google.com/recaptcha/api.js';
    const VERIFY_URI = 'https://www.google.com/recaptcha/api/siteverify';
    const POLYFILL_URI = 'https://cdn.polyfill.io/v2/polyfill.min.js';

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
     *  TODO: render HMTL through template ?
     * @return string
     */
    public function render($lang = null)
    {
        $key = uniqid();
        $html = '';

        if ($this->getOption('hideBadge', false))
        {
            $html .= '<style>.grecaptcha-badge{display:none;!important}</style>' . PHP_EOL;
        }

        $html .= "<script src='https://www.google.com/recaptcha/api.js?render=explicit&hl=$lang' async defer></script>";
        $html .= "<div class='g-recaptcha' id='$key' data-sitekey='$this->siteKey' data-size='invisible' data-badge='bottomleft'></div>";
        $html .= $this->getJsCode();

        return $html;
    }

    /**
     * Return Js Logic
     * @return string
     */
    protected function getJsCode()
    {
        return '<script>window.onload = function () {
                var widgets = {};
                    $(".g-recaptcha").each(function () {
                    var object = $(this);
                    widgets[object.attr("id")] = grecaptcha.render(object.attr("id"), {
                        "callback": function (token) {
                            object.parents("form").find(".g-recaptcha-response").val(token);
                            object.parents("form").submit();
                        }
                    });
                    $("input[type=\'submit\']", object.parents("form")).on("click", function(e){
                        e.preventDefault();
                        grecaptcha.execute(widgets[object.attr("id")]);
                    });
                });
            };
        </script>';
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
        if (empty($response))
        {
            return false;
        }

        $response = $this->sendVerifyRequest([
            'secret'   => $this->secretKey,
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
