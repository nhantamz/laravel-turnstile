<?php

namespace Nhantamz\Turnstile;

use Illuminate\Support\ServiceProvider;

class TurnstileServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $app = $this->app;

        $this->bootConfig();

        $app['validator']->extend('Turnstile', function ($attribute, $value) use ($app) {
            return $app['Turnstile']->verifyResponse($value, $app['request']->getClientIp());
        });

        if ($app->bound('form')) {
            $app['form']->macro('Turnstile', function ($attributes = []) use ($app) {
                return $app['Turnstile']->display($attributes, $app->getLocale());
            });
        }
    }

    /**
     * Booting configure.
     */
    protected function bootConfig()
    {
        $path = __DIR__ . '/config/config.php';

        $this->mergeConfigFrom($path, 'turnstile');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('turnstile.php')]);
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('Turnstile', function ($app) {
            return new Turnstile(
				$app['config']['turnstile.secret'],
				$app['config']['turnstile.sitekey'],
				$app['config']['turnstile.options'],
				$app['config']['turnstile.enabled'],
			);
        });

        $this->app->alias('Turnstile', Turnstile::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Turnstile'];
    }
}
