<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\Exceptions\InvalidIncludeQuery;
use Jackardios\JsonApiRequest\JsonApiRequest;

class IncludesTest extends TestCase
{
    /** @test */
    public function it_can_get_all_include_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'include' => 'fooBarBaz,bar,fooBarBaz,0,foo_bar',
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar']);

        $this->assertEquals($expected, $request->includes());
    }

    /** @test */
    public function it_can_filter_include_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'include' => ['fooBarBaz', 'bar', null, false, 'fooBarBaz', '', 'foo_bar', '0'],
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar']);

        $this->assertEquals($expected, $request->includes());
    }

    /** @test */
    public function it_can_get_allowed_include_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'include' => 'fooBarBaz,bar,0,bar,foo_bar',
        ]);
        $request->setAllowedIncludes([
            'fooBarBaz',
            'bar',
            'foo_bar',
            'bar_baz'
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar']);

        $this->assertEquals($expected, $request->includes());
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_include_query_params_are_passed(): void
    {
        $request = (new JsonApiRequest([
            'include' => 'fooBarBaz,bar,foo_bar,bar_baz',
        ]));

        $this->expectException(InvalidIncludeQuery::class);
        $request->setAllowedIncludes([
            'fooBarBaz',
            'bar',
            'bar_baz',
        ]);
    }

    /** @test */
    public function it_can_get_different_include_query_parameter_name(): void
    {
        config(['json-api-request.parameters.include' => 'include_it']);

        $request = new JsonApiRequest([
            'include_it' => 'fooBarBaz,bar,foo_bar,bar_baz',
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar', 'bar_baz']);

        $this->assertEquals($expected, $request->includes());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_include_query_params_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $request->includes());
    }
}
