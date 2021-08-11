<?php
namespace Jackardios\JsonApiRequest\Tests\TestClasses\Requests;

use Jackardios\JsonApiRequest\JsonApiRequest;

class ExampleJsonApiRequest extends JsonApiRequest
{
    protected function allowedFilters(): array
    {
        return ['id', 'name', 'email'];
    }

    protected function allowedFields(): array
    {
        return [
            'id',
            'name',
            'email',
            'is_admin',
            'created_at',
            'updated_at',
            'another_table.title',
            'another_table.content',
            'another_table.created_at',
        ];
    }

    protected function allowedSorts(): array
    {
        return ['id', 'name', 'created_at', 'updated_at'];
    }

    protected function allowedIncludes(): array
    {
        return ['friends', 'roles'];
    }

    protected function allowedAppends(): array
    {
        return ['full_name', 'another_attribute'];
    }

    // You can use it like FormRequest and define any of its methods

    public function rules(): array
    {
        return [
            'appends' => 'nullable|array',
            'appends.*' => 'string',
            'fields' => 'nullable|array',
            'fields.users.*' => 'nullable|array|string',
            'filter' => 'nullable|array',
            'filter.ids' => 'array',
            'filter.ids.*' => 'integer|exists:users,id',
            'filter.name' => 'string',
            'filter.email' => 'string',
            'includes' => 'nullable|array',
            'includes.*' => 'string',
            'sorts' => 'nullable|array',
            'sorts.*' => 'string',
            'another_parameter' => 'nullable|string'
        ];
    }

    // ...
}
