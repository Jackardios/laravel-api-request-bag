<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\Exceptions\InvalidAppendQuery;
use Jackardios\JsonApiRequest\JsonApiRequest;

class AppendsTest extends TestCase
{
    /** @test */
    public function it_can_get_all_append_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'append' => 'fooBarBaz,bar,fooBarBaz,0,foo_bar',
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar']);

        $this->assertEquals($expected, $request->appends());
    }

    /** @test */
    public function it_can_filter_append_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'append' => ['fooBarBaz', 'bar', null, false, 'fooBarBaz', '', 'foo_bar', '0'],
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar']);

        $this->assertEquals($expected, $request->appends());
    }

    /** @test */
    public function it_can_get_allowed_append_query_params_from_the_request(): void
    {
        $request = new JsonApiRequest([
            'append' => 'fooBarBaz,bar,0,bar,foo_bar',
        ]);
        $request->setAllowedAppends([
            'fooBarBaz',
            'bar',
            'foo_bar',
            'bar_baz'
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar']);

        $this->assertEquals($expected, $request->appends());
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_append_query_params_are_passed(): void
    {
        $request = (new JsonApiRequest([
            'append' => 'fooBarBaz,bar,foo_bar,bar_baz',
        ]));

        $this->expectException(InvalidAppendQuery::class);
        $request->setAllowedAppends([
            'fooBarBaz',
            'bar',
            'bar_baz',
        ]);
    }

    /** @test */
    public function it_can_get_different_append_query_parameter_name(): void
    {
        config(['json-api-request.parameters.append' => 'append_it']);

        $request = new JsonApiRequest([
            'append_it' => 'fooBarBaz,bar,foo_bar,bar_baz',
        ]);

        $expected = collect(['fooBarBaz', 'bar', 'foo_bar', 'bar_baz']);

        $this->assertEquals($expected, $request->appends());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_append_query_params_are_specified(): void
    {
        $request = new JsonApiRequest();

        $expected = collect();

        $this->assertEquals($expected, $request->appends());
    }
}
