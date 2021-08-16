<?php

namespace Jackardios\JsonApiRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Jackardios\JsonApiRequest\Concerns\HasFields;
use Jackardios\JsonApiRequest\Concerns\HasFilters;
use Jackardios\JsonApiRequest\Concerns\HasIncludes;
use Jackardios\JsonApiRequest\Concerns\HasAppends;
use Jackardios\JsonApiRequest\Concerns\HasSorts;

class JsonApiRequest extends FormRequest
{
    use HasFields;
    use HasIncludes;
    use HasAppends;
    use HasSorts;
    use HasFilters;

    public static function fromRequest(Request $request): self
    {
        return static::createFrom($request, new self());
    }

    public static function setArrayValueDelimiter(string $delimiter): void
    {
        static::$filtersArrayValueDelimiter = $delimiter;
        static::$includesArrayValueDelimiter = $delimiter;
        static::$appendsArrayValueDelimiter = $delimiter;
        static::$fieldsArrayValueDelimiter = $delimiter;
        static::$sortsArrayValueDelimiter = $delimiter;
    }

    protected function getRequestData(?string $key = null, $default = null)
    {
        if (config('json-api-request.request_data_source') === 'body') {
            return $this->input($key, $default);
        }

        return $this->query($key, $default);
    }
}
