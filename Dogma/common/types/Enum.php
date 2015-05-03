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
    use StrictBehaviorMixin;
    use NonIterableMixin;
    use NonCloneableMixin;
    use NonSerializableMixin;

    /** @var \Dogma\Enum[][] ($class => ($value => $enum)) */
    private static $instances = [];

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static $availableValues = [];

    /** @var mixed */
    private $value;

    /**
     * @param string
     * @param mixed
     */
    final private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param scalar
     * @return static
     */
    final public static function get($value)
    {
        if (empty(self::$availableValues[$class = get_called_class()])) {
            self::init($class);
        }

        $values = self::$availableValues[$class];
        if (in_array($value, self::$availableValues[$class])) {
            if (!array_key_exists($value, self::$instances[$class])) {
                self::$instances[$class][$value] = new static($value);
            }
            return self::$instances[$class][$value];
        }

        throw new \Dogma\InvalidValueException($value, $class);
    }

    /**
     * @return mixed
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    final public function getConstantName()
    {
        $constants = array_flip(self::$availableValues[get_called_class()]);
        return $constants[$this->value];
    }

    /**
     * @param mixed|\Dogma\Enum $value
     */
    final public function equals($value)
    {
        if (is_scalar($value)) {
            $value = static::get($value);
        } elseif (get_class($value) !== static::class) {
            throw new \Dogma\InvalidTypeException(static::class, $value);
        }

        return $this->getValue() === $value->getValue();
    }

    /**
     * @param mixed
     * @return boolean
     */
    final public static function isValid($value)
    {
        if (empty(self::$availableValues[$class = get_called_class()])) {
            self::init($class);
        }

        return in_array($value, self::$availableValues[$class]);
    }

    /**
     * Get possible values.
     * @return mixed[]
     */
    final public static function getAllowedValues()
    {
        if (empty(self::$availableValues[$class = get_called_class()])) {
            self::init($class);
        }

        return self::$availableValues[$class];
    }

    /**
     * Get all values as Enum objects.
     * @return static[]
     */
    final public static function getInstances()
    {
        if (empty(self::$availableValues[$class = get_called_class()])) {
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

    /**
     * @param string
     */
    final private static function init($class)
    {
        $ref = new \ReflectionClass($class);
        self::$availableValues[$class] = $ref->getConstants();
        self::$instances[$class] = [];
    }

}
