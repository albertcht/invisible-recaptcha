<?php

namespace AlbertCht\InvisibleReCaptcha;

use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

class InvisibleReCaptcha
{
    const API_URI = 'https://www.google.com/recaptcha/api.js';
    const VERIFY_URI = 'https://www.google.com/recaptcha/api/siteverify';

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
     * Render HTML reCaptcha by optional language param.
     *
     * @return string
     */
    public function render($lang = null, $nonce = null)
    {
        $html = $this->renderPolyfill();
        $html .= $this->renderCaptchaHTML($lang, $nonce);
        // $html .= $this->renderFooterJS($lang, $nonce);
        return $html;
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

    //https://gist.github.com/benzkji/4078af592c97810fdb141b5937a9aaf9
    //https://developers.google.com/recaptcha/docs/invisible#examples
    //https://stackoverflow.com/questions/43231850/how-to-add-multiple-invisible-recaptcha-in-single-page/46621784#46621784

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
        $html .= "<div class='_g-recaptcha' id='_g-recaptcha_{$this->renderedTimes}'></div>" . PHP_EOL;

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
        $nonce = ( isset($nonce) && ! empty($nonce) ) ? "nonce=\"$nonce\"" : '';
        $src = $this->getCaptchaJs($lang);

        // if ($this->getOption('debug', false)) {
        //     $html .= $this->renderDebug();
        // }

        if ( $this->getOption('hideBadge', false) ) {
$badge = <<<EOD
        _captchaBadge=document.querySelector('.grecaptcha-badge');
        if(_captchaBadge){_captchaBadge.style = 'display:none !important;';}            
EOD;
        } else { $badge = ''; }

$html = <<<EOD
<script src="{$src}" async defer {$nonce}></script>
<script>var _submitForm,_captchaCallback,_captchaForm,_execute=true,_captchaBadge;</script>
<script>
    window.addEventListener('load', _loadCaptcha);
    function {
        {$badge}
        _captchaForms=$("._g-recaptcha").closest("form");
        _captchaForms.each(function(){
            $(this)[0].addEventListener('submit', function(e) {
                e.preventDefault();
                if(typeof _beforeSubmit==='function') {
                    _execute=_beforeSubmit(e);
                }
                if(_execute){
                    grecaptcha.execute();
                }
            });
        });
        _submitForm=function(){
            if(typeof _submitEvent === "function"){
                _submitEvent();
                grecaptcha.reset();
            } else { 
                _captchaForm.submit(); 
            } 
        };

        _captchaCallback=function(){
            grecaptcha.render("_g-recaptcha_"+_renderedTimes,{
                sitekey:'{$this->siteKey}',
                size:'invisible',
                callback:_submitForm
            });
        }
    }
</script>
EOD;

        return $html;
    }

    public function initRender($lang)
    {
        $src = $this->getCaptchaJs($lang);

$html = <<<EOD
<script>var _renderedTimes,_captchaCallback,_captchaForms,_submitForm,_submitBtn;</script>
<script>var _submitAction=true,_captchaForm;</script>
<script>
$.getScript('$src').done(function(data,status,jqxhr){
    _renderedTimes=$("._g-recaptcha").length;
    $("._g-recaptcha").closest("form").each(function( index ){
        $(this)[0].addEventListener("submit",function(e){
            e.preventDefault();
            _captchaForm=$(this);
            _submitBtn=$(this).find(":submit");
            grecaptcha.execute();
        });
    });
    
    _submitForm=function(){
        _submitBtn.trigger("captcha");
        if(_submitAction){
            _captchaForm.submit();
        }
        grecaptcha.reset();
    };
    _captchaCallback=function(){
        grecaptcha.render("_g-recaptcha_"+_renderedTimes,{
            sitekey:'{$this->siteKey}',
            size:'invisible',
            callback:_submitForm
        });
    }
});
</script>
EOD;
        $this->renderedTimes++;
        return $html;
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
