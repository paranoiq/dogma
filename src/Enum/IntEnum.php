<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Enum;

use Dogma\Equalable;
use Dogma\InvalidValueException;
use function array_search;
use function in_array;

/**
 * Base class for enums with integer values.
 *
 * @see about.md to find out how enum inheritance works.
 */
abstract class IntEnum implements Enum
{
    use EnumSetMixin;

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static array $availableValues = [];

    private int $value;

    final public function __construct(int $value)
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        if (!static::validateValue($value)) {
            throw new InvalidValueException($value, $class);
        }

        $this->value = $value;
    }

    /**
     * @return static
     */
    final public static function get(int $value): self
    {
        return new static($value);
    }

    /**
     * Validates given value. Can also normalize the value, if needed.
     */
    public static function validateValue(int &$value): bool
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return in_array($value, self::$availableValues[$class], true);
    }

    final public function getValue(): int
    {
        return $this->value;
    }

    final public function getConstantName(): string
    {
        /** @var string $result */
        $result = array_search($this->value, self::$availableValues[static::class], true);

        return $result;
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
     * @param IntEnum $other
     */
    final public function equals(Equalable $other): bool
    {
        $this->checkCompatibility($other);

        return $this->value === $other->value;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int $value
     */
    final public function equalsValue($value): bool
    {
        return $value === $this->value;
    }

}
