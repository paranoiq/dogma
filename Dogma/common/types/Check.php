<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Nette\Utils\Strings;

/**
 * Type and range validations
 */
final class Check
{
    use \Dogma\StaticClassMixin;

    // min length
    const NOT_EMPTY = 1;

    // strict type checks
    const STRICT = true;

    /**
     * @param &mixed $value
     * @param string|string[] $type
     * @param int|float|null $min
     * @param int|float|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function type(&$value, $type, $min = null, $max = null)
    {
        if (is_array($type)) {
            list($type, $itemTypes) = $type;
        }
        switch ($type) {
            case Type::NULL:
                if (!is_null($value)) {
                    throw new \Dogma\InvalidTypeException($type, $value);
                }
                break;
            case Type::BOOL:
                if ($min !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $min is not aplicable with type %s.', $type));
                } elseif ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not aplicable with type %s.', $type));
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
                    throw new \InvalidArgumentException(sprintf('Parameter $min is not aplicable with type %s.', $type));
                } elseif ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not aplicable with type %s.', $type));
                }
                self::object($value);
                break;
            case Type::RESOURCE:
                if ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not aplicable with type %s.', $type));
                }
                self::resource($value, $min);
                break;
            case Type::PHP_CALLABLE:
                if ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not aplicable with type %s.', $type));
                }
                self::callable($value);
                break;
            default:
                if ($min !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $min is not aplicable with type %s.', $type));
                } elseif ($max !== null) {
                    throw new \InvalidArgumentException(sprintf('Parameter $max is not aplicable with type %s.', $type));
                }
                self::object($value, $type);
                break;
        }
        if (isset($itemTypes)) {
            self::itemsOfTypes($value, $itemTypes);
        }
    }

    /**
     * @param &mixed $value
     * @param string $type
     * @param int|float|null $min
     * @param int|float|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableType(&$value, string $type, $min = null, $max = null)
    {
        if ($value === null) {
            return;
        }
        self::type($value, $type, $min, $max);
    }

    /**
     * @param &mixed $value
     * @param string[] $types
     * @param int|float|null $min
     * @param int|float|null $max
     * @throws \Dogma\InvalidTypeException
     */
    public static function types(&$value, array $types, $min = null, $max = null)
    {
        foreach ($types as $type) {
            if ($type === Type::NULL && $value === null) {
                return;
            }
            try {
                self::type($value, $type, $min, $max);
                return;
            } catch (\Dogma\InvalidTypeException $e) {
                // pass
            }
        }
        throw new \Dogma\InvalidTypeException($types, $value);
    }

    /**
     * @param &array|\Traversable $array
     * @param string $type
     * @param int|float|null $valueMin
     * @param int|float|null $valueMax
     * @throws \Dogma\InvalidTypeException
     */
    public static function itemsOfType($array, string $type, $valueMin = null, $valueMax = null)
    {
        foreach ($array as &$value) {
            self::type($value, $type, $valueMin, $valueMax);
        }
    }

    /**
     * @param &array|\Traversable $array
     * @param string[] $types
     * @param int|float|null $valueMin
     * @param int|float|null $valueMax
     * @throws \Dogma\InvalidTypeException
     */
    public static function itemsOfTypes($array, array $types, $valueMin = null, $valueMax = null)
    {
        foreach ($array as &$value) {
            self::types($value, $types, $valueMin, $valueMax);
        }
    }

    /**
     * @param &mixed $value
     * @throws \Dogma\InvalidTypeException
     */
    public static function bool(&$value)
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
        throw new \Dogma\InvalidTypeException(Type::BOOL, $value);
    }

    /**
     * @param &mixed $value
     * @throws \Dogma\InvalidTypeException
     */
    public static function nullableBoolean(&$value)
    {
        if ($value === null) {
            return;
        }
        self::bool($value);
    }

    /**
     * @param &mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function int(&$value, int $min = null, int $max = null)
    {
        if (is_integer($value)) {
            if ($min !== null || $max !== null) {
                self::range($value, $min, $max);
            }
            return;
        }
        if (!is_numeric($value)) {
            throw new \Dogma\InvalidTypeException(Type::INT, $value);
        }
        $actualType = gettype($value);
        $converted = (int) $value;
        $copy = $converted;
        settype($copy, $actualType);
        if ($copy !== $value && (!is_string($value) || rtrim(rtrim($value, '0'), '.') !== $copy)) {
            throw new \Dogma\InvalidTypeException(Type::INT, $value);
        }
        if ($min !== null || $max !== null) {
            self::range($value, $min, $max);
        }
        $value = $converted;
    }

    /**
     * @param &mixed $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableInteger(&$value, int $min = null, int $max = null)
    {
        if ($value === null) {
            return;
        }
        self::int($value, $min, $max);
    }

    /**
     * Positive integer (higher then 0)
     * @param &mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function natural(&$value, int $max = null)
    {
        self::int($value, 1, $max);
    }

    /**
     * Positive integer (higher then 0) or null
     * @param &mixed $value
     * @param int|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableNatural(&$value, int $max = null)
    {
        self::nullableInteger($value, 1, $max);
    }

    /**
     * @param &mixed $value
     * @param float|null $min
     * @param float|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\InvalidValueException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function float(&$value, float $min = null, float $max = null)
    {
        if (is_float($value)) {
            if (is_nan($value)) {
                throw new \Dogma\InvalidValueException($value, 'valid float');
            }
            if ($value === INF || $value === -INF) {
                throw new \Dogma\ValueOutOfRangeException($value, -INF, INF);
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
            throw new \Dogma\InvalidTypeException(Type::FLOAT, $value);
        }
        $actualType = gettype($value);
        $converted = (float) $value;
        if ($converted === INF || $converted === -INF) {
            throw new \Dogma\ValueOutOfRangeException($value, -INF, INF);
        }
        $copy = $converted;
        settype($copy, $actualType);
        if ($copy !== $value && (!is_string($value) || rtrim(rtrim($value, '0'), '.') !== $copy)) {
            throw new \Dogma\InvalidTypeException(Type::FLOAT, $value);
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
     * @param &mixed $value
     * @param float|null $min
     * @param float|null $max
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\InvalidValueException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableFloat(&$value, float $min = null, float $max = null)
    {
        if ($value === null) {
            return;
        }
        self::float($value, $min, $max);
    }

    /**
     * @param &mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function string(&$value, int $minLength = null, int $maxLength = null)
    {
        if (is_string($value)) {
            if ($minLength !== null || $maxLength !== null) {
                self::length($value, $minLength, $maxLength);
            }
            return;
        }
        if (!is_numeric($value)) {
            throw new \Dogma\InvalidTypeException(Type::STRING, $value);
        }
        if ($value === -0.0) {
            $value = 0.0;
        }
        $actualType = gettype($value);
        $converted = (string) $value;
        $copy = $converted;
        settype($copy, $actualType);
        if ($copy !== $value && !(is_nan($copy) && $converted === 'NAN')) {
            throw new \Dogma\InvalidTypeException(Type::FLOAT, $value);
        }
        if ($minLength !== null || $maxLength !== null) {
            self::length($value, $minLength, $maxLength);
        }
        $value = $converted;
    }

    /**
     * @param &mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function nullableString(&$value, int $minLength = null, int $maxLength = null)
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
    public static function traversable($value)
    {
        if (!self::isTraversable($value)) {
            throw new \Dogma\InvalidTypeException('array|Traversable', $value);
        }
    }

    /**
     * @param mixed $value
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     */
    public static function array($value, int $minLength = null, int $maxLength = null)
    {
        if (!is_array($value)) {
            throw new \Dogma\InvalidTypeException(Type::PHP_ARRAY, $value);
        }
        self::range(count($value), $minLength, $maxLength);
    }

    /**
     * @param mixed $array
     * @param int|null $minLength
     * @param int|null $maxLength
     * @throws \Dogma\InvalidTypeException
     */
    public static function plainArray($value, int $minLength = null, int $maxLength = null)
    {
        self::array($value, $minLength, $maxLength);
        if (!self::isPlainArray($value)) {
            throw new \Dogma\InvalidTypeException('array with integer keys from 0', $value);
        }
    }

    /**
     * @param mixed $value
     * @param string[] $types
     * @throws \Dogma\InvalidTypeException
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function tuple($value, array $types)
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
    public function nullableTuple($value, array $types)
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
    public static function object($value, string $className = null)
    {
        if (!is_object($value)) {
            throw new \Dogma\InvalidTypeException(Type::OBJECT, $value);
        }
        if ($className !== null && !is_a($value, $className)) {
            throw new \Dogma\InvalidTypeException($className, $value);
        }
    }

    /**
     * @param mixed $value
     * @param string|null $className
     * @throws \Dogma\InvalidTypeException
     */
    public static function nullableObject($value, string $className = null)
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
    public static function resource($value, string $type = null)
    {
        if (!is_resource($value)) {
            throw new \Dogma\InvalidTypeException(Type::RESOURCE, $value);
        }
        if ($type !== null && get_resource_type($value) !== $type) {
            throw new \Dogma\InvalidTypeException(sprintf('%s (%s)', Type::RESOURCE, $type), $value);
        }
    }

    /**
     * @param mixed $value
     * @throws \Dogma\InvalidTypeException
     */
    public static function callable($value)
    {
        if (!is_callable($value)) {
            throw new \Dogma\InvalidTypeException('callable', $value);
        }
    }

    /**
     * @param mixed $value
     * @param string $parentClass
     * @throws \Dogma\InvalidValueException
     */
    public static function className($value, string $parentClass = null)
    {
        self::string($value);
        if (!class_exists($value, true)) {
            throw new \Dogma\InvalidValueException($value, 'class name');
        }
        if ($parentClass !== null && !is_subclass_of($value, $parentClass)) {
            throw new \Dogma\InvalidTypeException(sprintf('child class of %s', $parentClass), $value);
        }
    }

    /**
     * @param mixed $value
     * @throws \Dogma\InvalidValueException
     */
    public static function typeName($value)
    {
        self::string($value);
        if (!class_exists($value, true) && !in_array($value, Type::listTypes())) {
            throw new \Dogma\InvalidValueException($value, 'type name');
        }
    }

    /**
     * @param string $value
     * @param int|null $min
     * @param int|null $max
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function length($value, int $min = null, int $max = null)
    {
        if (!is_string($value)) {
            throw new \Dogma\InvalidTypeException(Type::STRING, $value);
        }
        $length = Strings::length($value);
        self::range($length, $min, $max);
    }

    /**
     * @param mixed $value
     * @param int|float $min
     * @param int|float $max
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function range($value, int $min = null, int $max = null)
    {
        if ($min !== null && $value < $min) {
            throw new \Dogma\ValueOutOfRangeException($value, $min, $max);
        }
        if ($max !== null && $value > $max) {
            throw new \Dogma\ValueOutOfRangeException($value, $min, $max);
        }
    }

    /**
     * @param mixed $value
     * @param int|float $min
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function min($value, $min)
    {
        if ($value < $min) {
            throw new \Dogma\ValueOutOfRangeException($value, $min, null);
        }
    }

    /**
     * @param mixed $value
     * @param int|float $max
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function max($value, $max)
    {
        if ($value > $max) {
            throw new \Dogma\ValueOutOfRangeException($value, null, $max);
        }
    }

    /**
     * @param mixed ...$values
     * @throws \Dogma\ValueOutOfRangeException
     */
    public static function oneOf(...$values)
    {
        $count = 0;
        foreach ($values as $value) {
            if (isset($value)) {
                $count++;
            }
        }
        if ($count !== 1) {
            throw new \Dogma\ValueOutOfRangeException($count, 1, 1);
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isTraversable($value): bool
    {
        return is_array($value) || $value instanceof \stdClass
            || ($value instanceof \Traversable && !$value instanceof NonIterable);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isPlainArray($value): bool
    {
        return is_array($value) && (($count = count($value)) === 0 || array_keys($value) === range(0, $count - 1));
    }

}
