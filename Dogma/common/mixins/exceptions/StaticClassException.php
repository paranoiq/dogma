<?php

namespace Dogma;

class StaticClassException extends \Dogma\Exception
{

    public function __construct(string $class, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Cannot instanciate a static class %s.', $class), $previous);
    }

}
