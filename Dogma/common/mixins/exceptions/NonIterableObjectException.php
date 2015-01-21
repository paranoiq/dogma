<?php

namespace Dogma;

final class NonIterableObjectException extends \Dogma\InvalidTypeException
{

    /**
     * @param string $class
     * @param \Exception $previous
     */
    public function __construct($class, \Exception $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Iterating a non-iterable object of class %s.', $class), $previous);
    }

}
