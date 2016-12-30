<?php

namespace Dogma;

final class NonIterableObjectException extends \Dogma\Exception
{

    public function __construct(string $class, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Iterating a non-iterable object of class %s.', $class), $previous);
    }

}
