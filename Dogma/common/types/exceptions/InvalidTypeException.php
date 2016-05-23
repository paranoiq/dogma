<?php

namespace Dogma;

class InvalidTypeException extends \Dogma\Exception
{

    /**
     * @param string|string[]
     * @param string|object
     * @param \Throwable|null
     */
    public function __construct($expectedType, $actualType, \Throwable $previous = null)
    {
        if (is_array($expectedType)) {
            $expectedType = implode(' or ', $expectedType);
        }
        if (is_object($actualType)) {
            $actualType = get_class($actualType);
        } elseif (!is_string($actualType)) {
            $actualType = gettype($actualType);
        }
        parent::__construct(sprintf('Expected a value of type %s. %s given.', $expectedType, $actualType), $previous);
    }

}
