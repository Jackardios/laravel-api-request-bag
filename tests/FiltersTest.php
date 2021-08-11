<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\Exceptions\InvalidFilterQuery;
use Jackardios\JsonApiRequest\JsonApiRequest;

class FiltersTest extends TestCase
{
    protected function getNestedFilter(): array {
        return [
            'request' => [
                'bar' => [
                    'baz' => [
                        'first' => 'some_text',
                        'second' => 'false',
                        'third' => null,
                        'fourth' => 'true',
                        'fifth' => ['fooBar','bar_baz','bat','false','qwerty-asd','true']
                    ]
                ]
            ],
            'expected' => [
                'bar' => [
                    'baz' => [
                        'first' => 'some_text',
                        'second' => false,
                        'third' => null,
                        'fourth' => true,
                        'fifth' => ['fooBar','bar_baz','bat',false,'qwerty-asd',true]
                    ]
                ]
            ]
        ];
    }

    /** @test */
    public function it_can_get_all_filters_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => $this->getNestedFilter()['request'],
                'another_filter' => 'false',
                'secret_filter' => 'secret',
            ]
        ]);

        $expected = collect([
            'foo' => $this->getNestedFilter()['expected'],
            'another_filter' => false,
            'secret_filter' => 'secret',
        ]);

        $this->assertEquals($expected->toArray(), $request->filters()->toArray());
    }

    /** @test */
    public function it_can_get_allowed_filters_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => $this->getNestedFilter()['request'],
                'another_filter' => 'false',
                'secret_filter' => 'secret',
            ]
        ]);

        $request->setAllowedFilters([
            'foo',
            'secret_filter',
            'another_filter'
        ]);

        $expected = collect([
            'foo' => $this->getNestedFilter()['expected'],
            'another_filter' => false,
            'secret_filter' => 'secret',
        ]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_filters_are_passed(): void
    {
        $request = new JsonApiRequest([
            'filter' => [
                'foo' => $this->getNestedFilter()['request'],
                'another_filter' => 'false',
                'secret_filter' => 'secret',
            ]
        ]);

        $this->expectException(InvalidFilterQuery::class);

        $request->setAllowedFilters([
            'foo',
            'another_filter'
        ]);
    }

    /** @test */
    public function it_can_get_different_filter_query_parameter_name(): void
    {
        config(['json-api-request.parameters.filter' => 'filters']);

        $request = new JsonApiRequest([
            'filters' => [
                'foo' => $this->getNestedFilter()['request'],
                'another_filter' => 'false',
                'secret_filter' => 'secret',
            ]
        ]);

        $expected = collect([
            'foo' => $this->getNestedFilter()['expected'],
            'another_filter' => false,
            'secret_filter' => 'secret',
        ]);

        $this->assertEquals($expected, $request->filters());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_filters_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $request->filters());
    }
}
