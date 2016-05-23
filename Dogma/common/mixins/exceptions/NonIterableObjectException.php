<?php

namespace Dogma;

final class NonIterableObjectException extends \Dogma\InvalidTypeException
{

    public function __construct(string $class, \Throwable $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Iterating a non-iterable object of class %s.', $class), $previous);
    }

}
