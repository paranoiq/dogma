<?php

namespace Dogma\Time;

class InvalidDateTimeException extends \Dogma\Exception
{

    /**
     * @param string $dateTimeString
     * @param \Exception $previous
     */
    public function __construct($dateTimeString, \Exception $previous = null)
    {
        parent::__construct(sprintf('Invalid date/time string: %s', $dateTimeString), $previous);
    }

}
