<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

trait StrictBehaviorMixin
{

    /**
     * Call to undefined method
     * @deprecated
     * @param string $name
     * @param mixed $args
     * @throws \Dogma\UndefinedMethodException
     */
    public function __call(string $name, $args): void
    {
        throw new UndefinedMethodException(static::class, $name);
    }

    /**
     * Call to undefined static method
     * @deprecated
     * @param string $name
     * @param mixed $args
     * @throws \Dogma\UndefinedMethodException
     */
    public static function __callStatic(string $name, $args): void
    {
        throw new UndefinedMethodException(static::class, $name);
    }

    /**
     * Access to undefined property
     * @deprecated
     * @param string $name
     * @throws \Dogma\UndefinedPropertyException
     */
    public function &__get(string $name): void
    {
        throw new UndefinedPropertyException(static::class, $name);
    }

    /**
     * Write to undefined property
     * @deprecated
     * @param string $name
     * @param mixed $value
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __set(string $name, $value): void
    {
        throw new UndefinedPropertyException(static::class, $name);
    }

    /**
     * Isset undefined property
     * @deprecated
     * @param string $name
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __isset(string $name): void
    {
        throw new UndefinedPropertyException(static::class, $name);
    }

    /**
     * Unset undefined property
     * @deprecated
     * @param string $name
     * @throws \Dogma\UndefinedPropertyException
     */
    public function __unset(string $name): void
    {
        throw new UndefinedPropertyException(static::class, $name);
    }

}
