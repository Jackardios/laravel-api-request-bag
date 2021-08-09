<?php

namespace Jackardios\JsonApiRequest\Exceptions;

use BadMethodCallException;

class AllowedFieldsMustBeCalledBeforeAllowedIncludes extends BadMethodCallException
{
    public function __construct()
    {
        parent::__construct("The QueryBuilder's `setAllowedFields` method must be called before the `setAllowedIncludes` method.");
    }
}
