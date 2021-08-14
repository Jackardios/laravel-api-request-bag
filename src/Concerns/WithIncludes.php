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
        if (!($this->allowedIncludes instanceof Collection)) {
            $allowedIncludesFromCallback = $this->allowedIncludes();

            if ($allowedIncludesFromCallback) {
                $this->setAllowedIncludes($allowedIncludesFromCallback);
            }
        }

        return $this->allowedIncludes;
    }

    public function includes(): Collection
    {
        if ($this->requestedIncludes instanceof Collection) {
            return $this->requestedIncludes;
        }

        // ensure all includes allowed
        $this->getAllowedIncludes();

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
