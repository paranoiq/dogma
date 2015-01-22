<?php

namespace Dogma;

trait StrictBehaviorMixin
{

    /**
     * Call to undefined method
     * @param string $name method name
     * @param mixed[] $args method args
     * @throws \Dogma\UndefinedMethodException
     */
    public function __call($name, $args)
    {
        throw new \Dogma\UndefinedMethodException(get_class($this), $name);
    }

    /**
     * Call to undefined static method
     * @param string $name method name
     * @param mixed[] $args method args
     * @throws \Dogma\UndefinedMethodException
     */
    public static function __callStatic($name, $args)
    {
        throw new \Dogma\UndefinedMethodException(get_called_class(), $name);
    }

    /**
     * Access to undefined property
     * @param string $name
     * @throws \Dogma\UndefinedPropertyException
     */
    public function &__get($name)
    {
        throw new \Dogma\UndefinedPropertyException(get_class($this), $name);
    }

    /**
     * Write to undefined property
     * @param string $name
     * @param mixed $value
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __set($name, $value)
    {
        throw new \Dogma\UndefinedPropertyException(get_class($this), $name);
    }

    /**
     * Isset undefined property
     * @param string $name
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __isset($name)
    {
        throw new \Dogma\UndefinedPropertyException(get_class($this), $name);
    }

    /**
     * Unset undefined property
     * @param string $name
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __unset($name)
    {
        throw new \Dogma\UndefinedPropertyException(get_class($this), $name);
    }

}
