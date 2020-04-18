<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use const INF;
use function array_keys;
use function class_exists;
use function count;
use function explode;
use function get_resource_type;
use function gettype;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function is_callable;
use function is_float;
use function is_integer;
use function is_nan;
use function is_numeric;
use function is_object;
use function is_resource;
use function is_scalar;
use function is_string;
use function is_subclass_of;
use function method_exists;
use function preg_match;
use function range;
use function rtrim;
use function settype;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strval;
use function substr;
use function trim;

/**
 * Type and range validations
 */
final class Check
{
    use StaticClassMixin;

    // min length
    public const NOT_EMPTY = 1;

    // strict type checks
    public const STRICT = true;

    /**
     * @param mixed $value
     * @param string|string[] $type
     * @param int|float|null $min
     * @param int|float|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function type(&$value, $type, $min = null, $max = null): void
    {
        if (is_array($type)) {
            [$type, $itemTypes] = $type;
        } elseif (strpos($type, '<') !== false) {
            [$type, $itemType] = explode('<', $type);
            $itemTypes = [trim($itemType, '>')];
        }
        switch ($type) {
            case Type::NULL:
                if ($value !== null) {
                    throw new InvalidTypeException($type, $value);
                }
                break;
            case Type::BOOL:
                if ($min !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $min is not applicable with type %s.', $type));
                } elseif ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not applicable with type %s.', $type));
                }
                self::bool($value);
                break;
            case Type::INT:
                self::int($value, $min, $max);
                break;
            case Type::FLOAT:
                self::float($value, $min, $max);
                break;
            case Type::STRING:
                self::string($value, $min, $max);
                break;
            case Type::PHP_ARRAY:
                self::array($value, $min, $max);
                break;
            case Type::OBJECT:
                if ($min !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $min is not applicable with type %s.', $type));
                } elseif ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not applicable with type %s.', $type));
                }
                self::object($value);
                break;
            case Type::RESOURCE:
                if ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not applicable with type %s.', $type));
                }
                self::resource($value, $min);
                break;
            case Type::PHP_CALLABLE:
                if ($min !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $min is not applicable with type %s.', $type));
                } elseif ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not applicable with type %s.', $type));
                }
                self::callable($value);
                break;
            default:
                if ($min !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $min is not applicable with type %s.', $type));
                } elseif ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not applicable with type %s.', $type));
                }
                self::object($value, $type);
                break;
        }
        if (isset($itemTypes)) {
            self::itemsOfTypes($value, $itemTypes);
        }
    }

    /**
     * @param mixed $value
     * @param string $type
     * @param int|float|null $min
     * @param int|float|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableType(&$value, string $type, $min = null, $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::type($value, $type, $min, $max);
    }

    /**
     * @param mixed $value
     * @param string[] $types
     * @param int|float|null $min
     * @param int|float|null $max
     * @throws \Dogma\InvalidTypeException
     */
    public static function types(&$value, array $types, $min = null, $max = null): void
    {
        foreach ($types as $type) {
            if ($type === Type::NULL && $value === null) {
                return;
            }
            try {
                self::type($value, $type, $min, $max);
                return;
            } catch (InvalidTypeException $e) {
                // pass
            }
        }
        throw new InvalidTypeException($types, $value);
    }

    /**
     * @param iterable|mixed[] $items
     * @param string $type
     * @param int|float|null $valueMin
     * @param int|float|null $valueMax
     * @throws \Dogma\InvalidTypeException
     */
    public static function itemsOfType(iterable $items, string $type, $valueMin = null, $valueMax = null): void
    {
        foreach ($items as &$value) {
            self::type($value, $type, $valueMin, $valueMax);
        }
    }

    /**
     * @param iterable|mixed[] $items
     * @param string[] $types
     * @param int|float|null $valueMin
     * @param int|float|null $valueMax
     * @throws \Dogma\InvalidTypeException
     */
    public static function itemsOfTypes(iterable $items, array $types, $valueMin = null, $valueMax = null): void
    {
        foreach ($items as &$value) {
            self::types($value, $types, $valueMin, $valueMax);
        }
    }

    /**
     * @param mixed $value
     * @throws \Dogma\InvalidTypeException
     */
    public static function null($value): void
    {
        if ($value !== null) {
            throw new InvalidTypeException(Type::NULL, $value);
        }
    }

    /**
     * @param mixed $value
     * @throws \Dogma\InvalidTypeException
     */
    public static function bool(&$value): void
    {
        if ($value === true || $value === false) {
            return;
        }
        if ($value === 0 || $value === 1 || $value === 0.0 || $value === 1.0 || $value === ''
            || $value === '0' || $value === '1' || $value === '0.0' || $value === '1.0'
        ) {
            $value = (bool) (int) $value;
            return;
        }
        throw new InvalidTypeException(Type::BOOL, $value);
    }

    /**
     * @param mixed $value
     * @throws \Dogma\InvalidTypeException
     */
    public static function nullableBool(&$value): void
    {
        if ($value === null) {
            return;
        }
        self::bool($value);
    }

    // integers --------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function int(&$value, ?int $min = null, ?int $max = null): void
    {
        if (is_integer($value)) {
            if ($min !== null || $max !== null) {
                self::range($value, $min, $max);
            }
            return;
        }
        if (!is_numeric($value)) {
            throw new InvalidTypeException(Type::INT, $value);
        }
        $actualType = gettype($value);
        $converted = (int) $value;
        $copy = $converted;
        settype($copy, $actualType);
        if ($copy !== $value && (!is_string($value) || rtrim(rtrim($value, '0'), '.') !== strval($copy))) {
            throw new InvalidTypeException(Type::INT, $value);
        }
        if ($min !== null || $max !== null) {
            self::range($value, $min, $max);
        }
        $value = $converted;
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableInt(&$value, ?int $min = null, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, $min, $max);
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function uint(&$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0) {
            throw new ValueOutOfBoundsException($value, 'uint(64)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableUint(&$value, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, null, $max);

        if ($value < 0) {
            throw new ValueOutOfBoundsException($value, 'int(64)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function int8(&$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT8_MIN || $value > IntBounds::INT8_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(8)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableInt8(&$value, ?int $min = null, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, $min, $max);

        if ($value < IntBounds::INT8_MIN || $value > IntBounds::INT8_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(8)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function uint8(&$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT8_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(8)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableUint8(&$value, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT8_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(8)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function int16(&$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT16_MIN || $value > IntBounds::INT16_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(16)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableInt16(&$value, ?int $min = null, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, $min, $max);

        if ($value < IntBounds::INT16_MIN || $value > IntBounds::INT16_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(16)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function uint16(&$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT16_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(16)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableUint16(&$value, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT16_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(16)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function int24(&$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT24_MIN || $value > IntBounds::INT24_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(24)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableInt24(&$value, ?int $min = null, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, $min, $max);

        if ($value < IntBounds::INT24_MIN || $value > IntBounds::INT24_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(24)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function uint24(&$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT24_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(24)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableUint24(&$value, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT24_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(24)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function int32(&$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT32_MIN || $value > IntBounds::INT32_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(32)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableInt32(&$value, ?int $min = null, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, $min, $max);

        if ($value < IntBounds::INT32_MIN || $value > IntBounds::INT32_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(32)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function uint32(&$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT32_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(32)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableUint32(&$value, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT32_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(32)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function int48(&$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT48_MIN || $value > IntBounds::INT48_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(48)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableInt48(&$value, ?int $min = null, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, $min, $max);

        if ($value < IntBounds::INT48_MIN || $value > IntBounds::INT48_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(48)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function uint48(&$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT48_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(48)');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableUint48(&$value, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT48_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(48)');
        }
    }

    // floats ----------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $value
     * @param float|null $min
     * @param float|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\InvalidValueException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function float(&$value, ?float $min = null, ?float $max = null): void
    {
        if (is_float($value)) {
            if (is_nan($value)) {
                throw new InvalidValueException($value, 'valid float');
            }
            if ($value === INF || $value === -INF) {
                throw new ValueOutOfRangeException($value, -INF, INF);
            }
            if ($min !== null || $max !== null) {
                self::range($value, $min, $max);
            }
            if ($value === -0.0) {
                $value = 0.0;
            }
            return;
        }
        if (!is_numeric($value)) {
            throw new InvalidTypeException(Type::FLOAT, $value);
        }
        $actualType = gettype($value);
        $converted = (float) $value;
        if ($converted === INF || $converted === -INF) {
            throw new ValueOutOfRangeException($value, -INF, INF);
        }
        $copy = $converted;
        settype($copy, $actualType);
        if ($copy !== $value && (!is_string($value) || rtrim(rtrim($value, '0'), '.') !== strval($copy))) {
            throw new InvalidTypeException(Type::FLOAT, $value);
        }
        if ($min !== null || $max !== null) {
            self::range($value, $min, $max);
        }
        if ($converted === -0.0) {
            $converted = 0.0;
        }
        $value = $converted;
    }

    /**
     * @param mixed $value
     * @param float|null $min
     * @param float|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\InvalidValueException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableFloat(&$value, ?float $min = null, ?float $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::float($value, $min, $max);
    }

    // decimal (float) -------------------------------------------------------------------------------------------------

    /**
     * @param mixed $value
     * @param float|null $totalDigits
     * @param float|null $afterDigits
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\InvalidValueException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function decimal(&$value, int $totalDigits, int $afterDigits = 0): void
    {
        self::float($value);
        $stringValue = $value . '.';
        [$before, $after] = explode('.', $stringValue);
        if ($before[0] === '-') {
            $before = substr($before, 1);
        }
        if (strlen($after) > $afterDigits) {
            throw new ValueOutOfBoundsException($value, sprintf('decimal(%d,%d)', $totalDigits, $afterDigits));
        }
        if (strlen($before) + strlen($after) > $totalDigits) {
            throw new ValueOutOfBoundsException($value, sprintf('decimal(%d,%d)', $totalDigits, $afterDigits));
        }
    }

    /**
     * @param mixed $value
     * @param float|null $totalDigits
     * @param float|null $afterDigits
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\InvalidValueException
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function nullableDecimal(&$value, int $totalDigits, int $afterDigits = 0): void
    {
        if ($value === null) {
            return;
        }
        self::decimal($value, $totalDigits, $afterDigits);
    }

    // strings ---------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function string(&$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        if (is_string($value)) {
            if ($minLength !== null || $maxLength !== null) {
                self::length($value, $minLength, $maxLength);
            }
            return;
        }
        if (!is_numeric($value)) {
            throw new InvalidTypeException(Type::STRING, $value);
        }
        self::float($value);
        $value = (string) $value;
    }

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableString(&$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        if ($value === null) {
            return;
        }
        self::string($value, $minLength, $maxLength);
    }

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function ascii(&$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        self::string($value, $minLength, $maxLength);

        if ($value !== Str::toAscii($value)) {
            throw new InvalidEncodingException($value, 'ascii');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableAscii(&$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        if ($value === null) {
            return;
        }
        self::string($value, $minLength, $maxLength);

        if ($value !== Str::toAscii($value)) {
            throw new InvalidEncodingException($value, 'ascii');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function utf8(&$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        self::string($value, $minLength, $maxLength);

        if (!Str::checkEncoding($value)) {
            throw new InvalidEncodingException($value, 'utf-8');
        }
    }

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableUtf8(&$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        if ($value === null) {
            return;
        }
        self::string($value, $minLength, $maxLength);

        if (!Str::checkEncoding($value)) {
            throw new InvalidEncodingException($value, 'utf-8');
        }
    }

    // lists -----------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $value
     * @throws \Dogma\InvalidTypeException
     */
    public static function traversable($value): void
    {
        if (!self::isIterable($value)) {
            throw new InvalidTypeException('array|Traversable', $value);
        }
    }

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     */
    public static function array($value, ?int $minLength = null, ?int $maxLength = null): void
    {
        if (!is_array($value)) {
            throw new InvalidTypeException(Type::PHP_ARRAY, $value);
        }
        self::range(count($value), $minLength, $maxLength);
    }

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     */
    public static function plainArray($value, ?int $minLength = null, ?int $maxLength = null): void
    {
        self::array($value, $minLength, $maxLength);
        if (!self::isPlainArray($value)) {
            throw new InvalidTypeException('array with integer keys from 0', $value);
        }
    }

    /**
     * @param mixed $value
     * @param string[] $types
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function tuple($value, array $types): void
    {
        self::object($value, Tuple::class);
        self::range(count($value), $length = count($types), $length);
        foreach ($value as $i => $val) {
            self::type($val, $types[$i]);
        }
    }

    /**
     * @param mixed $value
     * @param string[] $types
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public function nullableTuple($value, array $types): void
    {
        if ($value === null) {
            return;
        }
        self::tuple($value, $types);
    }

    // objects ---------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $value
     * @param string|null $className
     * @throws \Dogma\InvalidTypeException
     */
    public static function object($value, ?string $className = null): void
    {
        if (!is_object($value)) {
            throw new InvalidTypeException(Type::OBJECT, $value);
        }
        if ($className !== null && !is_a($value, $className)) {
            throw new InvalidTypeException($className, $value);
        }
    }

    /**
     * @param mixed $value
     * @param string|null $className
     * @throws \Dogma\InvalidTypeException
     */
    public static function instance($value, string $className): void
    {
        if ($className !== null && !is_a($value, $className)) {
            throw new InvalidTypeException($className, $value);
        }
    }

    /**
     * @param mixed $value
     * @param string|null $className
     * @throws \Dogma\InvalidTypeException
     */
    public static function nullableObject($value, ?string $className = null): void
    {
        if ($value === null) {
            return;
        }
        self::object($value, $className);
    }

    // other -----------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $value
     * @param string $type
     * @throws \Dogma\InvalidTypeException
     */
    public static function resource($value, ?string $type = null): void
    {
        if (!is_resource($value)) {
            throw new InvalidTypeException(Type::RESOURCE, $value);
        }
        if ($type !== null && get_resource_type($value) !== $type) {
            throw new InvalidTypeException(sprintf('%s (%s)', Type::RESOURCE, $type), $value);
        }
    }

    /**
     * @param mixed $value
     * @throws \Dogma\InvalidTypeException
     */
    public static function callable($value): void
    {
        if (!is_callable($value)) {
            throw new InvalidTypeException('callable', $value);
        }
    }

    /**
     * @param mixed $value
     * @param string $parentClass
     * @throws \Dogma\InvalidValueException
     */
    public static function className($value, ?string $parentClass = null): void
    {
        self::string($value);
        if (!class_exists($value, true)) {
            throw new InvalidValueException($value, 'class name');
        }
        if ($parentClass !== null && !is_subclass_of($value, $parentClass)) {
            throw new InvalidTypeException(sprintf('child class of %s', $parentClass), $value);
        }
    }

    /**
     * @param mixed $value
     * @throws \Dogma\InvalidValueException
     */
    public static function typeName($value): void
    {
        self::string($value);
        if (!class_exists($value, true) && !in_array($value, Type::listTypes())) {
            throw new InvalidValueException($value, 'type name');
        }
    }

    // helpers ---------------------------------------------------------------------------------------------------------

    /**
     * @param string $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function length(string $value, ?int $min = null, ?int $max = null): void
    {
        $length = Str::length($value);
        self::range($length, $min, $max);
    }

    /**
     * @param mixed[] $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function count(array $value, ?int $min = null, ?int $max = null): void
    {
        $count = count($value);
        self::range($count, $min, $max);
    }

    /**
     * @param string $value
     * @param string $regexp
     * @throws \Dogma\InvalidValueException
     */
    public static function match(string $value, string $regexp): void
    {
        if (!preg_match($regexp, $value)) {
            throw new InvalidValueException($value, $regexp);
        }
    }

    /**
     * Checks type specific bounds
     * @param mixed $value
     * @param \Dogma\Type $type
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function bounds($value, Type $type): void
    {
        if ($type->isInt()) {
            try {
                self::range($value, ...IntBounds::getRange($type->getSize(), $type->isSigned() ? Sign::SIGNED : Sign::UNSIGNED));
            } catch (ValueOutOfRangeException $e) {
                throw new ValueOutOfBoundsException($value, $type, $e);
            }
        } elseif ($type->isFloat() && $type->getSize() === BitSize::BITS_32) {
            $length = strlen(rtrim(str_replace('.', '', $value), '0'));
            // single precision float can handle up to 9 digits of precision
            if ($length > 9) {
                throw new ValueOutOfBoundsException($value, $type);
            }
        } elseif ($type->isString()) {
            try {
                /// todo: take into account string encoding?
                self::range(Str::length($value), 0, $type->getSize());
            } catch (ValueOutOfRangeException $e) {
                throw new ValueOutOfBoundsException($value, $type, $e);
            }
        } else {
            throw new InvalidArgumentException(sprintf('Cannot check bounds of type %s.', $type->getId()));
        }
    }

    /**
     * Checks type specific bounds for integers
     * @param mixed $value
     * @param int $size
     * @param bool $signed
     * @throws \Dogma\ValueOutOfBoundsException
     */
    public static function intBounds($value, int $size, bool $signed = true): void
    {
        try {
            self::range($value, ...IntBounds::getRange($size, $signed ? Sign::SIGNED : Sign::UNSIGNED));
        } catch (ValueOutOfRangeException $e) {
            throw new ValueOutOfBoundsException($value, sprintf('%sint(%d)', $signed ? '' : 'u', $size), $e);
        }
    }

    /**
     * Checks user defined range
     * @param mixed $value
     * @param int|float|null $min
     * @param int|float|null $max
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function range($value, $min = null, $max = null): void
    {
        if ($min !== null && $value < $min) {
            throw new ValueOutOfRangeException($value, $min, $max);
        }
        if ($max !== null && $value > $max) {
            throw new ValueOutOfRangeException($value, $min, $max);
        }
    }

    /**
     * @param mixed $value
     * @param int|float $min
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function min($value, $min): void
    {
        if ($value < $min) {
            throw new ValueOutOfRangeException($value, $min, null);
        }
    }

    /**
     * @param mixed $value
     * @param int|float $max
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function max($value, $max): void
    {
        if ($value > $max) {
            throw new ValueOutOfRangeException($value, null, $max);
        }
    }

    /**
     * @param int|float $value
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function positive($value): void
    {
        if ($value <= 0) {
            throw new ValueOutOfRangeException($value, 0, null);
        }
    }

    /**
     * @param int|float $value
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nonNegative($value): void
    {
        if ($value < 0) {
            throw new ValueOutOfRangeException($value, 0, null);
        }
    }

    /**
     * @param int|float $value
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nonPositive($value): void
    {
        if ($value > 0) {
            throw new ValueOutOfRangeException($value, null, 0);
        }
    }

    /**
     * @param int|float $value
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function negative($value): void
    {
        if ($value >= 0) {
            throw new ValueOutOfRangeException($value, null, 0);
        }
    }

    /**
     * @param mixed ...$values
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function oneOf(...$values): void
    {
        $count = 0;
        foreach ($values as $value) {
            if (isset($value)) {
                $count++;
            }
        }
        if ($count !== 1) {
            throw new ValueOutOfRangeException($count, 1, 1);
        }
    }

    /**
     * @param mixed $value
     * @param mixed ...$allowedValues
     * @throws \Dogma\InvalidValueException
     */
    public static function enum($value, ...$allowedValues): void
    {
        if (!in_array($value, $allowedValues, true)) {
            $allowed = implode('|', Arr::map($allowedValues, function ($value) {
                return (is_scalar($value) || is_object($value) && method_exists($value, '__toString'))
                    ? (string) $value
                    : gettype($value);
            }));
            throw new InvalidValueException($value, $allowed);
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isIterable($value): bool
    {
        return is_array($value)
            || $value instanceof \stdClass
            || ($value instanceof \Traversable);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isPlainArray($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        $count = count($value);

        return $count === 0 || array_keys($value) === range(0, $count - 1);
    }

}
