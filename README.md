Invisible reCAPTCHA
==========
![php-badge](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)
[![packagist-badge](https://img.shields.io/packagist/v/albertcht/invisible-recaptcha.svg)](https://packagist.org/packages/albertcht/invisible-recaptcha)
[![travis-badge](https://api.travis-ci.org/albertcht/invisible-recaptcha.svg?branch=master)](https://travis-ci.org/albertcht/invisible-recaptcha)

![invisible_recaptcha_demo](http://i.imgur.com/1dZ9XKn.png)

## Why Invisible reCPATCHA?
Invisible reCAPTCHA is an improved version of reCAPTCHA v2(no captcha).
In reCAPTCHA v2, users need to click the button: "I'm not a robot" to prove they are human. In invisible reCAPTCHA, there will be not embed a captcha box for users to click. It's totally invisible! Only the badge will show on the buttom of the page to hint users that your website is using this technology. (The badge could be hidden, but not suggested.)

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
Before you set your config, remember to choose `invisible reCAPTCHA` while applying for keys.
![invisible_recaptcha_setting](http://i.imgur.com/zIAlKbY.jpg)

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
    <?php echo $captcha->render(); ?>
    <button type="submit">Submit</button>
</form>
```

## Customize Submit Function
If you want to customize your submit function, for example: doing something after click the submit button or changing your submit to ajax call, etc.

The only thing you need to do is to implement `_submitEvent` in javascript
```javascript
_submitEvent = function() {
    console.log('submit button clicked.');
    // write your logic here
}
```
Here's am example to use an ajax submit (using jquery selector)
```javascript
_submitEvent = function() {
    $.ajax({
        type: "POST",
        url: "{{route('message.send')}}",
         data: {
            "name": $("#name").val(),
            "email": $("#email").val(),
            "content": $("#content").val(),
            // important! don't forget to send `g-recaptcha-response`
            "g-recaptcha-response": $("#g-recaptcha-response").val()
        },
        dataType: "json",
        success: function(data) {
            // success logic
        },
        error: function(data) {
            // error logic
        }
    });
};
```


## Notes
* `render()` function needs to be called within a form element.
* There can only be one submit button in this form, and the `type` attribute has to be `submit` as well.

## Credits 
anhskohbo (the author of no-captcha package)
