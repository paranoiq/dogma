<?php

namespace Dogma;

class InvalidValueException extends \Dogma\Exception
{

    /**
     * @param mixed
     * @param string
     * @param \Exception|null
     */
    public function __construct($value, $type, \Exception $previous = null)
    {
        parent::__construct(
            sprintf('Value %s is not a valid value of %s.', ExceptionValueFormater::format($value), $type),
            $previous
        );
    }

}
