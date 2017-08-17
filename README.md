Invisible reCAPTCHA
==========
![php-badge](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)
[![packagist-badge](https://img.shields.io/packagist/v/albertcht/invisible-recaptcha.svg)](https://packagist.org/packages/albertcht/invisible-recaptcha)
[![travis-badge](https://api.travis-ci.org/albertcht/invisible-recaptcha.svg?branch=master)](https://travis-ci.org/albertcht/invisible-recaptcha)

![invisible_recaptcha_demo](http://i.imgur.com/1dZ9XKn.png)

## Notice
* This branch is for multi-forms purpose.
* In most of cases, there should be only one captcha in your page. You should use master branch normally.
* **Do not use multiple captchas in one page for protecting every form far from bots**, it will cause terrible user experience.

## Usage Example
```
{!! Form::open(['url' => '/', 'id' => 'form1']) !!}
@captcha()
{!! Form::submit('Sumbit', ['id'=>'s1']) !!}
{!! Form::close() !!}

{!! Form::open(['url' => '/']) !!}
@captcha()
{!! Form::submit('Sumbit2', ['id'=>'s2']) !!}
{!! Form::close() !!}
```
> Just call captcha function in forms directly, it will render only one captcha and all the forms will share the same captcha validation.

```
<script type="text/javascript">
    $('#s2').on('captcha', function(e) {
        // set it as false if your don't want to submit your from directly
        _submitAction = false;
        // do other stuff
    });
</script>
```
> In this branch, you can cutomize your submit behavior by listening a captcha event.

## Diffs
* There's no `INVISIBLE_RECAPTCHA_DEBUG` config in this branch.
* This package will include `jquery 3.2.1` instead of `pilyfill.js`.

### This branch is still under develop, welcome for any deg report or advice.
