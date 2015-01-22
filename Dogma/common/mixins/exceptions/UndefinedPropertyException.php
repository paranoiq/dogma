<?php

namespace Dogma;

class UndefinedPropertyException extends \Dogma\Exception
{

    /**
     * @param string $class
     * @param string $property
     * @param \Exception|null $previous
     */
    public function __construct($class, $property, \Exception $previous = null)
    {
        parent::__construct(sprintf('Property %s::$%s is not defined or is not accessible', $class, $property), $previous);
    }

}
