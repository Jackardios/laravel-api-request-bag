<?php

namespace Jackardios\JsonApiRequest\Tests\TestClasses\Requests;

class ExampleJsonApiRequestWithoutDefaultTable extends ExampleJsonApiRequest
{
    protected function defaultTable(): string
    {
        return '';
    }
}
