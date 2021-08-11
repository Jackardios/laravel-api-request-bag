<?php

namespace Jackardios\JsonApiRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Jackardios\JsonApiRequest\Concerns\WithFields;
use Jackardios\JsonApiRequest\Concerns\WithFilters;
use Jackardios\JsonApiRequest\Concerns\WithIncludes;
use Jackardios\JsonApiRequest\Concerns\WithAppends;
use Jackardios\JsonApiRequest\Concerns\WithSorts;

class JsonApiRequest extends FormRequest
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

    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validateResolved(): void
    {
        parent::validateResolved();

        if (method_exists($this, 'allowedAppends')) {
            $this->setAllowedAppends($this->allowedAppends());
        }

        if (method_exists($this, 'allowedFields')) {
            $this->setAllowedFields($this->allowedFields());
        }

        if (method_exists($this, 'allowedFilters')) {
            $this->setAllowedFilters($this->allowedFilters());
        }

        if (method_exists($this, 'allowedIncludes')) {
            $this->setAllowedIncludes($this->allowedIncludes());
        }

        if (method_exists($this, 'allowedSorts')) {
            $this->setAllowedSorts($this->allowedSorts());
        }
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
