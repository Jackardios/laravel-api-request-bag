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

    protected function allowedIncludes(): array
    {
        return [];
    }

    protected function setAllowedIncludesFromCallbackIfNotDefined(): self
    {
        $allowedIncludesFromCallback = $this->allowedIncludes();
        if (!($this->allowedIncludes instanceof Collection) && $allowedIncludesFromCallback) {
            $this->setAllowedIncludes($allowedIncludesFromCallback);
        }

        return $this;
    }

    public function setAllowedIncludes($includes): self
    {
        $includes = is_array($includes) ? $includes : func_get_args();

        $this->allowedIncludes = collect($includes)
            ->filter()
            ->unique();

        $this->ensureAllIncludesAllowed();

        return $this;
    }

    public function getAllowedIncludes(): ?Collection
    {
        return $this->allowedIncludes;
    }

    public function includes(): Collection
    {
        $this->setAllowedIncludesFromCallbackIfNotDefined();

        if ($this->requestedIncludes) {
            return $this->requestedIncludes;
        }

        $includeParameterName = config('json-api-request.parameters.include');
        $includeParts = $this->getRequestData($includeParameterName);

        if (is_string($includeParts)) {
            $includeParts = explode(static::getIncludesArrayValueDelimiter(), $includeParts);
        }

        $this->requestedIncludes = collect($includeParts)
            ->filter()
            ->unique()
            ->values();

        return $this->requestedIncludes;
    }

    protected function ensureAllIncludesAllowed(): self
    {
        $includes = $this->includes();

        $diff = $includes->diff($this->allowedIncludes);

        if ($diff->isNotEmpty()) {
            throw InvalidIncludeQuery::includesNotAllowed($diff, $this->allowedIncludes);
        }

        return $this;
    }
}
