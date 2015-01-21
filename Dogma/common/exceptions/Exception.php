<?php

namespace Dogma;


class Exception extends \Exception
{

    /**
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

}
