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
use Dogma\InvalidValueException;

abstract class StringEnum
{
    use EnumMixin;

    /** @var \Dogma\Enum\StringEnum[][] ($class => ($value => $enum)) */
    private static $instances = [];

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static $availableValues = [];

    /** @var string */
    private $value;

    final private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     * @return static
     */
    final public static function get(string $value): self
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        if (!static::validateValue($value)) {
            throw new InvalidValueException($value, $class);
        }

        if (!Arr::containsKey(self::$instances[$class], $value)) {
            self::$instances[$class][$value] = new static($value);
        }

        return self::$instances[$class][$value];
    }

    /**
     * Validates given value. Can also normalize the value, if needed.
     *
     * @param string $value
     * @return bool
     */
    public static function validateValue(string &$value): bool
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return Arr::contains(self::$availableValues[$class], $value);
    }

    final public function getValue(): string
    {
        return $this->value;
    }

    final public static function isValid(string $value): bool
    {
        return self::validateValue($value);
    }

    /**
     * @return string[]
     */
    final public static function getAllowedValues(): array
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return self::$availableValues[$class];
    }

}
