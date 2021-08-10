<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Jackardios\JsonApiRequest\Exceptions\InvalidSortQuery;

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

    public function setAllowedSorts($sorts): self
    {
        $sorts = is_array($sorts) ? $sorts : func_get_args();

        $this->allowedSorts = collect($sorts)
            ->filter()
            ->unique();

        $this->ensureAllSortsExist();

        return $this;
    }

    public function getAllowedSorts(): ?Collection {
        return $this->allowedSorts;
    }

    public function sorts(): Collection {
        if ($this->requestedSorts) {
            return $this->requestedSorts;
        }

        $sortParameterName = config('json-api-request.parameters.sort');
        $sortParts = $this->getRequestData($sortParameterName);

        if (is_string($sortParts)) {
            $sortParts = explode(static::getSortsArrayValueDelimiter(), $sortParts);
        }

        $this->requestedSorts = collect($sortParts)
            ->filter()
            ->unique();

        return $this->requestedSorts;
    }

    protected function ensureAllSortsExist(): self
    {
        $requestedSortNames = $this->sorts()->map(function (string $sort) {
            return ltrim($sort, '-');
        });

        $unknownSorts = $requestedSortNames->diff($this->allowedSorts);

        if ($unknownSorts->isNotEmpty()) {
            throw InvalidSortQuery::sortsNotAllowed($unknownSorts, $this->allowedSorts);
        }

        return $this;
    }
}
