<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

abstract class Enum implements \Dogma\NonIterable
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;
    use \Dogma\NonCloneableMixin;
    use \Dogma\NonSerializableMixin;

    /** @var \Dogma\Enum[][] ($class => ($value => $enum)) */
    private static $instances = [];

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static $availableValues = [];

    /** @var mixed */
    private $value;

    /**
     * @param int|string
     */
    final private function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return sprintf('%s: %s', end(explode('\\', get_called_class())), $this->value);
    }

    /**
     * @param int|string
     * @return static
     */
    final public static function get($value): self
    {
        $class = get_called_class();
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        if (!static::validateValue($value)) {
            throw new \Dogma\InvalidValueException($value, $class);
        }

        if (!Arr::containsKey(self::$instances[$class], $value)) {
            self::$instances[$class][$value] = new static($value);
        }

        return self::$instances[$class][$value];
    }

    /**
     * Validates given value. Can also normalise the value, if needed.
     *
     * @param int|string &$value
     * @return bool
     */
    public static function validateValue(&$value): bool
    {
        $class = get_called_class();
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return Arr::contains(self::$availableValues[$class], $value);
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
     * @return int|string
     */
    final public function getValue()
    {
        return $this->value;
    }

    final public function getConstantName(): string
    {
        $class = get_called_class();

        return Arr::indexOf(self::$availableValues[$class], $this->value);
    }

    /**
     * @param int|string|\Dogma\Enum $value
     */
    final public function equals($value): bool
    {
        if (is_scalar($value)) {
            $value = static::get($value);
        } elseif (get_class($value) !== static::class) {
            throw new \Dogma\InvalidTypeException(static::class, $value);
        }

        return $this->getValue() === $value->getValue();
    }

    /**
     * @param int|string $value
     * @return bool
     */
    final public static function isValid($value): bool
    {
        return self::validateValue($value);
    }

    /**
     * Get possible values.
     * @return mixed[]
     */
    final public static function getAllowedValues(): array
    {
        $class = get_called_class();
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return self::$availableValues[$class];
    }

    /**
     * Get all values as Enum objects.
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
