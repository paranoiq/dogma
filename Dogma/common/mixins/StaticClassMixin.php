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
     * @deprecated
     * @throws \Dogma\UndefinedMethodException
     */
    public static function __callStatic(string $name, $args)
    {
        throw new \Dogma\UndefinedMethodException(get_called_class(), $name);
    }

}
