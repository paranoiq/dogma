<?php

namespace Dogma;

class InvalidArgumentException extends \Dogma\InvalidValueException
{

    public function __construct(string $message, \Throwable $previous = null)
    {
        Exception::__construct($message, $previous);
    }

}
