<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

abstract class EnumInt implements \Dogma\NonIterable
{
    use \Dogma\EnumMixin;

    /** @var \Dogma\EnumInt[][] ($class => ($value => $enum)) */
    private static $instances = [];

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static $availableValues = [];

    /** @var int */
    private $value;

    final private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @param int $value
     * @return static
     */
    final public static function get(int $value): self
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
     * @param int $value
     * @return bool
     */
    public static function validateValue(int &$value): bool
    {
        $class = get_called_class();
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return Arr::contains(self::$availableValues[$class], $value);
    }

    final public function getValue(): int
    {
        return $this->value;
    }

    final public static function isValid(int $value): bool
    {
        return self::validateValue($value);
    }

    /**
     * @return int[]
     */
    final public static function getAllowedValues(): array
    {
        $class = get_called_class();
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return self::$availableValues[$class];
    }

}
