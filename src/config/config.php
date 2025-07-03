<?php

return [
    'enabled'           => env('TURNSTILE_ENABLED', true), //Enable or disable TURNSTILE for development environments
    'sitekey'           => env('TURNSTILE_SITEKEY'),
    'secret'            => env('TURNSTILE_SECRET'),
    'theme' 			=> 'auto', // light, dark, auto
    'language' 			=> 'auto', // en, vn, ... or en-us, ... | https://developers.cloudflare.com/turnstile/reference/supported-languages/
    'size' 				=> 'flexible', // normal, flexible, compact | https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#widget-size
	'options'           => [
        'timeout' => 30, // HTTP Client options
    ],
];
