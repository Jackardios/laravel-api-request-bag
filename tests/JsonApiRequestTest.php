<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\JsonApiRequest;

class JsonApiRequestTest extends TestCase
{
    /** @test */
    public function it_can_filter_nested_arrays(): void
    {
        $expected = [
            'info' => [
                'foo' => [
                    'bar' => 1,
                ],
            ],
        ];

        $request = new JsonApiRequest([
            'filter' => $expected,
        ]);

        $this->assertEquals($expected, $request->filters()->toArray());
    }

    /** @test */
    public function it_can_get_empty_filters_recursively(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'info' => [
                    'foo' => [
                        'bar' => null,
                    ],
                ],
            ],
        ]);

        $expected = [
            'info' => [
                'foo' => [
                    'bar' => '',
                ],
            ],
        ];

        $this->assertEquals($expected, $request->filters()->toArray());
    }

    /** @test */
    public function it_will_map_true_and_false_as_booleans_recursively(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'info' => [
                    'foo' => [
                        'bar' => 'true',
                        'baz' => 'false',
                        'bazs' => '0',
                    ],
                ],
            ],
        ]);

        $expected = [
            'info' => [
                'foo' => [
                    'bar' => true,
                    'baz' => false,
                    'bazs' => '0',
                ],
            ],
        ];

        $this->assertEquals($expected, $request->filters()->toArray());
    }

    /** @test */
    public function it_can_get_the_sort_query_param_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'sort' => 'foobar',
        ]);

        $this->assertEquals(['foobar'], $request->sorts()->toArray());
    }

    /** @test */
    public function it_can_get_the_sort_query_param_from_the_request_body(): void
    {
        config(['json-api-request.request_data_source' => 'body']);

        $request = new JsonApiRequest([], [
            'sort' => 'foobar',
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $this->assertEquals(['foobar'], $request->sorts()->toArray());
    }

    /** @test */
    public function it_can_get_different_sort_query_parameter_name(): void
    {
        config(['json-api-request.parameters.sort' => 'sorts']);

        $request = new JsonApiRequest([
            'sorts' => 'foobar',
        ]);

        $this->assertEquals(['foobar'], $request->sorts()->toArray());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_sort_query_param_is_specified(): void
    {
        $request = new JsonApiRequest();

        $this->assertEmpty($request->sorts());
    }

    /** @test */
    public function it_can_get_multiple_sort_parameters_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'sort' => 'foo,bar',
        ]);

        $expected = collect(['foo', 'bar']);

        $this->assertEquals($expected, $request->sorts());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_sort_query_params_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $request->sorts());
    }

    /** @test */
    public function it_can_get_the_filter_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ]);

        $expected = collect([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_can_get_the_filter_query_params_from_the_request_body(): void
    {
        config(['json-api-request.request_data_source' => 'body']);

        $request = new JsonApiRequest([], [
            'filter' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $expected = collect([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_can_get_different_filter_query_parameter_name(): void
    {
        config(['json-api-request.parameters.filter' => 'filters']);

        $request = new JsonApiRequest([
            'filters' => [
                'foo' => 'bar',
                'baz' => 'qux,lex',
            ],
        ]);

        $expected = collect([
            'foo' => 'bar',
            'baz' => ['qux', 'lex'],
        ]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_can_get_empty_filters(): void
    {
        config(['json-api-request.parameters.filter' => 'filters']);

        $request = new JsonApiRequest([
            'filters' => [
                'foo' => 'bar',
                'baz' => null,
            ],
        ]);

        $expected = collect([
            'foo' => 'bar',
            'baz' => '',
        ]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_filter_query_params_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_will_map_true_and_false_as_booleans_when_given_in_a_filter_query_string(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => 'true',
                'bar' => 'false',
                'baz' => '0',
            ],
        ]);

        $expected = collect([
            'foo' => true,
            'bar' => false,
            'baz' => '0',
        ]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_will_map_comma_separated_values_as_arrays_when_given_in_a_filter_query_string(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => 'bar,baz',
            ],
        ]);

        $expected = collect(['foo' => ['bar', 'baz']]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_will_map_array_in_filter_recursively_when_given_in_a_filter_query_string(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => 'bar,baz',
                'bar' => [
                    'foobar' => 'baz,bar',
                ],
            ],
        ]);

        $expected = collect(['foo' => ['bar', 'baz'], 'bar' => ['foobar' => ['baz', 'bar']]]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_will_map_comma_separated_values_as_arrays_when_given_in_a_filter_query_string_and_get_those_by_key(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => 'bar,baz',
            ],
        ]);

        $expected = ['foo' => ['bar', 'baz']];

        $this->assertEquals($expected, $request->filters()->toArray());
    }

    /** @test */
    public function it_can_get_the_include_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'include' => 'foo,bar',
        ]);

        $expected = collect(['foo', 'bar']);

        $this->assertEquals($expected, $request->includes());
    }

    /** @test */
    public function it_can_get_the_include_from_the_request_body(): void
    {
        config(['json-api-request.request_data_source' => 'body']);

        $request = new JsonApiRequest([], [
            'include' => 'foo,bar',
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $expected = collect(['foo', 'bar']);

        $this->assertEquals($expected, $request->includes());
    }

    /** @test */
    public function it_can_get_different_include_query_parameter_name(): void
    {
        config(['json-api-request.parameters.include' => 'includes']);

        $request = new JsonApiRequest([
            'includes' => 'foo,bar',
        ]);

        $expected = collect(['foo', 'bar']);

        $this->assertEquals($expected, $request->includes());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_include_query_params_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $request->includes());
    }

    /** @test */
    public function it_can_get_requested_fields(): void
    {
        $request = new JsonApiRequest([
            'fields' => [
                'table' => 'name,email',
            ],
        ]);

        $expected = collect(['table' => ['name', 'email']]);

        $this->assertEquals($expected, $request->fields());
    }

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
    public function it_can_get_different_fields_parameter_name(): void
    {
        config(['json-api-request.parameters.fields' => 'field']);

        $request = new JsonApiRequest([
            'field' => [
                'column' => 'name,email',
            ],
        ]);

        $expected = collect(['column' => ['name', 'email']]);

        $this->assertEquals($expected, $request->fields());
    }

    /** @test */
    public function it_can_get_the_append_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'append' => 'foo,bar',
        ]);

        $expected = collect(['foo', 'bar']);

        $this->assertEquals($expected, $request->appends());
    }

    /** @test */
    public function it_can_get_different_append_query_parameter_name(): void
    {
        config(['json-api-request.parameters.append' => 'appendit']);

        $request = new JsonApiRequest([
            'appendit' => 'foo,bar',
        ]);

        $expected = collect(['foo', 'bar']);

        $this->assertEquals($expected, $request->appends());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_append_query_params_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $request->appends());
    }

    /** @test */
    public function it_can_get_the_append_query_params_from_the_request_body(): void
    {
        config(['json-api-request.request_data_source' => 'body']);

        $request = new JsonApiRequest([], [
            'append' => 'foo,bar',
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $expected = collect(['foo', 'bar']);

        $this->assertEquals($expected, $request->appends());
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

    /** @test */
    public function it_adds_any_appends_as_they_come_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'append' => 'aCamelCaseAppend,anotherappend',
        ]);

        $expected = collect(['aCamelCaseAppend', 'anotherappend']);

        $this->assertEquals($expected, $request->appends());
    }
}
