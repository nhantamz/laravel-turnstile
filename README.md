# Introduction
The package is based on https://github.com/Scyllaly/hcaptcha but for Cloudflare Turnstile. If you use HCaptcha, please use Scyllaly's package.

Thank you Scyllaly!

## Installation

```
composer require nhantamz/laravel-turnstile
```

## Laravel 5 and above

### Setup

In `app/config/app.php` add the following :

Step 1: The ServiceProvider to the providers array :

```php
Nhantamz\Turnstile\TurnstileServiceProvider::class,
```

Step 2: The class alias to the aliases array :

```php
'Turnstile' => Nhantamz\Turnstile\Facades\Turnstile::class,
```

Step 3: Publish the config file (turnstile.php)

```Shell
php artisan vendor:publish --provider="Nhantamz\Turnstile\TurnstileServiceProvider"
```

### Configuration

Add `TURNSTILE_SECRET`, `TURNSTILE_SITEKEY` and `TURNSTILE_ENABLED` in **.env** file :

```
TURNSTILE_ENABLED=true
TURNSTILE_SITEKEY=site-key
TURNSTILE_SECRET=secret-key
```

(You can obtain them by following the instructions from [Official Developer Guide](https://developers.cloudflare.com/turnstile/get-started/))

### Usage

#### Init js source

With default options :

```php
 {!! Turnstile::renderJs() !!}
```

With callback :

```php
 {!! Turnstile::renderJs(true, 'TurnstileCallback') !!}
```

#### Display Turnstile

Default widget :

```php
{!! Turnstile::display() !!}
```

With [custom attributes](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/) (You can set language, theme, size, appearance in config file or set directly as attributes.) :

```php
{!! Turnstile::display(['class' => 'css-class', 'data-theme' => 'light']) !!}
```

#### Validation

There are two ways to apply Turnstile validation to your form:

#### 1. Basic Approach

This method always applies the Turnstile validation rule.

```php
$validate = Validator::make(Input::all(), [
    'cf-turnstile-response' => 'required|Turnstile'
]);

```

In this approach, the `cf-turnstile-response` field is required and validated using the `Turnstile` rule without any conditions.

#### 2. Conditional Approach

This method applies the Turnstile validation rule only if the `TURNSTILE_ENABLED` environment variable is set to `true`.

```php
$isTurnstileEnabled = env('TURNSTILE_ENABLED');
$rules = [
    // Other validation rules...
];

if ($isTurnstileEnabled) {
    $rules['cf-turnstile-response'] = 'required|Turnstile';
}

$request->validate($rules);

```

In this approach, the `cf-turnstile-response` field will be required and validated using the `Turnstile` rule only when `TURNSTILE_ENABLED` is set to `true`. This adds flexibility to your validation logic, allowing you to enable or disable Turnstile validation as needed.

##### Custom Validation Message

Add the following values to the `custom` array in the `validation` language file :

```php
'custom' => [
    'cf-turnstile-response' => [
        'required' => 'Please verify that you are not a robot.',
        'turnstile' => 'Captcha error! try again later or contact site admin.',
    ],
],
```

Then check for captcha errors in the `Form` :

```php
@if ($errors->has('cf-turnstile-response'))
    <span class="help-block">
        <strong>{{ $errors->first('cf-turnstile-response') }}</strong>
    </span>
@endif
```

## Without Laravel

Checkout example below:

```php
<?php

require_once "vendor/autoload.php";

$secret  = 'CAPTCHA-SECRET';
$sitekey = 'CAPTCHA-SITEKEY';
$captcha = new \Nhantamz\Turnstile\Turnstile($secret, $sitekey);

if (! empty($_POST)) {
    var_dump($captcha->verifyResponse($_POST['cf-turnstile-response']));
    exit();
}

?>

<form action="?" method="POST">
    <?php echo $captcha->display(); ?>
    <button type="submit">Submit</button>
</form>

<?php echo $captcha->renderJs(); ?>
```
