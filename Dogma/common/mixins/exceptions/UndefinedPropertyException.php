<?php

namespace Dogma;

class UndefinedPropertyException extends \Dogma\Exception
{

    public function __construct(string $class, string $property, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Property %s::$%s is not defined or is not accessible', $class, $property), $previous);
    }

}
