<?php

namespace Dogma;

class ValueOutOfRangeException extends \Dogma\InvalidValueException
{

    /**
     * @param integer|float
     * @param integer|float|null
     * @param integer|float|null
     * @param \Exception|null
     */
    public function __construct($value, $min, $max, \Exception $previous = null)
    {
        if ($min === null) {
            Exception::__construct(
                sprintf('Expected a value lower than %s. Value %s given.', $max, ExceptionValueFormater::format($value)),
                $previous
            );
        } elseif ($max === null) {
            Exception::__construct(
                sprintf('Expected a value higher than %s. Value %s given.', $min, ExceptionValueFormater::format($value)),
                $previous
            );
        } else {
            Exception::__construct(
                sprintf('Expected a value within the range of %s and %s. Value %s given.', $min, $max, ExceptionValueFormater::format($value)),
                $previous
            );
        }
    }

}
