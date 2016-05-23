<?php

namespace Dogma;

trait StrictBehaviorMixin
{

    /**
     * Call to undefined method
     * @throws \Dogma\UndefinedMethodException
     */
    public function __call(string $name, $args)
    {
        throw new \Dogma\UndefinedMethodException(get_class($this), $name);
    }

    /**
     * Call to undefined static method
     * @throws \Dogma\UndefinedMethodException
     */
    public static function __callStatic(string $name, $args)
    {
        throw new \Dogma\UndefinedMethodException(get_called_class(), $name);
    }

    /**
     * Access to undefined property
     * @throws \Dogma\UndefinedPropertyException
     */
    public function &__get(string $name)
    {
        throw new \Dogma\UndefinedPropertyException(get_class($this), $name);
    }

    /**
     * Write to undefined property
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __set(string $name, $value)
    {
        throw new \Dogma\UndefinedPropertyException(get_class($this), $name);
    }

    /**
     * Isset undefined property
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __isset(string $name)
    {
        throw new \Dogma\UndefinedPropertyException(get_class($this), $name);
    }

    /**
     * Unset undefined property
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __unset(string $name)
    {
        throw new \Dogma\UndefinedPropertyException(get_class($this), $name);
    }

}
