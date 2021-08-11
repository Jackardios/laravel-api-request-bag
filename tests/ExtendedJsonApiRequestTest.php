<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\Exceptions\InvalidAppendQuery;
use Jackardios\JsonApiRequest\Exceptions\InvalidFieldQuery;
use Jackardios\JsonApiRequest\Exceptions\InvalidFilterQuery;
use Jackardios\JsonApiRequest\Exceptions\InvalidIncludeQuery;
use Jackardios\JsonApiRequest\Exceptions\InvalidSortQuery;
use Jackardios\JsonApiRequest\Tests\TestClasses\Requests\ExampleJsonApiRequest;

class ExtendedJsonApiRequestTest extends TestCase
{
    protected function createExampleJsonApiRequest(?array $data = null): ExampleJsonApiRequest
    {
        return (new ExampleJsonApiRequest($data))->setDefaultTable('default_table_name');
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_append_query_params_are_passed(): void
    {
        $request = $this->createExampleJsonApiRequest([
            'append' => 'full_name,another_attribute,unallowed_attribute',
        ]);

        $this->expectException(InvalidAppendQuery::class);

        $request->appends();
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_fields_query_params_are_passed(): void
    {
        $request = $this->createExampleJsonApiRequest([
            'fields' => [
                'default_table_name' => 'name,email,phone_number,is_admin',
                'another_table' => 'title,content,created_at',
            ]
        ]);

        $this->expectException(InvalidFieldQuery::class);

        $request->fields();
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_filter_query_params_are_passed(): void
    {
        $request = $this->createExampleJsonApiRequest([
            'filter' => [
                'id' => 1,
                'unallowed_field' => 'unallowed_value',
            ],
        ]);

        $this->expectException(InvalidFilterQuery::class);

        $request->filters();
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_include_query_params_are_passed(): void
    {
        $request = $this->createExampleJsonApiRequest([
            'include' => 'roles,unallowedIncludes',
        ]);

        $this->expectException(InvalidIncludeQuery::class);

        $request->includes();
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_sort_query_params_are_passed(): void
    {
        $request = $this->createExampleJsonApiRequest([
            'sort' => 'id,name,unallowed_query',
        ]);

        $this->expectException(InvalidSortQuery::class);

        $request->sorts();
    }
}
