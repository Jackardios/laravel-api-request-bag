<?php

namespace Jackardios\JsonApiRequest;

use Illuminate\Support\ServiceProvider;

class JsonApiRequestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole() && function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/api-request-bag.php' => config_path('api-request-bag.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(__DIR__.'/../config/api-request-bag.php', 'api-request-bag');
    }

    public function register()
    {
        $this->app->bind(JsonApiRequest::class, function ($app) {
            return JsonApiRequest::fromRequest($app['request']);
        });
    }

    public function provides()
    {
        return [
            JsonApiRequest::class,
        ];
    }
}
