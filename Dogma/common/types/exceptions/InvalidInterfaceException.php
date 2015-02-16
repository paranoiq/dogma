<?php

namespace Dogma;

class InvalidInterfaceException extends \Dogma\InvalidTypeException
{

    /**
     * @param string
     * @param mixed
     * @param \Exception|null
     */
    public function __construct($expectedInterface, $value, \Exception $previous = null)
    {
        if (is_object($value)) {
            $type = get_class($value);
        } else {
            $type = gettype($value);
        }
        $class = true;
        if (interface_exists($expectedInterface)) {
            $class = false;
        }
        if ($class) {
            \Dogma\Exception::__construct(sprintf('Expected an instance of %s. %s given.', $expectedInterface, $type), $previous);
        } else {
            \Dogma\Exception::__construct(sprintf('Expected an object implementing %s. %s given.', $expectedInterface, $type), $previous);
        }
    }

}
