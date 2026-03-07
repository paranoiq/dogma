<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable

namespace Dogma;

use stdClass;
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
use function is_infinite;
use function is_int;
use function is_iterable;
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
use function str_replace;
use function strlen;
use function strpos;
use function strval;
use function substr;
use function trim;
use const INF;

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
     * @param string|array<string> $type
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function type(
        mixed &$value,
        string|array $type,
        int|float|string|null $min = null,
        int|float|null $max = null,
    ): void
    {
        $itemTypes = null;
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
                    throw new InvalidArgumentException("Parameter \$min is not applicable with type $type.");
                } elseif ($max !== null) {
                    throw new InvalidArgumentException("Parameter \$max is not applicable with type $type.");
                }
                self::bool($value);
                break;
            case Type::INT:
                /**
                 * @var int|null $min
                 * @var int|null $max
                 */
                self::int($value, $min, $max);
                break;
            case Type::FLOAT:
                /** @var float|null $min */
                self::float($value, $min, $max);
                break;
            case Type::STRING:
                /**
                 * @var int|null $min
                 * @var int|null $max
                 */
                self::string($value, $min, $max);
                break;
            case Type::PHP_ARRAY:
                /**
                 * @var int|null $min
                 * @var int|null $max
                 */
                self::array($value, $min, $max);
                break;
            case Type::OBJECT:
                if ($min !== null) {
                    throw new InvalidArgumentException("Parameter \$min is not applicable with type $type.");
                } elseif ($max !== null) {
                    throw new InvalidArgumentException("Parameter \$max is not applicable with type $type.");
                }
                self::object($value);
                break;
            case Type::RESOURCE:
                if ($max !== null) {
                    throw new InvalidArgumentException("Parameter \$max is not applicable with type $type.");
                }
                /** @var string|null $min */
                self::resource($value, $min);
                break;
            case Type::PHP_CALLABLE:
                if ($min !== null) {
                    throw new InvalidArgumentException("Parameter \$min is not applicable with type $type.");
                } elseif ($max !== null) {
                    throw new InvalidArgumentException("Parameter \$max is not applicable with type $type.");
                }
                self::callable($value);
                break;
            default:
                if ($min !== null) {
                    throw new InvalidArgumentException("Parameter \$min is not applicable with type $type.");
                } elseif ($max !== null) {
                    throw new InvalidArgumentException("Parameter \$max is not applicable with type $type.");
                }
                self::object($value, $type);
                break;
        }
        if ($itemTypes !== null) {
            /** @var array<string> $itemTypes */
            self::itemsOfTypes($value, $itemTypes);
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function nullableType(mixed &$value, string $type, int|float|null $min = null, int|float|null $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::type($value, $type, $min, $max);
    }

    /**
     * @param array<string> $types
     * @throws InvalidTypeException
     */
    public static function types(mixed &$value, array $types, int|float|null $min = null, int|float|null $max = null): void
    {
        foreach ($types as $type) {
            if ($type === Type::NULL && $value === null) {
                return;
            }
            try {
                self::type($value, $type, $min, $max);
                return;
            } catch (InvalidTypeException) {
                // pass
            }
        }
        throw new InvalidTypeException($types, $value);
    }

    /**
     * @param iterable<mixed> $items
     * @throws InvalidTypeException
     */
    public static function itemsOfType(iterable $items, string $type, int|float|null $valueMin = null, int|float|null $valueMax = null): void
    {
        foreach ($items as &$value) {
            self::type($value, $type, $valueMin, $valueMax);
        }
    }

    /**
     * @param iterable<mixed> $items
     * @param array<string> $types
     * @throws InvalidTypeException
     */
    public static function itemsOfTypes(iterable $items, array $types, int|float|null $valueMin = null, int|float|null $valueMax = null): void
    {
        foreach ($items as &$value) {
            self::types($value, $types, $valueMin, $valueMax);
        }
    }

    /**
     * @throws InvalidTypeException
     */
    public static function null(mixed $value): void
    {
        if ($value !== null) {
            throw new InvalidTypeException(Type::NULL, $value);
        }
    }

    /**
     * @throws InvalidTypeException
     */
    public static function bool(mixed &$value): void
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
     * @throws InvalidTypeException
     */
    public static function nullableBool(mixed &$value): void
    {
        if ($value === null) {
            return;
        }
        self::bool($value);
    }

    // integers --------------------------------------------------------------------------------------------------------

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function int(mixed &$value, ?int $min = null, ?int $max = null): void
    {
        if (is_int($value)) {
            if ($min !== null || $max !== null) {
                self::range($value, $min, $max);
            }
            return;
        }
        if (!is_numeric($value) || (is_float($value) && (is_nan($value) || is_infinite($value)))) {
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
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function nullableInt(mixed &$value, ?int $min = null, ?int $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::int($value, $min, $max);
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function uint(mixed &$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0) {
            throw new ValueOutOfBoundsException($value, 'uint(64)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function nullableUint(mixed &$value, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function int8(mixed &$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT8_MIN || $value > IntBounds::INT8_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(8)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableInt8(mixed &$value, ?int $min = null, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function uint8(mixed &$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT8_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(8)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableUint8(mixed &$value, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function int16(mixed &$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT16_MIN || $value > IntBounds::INT16_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(16)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableInt16(mixed &$value, ?int $min = null, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function uint16(mixed &$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT16_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(16)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableUint16(mixed &$value, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function int24(mixed &$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT24_MIN || $value > IntBounds::INT24_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(24)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableInt24(mixed &$value, ?int $min = null, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function uint24(mixed &$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT24_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(24)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableUint24(mixed &$value, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function int32(mixed &$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT32_MIN || $value > IntBounds::INT32_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(32)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableInt32(mixed &$value, ?int $min = null, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function uint32(mixed &$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT32_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(32)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableUint32(mixed &$value, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function int48(mixed &$value, ?int $min = null, ?int $max = null): void
    {
        self::int($value, $min, $max);

        if ($value < IntBounds::INT48_MIN || $value > IntBounds::INT48_MAX) {
            throw new ValueOutOfBoundsException($value, 'int(48)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableInt48(mixed &$value, ?int $min = null, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function uint48(mixed &$value, ?int $max = null): void
    {
        self::int($value, null, $max);

        if ($value < 0 || $value > IntBounds::UINT48_MAX) {
            throw new ValueOutOfBoundsException($value, 'uint(48)');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfBoundsException
     * @throws ValueOutOfRangeException
     */
    public static function nullableUint48(mixed &$value, ?int $max = null): void
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
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws ValueOutOfRangeException
     */
    public static function float(mixed &$value, ?float $min = null, ?float $max = null): void
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
            throw new ValueOutOfRangeException($converted, -INF, INF);
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
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws ValueOutOfRangeException
     */
    public static function nullableFloat(mixed &$value, ?float $min = null, ?float $max = null): void
    {
        if ($value === null) {
            return;
        }
        self::float($value, $min, $max);
    }

    // decimal (float) -------------------------------------------------------------------------------------------------

    /**
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws ValueOutOfBoundsException
     */
    public static function decimal(mixed &$value, int $totalDigits, int $afterDigits = 0): void
    {
        self::float($value);
        $stringValue = $value . '.';
        [$before, $after] = explode('.', $stringValue);
        if ($before[0] === '-') {
            $before = substr($before, 1);
        }
        if (strlen($after) > $afterDigits) {
            throw new ValueOutOfBoundsException($value, "decimal($totalDigits, $afterDigits)");
        }
        if (strlen($before) + strlen($after) > $totalDigits) {
            throw new ValueOutOfBoundsException($value, "decimal($totalDigits, $afterDigits)");
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws ValueOutOfBoundsException
     */
    public static function nullableDecimal(mixed &$value, int $totalDigits, int $afterDigits = 0): void
    {
        if ($value === null) {
            return;
        }
        self::decimal($value, $totalDigits, $afterDigits);
    }

    // strings ---------------------------------------------------------------------------------------------------------

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function string(mixed &$value, ?int $minLength = null, ?int $maxLength = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function nullableString(mixed &$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        if ($value === null) {
            return;
        }
        self::string($value, $minLength, $maxLength);
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function ascii(mixed &$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        self::string($value, $minLength, $maxLength);

        if ($value !== Str::toAscii($value)) {
            throw new InvalidEncodingException($value, 'ascii');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function nullableAscii(mixed &$value, ?int $minLength = null, ?int $maxLength = null): void
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
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function utf8(mixed &$value, ?int $minLength = null, ?int $maxLength = null): void
    {
        self::string($value, $minLength, $maxLength);

        if (!Str::checkEncoding($value)) {
            throw new InvalidEncodingException($value, 'utf-8');
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function nullableUtf8(mixed &$value, ?int $minLength = null, ?int $maxLength = null): void
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
     * @throws InvalidTypeException
     */
    public static function traversable(mixed $value): void
    {
        if (!self::isIterable($value)) {
            throw new InvalidTypeException('array|Traversable', $value);
        }
    }

    /**
     * @throws InvalidTypeException
     */
    public static function array(mixed $value, ?int $minLength = null, ?int $maxLength = null): void
    {
        if (!is_array($value)) {
            throw new InvalidTypeException(Type::PHP_ARRAY, $value);
        }
        self::range(count($value), $minLength, $maxLength);
    }

    /**
     * @throws InvalidTypeException
     */
    public static function plainArray(mixed $value, ?int $minLength = null, ?int $maxLength = null): void
    {
        self::array($value, $minLength, $maxLength);
        if (!self::isPlainArray($value)) {
            throw new InvalidTypeException('array with integer keys from 0', $value);
        }
    }

    /**
     * @param array<string> $types
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public static function tuple(mixed $value, array $types): void
    {
        self::object($value, Tuple::class);
        self::range(count($value), $length = count($types), $length);
        foreach ($value as $i => $val) {
            self::type($val, $types[$i]);
        }
    }

    /**
     * @param array<string> $types
     * @throws InvalidTypeException
     * @throws ValueOutOfRangeException
     */
    public function nullableTuple(mixed $value, array $types): void
    {
        if ($value === null) {
            return;
        }
        self::tuple($value, $types);
    }

    // objects ---------------------------------------------------------------------------------------------------------

    /**
     * @throws InvalidTypeException
     */
    public static function object(mixed $value, ?string $className = null): void
    {
        if (!is_object($value)) {
            throw new InvalidTypeException(Type::OBJECT, $value);
        }
        if ($className !== null && !is_a($value, $className)) {
            throw new InvalidTypeException($className, $value);
        }
    }

    /**
     * @throws InvalidTypeException
     */
    public static function instance(mixed $value, string $className): void
    {
        if (!is_a($value, $className)) {
            throw new InvalidTypeException($className, $value);
        }
    }

    /**
     * @throws InvalidTypeException
     */
    public static function nullableObject(mixed $value, ?string $className = null): void
    {
        if ($value === null) {
            return;
        }
        self::object($value, $className);
    }

    // other -----------------------------------------------------------------------------------------------------------

    /**
     * @throws InvalidTypeException
     */
    public static function resource(mixed $value, ?string $type = null): void
    {
        if (!is_resource($value)) {
            throw new InvalidTypeException(Type::RESOURCE, $value);
        }
        if ($type !== null && get_resource_type($value) !== $type) {
            throw new InvalidTypeException("resource($type)", $value);
        }
    }

    /**
     * @throws InvalidTypeException
     */
    public static function callable(mixed $value): void
    {
        if (!is_callable($value)) {
            throw new InvalidTypeException('callable', $value);
        }
    }

    /**
     * @throws InvalidValueException
     */
    public static function className(mixed $value, ?string $parentClass = null): void
    {
        self::string($value);
        if (!class_exists($value, true)) {
            throw new InvalidValueException($value, 'class name');
        }
        if ($parentClass !== null && !is_subclass_of($value, $parentClass)) {
            throw new InvalidTypeException("child class of $parentClass", $value);
        }
    }

    /**
     * @throws InvalidValueException
     */
    public static function typeName(mixed $value): void
    {
        self::string($value);
        if (!class_exists($value, true) && !in_array($value, Type::listTypes(), true)) {
            throw new InvalidValueException($value, 'type name');
        }
    }

    // helpers ---------------------------------------------------------------------------------------------------------

    /**
     * @throws ValueOutOfRangeException
     */
    public static function length(string $value, ?int $min = null, ?int $max = null): void
    {
        $length = Str::length($value);
        self::range($length, $min, $max);
    }

    /**
     * @param array<mixed> $value
     * @throws ValueOutOfRangeException
     */
    public static function count(array $value, ?int $min = null, ?int $max = null): void
    {
        $count = count($value);
        self::range($count, $min, $max);
    }

    /**
     * @throws InvalidValueException
     */
    public static function match(string $value, string $regexp): void
    {
        if (!preg_match($regexp, $value)) {
            throw new InvalidValueException($value, $regexp);
        }
    }

    /**
     * Checks type specific bounds
     * @throws ValueOutOfBoundsException
     */
    public static function bounds(mixed $value, Type $type): void
    {
        if ($type->isInt()) {
            try {
                /** @var int $size */
                $size = $type->getSize();
                self::range($value, ...IntBounds::getRange($size, $type->isSigned() ? Sign::SIGNED : Sign::UNSIGNED));
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
                // todo: take into account string encoding?
                /** @var int $size */
                $size = $type->getSize();
                self::range(Str::length($value), 0, $size);
            } catch (ValueOutOfRangeException $e) {
                throw new ValueOutOfBoundsException($value, $type, $e);
            }
        } else {
            throw new InvalidArgumentException("Cannot check bounds of type {$type->getId()}.");
        }
    }

    /**
     * Checks type specific bounds for integers
     * @throws ValueOutOfBoundsException
     */
    public static function intBounds(mixed $value, int $size, bool $signed = true): void
    {
        try {
            self::range($value, ...IntBounds::getRange($size, $signed ? Sign::SIGNED : Sign::UNSIGNED));
        } catch (ValueOutOfRangeException $e) {
            throw new ValueOutOfBoundsException($value, $signed ? "int($size)" : "uint($size)", $e);
        }
    }

    /**
     * Checks user defined range
     * @throws ValueOutOfRangeException
     */
    public static function range(mixed $value, int|float|null $min = null, int|float|null $max = null): void
    {
        if ($min !== null && $value < $min) {
            throw new ValueOutOfRangeException($value, $min, $max);
        }
        if ($max !== null && $value > $max) {
            throw new ValueOutOfRangeException($value, $min, $max);
        }
    }

    /**
     * @throws ValueOutOfRangeException
     */
    public static function min(mixed $value, int|float $min): void
    {
        if ($value < $min) {
            throw new ValueOutOfRangeException($value, $min, null);
        }
    }

    /**
     * @throws ValueOutOfRangeException
     */
    public static function max(mixed $value, int|float $max): void
    {
        if ($value > $max) {
            throw new ValueOutOfRangeException($value, null, $max);
        }
    }

    /**
     * @throws ValueOutOfRangeException
     */
    public static function positive(int|float $value): void
    {
        if ($value <= 0) {
            throw new ValueOutOfRangeException($value, 0, null);
        }
    }

    /**
     * @throws ValueOutOfRangeException
     */
    public static function nonNegative(int|float $value): void
    {
        if ($value < 0) {
            throw new ValueOutOfRangeException($value, 0, null);
        }
    }

    /**
     * @throws ValueOutOfRangeException
     */
    public static function nonPositive(int|float $value): void
    {
        if ($value > 0) {
            throw new ValueOutOfRangeException($value, null, 0);
        }
    }

    /**
     * @throws ValueOutOfRangeException
     */
    public static function negative(int|float $value): void
    {
        if ($value >= 0) {
            throw new ValueOutOfRangeException($value, null, 0);
        }
    }

    /**
     * @throws ValueOutOfRangeException
     */
    public static function oneOf(mixed ...$values): void
    {
        $count = 0;
        foreach ($values as $value) {
            if ($value !== null) {
                $count++;
            }
        }
        if ($count !== 1) {
            throw new ValueOutOfRangeException($count, 1, 1);
        }
    }

    /**
     * @throws InvalidValueException
     */
    public static function enum(mixed $value, mixed ...$allowedValues): void
    {
        if (!in_array($value, $allowedValues, true)) {
            $allowed = implode('|', Arr::map($allowedValues, static function ($value) {
                return (is_scalar($value) || (is_object($value) && method_exists($value, '__toString')))
                    ? (string) $value
                    : gettype($value);
            }));
            throw new InvalidValueException($value, $allowed);
        }
    }

    public static function isIterable(mixed $value): bool
    {
        return is_iterable($value) || $value instanceof stdClass;
    }

    public static function isPlainArray(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        $count = count($value);

        return $count === 0 || array_keys($value) === range(0, $count - 1);
    }

}
