Invisible reCAPTCHA
==========

![invisible_recaptcha_demo](http://i.imgur.com/1dZ9XKn.png)

## Why Invisible reCPATCHA?
Invisible reCAPTCHA is an improved version of reCAPTCHA v2(no captcha).
In reCAPTCHA v2, users need to click the button: "I'm not a robot" to prove they are human. In invisible reCAPTCHA, there will be not embed a captcha box for users to click. It's totally invisible! Only the badge will show on the buttom of the page to hint users that your website is using this technology. (The badge could be hidden, but not commented.)

## Installation

```
composer require albertcht/invisible-recaptcha
```

## Laravel 5

### Setup

Add ServiceProvider to the providers array in `app/config/app.php`.

```
AlbertCht\InvisibleReCaptcha\InvisibleReCaptchaServiceProvider::class,
```

### Configuration

Add `INVISIBLE_RECAPTCHA_SITEKEY`, `INVISIBLE_RECAPTCHA_SECRETKEY` and `INVISIBLE_RECAPTCHA_BADGEHIDE`(optional) to **.env** file.

```
INVISIBLE_RECAPTCHA_SITEKEY={siteKey}
INVISIBLE_RECAPTCHA_SECRETKEY={secretKey}
INVISIBLE_RECAPTCHA_BADGEHIDE=false
```
> If you set `INVISIBLE_RECAPTCHA_BADGEHIDE` to true, you can hide the badge logo.

### Usage

##### Display reCAPTCHA

```php
{!! app('captcha')->render(); !!}
```

With custom language support:

```
{!! app('captcha')->render($lang = null); !!}
```

##### Validation

Add `'g-recaptcha-response' => 'required|captcha'` to rules array.

```php

$validate = Validator::make(Input::all(), [
	'g-recaptcha-response' => 'required|captcha'
]);

```

## Without Laravel

Checkout example below:

```php
<?php

require_once "vendor/autoload.php";

$siteKey = '';
$secretKey = '';
$hideBadge = false;
$captcha = new \AlbertCht\InvisibleReCaptcha\InvisibleReCaptcha($siteKey, $secretKey, $hideBadge);

if (!empty($_POST)) {
    var_dump($captcha->verifyResponse($_POST['g-recaptcha-response']));
    exit();
}

?>

<form action="?" method="POST">
    <?php echo $captcha->display(); ?>
    <button type="submit">Submit</button>
</form>

```
## Notes
* `render()` function needs to be called within a form element.
* There can only be one submit button in this form, and the `type` attribute has to be `submit` as well.
* Don't try to bind any events to your submit button unless you know what you're doing. Otherwise the captcha will probably not take effect.
* Currently not supported for ajax submit.

## Credits 
https://github.com/anhskohbo/no-captcha
