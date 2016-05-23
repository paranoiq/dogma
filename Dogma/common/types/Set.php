<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Nette\Utils\ObjectMixin;


/**
 * Set type. Similar to 'set' from MySql. Allowed values are defined as class constants.
 */
abstract class Set
{

    /** @var mixed[] */
    private static $values = [];

    /** @var mixed[] */
    private $set = [];

    /**
     * @param self|string|string[]
     */
    final public function __construct($set = [])
    {
        $this->add($set);
    }

    /**
     * @param self|string|string[]
     */
    public function add($set)
    {
        $this->checkSet($set);

        foreach ($set as $value) {
            if ($value === '') {
                continue;
            }

            if (!self::isValid($value)) {
                throw new \InvalidArgumentException('Invalid value given.');
            }

            if (!in_array($value, $this->set)) {
                $this->set[] = $value;
            }
        }
    }

    /**
     * @param self|string|string[]
     */
    public function remove($set)
    {
        $this->checkSet($set);

        foreach ($set as $value) {
            $key = array_search($value, $this->set);
            if ($key !== false) {
                unset($this->set[$key]);
            }
        }
    }

    /**
     * @param self|string|string[]
     * @return bool
     */
    public function contains($set): bool
    {
        $this->checkSet($set);

        foreach ($set as $value) {
            if (!in_array($value, $this->set)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param self|string|string[]
     */
    private function checkSet(&$set)
    {
        if (is_string($set)) {
            $set = explode(',', $set);

        } elseif ($set instanceof self) { // is_object($set) && get_class($set) === get_called_class() ?
            $set = $set->getValues();

        } elseif (!is_array($set)) {
            throw new \InvalidArgumentException('Value must be a string, array or a Set.');
        }
    }

    final public function __toString(): string
    {
        return implode(',', $this->set);
    }

    /**
     * @return mixed[]
     */
    final public function getValues(): array
    {
        return $this->set;
    }

    /**
     * Set more values at once
     * @param bool[] (string $name => bool $value)
     */
    public function setValues(array $values)
    {
        foreach ($values as $name => $value) {
            $this->__set($name, $value);
        }
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param string|string[]
     * @return bool
     */
    final public static function isValid($value): bool
    {
        if (!isset(self::$values[$class = get_called_class()])) {
            self::init($class);
        }

        if (is_array($value)) {
            foreach ($value as $val) {
                if (!self::isValid($val)) {
                    return false;
                }
            }
            return true;
        }

        return in_array($value, self::$values[$class]);
    }

    public static function getAllowedValues(): array
    {
        if (!isset(self::$values[$class = get_called_class()])) {
            self::init($class);
        }

        return self::$values[$class];
    }

    final private static function init(string $class)
    {
        $ref = new \ReflectionClass($class);
        self::$values[$class] = $ref->getConstants();
    }

    // magic motherfucker ----------------------------------------------------------------------------------------------

    /**
     * @param string
     * @return mixed
     */
    final public function __get(string $name)
    {
        if (self::isValid($name)) {
            return $this->contains($name);
        }

        return ObjectMixin::get($this, $name);
    }

    /**
     * @param string
     * @return mixed
     */
    final public function __isset(string $name)
    {
        if (self::isValid($name)) {
            return $this->contains($name);
        }

        return ObjectMixin::has($this, $name);
    }

    /**
     * @param string
     * @param mixed
     */
    final public function __set(string $name, $value)
    {
        if (self::isValid($name)) {
            if (is_string($value)) {
                $norm = new Normalizer;
                $bool = $norm->detectBool($value);
                if (isset($bool)) {
                    $value = $bool;
                }
            }

            $value ? $this->add($name) : $this->remove($name);
            return;
        }

        ObjectMixin::set($this, $name, $value);
    }

    /**
     * @param string
     * @param mixed
     */
    final public function __unset(string $name)
    {
        if (self::isValid($name)) {
            $this->remove($name);
            return;
        }

        ObjectMixin::remove($this, $name);
    }

    final public function __sleep()
    {
        throw new \Exception('Set type cannot be serialized. Use its values instead.');
    }

    final public function __wakeup()
    {
        throw new \Exception('Set type cannot be serialized. Use its values instead.');
    }

}
