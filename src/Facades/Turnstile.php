<?php

namespace Nhantamz\Turnstile\Facades;

use Illuminate\Support\Facades\Facade;

class Turnstile extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Turnstile';
    }
}
