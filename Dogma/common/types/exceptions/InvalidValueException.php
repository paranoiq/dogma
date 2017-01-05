<?php

namespace Dogma;

class InvalidValueException extends \Dogma\Exception
{

    /**
     * @param mixed
     * @param string
     * @param \Throwable|null
     */
    public function __construct($value, $type, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Value %s is not a valid value of %s.', ExceptionValueFormater::format($value), ExceptionTypeFormater::format($type)),
            $previous
        );
    }

}
