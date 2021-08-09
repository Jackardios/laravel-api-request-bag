<?php

namespace Jackardios\JsonApiRequest\Exceptions;

use Exception;
use Jackardios\JsonApiRequest\Enums\SortDirection;

class InvalidDirection extends Exception
{
    public static function make(string $sort)
    {
        return new static('The direction should be either `'.SortDirection::DESCENDING.'` or `'.SortDirection::ASCENDING)."`. ${sort} given.";
    }
}
