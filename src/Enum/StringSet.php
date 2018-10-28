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
use function array_search;
use function implode;
use function in_array;
use function sort;

abstract class StringSet
{
    use SetMixin;

    /** @var \Dogma\Enum\StringSet[][] ($class => ($value => $enum)) */
    private static $instances = [];

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static $availableValues = [];

    /** @var string */
    private $value;

    /** @var string[] */
    private $values = [];

    /**
     * @param string $value
     * @param string[] $values
     */
    final private function __construct(string $value, array $values)
    {
        $this->value = $value;
        $this->values = $values;
    }

    /**
     * @param string ...$values
     * @return static
     */
    final public static function get(string ...$values): self
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        sort($values);
        foreach ($values as $val) {
            if (static::validateValue($val)) {
                throw new InvalidValueException($val, $class);
            }
        }
        $value = implode(',', $values);
        if (!Arr::containsKey(self::$instances[$class], $value)) {
            self::$instances[$class][$value] = new static($value, $values);
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

    /**
     * @return string[]
     */
    final public function getValues(): array
    {
        return $this->values;
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

    /**
     * @param string ...$addValues
     * @return static
     */
    public function add(string ...$addValues): self
    {
        $values = $this->getValues();
        foreach ($addValues as $val) {
            self::check($val);

            if (!in_array($val, $values)) {
                $values[] = $val;
            }
        }

        return static::get(...$values);
    }

    /**
     * @param string ...$removeValues
     * @return static
     */
    public function remove(string ...$removeValues): self
    {
        $values = $this->getValues();
        foreach ($removeValues as $val) {
            self::check($val);

            $key = array_search($val, $values);
            unset($values[$key]);
        }

        return static::get(...$values);
    }

    /**
     * @param string ...$containsValues
     * @return bool
     */
    public function contains(string ...$containsValues): bool
    {
        foreach ($containsValues as $val) {
            self::check($val);

            if (!in_array($val, $this->values)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string ...$containsValues
     * @return bool
     */
    public function containsAny(string ...$containsValues): bool
    {
        foreach ($containsValues as $val) {
            self::check($val);

            if (in_array($val, $this->values)) {
                return true;
            }
        }

        return false;
    }

}
