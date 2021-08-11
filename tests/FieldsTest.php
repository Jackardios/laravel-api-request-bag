<?php

namespace Jackardios\JsonApiRequest\Tests;

use Jackardios\JsonApiRequest\Exceptions\DefaultTableIsNotDefined;
use Jackardios\JsonApiRequest\Exceptions\InvalidFieldQuery;
use Jackardios\JsonApiRequest\JsonApiRequest;

class FieldsTest extends TestCase
{
    protected function createJsonApiRequestWithFields($fields = null, $key = 'fields'): JsonApiRequest {
        $request = new JsonApiRequest([
            $key => $fields
        ]);

        $request->setDefaultTable('default_table_name');

        return $request;
    }

    /** @test */
    public function it_can_get_all_fields_query_params_from_the_request(): void
    {
        $request = $this->createJsonApiRequestWithFields([
            'default_table_name' => 'name,email,phone_number',
            'another_table' => 'title,content,created_at',
        ]);

        $expected = collect([
            'default_table_name' => ['name','email','phone_number'],
            'another_table' => ['title','content','created_at'],
        ]);

        $this->assertEquals($expected, $request->fields());
    }

    /** @test */
    public function it_can_filter_fields_query_params_from_the_request(): void
    {
        $request = $this->createJsonApiRequestWithFields([
            'default_table_name' => ['name','email','',null,'phone_number','email', 0],
            'another_table' => ['title',null,'content',false,'created_at'],
            'third_table' => ''
        ]);

        $expected = collect([
            'default_table_name' => ['name','email','phone_number'],
            'another_table' => ['title','content','created_at'],
        ]);

        $this->assertEquals($expected, $request->fields());
    }

    /** @test */
    public function it_can_get_allowed_fields_query_params_from_the_request(): void
    {
        $request = $this->createJsonApiRequestWithFields([
            'default_table_name' => 'name,email,phone_number',
            'another_table' => 'title,content,created_at',
        ]);
        $request->setAllowedFields([
            'name',
            'email',
            'phone_number',
            'another_table.title',
            'another_table.content',
            'another_table.created_at',
        ]);

        $expected = collect([
            'default_table_name' => ['name','email','phone_number'],
            'another_table' => ['title','content','created_at'],
        ]);

        $this->assertEquals($expected, $request->fields());
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_fields_query_params_are_passed(): void
    {
        $request = $this->createJsonApiRequestWithFields([
            'default_table_name' => 'name,email,phone_number,password',
            'another_table' => 'title,content,created_at',
        ]);

        $this->expectException(InvalidFieldQuery::class);
        $request->setAllowedFields([
            'name',
            'email',
            'phone_number',
            'another_table.title',
            'another_table.content',
            'another_table.created_at',
        ]);
    }

    /** @test */
    public function it_throws_an_exception_when_not_allowed_relation_fields_query_params_are_passed(): void
    {
        $request = $this->createJsonApiRequestWithFields([
            'default_table_name' => 'name,email,phone_number',
            'another_table' => 'title,content,created_at,secret_field',
        ]);

        $this->expectException(InvalidFieldQuery::class);
        $request->setAllowedFields([
            'name',
            'email',
            'phone_number',
            'secret_field',
            'another_table.title',
            'another_table.content',
            'another_table.created_at',
        ]);
    }

    /** @test */
    public function it_throws_an_exception_when_no_default_table_defined(): void
    {
        $request = new JsonApiRequest([
            'fields' => [
                'default_table_name' => 'name,email,phone_number',
                'another_table' => 'title,content,created_at',
            ]
        ]);

        $this->expectException(DefaultTableIsNotDefined::class);
        $request->setAllowedFields([
            'name',
            'email',
            'phone_number',
            'another_table.title',
            'another_table.content',
            'another_table.created_at',
        ]);
    }

    /** @test */
    public function it_can_get_different_fields_query_parameter_name(): void
    {
        config(['json-api-request.parameters.fields' => 'select']);

        $request = $this->createJsonApiRequestWithFields([
            'default_table_name' => 'name,email,phone_number',
            'another_table' => 'title,content,created_at',
        ], 'select');


        $expected = collect([
            'default_table_name' => ['name','email','phone_number'],
            'another_table' => ['title','content','created_at'],
        ]);

        $this->assertEquals($expected, $request->fields());
    }

    /** @test */
    public function it_will_return_an_empty_collection_when_no_fields_query_params_are_specified(): void
    {
        $request = $this->createJsonApiRequestWithFields();

        $expected = collect();

        $this->assertEquals($expected, $request->fields());
    }
}
