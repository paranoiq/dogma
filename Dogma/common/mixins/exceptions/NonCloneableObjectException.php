<?php

namespace Dogma;

final class NonCloneableObjectException extends \Dogma\Exception
{

    public function __construct(string $class, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Cloning a non-cloneable object of class %s.', $class), $previous);
    }

}
