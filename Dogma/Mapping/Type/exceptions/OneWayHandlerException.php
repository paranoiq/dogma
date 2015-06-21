<?php

namespace Dogma\Mapping\Type;

class OneWayHandlerException extends \Dogma\Exception implements \Dogma\Mapping\Type\Exception
{

    /** @var mixed */
    private $instance;

    /** @var \Dogma\Mapping\Type\Handler */
    private $handler;

    /**
     * @param mixed $instance
     * @param \Dogma\Mapping\Type\Handler $handler
     * @param \Throwable|null $previous
     */
    public function __construct($instance, Handler $handler, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Cannot export an instance of %s using one way type handler %s.', get_class($instance), get_class($handler)),
            $previous
        );
        $this->instance = $instance;
        $this->handler = $handler;
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    public function getHandler(): Handler
    {
        return $this->handler;
    }

}
