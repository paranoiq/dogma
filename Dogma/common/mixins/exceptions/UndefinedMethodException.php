<?php

namespace Dogma;

class UndefinedMethodException extends \Dogma\Exception
{

    /**
     * @param string $class
     * @param string $method
     * @param \Exception|null $previous
     */
    public function __construct($class, $method, \Exception $previous = null)
    {
        parent::__construct(sprintf('Method %s::%s() is not defined or is not accessible', $class, $method), $previous);
    }

}
