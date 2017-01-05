<?php

namespace Dogma;

class InvalidTypeException extends \Dogma\Exception
{

    /**
     * @param string|string[]
     * @param string|object
     * @param \Throwable|null
     */
    public function __construct($expectedType, $actualType, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Expected a value of type %s. %s given.', ExceptionTypeFormater::format($expectedType), ExceptionTypeFormater::format($actualType)),
            $previous
        );
    }

}
