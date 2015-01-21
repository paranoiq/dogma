<?php

namespace Dogma;

class StaticClassException extends \Dogma\InvalidTypeException
{

    /**
     * @param string $class
     * @param \Exception $previous
     */
    public function __construct($class, \Exception $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Cannot instanciate a static class %s.', $class), $previous);
    }

}
