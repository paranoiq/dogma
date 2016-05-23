<?php

namespace Dogma;

final class NonCloneableObjectException extends \Dogma\InvalidTypeException
{

    public function __construct(string $class, \Throwable $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Cloning a non-cloneable object of class %s.', $class), $previous);
    }

}
