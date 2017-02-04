<?php

namespace Dogma\Web;

class InvalidUrlException extends \Dogma\InvalidValueException
{

    public function __construct(string $value, \Throwable $previous = null)
    {
        \Dogma\Exception::__construct(sprintf('Invalid URL format: \'%s\'', $value), $previous);
    }

}
