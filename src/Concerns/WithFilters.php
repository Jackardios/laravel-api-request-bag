<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jackardios\JsonApiRequest\Exceptions\InvalidFilterQuery;

trait WithFilters
{
    protected ?Collection $requestedFilters = null;
    protected ?Collection $allowedFilters = null;

    private static string $filtersArrayValueDelimiter = ',';

    public static function setFiltersArrayValueDelimiter(string $filtersArrayValueDelimiter): void
    {
        static::$filtersArrayValueDelimiter = $filtersArrayValueDelimiter;
    }

    public static function getFiltersArrayValueDelimiter(): string
    {
        return static::$filtersArrayValueDelimiter;
    }

    protected function allowedFilters(): array
    {
        return [];
    }

    protected function setAllowedFiltersFromCallbackIfNotDefined(): self
    {
        $allowedFiltersFromCallback = $this->allowedFilters();
        if (!($this->allowedFilters instanceof Collection) && $allowedFiltersFromCallback) {
            $this->setAllowedFilters($allowedFiltersFromCallback);
        }

        return $this;
    }

    public function setAllowedFilters($filters): self
    {
        $filters = is_array($filters) ? $filters : func_get_args();

        $this->allowedFilters = collect($filters)
            ->filter()
            ->unique();

        $this->ensureAllFiltersAllowed();

        return $this;
    }

    public function getAllowedFilters(): ?Collection
    {
        return $this->allowedFilters;
    }

    public function filters(): Collection
    {
        $this->setAllowedFiltersFromCallbackIfNotDefined();

        if ($this->requestedFilters) {
            return $this->requestedFilters;
        }

        $filterParameterName = config('json-api-request.parameters.filter');
        $filterParts = $this->getRequestData($filterParameterName, []);

        if (is_string($filterParts)) {
            return collect();
        }

        $this->requestedFilters = collect($filterParts)->map(function ($value) {
            return $this->getFilterValue($value);
        });

        return $this->requestedFilters;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function getFilterValue($value)
    {
        if (is_array($value)) {
            return collect($value)->map(function ($valueValue) {
                return $this->getFilterValue($valueValue);
            })->all();
        }

        if (is_string($value) && Str::contains($value, static::getFiltersArrayValueDelimiter())) {
            return explode(static::getFiltersArrayValueDelimiter(), $value);
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        return $value;
    }

    protected function ensureAllFiltersAllowed(): self
    {
        if (config('json-api-request.disable_invalid_filter_query_exception')) {
            return $this;
        }

        $filterNames = $this->filters()->keys();

        $allowedFilterNames = $this->allowedFilters;

        $unknownFilters = $filterNames->diff($allowedFilterNames);

        if ($unknownFilters->isNotEmpty()) {
            throw InvalidFilterQuery::filtersNotAllowed($unknownFilters, $allowedFilterNames);
        }

        return $this;
    }
}
