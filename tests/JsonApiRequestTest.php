<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\JsonApiRequest;

class JsonApiRequestTest extends TestCase
{
    /** @test */
    public function it_can_get_requested_fields_from_the_request_body(): void
    {
        config(['json-api-request.request_data_source' => 'body']);

        $request = new JsonApiRequest([], [
            'fields' => [
                'table' => 'name,email',
            ],
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $expected = collect(['table' => ['name', 'email']]);

        $this->assertEquals($expected, $request->fields());
    }

    /** @test */
    public function it_takes_custom_delimiters_for_splitting_request_parameters(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => 'values, contain, commas|and are split on vertical| lines',
            ],
        ]);

        JsonApiRequest::setArrayValueDelimiter('|');

        $expected = ['foo' => ['values, contain, commas', 'and are split on vertical', ' lines']];

        $this->assertEquals($expected, $request->filters()->toArray());
    }
}
