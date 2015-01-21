<?php

namespace Dogma;

trait StaticClassMixin
{

    /**
     * @throws \Dogma\StaticClassException
     */
    final public function __construct()
    {
        throw new \Dogma\StaticClassException(get_called_class());
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

}
