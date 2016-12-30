<?php

namespace Dogma;

final class NonSerializableObjectException extends \Dogma\Exception
{

    public function __construct(string $class, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Serializing a non-serializable object of class %s.', $class), $previous);
    }

}
