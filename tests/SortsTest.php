<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\Exceptions\InvalidSortQuery;
use Jackardios\JsonApiRequest\JsonApiRequest;

class SortsTest extends TestCase
{
    /** @test */
    public function it_can_get_all_sort_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'sort' => 'fooBarBaz,bar,fooBarBaz,0,-foo_bar',
        ]);

        $expected = collect(['fooBarBaz', 'bar', '-foo_bar']);

        $this->assertEquals($expected, $request->sorts());
    }

    /** @test */
    public function it_can_filter_sort_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'sort' => ['-fooBarBaz', 'bar', null, 'bar', false, 'fooBarBaz', '', 'foo_bar', '0'],
        ]);

        $expected = collect(['-fooBarBaz', 'bar', 'foo_bar']);

        $this->assertEquals($expected, $request->sorts());
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

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar']);

        $this->assertEquals($expected, $request->sorts());
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
            'sorts' => 'fooBarBaz,bar,foo_bar,bar_baz',
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar', 'bar_baz']);

        $this->assertEquals($expected, $request->sorts());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_sort_query_params_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $request->sorts());
    }
}
