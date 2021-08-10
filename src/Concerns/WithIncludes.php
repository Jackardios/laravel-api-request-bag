<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Jackardios\JsonApiRequest\Exceptions\InvalidIncludeQuery;

trait WithIncludes
{
    protected ?Collection $requestedIncludes = null;
    protected ?Collection $allowedIncludes = null;

    private static string $includesArrayValueDelimiter = ',';

    public static function setIncludesArrayValueDelimiter(string $includesArrayValueDelimiter): void
    {
        static::$includesArrayValueDelimiter = $includesArrayValueDelimiter;
    }

    public static function getIncludesArrayValueDelimiter(): string
    {
        return static::$includesArrayValueDelimiter;
    }

    public function setAllowedIncludes($includes): self
    {
        $includes = is_array($includes) ? $includes : func_get_args();

        $this->allowedIncludes = collect($includes)
            ->filter()
            ->unique();

        $this->ensureAllIncludesExist();

        return $this;
    }

    public function getAllowedIncludes(): ?Collection {
        return $this->allowedIncludes;
    }

    public function includes(): Collection {
        if ($this->requestedIncludes) {
            return $this->requestedIncludes;
        }

        $includeParameterName = config('api-request-bag.parameters.include');
        $includeParts = $this->getRequestData($includeParameterName);

        if (is_string($includeParts)) {
            $includeParts = explode(static::getIncludesArrayValueDelimiter(), $includeParts);
        }

        $this->requestedIncludes = collect($includeParts)
            ->filter()
            ->unique();

        return $this->requestedIncludes;
    }

    protected function ensureAllIncludesExist(): self
    {
        $includes = $this->includes();

        $diff = $includes->diff($this->allowedIncludes);

        if ($diff->isNotEmpty()) {
            throw InvalidIncludeQuery::includesNotAllowed($diff, $this->allowedIncludes);
        }

        return $this;
    }
}
