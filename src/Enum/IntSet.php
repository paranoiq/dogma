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
use function in_array;

abstract class IntSet
{
    use SetMixin;

    /** @var \Dogma\Enum\IntSet[][] ($class => ($value => $enum)) */
    private static $instances = [];

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static $availableValues = [];

    /** @var int */
    private $value;

    /** @var int[] */
    private $values = [];

    /**
     * @param int $value
     * @param int[] $values
     */
    final private function __construct(int $value, array $values)
    {
        $this->value = $value;
        $this->values = $values;
    }

    /**
     * @param int ...$values
     * @return static
     */
    final public static function get(int ...$values): self
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }
        $value = 0;
        foreach ($values as $val) {
            $value |= $val;
        }
        $values = [];
        $n = 0;
        $val = 1;
        while ($n++ < 62) {
            $found = $value & $val;
            if ($found) {
                if (!static::validateValue($val)) {
                    throw new InvalidValueException($val, $class);
                }
                $values[] = $val;
            }
            $val = $val << 1;
        }

        if (!isset(self::$instances[$class][$value])) {
            self::$instances[$class][$value] = new static($value, $values);
        }

        return self::$instances[$class][$value];
    }

    /**
     * Validates given value. Can also normalize the value, if needed.
     *
     * @param int $value
     * @return bool
     */
    public static function validateValue(int &$value): bool
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return Arr::contains(self::$availableValues[$class], $value);
    }

    final public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return int[]
     */
    final public function getValues(): array
    {
        return $this->values;
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
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return self::$availableValues[$class];
    }

    /**
     * @param int ...$addValues
     * @return static
     */
    public function add(int ...$addValues): self
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
     * @param int ...$removeValues
     * @return static
     */
    public function remove(int ...$removeValues): self
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
     * @param int ...$containsValues
     * @return bool
     */
    public function contains(int ...$containsValues): bool
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
     * @param int ...$containsValues
     * @return bool
     */
    public function containsAny(int ...$containsValues): bool
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
