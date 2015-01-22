<?php

namespace Dogma;

final class NonCloneableObjectException extends \Dogma\InvalidTypeException
{

    /**
     * @param string $class
     * @param \Exception $previous
     */
    public function __construct($class, \Exception $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Cloning a non-cloneable object of class %s.', $class), $previous);
    }

}
