<?php

namespace Jackardios\JsonApiRequest;

use Illuminate\Support\ServiceProvider;

class JsonApiRequestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole() && function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/json-api-request.php' => config_path('json-api-request.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/json-api-request.php', 'json-api-request');
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
