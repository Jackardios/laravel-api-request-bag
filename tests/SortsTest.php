<?php

namespace Jackardios\JsonApiRequest\Tests;

use Illuminate\Support\Collection;
use Jackardios\JsonApiRequest\Enums\SortDirection;
use Jackardios\JsonApiRequest\Exceptions\InvalidSortQuery;
use Jackardios\JsonApiRequest\JsonApiRequest;
use Jackardios\JsonApiRequest\Values\Sort;

class SortsTest extends TestCase
{
    protected function getSortsAsArraysFromRequest(JsonApiRequest $request): Collection {
        return $request->sorts()->map(function(Sort $sort) {
            return [$sort->getField(), $sort->getDirection()];
        });
    }

    /** @test */
    public function it_can_get_all_sort_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'sort' => 'fooBarBaz,bar,fooBarBaz,0,-foo_bar',
        ]);

        $expected = collect([
            ['fooBarBaz', SortDirection::ASCENDING],
            ['bar', SortDirection::ASCENDING],
            ['foo_bar', SortDirection::DESCENDING]
        ]);

        $this->assertEquals($expected, $this->getSortsAsArraysFromRequest($request));
    }

    /** @test */
    public function it_can_filter_sort_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'sort' => ['-fooBarBaz', 'bar', null, 'bar', false, 'fooBarBaz', '', 'foo_bar', '0'],
        ]);

        $expected = collect([
            ['fooBarBaz', SortDirection::DESCENDING],
            ['bar', SortDirection::ASCENDING],
            ['foo_bar', SortDirection::ASCENDING]
        ]);

        $this->assertEquals($expected, $this->getSortsAsArraysFromRequest($request));
    }

    /** @test */
    public function it_can_get_allowed_sort_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'sort' => 'fooBarBaz,bar,0,-bar,foo_bar',
        ]);
        $request->setAllowedSorts([
            'fooBarBaz',
            'bar',
            'foo_bar',
            'bar_baz'
        ]);

        $expected = collect([
            ['fooBarBaz', SortDirection::ASCENDING],
            ['bar', SortDirection::ASCENDING],
            ['foo_bar', SortDirection::ASCENDING]
        ]);

        $this->assertEquals($expected, $this->getSortsAsArraysFromRequest($request));
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_sort_query_params_are_passed(): void
    {
        $request = (new JsonApiRequest([
            'sort' => 'fooBarBaz,bar,foo_bar,bar_baz',
        ]));

        $this->expectException(InvalidSortQuery::class);
        $request->setAllowedSorts([
            'fooBarBaz',
            'bar',
            'bar_baz',
        ]);
    }

    /** @test */
    public function it_can_get_different_sort_query_parameter_name(): void
    {
        config(['json-api-request.parameters.sort' => 'sorts']);

        $request = new JsonApiRequest([
            'sorts' => 'fooBarBaz,-bar,foo_bar,bar_baz',
        ]);


        $expected = collect([
            ['fooBarBaz', SortDirection::ASCENDING],
            ['bar', SortDirection::DESCENDING],
            ['foo_bar', SortDirection::ASCENDING],
            ['bar_baz', SortDirection::ASCENDING],
        ]);

        $this->assertEquals($expected, $this->getSortsAsArraysFromRequest($request));
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_sort_query_params_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $this->getSortsAsArraysFromRequest($request));
    }
}
