<?php

namespace Jackardios\JsonApiRequest;

use Illuminate\Http\Request;
use Jackardios\JsonApiRequest\Concerns\WithFields;
use Jackardios\JsonApiRequest\Concerns\WithFilters;
use Jackardios\JsonApiRequest\Concerns\WithIncludes;
use Jackardios\JsonApiRequest\Concerns\WithAppends;
use Jackardios\JsonApiRequest\Concerns\WithSorts;

class JsonApiRequest extends Request
{
    use WithFields;
    use WithIncludes;
    use WithAppends;
    use WithSorts;
    use WithFilters;

    public static function fromRequest(Request $request): self
    {
        return static::createFrom($request, new self());
    }

    protected function getRequestData(?string $key = null, $default = null)
    {
        if (config('api-request-bag.request_data_source') === 'body') {
            return $this->input($key, $default);
        }

        return $this->query($key, $default);
    }

    public static function setArrayValueDelimiter(string $delimiter): void
    {
        static::$filtersArrayValueDelimiter = $delimiter;
        static::$includesArrayValueDelimiter = $delimiter;
        static::$appendsArrayValueDelimiter = $delimiter;
        static::$fieldsArrayValueDelimiter = $delimiter;
        static::$sortsArrayValueDelimiter = $delimiter;
    }
}
