<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

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
        }
        switch ($type) {
            case Type::NULL:
                if (!is_null($value)) {
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
                if ($max !== null) {
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
     * @param iterable $items
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
     * @param iterable $items
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
        if ($copy !== $value && (!is_string($value) || rtrim(rtrim($value, '0'), '.') !== $copy)) {
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
        if ($copy !== $value && (!is_string($value) || rtrim(rtrim($value, '0'), '.') !== $copy)) {
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
    public static function nullableObject($value, ?string $className = null): void
    {
        if ($value === null) {
            return;
        }
        self::object($value, $className);
    }

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
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function bounds($value, Type $type): void
    {
        if ($type->isInt()) {
            try {
                self::range($value, ...BitSize::getIntRange($type->getSize(), $type->isSigned() ? Sign::SIGNED : Sign::UNSIGNED));
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
     * @return bool
     */
    public static function isIterable($value): bool
    {
        return is_array($value)
            || $value instanceof \stdClass
            || ($value instanceof \Traversable && !$value instanceof NonIterable);
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
