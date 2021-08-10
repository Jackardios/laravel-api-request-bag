<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\JsonApiRequestServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [JsonApiRequestServiceProvider::class];
    }
}
