<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Jackardios\JsonApiRequest\Exceptions\InvalidAppendQuery;

trait WithAppends
{
    protected ?Collection $requestedAppends;
    protected ?Collection $allowedAppends;

    private static string $appendsArrayValueDelimiter = ',';

    public static function setAppendsArrayValueDelimiter(string $appendsArrayValueDelimiter): void
    {
        static::$appendsArrayValueDelimiter = $appendsArrayValueDelimiter;
    }

    public static function getAppendsArrayValueDelimiter(): string
    {
        return static::$appendsArrayValueDelimiter;
    }

    public function setAllowedAppends($appends): self
    {
        $appends = is_array($appends) ? $appends : func_get_args();

        $this->allowedAppends = collect($appends)
            ->filter()
            ->unique();

        $this->ensureAllAppendsExist();

        return $this;
    }

    public function getAllowedAppends(): ?Collection {
        return $this->allowedAppends;
    }

    public function appends(): Collection {
        if ($this->requestedAppends) {
            return $this->requestedAppends;
        }

        $appendParameterName = config('api-request-bag.parameters.append');
        $appendParts = $this->getRequestData($appendParameterName);

        if (is_string($appendParts)) {
            $appendParts = explode(static::getAppendsArrayValueDelimiter(), $appendParts);
        }

        $this->requestedAppends = collect($appendParts)
            ->filter()
            ->unique();

        return $this->requestedAppends;
    }

    protected function ensureAllAppendsExist(): self
    {
        $appends = $this->appends();

        $diff = $appends->diff($this->allowedAppends);

        if ($diff->isNotEmpty()) {
            throw InvalidAppendQuery::appendsNotAllowed($diff, $this->allowedAppends);
        }

        return $this;
    }
}
