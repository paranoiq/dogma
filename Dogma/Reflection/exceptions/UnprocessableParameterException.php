<?php

namespace Dogma\Reflection;

class UnprocessableParameterException extends \Dogma\Exception implements \Dogma\Reflection\Exception
{

    /**
     * @param string $class
     * @param string $method
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct(\ReflectionMethod $method, $message, \Exception $previous = null)
    {
        parent::__construct(sprintf(
            'Unprocessable parameter on %s::%s: %s',
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $message
        ), $previous);
    }

}
