# Laravel JSON API Request
This package will help you filter and prepare request parameters
(parameter names correspond to [JSON API specification](https://jsonapi.org/)).

Much of this package comes from [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder), but unlike it, this package gives you complete control over how your requests are handled.

## Installation
1) Install the package via composer:
```bash
composer require jackardios/laravel-json-api-request
```
The package will automatically register its service provider.

2) (Optional) You can publish the config file with
```bash
php artisan vendor:publish --provider="Jackardios\JsonApiRequest\JsonApiRequestServiceProvider" --tag="config"
```
These are the contents of the default config file that will be published:
```php
<?php

return [

    /*
     * By default the package will use the `include`, `filter`, `sort`
     * and `fields` query parameters as described in the readme.
     *
     * You can customize these query string parameters here.
     */
    'parameters' => [
        'include' => 'include',

        'filter' => 'filter',

        'sort' => 'sort',

        'fields' => 'fields',

        'append' => 'append',
    ],

    /*
     * By default the package will throw an `InvalidFilterQuery` exception when a filter in the
     * URL is not allowed in the `getAllowedFilters()` method.
     */
    'disable_invalid_filter_query_exception' => false,

    /*
     * By default the package inspects query string of request using $request->query().
     * You can change this behavior to inspect the request body using $request->input()
     * by setting this value to `body`.
     *
     * Possible values: `query_string`, `body`
     */
    'request_data_source' => 'query_string',
];
```

## Use cases
### Use JsonApiRequest directly
Example:
```php
use Jackardios\JsonApiRequest\JsonApiRequest;

$request = app(JsonApiRequest::class)
    ->setAllowedFilters('id', 'name', 'email')
    ->setAllowedFields('id', 'name', 'email', 'is_admin', 'created_at', 'updated_at')
    ->setAllowedSorts('id', 'name', 'created_at', 'updated_at')
    ->setAllowedIncludes('friends')
    ->setAllowedAppends('full_name');

// All of these methods below return Illuminate\Support\Collection (https://laravel.com/docs/8.x/collections)
$fields = $request->fields();
$filters = $request->filters();
$sorts = $request->sorts();
$includes = $request->includes();
$appends = $request->appends();
```

### Or make new class, extend it with JsonApiRequest and use it like [FormRequest](https://laravel.com/docs/8.x/validation#form-request-validation)
Example:
```php
use Jackardios\JsonApiRequest\JsonApiRequest;

class ListUsersRequest extends JsonApiRequest
{
    protected function allowedFilters(): array
    {
        return ['id', 'name', 'email'];
    }
    
    protected function allowedFields(): array
    {
        return ['id', 'name', 'email', 'is_admin', 'created_at', 'updated_at'];
    }
    
    protected function allowedSorts(): array
    {
        return ['id', 'name', 'created_at', 'updated_at'];
    }
    
    protected function allowedIncludes(): array
    {
        return ['friends'];
    }
    
    protected function allowedAppends(): array
    {
        return ['full_name'];
    }
    
    // You can use it like FormRequest and define any of its methods
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
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
}

$request = app(ListUsersRequest::class);

// All of these methods below return Illuminate\Support\Collection (https://laravel.com/docs/8.x/collections)
$fields = $request->fields();
$filters = $request->filters();
$sorts = $request->sorts();
$includes = $request->includes();
$appends = $request->appends();
```
