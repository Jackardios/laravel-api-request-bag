<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jackardios\JsonApiRequest\Exceptions\InvalidFilterQuery;

trait WithFilters
{
    protected ?Collection $requestedFilters;
    protected ?Collection $allowedFilters;

    private static string $filtersArrayValueDelimiter = ',';

    public static function setFiltersArrayValueDelimiter(string $filtersArrayValueDelimiter): void
    {
        static::$filtersArrayValueDelimiter = $filtersArrayValueDelimiter;
    }

    public static function getFiltersArrayValueDelimiter(): string
    {
        return static::$filtersArrayValueDelimiter;
    }

    public function setAllowedFilters($filters): self
    {
        $filters = is_array($filters) ? $filters : func_get_args();

        $this->allowedFilters = collect($filters)
            ->filter()
            ->unique();

        $this->ensureAllFiltersExist();

        return $this;
    }

    public function getAllowedFilters(): ?Collection {
        return $this->allowedFilters;
    }

    public function filters(): Collection
    {
        if ($this->requestedFilters) {
            return $this->requestedFilters;
        }

        $filterParameterName = config('api-request-bag.parameters.filter');
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
     * @return array|bool
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

    protected function ensureAllFiltersExist(): self
    {
        $filterNames = $this->filters()->keys();

        $allowedFilterNames = $this->allowedFilters;

        $unknownFilters = $filterNames->diff($allowedFilterNames);

        if ($unknownFilters->isNotEmpty()) {
            throw InvalidFilterQuery::filtersNotAllowed($unknownFilters, $allowedFilterNames);
        }

        return $this;
    }
}
