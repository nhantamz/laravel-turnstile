<?php

use Nhantamz\Turnstile\Turnstile;

class TurnstileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TurnstileTest
     */
    private $captcha;

    public function setUp()
    {
        parent::setUp();
        $this->captcha = new Turnstile('{secret-key}', '{site-key}');
    }
}
