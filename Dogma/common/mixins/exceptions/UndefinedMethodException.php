<?php

namespace Dogma;

class UndefinedMethodException extends \Dogma\Exception
{

    public function __construct(string $class, string $method, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Method %s::%s() is not defined or is not accessible', $class, $method), $previous);
    }

}
