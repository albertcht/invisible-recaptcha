Invisible reCAPTCHA
==========
![php-badge](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)
[![packagist-badge](https://img.shields.io/packagist/v/albertcht/invisible-recaptcha.svg)](https://packagist.org/packages/albertcht/invisible-recaptcha)
[![travis-badge](https://api.travis-ci.org/albertcht/invisible-recaptcha.svg?branch=master)](https://travis-ci.org/albertcht/invisible-recaptcha)

![invisible_recaptcha_demo](http://i.imgur.com/1dZ9XKn.png)

## Why Invisible reCAPTCHA?
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

Add `INVISIBLE_RECAPTCHA_SITEKEY`, `INVISIBLE_RECAPTCHA_SECRETKEY`, `INVISIBLE_RECAPTCHA_BADGEHIDE`(optional), `INVISIBLE_RECAPTCHA_DEBUG`(optional) to **.env** file.

```
INVISIBLE_RECAPTCHA_SITEKEY={siteKey}
INVISIBLE_RECAPTCHA_SECRETKEY={secretKey}
INVISIBLE_RECAPTCHA_BADGEHIDE=false
INVISIBLE_RECAPTCHA_DEBUG=false
```
> If you set `INVISIBLE_RECAPTCHA_BADGEHIDE` to true, you can hide the badge logo.

> You can see the binding status of those catcha elements on browser console by setting `INVISIBLE_RECAPTCHA_DEBUG` as true.

### Usage

Before you render the captcha, please keep those notices in mind:

* `render()` function needs to be called within a form element.
* You have to ensure the `type` attribute of your submit button has to be `submit`.
* There can only be one submit button in your form.

##### Display reCAPTCHA in Your View

```php
{!! app('captcha')->render(); !!}

// or you can use this in blade
@captcha()
```

With custom language support:

```php
{!! app('captcha')->render($lang = null); !!}

// or you can use this in blade
@captcha($lang = null)
```

##### Validation

Add `'g-recaptcha-response' => 'required|captcha'` to rules array.

```php

$validate = Validator::make(Input::all(), [
    'g-recaptcha-response' => 'required|captcha'
]);

```

## CodeIgniter 3.x

set in application/config/config.php :
```php
$config['composer_autoload'] = TRUE;  //around line 134
```

add lines in application/config/config.php :
```php
$config['recaptcha.sitekey'] = 'keyhere'; 
$config['recaptcha.secret'] = 'secrethere';
$config['recaptcha.badgehide'] = FALSE;
$config['recaptcha.debug'] = FALSE;
```

In controller, use:
```php
$data['captcha'] = new \AlbertCht\InvisibleReCaptcha\InvisibleReCaptcha(
    $this->config->item('recaptcha.sitekey'),
    $this->config->item('recaptcha.secret'),
    $this->config->item('recaptcha.badgehide'),
    $this->config->item('recaptcha.debug'),
);
```

In view, in your form:
```php
<?php echo $captcha->render(); ?>
```

Then back in your controller you can verify it:
```php
$captcha->verifyResponse($_POST['g-recaptcha-response']);
```

## Without Laravel or CodeIgniter

Checkout example below:

```php
<?php

require_once "vendor/autoload.php";

$siteKey = '';
$secretKey = '';
$hideBadge = false;
$debug = false;
$captcha = new \AlbertCht\InvisibleReCaptcha\InvisibleReCaptcha($siteKey, $secretKey, $hideBadge, $debug);

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
    // submit your form
    _submitForm();
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

## Showcases

* [Laravel Boilerplate](https://github.com/Labs64/laravel-boilerplate)

## Credits 

* anhskohbo (the author of no-captcha package)
* [Contributors](https://github.com/albertcht/invisible-recaptcha/graphs/contributors)
