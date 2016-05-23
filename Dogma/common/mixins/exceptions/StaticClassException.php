<?php

namespace Dogma;

class StaticClassException extends \Dogma\InvalidTypeException
{

    public function __construct(string $class, \Throwable $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Cannot instanciate a static class %s.', $class), $previous);
    }

}
