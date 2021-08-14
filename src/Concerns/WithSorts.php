<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Jackardios\JsonApiRequest\Exceptions\InvalidSortQuery;
use Jackardios\JsonApiRequest\Values\Sort;

trait WithSorts
{
    protected ?Collection $requestedSorts = null;
    protected ?Collection $allowedSorts = null;

    private static string $sortsArrayValueDelimiter = ',';

    public static function setSortsArrayValueDelimiter(string $sortsArrayValueDelimiter): void
    {
        static::$sortsArrayValueDelimiter = $sortsArrayValueDelimiter;
    }

    public static function getSortsArrayValueDelimiter(): string
    {
        return static::$sortsArrayValueDelimiter;
    }

    protected function allowedSorts(): array
    {
        return [];
    }

    public function setAllowedSorts($sorts): self
    {
        $sorts = is_array($sorts) ? $sorts : func_get_args();

        $this->allowedSorts = collect($sorts)
            ->filter()
            ->unique();

        $this->ensureAllSortsAllowed();

        return $this;
    }

    public function getAllowedSorts(): ?Collection
    {
        if (!($this->allowedSorts instanceof Collection)) {
            $allowedSortsFromCallback = $this->allowedSorts();

            if ($allowedSortsFromCallback) {
                $this->setAllowedSorts($allowedSortsFromCallback);
            }
        }

        return $this->allowedSorts;
    }

    public function sorts(): Collection
    {
        if ($this->requestedSorts instanceof Collection) {
            return $this->requestedSorts;
        }

        // ensure all sorts allowed
        $this->getAllowedSorts();

        $sortParameterName = config('json-api-request.parameters.sort');
        $sortParts = $this->getRequestData($sortParameterName);

        if (is_string($sortParts)) {
            $sortParts = explode(static::getSortsArrayValueDelimiter(), $sortParts);
        }

        $this->requestedSorts = collect($sortParts)
            ->filter()
            ->unique(function($sort) {
                return ltrim($sort, '-');
            })
            ->values()
            ->map(function($field) {
                return new Sort((string)$field);
            });

        return $this->requestedSorts;
    }

    protected function ensureAllSortsAllowed(): self
    {
        $requestedSorts = $this->sorts()->map(function (Sort $sort) {
            return $sort->getField();
        });

        $unknownSorts = $requestedSorts->diff($this->allowedSorts);

        if ($unknownSorts->isNotEmpty()) {
            throw InvalidSortQuery::sortsNotAllowed($unknownSorts, $this->allowedSorts);
        }

        return $this;
    }
}
