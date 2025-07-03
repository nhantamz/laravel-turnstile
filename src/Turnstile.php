<?php

namespace Nhantamz\Turnstile;

use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

class Turnstile
{
    const CLIENT_API = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * The Turnstile secret key.
     *
     * @var string
     */
    protected $secret;

    /**
     * The Turnstile sitekey key.
     *
     * @var string
     */
    protected $sitekey;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * Whether to use Turnstile or not.
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Turnstile.
     *
     * @param string $secret
     * @param string $sitekey
     * @param array  $options
     * @param bool   $enabled
     */
    public function __construct($secret, $sitekey, $options = [], $enabled = true)
    {
        $this->secret = $secret;
        $this->sitekey = $sitekey;
        $this->http = new Client($options);
        $this->enabled = $enabled;
    }

    /**
     * Prepare HTML attributes and assure that the correct classes and attributes for captcha are inserted.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function prepareAttributes(array $attributes)
    {
        $attributes['data-sitekey'] = $this->sitekey;

        if (!isset($attributes['class']))
            $attributes['class'] = '';

         $attributes['class'] = trim('cf-turnstile ' . $attributes['class']);
		
		if (!isset($attributes['data-theme']))
			$attributes['data-theme'] = config('turnstile.theme', 'auto');
		
		if (!isset($attributes['data-language']))
			$attributes['data-language'] = config('turnstile.language', 'auto');
		
		if (!isset($attributes['data-size']))
			$attributes['data-size'] = config('turnstile.size', 'normal');

        return $attributes;
    }

    /**
     * Build HTML attributes.
     *
     * @param array $attributes
     *
     * @return string
     */
    protected function buildAttributes(array $attributes)
    {
        $html = [];

        foreach ($attributes as $key => $value) {
            $html[] = $key . '="' . $value . '"';
        }

        return count($html) ? ' ' . implode(' ', $html) : '';
    }
	
	/**
     * Render js source
     *
     * @param null   $lang
     * @param bool   $callback
     * @param string $onLoadClass
     *
     * @return string
     */
    public function renderJs($callback = false, $onLoadClass = 'onloadTurnstileCallback')
    {
        if (!$this->enabled) {
            return '';
        }

        return '<script src="' . $this->getJsLink($callback, $onLoadClass) . '" async defer></script>' . "\n";
    }

    /**
     * Get Turnstile js link.
     *
     * @param string  $lang
     * @param boolean $callback
     * @param string  $onLoadClass
     *
     * @return string
     */
    public function getJsLink($callback = false, $onLoadClass = 'onloadTurnstileCallback')
    {
        if (!$this->enabled) {
            return '';
        }

        $client_api = static::CLIENT_API;
        $params = [];

        $callback ? $this->setCallBackParams($params, $onLoadClass) : false;

        return $client_api . '?' . http_build_query($params);
    }
	
    /**
     * Render HTML captcha.
     *
     * @param array $attributes
     *
     * @return string
     */
    public function display($attributes = [])
    {
        if (!$this->enabled) {
            return '';
        }

        $attributes = $this->prepareAttributes($attributes);
        return '<div' . $this->buildAttributes($attributes) . '></div>';
    }

    /**
     * @param $params
     * @param $onLoadClass
     */
    protected function setCallBackParams(&$params, $onLoadClass)
    {
        $params['render'] = 'explicit';
        $params['onload'] = $onLoadClass;
    }
	
	/**
     * Send verify request.
     *
     * @param array $query
     *
     * @return array
     */
    protected function sendRequestVerify(array $query = [])
    {
        $response = $this->http->request('POST', static::VERIFY_URL, [
            'form_params' => $query,
        ]);

        return json_decode($response->getBody(), true);
    }
	
    /**
     * Verify Turnstile response.
     *
     * @param string $response
     * @param string $clientIp
     *
     * @return bool
     */
    public function verifyResponse($response, $clientIp = null)
    {
        if (!$this->enabled) {
            return true; // Always true if Turnstile is disabled
        }

        if (empty($response)) {
            return false;
        }

        $verifyResponse = $this->sendRequestVerify([
            'secret'   => $this->secret,
            'response' => $response,
            'remoteip' => $clientIp,
        ]);

        if (isset($verifyResponse['success']) && $verifyResponse['success'] === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verify Turnstile response by Symfony Request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return $this->verifyResponse(
            $request->get('cf-turnstile-response'),
            $request->getClientIp()
        );
    }
}
