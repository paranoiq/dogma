<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Enum;

use Dogma\Arr;
use Dogma\InvalidTypeException;
use Dogma\InvalidValueException;
use Dogma\NonCloneableMixin;
use Dogma\NonIterableMixin;
use Dogma\NonSerializableMixin;
use Dogma\StrictBehaviorMixin;

trait SetMixin
{
    use StrictBehaviorMixin;
    use NonIterableMixin;
    use NonCloneableMixin;
    use NonSerializableMixin;

    public function __toString(): string
    {
        $classNameParts = explode('\\', get_called_class());

        return sprintf('%s: %s', end($classNameParts), implode(',', $this->values));
    }

    /**
     * Returns case sensitive regular expression for value validation.
     * Only the body or expression without modifiers, delimiters and start/end assertions ('^' and '$').
     */
    public static function getValueRegexp(): string
    {
        return implode('|', self::getAllowedValues());
    }

    /**
     * @return string[]|int[]
     */
    final public function getConstantNames(): array
    {
        $class = get_called_class();
        $names = [];
        foreach ($this->getValues() as $value) {
            $names[] = Arr::indexOf(self::$availableValues[$class], $value);
        }

        return $names;
    }

    /**
     * @param string|int $value
     */
    final public function check($value): void
    {
        if (!self::isValid($value)) {
            $class = get_called_class();
            throw new InvalidValueException($value, $class);
        }
    }

    /**
     * @param int|string|\Dogma\Enum\Enum $value
     */
    final public function equals($value): bool
    {
        if (is_scalar($value)) {
            $value = static::get($value);
        } elseif (get_class($value) !== static::class) {
            throw new InvalidTypeException(static::class, $value);
        }

        return $this->value === $value->value;
    }

    /**
     * @return static[]
     */
    final public static function getInstances(): array
    {
        $class = get_called_class();
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        if (count(self::$availableValues[$class]) !== count(self::$instances[$class])) {
            foreach (self::$availableValues[$class] as $identifier => $value) {
                if (!isset(self::$instances[$class][$identifier])) {
                    self::$instances[$class][$identifier] = new static($identifier, self::$availableValues[$class][$identifier]);
                }
            }
        }

        return self::$instances[$class];
    }

    final private static function init(string $class): void
    {
        $ref = new \ReflectionClass($class);
        self::$availableValues[$class] = $ref->getConstants();
        self::$instances[$class] = [];
    }

}
