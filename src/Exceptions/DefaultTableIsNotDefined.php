<?php

namespace Jackardios\JsonApiRequest\Exceptions;

use LogicException;

class DefaultTableIsNotDefined extends LogicException
{
    public function __construct()
    {
        parent::__construct("`defaultTable` is not defined for JsonApiRequest.");
    }
}
