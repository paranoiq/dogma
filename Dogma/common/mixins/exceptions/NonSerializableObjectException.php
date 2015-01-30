<?php

namespace Dogma;

final class NonSerializableObjectException extends \Dogma\InvalidTypeException
{

    /**
     * @param string $class
     * @param \Exception $previous
     */
    public function __construct($class, \Exception $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Serializing a non-serializable object of class %s.', $class), $previous);
    }

}
