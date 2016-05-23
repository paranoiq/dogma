<?php

namespace Dogma;

final class NonSerializableObjectException extends \Dogma\InvalidTypeException
{

    public function __construct(string $class, \Throwable $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Serializing a non-serializable object of class %s.', $class), $previous);
    }

}
