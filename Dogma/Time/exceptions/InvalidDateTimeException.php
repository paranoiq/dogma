<?php

namespace Dogma\Time;

class InvalidDateTimeException extends \Dogma\Exception
{

    public function __construct(string $dateTimeString, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Invalid date/time string: %s', $dateTimeString), $previous);
    }

}
